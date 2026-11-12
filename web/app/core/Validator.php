<?php
/**
 * ============================================================
 *  Validator
 *  Input validation helper used across all controllers.
 * ============================================================
 */

declare(strict_types=1);

class Validator
{
    protected array $errors = [];

    /**
     * Validate $data against a rules array.
     *
     * Rules format:
     *   'field' => 'required|min:3|max:50|email|numeric|date|in:a,b'
     *
     * @return bool true when valid
     */
    public function validate(array $data, array $rules): bool
    {
        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? null;
            $rules = array_map('trim', explode('|', $ruleString));

            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Apply a single rule to a field.
     */
    protected function applyRule(string $field, mixed $value, string $rule): void
    {
        // Parse parameterised rules e.g. min:3
        [$ruleName, $param] = array_pad(explode(':', $rule, 2), 2, null);

        switch ($ruleName) {
            case 'required':
                if ($value === null || $value === '') {
                    $this->errors[$field][] = 'The ' . $this->label($field) . ' field is required.';
                }
                break;

            case 'email':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = 'The ' . $this->label($field) . ' must be a valid email address.';
                }
                break;

            case 'numeric':
                if ($value !== null && $value !== '' && !is_numeric($value)) {
                    $this->errors[$field][] = 'The ' . $this->label($field) . ' must be a number.';
                }
                break;

            case 'integer':
                if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->errors[$field][] = 'The ' . $this->label($field) . ' must be an integer.';
                }
                break;

            case 'date':
                if ($value !== null && $value !== '' && !self::isValidDate((string) $value)) {
                    $this->errors[$field][] = 'The ' . $this->label($field) . ' must be a valid date.';
                }
                break;

            case 'min':
                $len = is_numeric($value) ? (float) $value : mb_strlen((string) $value);
                if ($value !== null && $value !== '' && $len < (int) $param) {
                    $this->errors[$field][] = 'The ' . $this->label($field) . ' must be at least ' . $param . '.';
                }
                break;

            case 'max':
                $len = is_numeric($value) ? (float) $value : mb_strlen((string) $value);
                if ($len > (int) $param) {
                    $this->errors[$field][] = 'The ' . $this->label($field) . ' must not exceed ' . $param . '.';
                }
                break;

            case 'in':
                $allowed = array_map('trim', explode(',', (string) $param));
                if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
                    $this->errors[$field][] = 'The selected ' . $this->label($field) . ' is invalid.';
                }
                break;

            case 'url':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->errors[$field][] = 'The ' . $this->label($field) . ' must be a valid URL.';
                }
                break;

            case 'alpha':
                if ($value !== null && $value !== '' && !preg_match('/^[a-zA-Z\s]+$/', (string) $value)) {
                    $this->errors[$field][] = 'The ' . $this->label($field) . ' may only contain letters.';
                }
                break;

            case 'phone':
                if ($value !== null && $value !== '' && !preg_match('/^[0-9+\-\s()]{7,20}$/', (string) $value)) {
                    $this->errors[$field][] = 'The ' . $this->label($field) . ' must be a valid phone number.';
                }
                break;
        }
    }

    /**
     * Human friendly field label.
     */
    protected function label(string $field): string
    {
        return str_replace('_', ' ', $field);
    }

    /**
     * Check whether a string is a valid Y-m-d date.
     */
    public static function isValidDate(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d !== false && $d->format('Y-m-d') === $date;
    }

    /**
     * Return all collected errors.
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Return errors flattened into a single string.
     */
    public function errorsFlat(): string
    {
        $messages = [];
        foreach ($this->errors as $fieldErrors) {
            $messages = array_merge($messages, $fieldErrors);
        }
        return implode('<br>', $messages);
    }
}
