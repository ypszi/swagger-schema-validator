<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Rule;

use InvalidArgumentException;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\RegexpConstraint;

class RuleNormalizer
{
    private const RULE_SEPARATOR = '|';
    private const RULE_CONTEXT_SEPARATOR = ':';
    private const RULE_CONTEXT_VALUE_SEPARATOR = ',';

    public function getNormalizeRules(array $rulesByField): RuleCollection
    {
        $normalizedRules = new RuleCollection();

        foreach ($rulesByField as $field => $rules) {
            if (is_string($rules)) {
                $this->checkForInvalidRuleUsage($rules);

                $rules = explode(self::RULE_SEPARATOR, $rules);
            }

            foreach ($rules as $ruleWithContext) {
                $ruleName = $this->getRuleNameFromRule($ruleWithContext);
                $context = $this->getContextFromRule($ruleWithContext);

                $normalizedRules->add(new Rule($field, $ruleName, $context));
            }
        }

        return $normalizedRules;
    }

    private function checkForInvalidRuleUsage(string $rules): void
    {
        $regexpConstraintName = RegexpConstraint::name();

        if (strpos($rules, $regexpConstraintName) === 0
            || strpos($rules, self::RULE_SEPARATOR . $regexpConstraintName) !== false) {
            throw new InvalidArgumentException(
                sprintf('%s constraint cannot be used in string rules.', $regexpConstraintName)
            );
        }
    }

    private function getRuleNameFromRule(string $ruleWithContext): string
    {
        $ruleParts = explode(self::RULE_CONTEXT_SEPARATOR, $ruleWithContext);

        return array_shift($ruleParts);
    }

    private function getContextFromRule(string $ruleWithContext): array
    {
        $context = [];
        $ruleName = $this->getRuleNameFromRule($ruleWithContext);
        $contextSeparatorPosition = $this->getRuleContextSeparatorPosition($ruleWithContext);

        if ($contextSeparatorPosition !== null) {
            $contextString = substr($ruleWithContext, $contextSeparatorPosition + 1);

            if (!empty($contextString)) {
                if ($ruleName === RegexpConstraint::name()) {
                    $context = [$contextString];
                }
                else {
                    $context = explode(self::RULE_CONTEXT_VALUE_SEPARATOR, $contextString);
                }
            }
        }

        return $context;
    }

    private function getRuleContextSeparatorPosition(string $string): ?int
    {
        $ruleContextSeparatorPosition = strpos($string, self::RULE_CONTEXT_SEPARATOR);

        return $ruleContextSeparatorPosition !== false ? (int)$ruleContextSeparatorPosition : null;
    }
}
