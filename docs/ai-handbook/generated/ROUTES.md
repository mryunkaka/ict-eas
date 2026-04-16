# Routes

Generated at: `2026-04-15 08:29:06`

This file is auto-generated. Use it to quickly map URLs/route-names to controllers.


## `forms/ict-requests`

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

## `forms/assets`

| Method | URI | Name | Action |
|---|---|---|---|
| `GET|HEAD` | `forms/assets` | `forms.assets.index` | `App\Http\Controllers\Form\AssetController@index` |
| `GET|HEAD` | `forms/assets/{asset}` | `forms.assets.show` | `App\Http\Controllers\Form\AssetController@show` |
| `POST` | `forms/assets/{asset}/lifecycle` | `forms.assets.lifecycle.update` | `App\Http\Controllers\Form\AssetController@updateLifecycle` |

## `approvals`

| Method | URI | Name | Action |
|---|---|---|---|
| `GET|HEAD` | `approvals` | `approvals.index` | `App\Http\Controllers\ApprovalController@index` |
| `POST` | `approvals/email-requests/{emailRequest}` | `approvals.email.update` | `App\Http\Controllers\ApprovalController@updateEmail` |
| `POST` | `approvals/ict-requests/{ictRequest}` | `approvals.ict.update` | `App\Http\Controllers\ApprovalController@updateIct` |

## `reports`

| Method | URI | Name | Action |
|---|---|---|---|
| `GET|HEAD` | `reports` | `reports.index` | `App\Http\Controllers\ReportController@index` |
| `GET|HEAD` | `reports/excel` | `reports.export.excel` | `App\Http\Controllers\ReportController@exportExcel` |
| `GET|HEAD` | `reports/monitoring-pp` | `reports.monitoring-pp` | `App\Http\Controllers\ReportController@monitoringPp` |
| `GET|HEAD` | `reports/pdf` | `reports.export.pdf` | `App\Http\Controllers\ReportController@exportPdf` |

## `dashboard`

| Method | URI | Name | Action |
|---|---|---|---|
| `GET|HEAD` | `dashboard` | `dashboard` | `App\Http\Controllers\DashboardController@index` |
| `GET|HEAD` | `dashboard/stats` | `dashboard.stats` | `App\Http\Controllers\DashboardController@stats` |

## `inventory`

| Method | URI | Name | Action |
|---|---|---|---|
| `GET|HEAD` | `inventory` | `inventory.index` | `App\Http\Controllers\Form\InventoryController@index` |

## `tools`

| Method | URI | Name | Action |
|---|---|---|---|
| `GET|HEAD` | `tools/ping-server` | `tools.ping.index` | `App\Http\Controllers\Tools\PingServerController@index` |
| `POST` | `tools/ping-server` | `tools.ping.check` | `App\Http\Controllers\Tools\PingServerController@check` |
| `GET|HEAD` | `tools/users` | `tools.users.index` | `App\Http\Controllers\Tools\UserManagementController@index` |
| `POST` | `tools/users` | `tools.users.store` | `App\Http\Controllers\Tools\UserManagementController@store` |
| `PUT` | `tools/users/{user}` | `tools.users.update` | `App\Http\Controllers\Tools\UserManagementController@update` |

## All routes (compact)

| Method | URI | Name | Action |
|---|---|---|---|
| `GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS` | `/` | `-` | `Illuminate\Routing\RedirectController` |
| `GET|HEAD` | `approvals` | `approvals.index` | `App\Http\Controllers\ApprovalController@index` |
| `POST` | `approvals/email-requests/{emailRequest}` | `approvals.email.update` | `App\Http\Controllers\ApprovalController@updateEmail` |
| `POST` | `approvals/ict-requests/{ictRequest}` | `approvals.ict.update` | `App\Http\Controllers\ApprovalController@updateIct` |
| `GET|HEAD` | `confirm-password` | `password.confirm` | `App\Http\Controllers\Auth\ConfirmablePasswordController@show` |
| `POST` | `confirm-password` | `-` | `App\Http\Controllers\Auth\ConfirmablePasswordController@store` |
| `GET|HEAD` | `dashboard` | `dashboard` | `App\Http\Controllers\DashboardController@index` |
| `GET|HEAD` | `dashboard/stats` | `dashboard.stats` | `App\Http\Controllers\DashboardController@stats` |
| `POST` | `email/verification-notification` | `verification.send` | `App\Http\Controllers\Auth\EmailVerificationNotificationController@store` |
| `GET|HEAD` | `forgot-password` | `password.request` | `App\Http\Controllers\Auth\PasswordResetLinkController@create` |
| `POST` | `forgot-password` | `password.email` | `App\Http\Controllers\Auth\PasswordResetLinkController@store` |
| `GET|HEAD` | `forms/asset-handovers` | `forms.asset-handovers.index` | `App\Http\Controllers\Form\AssetHandoverController@index` |
| `POST` | `forms/asset-handovers` | `forms.asset-handovers.store` | `App\Http\Controllers\Form\AssetHandoverController@store` |
| `GET|HEAD` | `forms/asset-handovers/create` | `forms.asset-handovers.create` | `App\Http\Controllers\Form\AssetHandoverController@create` |
| `GET|HEAD` | `forms/asset-handovers/{assetHandover}/pdf` | `forms.asset-handovers.pdf` | `App\Http\Controllers\Form\AssetHandoverController@pdf` |
| `GET|HEAD` | `forms/assets` | `forms.assets.index` | `App\Http\Controllers\Form\AssetController@index` |
| `GET|HEAD` | `forms/assets/{asset}` | `forms.assets.show` | `App\Http\Controllers\Form\AssetController@show` |
| `POST` | `forms/assets/{asset}/lifecycle` | `forms.assets.lifecycle.update` | `App\Http\Controllers\Form\AssetController@updateLifecycle` |
| `GET|HEAD` | `forms/email-requests` | `forms.email-requests.index` | `App\Http\Controllers\Form\EmailRequestController@index` |
| `POST` | `forms/email-requests` | `forms.email-requests.store` | `App\Http\Controllers\Form\EmailRequestController@store` |
| `GET|HEAD` | `forms/email-requests/create` | `forms.email-requests.create` | `App\Http\Controllers\Form\EmailRequestController@create` |
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
| `GET|HEAD` | `forms/incidents` | `forms.incidents.index` | `App\Http\Controllers\Form\IncidentReportController@index` |
| `POST` | `forms/incidents` | `forms.incidents.store` | `App\Http\Controllers\Form\IncidentReportController@store` |
| `GET|HEAD` | `forms/incidents/create` | `forms.incidents.create` | `App\Http\Controllers\Form\IncidentReportController@create` |
| `GET|HEAD` | `forms/incidents/{incident}` | `forms.incidents.show` | `App\Http\Controllers\Form\IncidentReportController@show` |
| `POST` | `forms/incidents/{incident}/maintenance` | `forms.incidents.maintenance.store` | `App\Http\Controllers\Form\IncidentReportController@storeMaintenance` |
| `GET|HEAD` | `forms/projects` | `forms.projects.index` | `App\Http\Controllers\Form\ProjectRequestController@index` |
| `POST` | `forms/projects` | `forms.projects.store` | `App\Http\Controllers\Form\ProjectRequestController@store` |
| `GET|HEAD` | `forms/projects/create` | `forms.projects.create` | `App\Http\Controllers\Form\ProjectRequestController@create` |
| `GET|HEAD` | `forms/repairs` | `forms.repairs.index` | `App\Http\Controllers\Form\RepairRequestController@index` |
| `POST` | `forms/repairs` | `forms.repairs.store` | `App\Http\Controllers\Form\RepairRequestController@store` |
| `GET|HEAD` | `forms/repairs/create` | `forms.repairs.create` | `App\Http\Controllers\Form\RepairRequestController@create` |
| `GET|HEAD` | `inventory` | `inventory.index` | `App\Http\Controllers\Form\InventoryController@index` |
| `GET|HEAD` | `login` | `login` | `App\Http\Controllers\Auth\AuthenticatedSessionController@create` |
| `POST` | `login` | `-` | `App\Http\Controllers\Auth\AuthenticatedSessionController@store` |
| `POST` | `logout` | `logout` | `App\Http\Controllers\Auth\AuthenticatedSessionController@destroy` |
| `PUT` | `password` | `password.update` | `App\Http\Controllers\Auth\PasswordController@update` |
| `GET|HEAD` | `profile` | `profile.edit` | `App\Http\Controllers\ProfileController@edit` |
| `PATCH` | `profile` | `profile.update` | `App\Http\Controllers\ProfileController@update` |
| `DELETE` | `profile` | `profile.destroy` | `App\Http\Controllers\ProfileController@destroy` |
| `GET|HEAD` | `register` | `register` | `App\Http\Controllers\Auth\RegisteredUserController@create` |
| `POST` | `register` | `-` | `App\Http\Controllers\Auth\RegisteredUserController@store` |
| `GET|HEAD` | `reports` | `reports.index` | `App\Http\Controllers\ReportController@index` |
| `GET|HEAD` | `reports/excel` | `reports.export.excel` | `App\Http\Controllers\ReportController@exportExcel` |
| `GET|HEAD` | `reports/monitoring-pp` | `reports.monitoring-pp` | `App\Http\Controllers\ReportController@monitoringPp` |
| `GET|HEAD` | `reports/pdf` | `reports.export.pdf` | `App\Http\Controllers\ReportController@exportPdf` |
| `POST` | `reset-password` | `password.store` | `App\Http\Controllers\Auth\NewPasswordController@store` |
| `GET|HEAD` | `reset-password/{token}` | `password.reset` | `App\Http\Controllers\Auth\NewPasswordController@create` |
| `GET|HEAD` | `storage/{path}` | `storage.local` | `Closure` |
| `PUT` | `storage/{path}` | `storage.local.upload` | `Closure` |
| `GET|HEAD` | `tools/ping-server` | `tools.ping.index` | `App\Http\Controllers\Tools\PingServerController@index` |
| `POST` | `tools/ping-server` | `tools.ping.check` | `App\Http\Controllers\Tools\PingServerController@check` |
| `GET|HEAD` | `tools/users` | `tools.users.index` | `App\Http\Controllers\Tools\UserManagementController@index` |
| `POST` | `tools/users` | `tools.users.store` | `App\Http\Controllers\Tools\UserManagementController@store` |
| `PUT` | `tools/users/{user}` | `tools.users.update` | `App\Http\Controllers\Tools\UserManagementController@update` |
| `GET|HEAD` | `up` | `-` | `Closure` |
| `GET|HEAD` | `verify-email` | `verification.notice` | `App\Http\Controllers\Auth\EmailVerificationPromptController` |
| `GET|HEAD` | `verify-email/{id}/{hash}` | `verification.verify` | `App\Http\Controllers\Auth\VerifyEmailController` |
