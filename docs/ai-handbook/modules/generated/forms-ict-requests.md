# Module: Forms / Ict Requests

Generated at: `2026-04-15 08:29:06`

Module key: `forms/ict-requests`

## Routes

| Method | URI | Name | Action |
|---|---|---|---|
| `GET|HEAD` | `forms/ict-requests` | `forms.ict-requests.index` | `App\Http\Controllers\Form\IctRequestController@index` |
| `POST` | `forms/ict-requests` | `forms.ict-requests.store` | `App\Http\Controllers\Form\IctRequestController@store` |
| `DELETE` | `forms/ict-requests/bulk-destroy` | `forms.ict-requests.bulk-destroy` | `App\Http\Controllers\Form\IctRequestController@bulkDestroy` |
| `GET|HEAD` | `forms/ict-requests/create` | `forms.ict-requests.create` | `App\Http\Controllers\Form\IctRequestController@create` |
| `GET|HEAD` | `forms/ict-requests/export` | `forms.ict-requests.export` | `App\Http\Controllers\Form\IctRequestController@export` |
| `POST` | `forms/ict-requests/{ictRequest}/goods-receipt` | `forms.ict-requests.goods-receipt.store` | `App\Http\Controllers\Form\IctRequestController@storeGoodsReceipt` |
| `GET|HEAD` | `forms/ict-requests/{ictRequest}/handover-report/{assetHandover}/pdf` | `forms.ict-requests.handover-report.pdf` | `App\Http\Controllers\Form\IctRequestController@handoverReportPdf` |
| `GET|HEAD` | `forms/ict-requests/{ictRequest}/pdf` | `forms.ict-requests.pdf` | `App\Http\Controllers\Form\IctRequestController@pdf` |
| `DELETE` | `forms/ict-requests/{ictRequest}/permanent` | `forms.ict-requests.permanent-destroy` | `App\Http\Controllers\Form\IctRequestController@permanentDestroy` |
| `POST` | `forms/ict-requests/{ictRequest}/po` | `forms.ict-requests.po.store` | `App\Http\Controllers\Form\IctRequestController@storePo` |
| `POST` | `forms/ict-requests/{ictRequest}/ppm` | `forms.ict-requests.ppm.store` | `App\Http\Controllers\Form\IctRequestController@storePpm` |
| `POST` | `forms/ict-requests/{ictRequest}/ppnk` | `forms.ict-requests.ppnk.store` | `App\Http\Controllers\Form\IctRequestController@storePpnk` |
| `POST` | `forms/ict-requests/{ictRequest}/print` | `forms.ict-requests.print` | `App\Http\Controllers\Form\IctRequestController@print` |
| `POST` | `forms/ict-requests/{ictRequest}/verify-audit` | `forms.ict-requests.verify-audit` | `App\Http\Controllers\Form\IctRequestController@verifyAuditPpnk` |
| `PUT|PATCH` | `forms/ict-requests/{ict_request}` | `forms.ict-requests.update` | `App\Http\Controllers\Form\IctRequestController@update` |
| `GET|HEAD` | `forms/ict-requests/{ict_request}/edit` | `forms.ict-requests.edit` | `App\Http\Controllers\Form\IctRequestController@edit` |

## Controllers

- `App\Http\Controllers\Form\IctRequestController`

## Views

- `resources/views/create.blade.php`
- `resources/views/handover-report.blade.php`
- `resources/views/index.blade.php`
- `resources/views/pdf.blade.php`

## Tests (related)

- `tests/Feature/ApprovalWorkflowTest.php`
- `tests/Feature/GuestAccessTest.php`
- `tests/Feature/IctRequestFormTest.php`
- `tests/Feature/IctRequestProcurementWorkflowTest.php`
