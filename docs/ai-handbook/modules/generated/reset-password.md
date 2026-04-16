# Module: Reset Password

Generated at: `2026-04-15 08:29:06`

Module key: `reset-password`

## Routes

| Method | URI | Name | Action |
|---|---|---|---|
| `POST` | `reset-password` | `password.store` | `App\Http\Controllers\Auth\NewPasswordController@store` |
| `GET|HEAD` | `reset-password/{token}` | `password.reset` | `App\Http\Controllers\Auth\NewPasswordController@create` |

## Controllers

- `App\Http\Controllers\Auth\NewPasswordController`

## Tests (related)

- `tests/Feature/Auth/PasswordResetTest.php`
