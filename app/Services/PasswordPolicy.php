<?php

namespace App\Services;

class PasswordPolicy
{
    /** @return array<string, string> */
    public function validate(string $password, string $confirmation): array
    {
        $errors = [];

        if (strlen($password) < 10) {
            $errors['password_length'] = 'La contraseña debe tener al menos 10 caracteres.';
        }

        if (! preg_match('/[a-z]/', $password) || ! preg_match('/[A-Z]/', $password) || ! preg_match('/\d/', $password) || ! preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors['password_strength'] = 'Incluye mayúsculas, minúsculas, números y un símbolo.';
        }

        if ($password !== $confirmation) {
            $errors['password_confirm'] = 'Las contraseñas no coinciden.';
        }

        return $errors;
    }
}
