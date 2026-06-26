<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        // 1. Añadimos las reglas de validación para el DNI y Teléfono
        Validator::make($input, [
            'name'     => ['required', 'string', 'max:255'],
            'dni'      => ['required', 'string', 'max:20', 'unique:users,dni'], // El DNI debe ser único
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:15'],
            'password' => $this->passwordRules(),
            'terms'    => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : [],
        ])->validate();

        // 2. Insertamos los nuevos valores en la base de datos
        return User::create([
            'name'     => $input['name'],
            'dni'      => $input['dni'],   // 🆕 Mapeado aquí
            'email'    => $input['email'],
            'phone'    => $input['phone'], // 🆕 Mapeado aquí
            'password' => Hash::make($input['password']),
            'role_id'  => 1, // <--- Por defecto los que se registren por la web serán Administradores (o el rol que prefieras)
        ]);
    }
}