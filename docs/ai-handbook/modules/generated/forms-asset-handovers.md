# Module: Forms / Asset Handovers

Generated at: `2026-04-15 08:29:06`

Module key: `forms/asset-handovers`

## Routes

| Method | URI | Name | Action |
|---|---|---|---|
| `GET|HEAD` | `forms/asset-handovers` | `forms.asset-handovers.index` | `App\Http\Controllers\Form\AssetHandoverController@index` |
| `POST` | `forms/asset-handovers` | `forms.asset-handovers.store` | `App\Http\Controllers\Form\AssetHandoverController@store` |
| `GET|HEAD` | `forms/asset-handovers/create` | `forms.asset-handovers.create` | `App\Http\Controllers\Form\AssetHandoverController@create` |
| `GET|HEAD` | `forms/asset-handovers/{assetHandover}/pdf` | `forms.asset-handovers.pdf` | `App\Http\Controllers\Form\AssetHandoverController@pdf` |

## Controllers

- `App\Http\Controllers\Form\AssetHandoverController`

## Views

- `resources/views/create.blade.php`
- `resources/views/index.blade.php`

## Tests (related)

- `tests/Feature/AssetHandoverFormTest.php`
