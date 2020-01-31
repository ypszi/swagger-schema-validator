<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Validator;

use Illuminate\Support\Arr;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ArrayConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ArrayOfConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ConstraintCollection;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ConstraintInterface;
use Ypszi\SwaggerSchemaValidator\Validator\Rule\Rule;
use Ypszi\SwaggerSchemaValidator\Validator\Rule\RuleNormalizer;

class Validator implements ValidatorInterface
{
    public const  ANY_KEY_WILDCARD = '*';
    private const MULTIDIMENSIONAL_SEPARATOR = '.';

    /** @var ConstraintCollection */
    private $constraintCollection;

    /** @var RuleNormalizer */
    private $ruleNormalizer;

    /** @var array */
    private $rules = [];

    /** @var array */
    private $errors = [];

    /** @var array */
    private $validatedData = [];

    /** @var array */
    private $normalizedData = [];

    public function __construct(
        ConstraintCollection $constraintCollection,
        RuleNormalizer $ruleNormalizer,
        array $rules = []
    ) {
        $this->constraintCollection = $constraintCollection;
        $this->ruleNormalizer = $ruleNormalizer;
        $this->rules = $rules;
    }

    /**
     * @param array $data
     *
     * @return ValidationResult
     *
     * @throws ValidationException
     */
    public function validate(array $data): ValidationResult
    {
        $this->validatedData = [];
        $this->errors = [];
        $this->normalizedData = $this->createNormalizedData($data);

        $normalizedRules = $this->ruleNormalizer->getNormalizeRules($this->getRules());

        foreach ($normalizedRules as $ruleKey => $rule) {
            $this->validateRule($rule, $data);
        }

        return new ValidationResult($this->validatedData, $this->errors);
    }

    protected function getRules(): array
    {
        return $this->rules;
    }

    protected function setRules(array $rules): self
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * 1. Flattens given $data
     * 2. Sets default values to missing keys based on $ruleKeys
     *
     * @param array $data
     *
     * @return array
     */
    private function createNormalizedData(array $data): array
    {
        $flattenedData = Arr::dot($data);
        $fields = array_keys($flattenedData);
        $ruleKeys = array_keys($this->getRules());
        $defaultValues = [];

        foreach ($ruleKeys as $ruleKey) {
            $exactFoundKeys = preg_grep($this->createExactRuleMatcherRegexp($ruleKey), $fields);

            if (empty($exactFoundKeys)) {
                $defaultValues += $this->createDefaultValuesForMissingData($ruleKey, $fields);
            }
        }

        return $flattenedData + $defaultValues;
    }

    private function createDefaultValuesForMissingData(string $ruleKey, array $fields): array
    {
        $defaultValues = [];
        $missingFields = $this->findMissingFieldsByRule($ruleKey, $fields);

        foreach ($missingFields as $missingField) {
            $missingFieldKey = $this->createMissingFieldKey($ruleKey, $missingField);

            if ($missingFieldKey !== null && !isset($defaultValues[$missingFieldKey])) {
                $defaultValues[$missingFieldKey] = null;
            }
        }

        return $defaultValues;
    }

    private function findMissingFieldsByRule(string $ruleKey, array $fields): array
    {
        $partialFoundFieldsForRule = preg_grep($this->createMultidimensionalPartialMatcherRegexp($ruleKey), $fields);

        if (empty($partialFoundFieldsForRule)) {
            return [$ruleKey];
        }

        $foundFieldsForRootRule = preg_grep(
            $this->createMultidimensionalPartialMatcherRegexp($this->getRootField($ruleKey)),
            $fields
        );

        if (count($partialFoundFieldsForRule) === count($foundFieldsForRootRule)) {
            return [];
        }

        $missingFields = [];
        $ruleKeyDepth = $this->getDepth($ruleKey);

        foreach ($foundFieldsForRootRule as $field) {
            $fieldDepth = $this->getDepth($field);

            if ($fieldDepth <= $ruleKeyDepth) {
                $missingFields[] = $field;
            }
        }

        return $missingFields;
    }

    private function createMissingFieldKey(string $ruleKey, string $missingField): ?string
    {
        $rulePartMatchingMissingField = $this->findRulePartMatchingMissingField($ruleKey, $missingField);

        if ($rulePartMatchingMissingField === null || $ruleKey === $rulePartMatchingMissingField) {
            return strtr($ruleKey, [self::ANY_KEY_WILDCARD => '0']);
        }

        $fieldPartMatchingRulePart = $this->getFieldPartMatchingRulePart($rulePartMatchingMissingField, $missingField);
        $missingRuleKeyPart = $this->getMissingRuleKeyPart($ruleKey, $fieldPartMatchingRulePart);

        return strtr($fieldPartMatchingRulePart, [self::ANY_KEY_WILDCARD => '0'])
            . self::MULTIDIMENSIONAL_SEPARATOR
            . strtr($missingRuleKeyPart, [self::ANY_KEY_WILDCARD => '0']);
    }

    private function getFieldPartMatchingRulePart(string $ruleKey, string $missingField): string
    {
        $ruleKeyDepth = $this->getDepth($ruleKey);
        $missingFieldParts = explode(self::MULTIDIMENSIONAL_SEPARATOR, $missingField);

        return implode(self::MULTIDIMENSIONAL_SEPARATOR, array_slice($missingFieldParts, 0, $ruleKeyDepth));
    }

    private function getMissingRuleKeyPart(string $ruleKey, string $missingField): string
    {
        $missingFieldKeyDepth = $this->getDepth($missingField);
        $ruleKeyParts = explode(self::MULTIDIMENSIONAL_SEPARATOR, $ruleKey);

        return implode(self::MULTIDIMENSIONAL_SEPARATOR, array_slice($ruleKeyParts, $missingFieldKeyDepth));
    }

    private function findRulePartMatchingMissingField(string $ruleKey, string $field): ?string
    {
        if (preg_match($this->createMultidimensionalMatcherRegexp($ruleKey), $field) === 1) {
            return $ruleKey;
        }

        $ruleKey = $this->removeLeafField($ruleKey);

        if ($ruleKey === null) {
            return null;
        }

        return $this->findRulePartMatchingMissingField($ruleKey, $field);
    }

    private function validateRule(Rule $rule, array $originalData): void
    {
        $ruleKey = $rule->getKey();
        $constraint = $this->constraintCollection->get($rule->getName());
        $originalFields = [$ruleKey];

        if ($this->containsAnyKeyWildcard($ruleKey)) {
            $originalFields = preg_grep(
                $this->createMultidimensionalPartialMatcherRegexp($ruleKey),
                array_keys($this->normalizedData)
            );
        }

        $validatedFields = [];

        foreach ($originalFields as $originalField) {
            $originalField = (string)$originalField;
            $ruleLevelField = $this->findSameDepthFieldAsRule($rule, $originalField);

            if (!isset($validatedFields[$ruleLevelField])) {
                $this->validateConstraint(
                    $constraint,
                    $rule,
                    $ruleLevelField,
                    $originalField,
                    $originalData
                );

                $validatedFields[$ruleLevelField] = true;
            }
        }
    }

    private function validateConstraint(
        ConstraintInterface $constraint,
        Rule $rule,
        string $ruleLevelField,
        string $originalField,
        array $originalData
    ): void {
        $value = Arr::get($originalData, $ruleLevelField);

        if ($constraint->validate($value, $rule->getContext(), $originalData + $this->normalizedData)) {
            $this->handleValidationSuccess($rule, $originalField, $value);

            return;
        }

        $this->handleValidationError($constraint, $rule, $ruleLevelField, $value);
    }

    private function handleValidationSuccess(Rule $rule, string $originalField, $value): void
    {
        if ($this->isRuleAppliedToEveryValue($rule)) {
            $this->setArrayValuesToValidatedData($originalField, $value);

            return;
        }

        if (!Arr::has($this->validatedData, $originalField)) {
            Arr::set($this->validatedData, $originalField, $value);
        }

        return;
    }

    private function handleValidationError(
        ConstraintInterface $constraint,
        Rule $rule,
        string $ruleLevelField,
        $value
    ): void {
        if ($this->isRuleAppliedToEveryValue($rule)) {
            $this->setArrayErrorValuesToValidatedData($constraint, $rule, $ruleLevelField);
        }
        else {
            Arr::set(
                $this->validatedData,
                $ruleLevelField,
                $this->isArrayRelatedConstraint($constraint) ? [] : null
            );
        }

        $this->addError($constraint, $ruleLevelField, $value, $rule->getContext());
    }

    private function findSameDepthFieldAsRule(Rule $rule, string $field): string
    {
        $ruleKey = $rule->getKey();

        if ($this->isRootField($field) || $ruleKey === $field) {
            return $field;
        }

        $ruleDepth = $this->getDepth($ruleKey);
        $fieldDepth = $this->getDepth($field);

        if ($fieldDepth === $ruleDepth) {
            return $field;
        }

        return $this->findSameDepthFieldAsRule($rule, $this->removeLeafField($field));
    }

    private function isArrayRelatedConstraint(ConstraintInterface $constraint): bool
    {
        return in_array($constraint::name(), [ArrayConstraint::name(), ArrayOfConstraint::name()]);
    }

    private function setArrayValuesToValidatedData(string $field, $value): void
    {
        if (Arr::has($this->validatedData, $field)) {
            return;
        }

        if ($this->isRootField($field)) {
            Arr::set($this->validatedData, $field, $value);

            return;
        }

        $parentField = $this->removeLeafField($field);

        if (Arr::has($this->validatedData, $parentField)) {
            if ($value !== null) {
                Arr::set($this->validatedData, $field, $value);
            }

            return;
        }

        if ($value === null) {
            Arr::set($this->validatedData, $parentField, []);

            return;
        }

        if (!is_array($value)) {
            $value = [$this->getLeafField($field) => $value];
        }

        $validatedValue = Arr::get($this->validatedData, $parentField) ?? [];

        Arr::set($this->validatedData, $parentField, array_merge($validatedValue, $value));
    }

    private function setArrayErrorValuesToValidatedData(
        ConstraintInterface $constraint,
        Rule $rule,
        string $field
    ): void {
        if (Arr::has($this->validatedData, $field)) {
            Arr::forget($this->validatedData, $field);

            return;
        }

        $parentField = $this->removeLeafField($field);

        if ($this->isArrayRelatedConstraint($constraint)) {
            $parentField = $this->findSameDepthFieldAsRule($rule, $field);
        }

        if (!Arr::has($this->validatedData, $parentField)) {
            Arr::set($this->validatedData, $parentField, []);
        }
    }

    private function addError(ConstraintInterface $constraint, string $field, $value, array $context): void
    {
        $this->errors[$field][] = $constraint->getMessage(
            $field,
            $value,
            $context
        );
    }

    private function isRuleAppliedToEveryValue(Rule $rule): bool
    {
        $ruleKey = $rule->getKey();

        return $ruleKey === self::ANY_KEY_WILDCARD
            || strpos($ruleKey, self::MULTIDIMENSIONAL_SEPARATOR . self::ANY_KEY_WILDCARD, -2) !== false;
    }

    private function createExactRuleMatcherRegexp(string $ruleKey): string
    {
        return '/^' . $this->createRuleMatcherRegexpBody($ruleKey, '\\' . self::ANY_KEY_WILDCARD) . '$/';
    }

    private function createMultidimensionalMatcherRegexp(string $ruleKey): string
    {
        return '/^' . $this->createRuleMatcherRegexpBody($ruleKey, '.+') . '$/';
    }

    private function createMultidimensionalPartialMatcherRegexp(string $ruleKey): string
    {
        return '/^' . $this->createRuleMatcherRegexpBody($ruleKey, '.+') . '(\..+)*$/';
    }

    private function createRuleMatcherRegexpBody(string $ruleKey, string $wildCardReplacement = null): string
    {
        if ($this->containsMultidimensionalSeparator($ruleKey)) {
            $regexpParts = [];

            foreach (explode(self::MULTIDIMENSIONAL_SEPARATOR, $ruleKey) as $ruleKeyPart) {
                $regexpParts[] = $this->createRegexpGroup($ruleKeyPart, $wildCardReplacement);
            }

            return implode('\\' . self::MULTIDIMENSIONAL_SEPARATOR, $regexpParts);
        }

        return $this->createRegexpGroup($ruleKey, $wildCardReplacement);
    }

    private function createRegexpGroup(string $field, string $wildCardReplacement = null): string
    {
        $regexpGroup = sprintf('(%s)', $field);

        if ($wildCardReplacement !== null && $this->containsAnyKeyWildcard($field)) {
            $regexpGroup = strtr($regexpGroup, [self::ANY_KEY_WILDCARD => $wildCardReplacement]);
        }

        return $regexpGroup;
    }

    private function containsMultidimensionalSeparator(string $string): bool
    {
        return strpos($string, self::MULTIDIMENSIONAL_SEPARATOR) !== false;
    }

    private function containsAnyKeyWildcard(string $string): bool
    {
        return strpos($string, self::ANY_KEY_WILDCARD) !== false;
    }

    private function getRootField(string $field)
    {
        $fieldParts = explode(self::MULTIDIMENSIONAL_SEPARATOR, $field);

        return array_shift($fieldParts);
    }

    private function isRootField(string $field): bool
    {
        return $this->getRootField($field) === $field;
    }

    private function removeLeafField(string $field): ?string
    {
        $fieldParts = explode(self::MULTIDIMENSIONAL_SEPARATOR, $field);

        array_pop($fieldParts);

        if (empty($fieldParts)) {
            return null;
        }

        return implode(self::MULTIDIMENSIONAL_SEPARATOR, $fieldParts);
    }

    private function getLeafField(string $field): ?string
    {
        $fieldParts = explode(self::MULTIDIMENSIONAL_SEPARATOR, $field);

        return array_pop($fieldParts);
    }

    private function getDepth(string $field): int
    {
        return count(explode(self::MULTIDIMENSIONAL_SEPARATOR, $field));
    }
}
