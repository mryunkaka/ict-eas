# Module: Reports

Generated at: `2026-04-15 08:29:06`

Module key: `reports`

## Routes

| Method | URI | Name | Action |
|---|---|---|---|
| `GET|HEAD` | `reports` | `reports.index` | `App\Http\Controllers\ReportController@index` |
| `GET|HEAD` | `reports/excel` | `reports.export.excel` | `App\Http\Controllers\ReportController@exportExcel` |
| `GET|HEAD` | `reports/monitoring-pp` | `reports.monitoring-pp` | `App\Http\Controllers\ReportController@monitoringPp` |
| `GET|HEAD` | `reports/pdf` | `reports.export.pdf` | `App\Http\Controllers\ReportController@exportPdf` |

## Controllers

- `App\Http\Controllers\ReportController`

## Views

- `resources/views/index.blade.php`
- `resources/views/monitoring-pp.blade.php`
- `resources/views/pdf.blade.php`

## Tests (related)

- `tests/Feature/MonitoringPpReportTest.php`
- `tests/Feature/ReportExportTest.php`
