<?php

namespace Xentixar\Validator;

use DateTime;

class Validator
{
    protected array $errors = [];
    protected array $messages = [];

    public function __construct()
    {
        $this->messages = $this->getMessages();
    }

    private function getMessages(): array
    {
        if (file_exists(__DIR__ . "/../../../../config/vendor/validator/messages.php")) {
            return require_once __DIR__ . "/../../../../config/vendor/validator/messages.php";
        } else {
            return require_once __DIR__ . "/../config/messages.php";
        }
    }

    public function validate(array $data, array $rules): bool
    {
        $data = $this->sanitize($data);

        foreach ($rules as $field => $ruleSet) {
            $rulesArray = explode('|', $ruleSet);

            foreach ($rulesArray as $rule) {
                $applicable = $this->applyRule($field, $rule, $data[$field] ?? null, $data);
                if (isset($this->errors[$field]) || $applicable === false) {
                    break;
                }
            }
        }

        return empty($this->errors);
    }

    protected function applyRule(string $field, string $rule, $value, array $data): ?false
    {
        $params = explode(':', $rule);
        $ruleName = $params[0];
        $param = $params[1] ?? null;

        if ($ruleName === 'nullable' && empty($value)) {
            return false;
        }

        $methods = [
            'required', 'email', 'min', 'max', 'between', 'numeric',
            'integer', 'url', 'date', 'confirmed', 'same', 'unique',
            'exists', 'in', 'regex', 'size', 'date_format', 'file', 'mimes'
        ];

        if (in_array($ruleName, $methods) && method_exists($this, $ruleName)) {
            $this->{$ruleName}($field, $value, $param, $data);
        }

        return null;
    }

    protected function addError(string $field, string $rule, $param = null, $extra = null): void
    {
        $message = $this->messages[$rule] ?? 'Validation error.';

        $message = str_replace(':field', $field, $message);
        $message = str_replace(':param', $param, $message);
        if ($extra) {
            $message = str_replace(':values', implode(', ', $extra), $message);
            $message = str_replace(':types', implode(', ', $extra), $message);
        }

        $this->errors[$field][] = $message;
    }

    protected function required(string $field, $value): void
    {
        if (empty($value) && $value !== '0') {
            $this->addError($field, 'required');
        }
    }

    protected function email(string $field, $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'email');
        }
    }

    protected function min(string $field, $value, $param): void
    {
        if (strlen($value ?? '') < $param) {
            $this->addError($field, 'min', $param);
        }
    }

    protected function max(string $field, $value, $param): void
    {
        if (strlen($value ?? '') > $param) {
            $this->addError($field, 'max', $param);
        }
    }

    protected function between(string $field, $value, $param): void
    {
        list($min, $max) = explode(',', $param);
        if (strlen($value) < $min || strlen($value) > $max) {
            $this->addError($field, 'between', null, [$min, $max]);
        }
    }

    protected function numeric(string $field, $value): void
    {
        if (!is_numeric($value)) {
            $this->addError($field, 'numeric');
        }
    }

    protected function integer(string $field, $value): void
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->addError($field, 'integer');
        }
    }

    protected function url(string $field, $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, 'url');
        }
    }

    protected function date(string $field, $value): void
    {
        $d = DateTime::createFromFormat('Y-m-d', $value);
        if (!$d || $d->format('Y-m-d') !== $value) {
            $this->addError($field, 'date');
        }
    }

    protected function confirmed(string $field, $value, $param, array $data): void
    {
        $confirmField = $field . '_confirmation';
        if ($value !== ($data[$confirmField] ?? null)) {
            $this->addError($field, 'confirmed');
        }
    }

    protected function same(string $field, $value, $param, array $data): void
    {
        if ($value !== ($data[$param] ?? null)) {
            $this->addError($field, 'same', $param);
        }
    }

    protected function unique(string $field, $value, $param): void
    {
        list($table, $column, $ignoreValue) = explode(',', $param) + [null, null, null];
        if ($this->isNotUnique($table, $column, $value, $ignoreValue)) {
            $this->addError($field, 'unique');
        }
    }

    protected function exists(string $field, $value, $param): void
    {
        list($table, $column) = explode(',', $param);
        if (!$this->existsInDatabase($table, $column, $value)) {
            $this->addError($field, 'exists');
        }
    }

    protected function in(string $field, $value, $param): void
    {
        $values = explode(',', $param);
        if (!in_array($value, $values)) {
            $this->addError($field, 'in', null, $values);
        }
    }

    protected function regex(string $field, $value, $param): void
    {
        if (!preg_match($param, $value)) {
            $this->addError($field, 'regex');
        }
    }

    protected function size(string $field, $value, $param): void
    {
        if (strlen($value) != $param) {
            $this->addError($field, 'size', $param);
        }
    }

    protected function date_format(string $field, $value, $param): void
    {
        $d = DateTime::createFromFormat($param, $value);
        if (!$d || $d->format($param) !== $value) {
            $this->addError($field, 'date_format', $param);
        }
    }

    protected function file(string $field, $value): void
    {
        if (!isset($value['tmp_name']) || !is_uploaded_file($value['tmp_name'])) {
            $this->addError($field, 'file');

        }
    }

    protected function mimes(string $field, $value, $param): void
    {
        $allowedMimes = explode(',', $param);
        if (isset($value['type']) && !in_array($value['type'], $allowedMimes)) {
            $this->addError($field, 'mimes', null, $allowedMimes);
        }
    }

    protected function isNotUnique(string $table, string $field, string $value, $ignore = null): bool
    {
        $sql = "SELECT COUNT(*) FROM $table WHERE $field = :value";
        if ($ignore !== null) {
            $sql .= " AND id != :ignore";
        }
        return $this->fetchCount($sql, $value, $ignore) > 0;
    }

    protected function existsInDatabase(string $table, string $field, string $value): bool
    {
        $sql = "SELECT COUNT(*) FROM $table WHERE $field = :value";
        return $this->fetchCount($sql, $value) > 0;
    }

    protected function fetchCount(string $sql, string $value, $ignore = null): int
    {
        $connection = Database::getInstance();
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':value', $value);
        if ($ignore !== null) {
            $stmt->bindParam(':ignore', $ignore);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    protected function sanitize(array $data): array
    {
        $sanitizedData = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitizedData[$key] = $this->sanitize($value);
            } else {
                $sanitizedData[$key] = $this->sanitizeValue($value);
            }
        }
        return $sanitizedData;
    }

    protected function sanitizeValue($value)
    {
        if (is_string($value)) {
            $value = trim(htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8'));
            $value = stripslashes($value);
        }
        return $value;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
