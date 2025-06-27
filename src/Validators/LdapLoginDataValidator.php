<?php

namespace WorkflowManager\Validators;

class LdapLoginDataValidator
{
    public static function validate(array $data): void
    {
        if (empty($data['employeeNo'])) {
            throw new \InvalidArgumentException('Missing required employee number');
        }
        if (empty($data['password'])) {
            throw new \InvalidArgumentException('Missing required password');
        }
    }

    public static function normalize(array $data): array
    {
        // Here you can add any normalization logic if needed
        return $data;
    }
}   