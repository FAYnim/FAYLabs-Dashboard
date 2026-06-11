<?php
// ============================================================
// Validator
// ============================================================

class Validator
{
    private array $errors = [];
    private array $data   = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    private function get(string $field): mixed
    {
        return $this->data[$field] ?? null;
    }

    public function required(string $field, string $label = ''): static
    {
        $label = $label ?: ucfirst($field);
        $value = $this->get($field);
        if ($value === null || $value === '') {
            $this->errors[$field] = "{$label} is required.";
        }
        return $this;
    }

    public function minLength(string $field, int $min, string $label = ''): static
    {
        $label = $label ?: ucfirst($field);
        $value = $this->get($field);
        if ($value !== null && mb_strlen((string) $value) < $min) {
            $this->errors[$field] = "{$label} must be at least {$min} characters.";
        }
        return $this;
    }

    public function maxLength(string $field, int $max, string $label = ''): static
    {
        $label = $label ?: ucfirst($field);
        $value = $this->get($field);
        if ($value !== null && mb_strlen((string) $value) > $max) {
            $this->errors[$field] = "{$label} must not exceed {$max} characters.";
        }
        return $this;
    }

    public function slug(string $field, string $label = ''): static
    {
        $label = $label ?: ucfirst($field);
        $value = $this->get($field);
        if ($value !== null && !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', (string) $value)) {
            $this->errors[$field] = "{$label} must be lowercase letters, numbers, and hyphens only.";
        }
        return $this;
    }

    public function url(string $field, string $label = ''): static
    {
        $label = $label ?: ucfirst($field);
        $value = $this->get($field);
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$field] = "{$label} must be a valid URL.";
        }
        return $this;
    }

    public function inList(string $field, array $allowed, string $label = ''): static
    {
        $label = $label ?: ucfirst($field);
        $value = $this->get($field);
        if ($value !== null && !in_array($value, $allowed, true)) {
            $this->errors[$field] = "{$label} has an invalid value.";
        }
        return $this;
    }

    public function between(string $field, int $min, int $max, string $label = ''): static
    {
        $label = $label ?: ucfirst($field);
        $value = (int) $this->get($field);
        if ($value < $min || $value > $max) {
            $this->errors[$field] = "{$label} must be between {$min} and {$max}.";
        }
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): string
    {
        return reset($this->errors) ?: '';
    }
}
