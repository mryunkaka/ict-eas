# Module: Forgot Password

Generated at: `2026-04-15 08:29:06`

Module key: `forgot-password`

## Routes

| Method | URI | Name | Action |
|---|---|---|---|
| `GET|HEAD` | `forgot-password` | `password.request` | `App\Http\Controllers\Auth\PasswordResetLinkController@create` |
| `POST` | `forgot-password` | `password.email` | `App\Http\Controllers\Auth\PasswordResetLinkController@store` |

## Controllers

- `App\Http\Controllers\Auth\PasswordResetLinkController`

## Tests (related)

- `tests/Feature/Auth/PasswordResetTest.php`
