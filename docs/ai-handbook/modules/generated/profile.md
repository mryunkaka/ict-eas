# Module: Profile

Generated at: `2026-04-15 08:29:06`

Module key: `profile`

## Routes

| Method | URI | Name | Action |
|---|---|---|---|
| `GET|HEAD` | `profile` | `profile.edit` | `App\Http\Controllers\ProfileController@edit` |
| `PATCH` | `profile` | `profile.update` | `App\Http\Controllers\ProfileController@update` |
| `DELETE` | `profile` | `profile.destroy` | `App\Http\Controllers\ProfileController@destroy` |

## Controllers

- `App\Http\Controllers\ProfileController`

## Views

- `resources/views/edit.blade.php`
- `resources/views/partials/delete-user-form.blade.php`
- `resources/views/partials/update-password-form.blade.php`
- `resources/views/partials/update-profile-information-form.blade.php`

## Tests (related)

- `tests/Feature/Auth/PasswordUpdateTest.php`
- `tests/Feature/IctRequestFormTest.php`
- `tests/Feature/ProfileTest.php`
