# Module: Forms / Assets

Generated at: `2026-04-15 08:29:06`

Module key: `forms/assets`

## Routes

| Method | URI | Name | Action |
|---|---|---|---|
| `GET|HEAD` | `forms/assets` | `forms.assets.index` | `App\Http\Controllers\Form\AssetController@index` |
| `GET|HEAD` | `forms/assets/{asset}` | `forms.assets.show` | `App\Http\Controllers\Form\AssetController@show` |
| `POST` | `forms/assets/{asset}/lifecycle` | `forms.assets.lifecycle.update` | `App\Http\Controllers\Form\AssetController@updateLifecycle` |

## Controllers

- `App\Http\Controllers\Form\AssetController`

## Views

- `resources/views/index.blade.php`
- `resources/views/show.blade.php`

## Tests (related)

- `tests/Feature/AdminToolsTest.php`
