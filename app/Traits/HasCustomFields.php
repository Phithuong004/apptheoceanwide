<?php
namespace App\Traits;

trait HasCustomFields
{
    public function setCustomField(string $key, mixed $value): void
    {
        $fields       = $this->custom_fields ?? [];
        $fields[$key] = $value;
        $this->update(['custom_fields' => $fields]);
    }

    public function getCustomField(string $key, mixed $default = null): mixed
    {
        return ($this->custom_fields ?? [])[$key] ?? $default;
    }

    public function removeCustomField(string $key): void
    {
        $fields = $this->custom_fields ?? [];
        unset($fields[$key]);
        $this->update(['custom_fields' => $fields]);
    }
}
