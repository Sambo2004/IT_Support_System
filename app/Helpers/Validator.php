<?php
/**
 * Server-Side Validation Helper
 * 
 * Validates form input data against a set of rules. Stores validation
 * errors and old input in the session for redisplay after a failed
 * form submission.
 * 
 * Usage:
 *   $validator = new Validator($_POST, [
 *       'username' => 'required|min:3|max:50',
 *       'email'    => 'required|email',
 *       'role'     => 'required|in:admin,technician,employee,manager',
 *   ]);
 *   if (!$validator->validate()) {
 *       // Redirect back — errors are in session
 *   }
 * 
 * Supported rules:
 *   required, email, min:N, max:N, numeric, integer,
 *   in:val1,val2,..., date, alpha_num, confirmed
 */
class Validator
{
    /** @var array The input data to validate */
    private array $data;

    /** @var array The validation rules keyed by field name */
    private array $rules;

    /** @var array Validation errors keyed by field name */
    private array $errors = [];

    /**
     * Create a new Validator instance.
     *
     * @param array $data  The data to validate (typically $_POST)
     * @param array $rules The validation rules ['field' => 'rule1|rule2']
     */
    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    /**
     * Run validation against all rules.
     * If validation fails, old input is stored in session for repopulating forms.
     * Password fields are never stored in old input for security.
     *
     * @return bool True if all validation passes
     */
    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $ruleString) {
            // Split the pipe-delimited rules: "required|email|max:255"
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        if (!empty($this->errors)) {
            // Store old input in session so forms can repopulate
            // Exclude password fields for security
            $safeInput = array_filter($this->data, function ($key) {
                return !str_contains(strtolower($key), 'password');
            }, ARRAY_FILTER_USE_KEY);

            $_SESSION['old_input'] = $safeInput;
            $_SESSION['validation_errors'] = $this->errors;
            $_SESSION['errors'] = $this->errors;
            return false;
        }

        // Validation passed — clear any stale old input
        unset($_SESSION['old_input']);
        unset($_SESSION['validation_errors']);
        unset($_SESSION['errors']);
        return true;
    }

    /**
     * Apply a single rule to a field value.
     *
     * @param string      $field The field name
     * @param mixed       $value The field value
     * @param string      $rule  The rule to apply (e.g., 'min:3')
     */
    private function applyRule(string $field, $value, string $rule): void
    {
        // Parse rule and parameter (e.g., "min:3" → rule="min", param="3")
        $param = null;
        if (strpos($rule, ':') !== false) {
            [$rule, $param] = explode(':', $rule, 2);
        }

        // Create a human-readable label from the field name
        $label = ucfirst(str_replace('_', ' ', $field));

        switch ($rule) {
            case 'required':
                if ($value === null || trim((string)$value) === '') {
                    $this->addError($field, "{$label} is required.");
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "{$label} must be a valid email address.");
                }
                break;

            case 'min':
                // Minimum string length
                if (!empty($value) && mb_strlen((string)$value) < (int)$param) {
                    $this->addError($field, "{$label} must be at least {$param} characters.");
                }
                break;

            case 'max':
                // Maximum string length
                if (!empty($value) && mb_strlen((string)$value) > (int)$param) {
                    $this->addError($field, "{$label} must not exceed {$param} characters.");
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, "{$label} must be a number.");
                }
                break;

            case 'integer':
                if (!empty($value) && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, "{$label} must be an integer.");
                }
                break;

            case 'in':
                // Value must be one of the listed options
                $allowed = explode(',', $param);
                if (!empty($value) && !in_array($value, $allowed, true)) {
                    $this->addError($field, "{$label} must be one of: " . implode(', ', $allowed) . ".");
                }
                break;

            case 'date':
                if (!empty($value) && strtotime($value) === false) {
                    $this->addError($field, "{$label} must be a valid date.");
                }
                break;

            case 'alpha_num':
                if (!empty($value) && !ctype_alnum((string)$value)) {
                    $this->addError($field, "{$label} must contain only letters and numbers.");
                }
                break;

            case 'confirmed':
                // Checks that a matching "{field}_confirmation" field exists and matches
                $confirmField = $field . '_confirmation';
                $confirmValue = $this->data[$confirmField] ?? null;
                if ($value !== $confirmValue) {
                    $this->addError($field, "{$label} confirmation does not match.");
                }
                break;
        }
    }

    /**
     * Add an error message for a field.
     *
     * @param string $field   The field name
     * @param string $message The error message
     */
    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    /**
     * Get all validation errors.
     *
     * @return array Associative array of field => [error messages]
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get the first error message for a specific field.
     *
     * @param string $field The field name
     * @return string|null The first error message, or null if no errors
     */
    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }
}
