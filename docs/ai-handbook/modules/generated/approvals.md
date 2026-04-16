# Module: Approvals

Generated at: `2026-04-15 08:29:06`

Module key: `approvals`

## Routes

| Method | URI | Name | Action |
|---|---|---|---|
| `GET|HEAD` | `approvals` | `approvals.index` | `App\Http\Controllers\ApprovalController@index` |
| `POST` | `approvals/email-requests/{emailRequest}` | `approvals.email.update` | `App\Http\Controllers\ApprovalController@updateEmail` |
| `POST` | `approvals/ict-requests/{ictRequest}` | `approvals.ict.update` | `App\Http\Controllers\ApprovalController@updateIct` |

## Controllers

- `App\Http\Controllers\ApprovalController`

## Views

- `resources/views/index.blade.php`

## Tests (related)

- `tests/Feature/ApprovalWorkflowTest.php`
- `tests/Feature/IctRequestFormTest.php`
- `tests/Feature/IctRequestProcurementWorkflowTest.php`
