<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        Validator::make(
            $input,
            [
                'name' => ['required', 'string', 'max:20'],
                'email' => [
                    'required',
                    'string',
                    'email',
                ],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'password_confirmation' => ['required'],
            ],
            [
                'name.required' => 'お名前を入力してください',
                'name.max' => 'お名前は20文字以内で入力してください',
                'email.required' => 'メールアドレスを入力してください',
                'email.email' => 'メールアドレスを正しく入力してください',
                'password.required' => 'パスワードを入力してください',
                'password.min' => 'パスワードは8文字以上で入力してください',
                'password_confirmation.required' => '確認用パスワードを入力してください',
                'password.confirmed' => 'パスワードと一致しません',
            ]
        )->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
