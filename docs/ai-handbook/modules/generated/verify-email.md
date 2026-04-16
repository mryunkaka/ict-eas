# Module: Verify Email

Generated at: `2026-04-15 08:29:06`

Module key: `verify-email`

## Routes

| Method | URI | Name | Action |
|---|---|---|---|
| `GET|HEAD` | `verify-email` | `verification.notice` | `App\Http\Controllers\Auth\EmailVerificationPromptController` |
| `GET|HEAD` | `verify-email/{id}/{hash}` | `verification.verify` | `App\Http\Controllers\Auth\VerifyEmailController` |

## Tests (related)

- `tests/Feature/Auth/EmailVerificationTest.php`
