# Module: Forms / Incidents

Generated at: `2026-04-15 08:29:06`

Module key: `forms/incidents`

## Routes

| Method | URI | Name | Action |
|---|---|---|---|
| `GET|HEAD` | `forms/incidents` | `forms.incidents.index` | `App\Http\Controllers\Form\IncidentReportController@index` |
| `POST` | `forms/incidents` | `forms.incidents.store` | `App\Http\Controllers\Form\IncidentReportController@store` |
| `GET|HEAD` | `forms/incidents/create` | `forms.incidents.create` | `App\Http\Controllers\Form\IncidentReportController@create` |
| `GET|HEAD` | `forms/incidents/{incident}` | `forms.incidents.show` | `App\Http\Controllers\Form\IncidentReportController@show` |
| `POST` | `forms/incidents/{incident}/maintenance` | `forms.incidents.maintenance.store` | `App\Http\Controllers\Form\IncidentReportController@storeMaintenance` |

## Controllers

- `App\Http\Controllers\Form\IncidentReportController`

## Tests (related)

- `tests/Feature/AdminToolsTest.php`
