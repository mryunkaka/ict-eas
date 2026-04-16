# Module: Approvals

Halaman:

- Index: `GET /approvals` → route `approvals.index` → view `resources/views/approvals/index.blade.php`

Action:

- ICT approvals: `POST /approvals/ict-requests/{ictRequest}` → route `approvals.ict.update` → `app/Http/Controllers/ApprovalController.php`
- Email approvals: `POST /approvals/email-requests/{emailRequest}` → route `approvals.email.update` → `app/Http/Controllers/ApprovalController.php`

## ICT approval (manual sign)

`ApprovalController::updateIct()` menangani:

- `action=approve`:
  - `drafted` → `ttd_in_progress` (oleh Staff ICT)
  - `ttd_in_progress` → `checked_by_asmen` (oleh Asmen ICT)
- `action=reject`: perlu `review_note` (set status `rejected`)
- `action=revise`: set status `needs_revision` + simpan catatan/attachment revisi
- `action=upload_signed_pdf`: hanya saat status `checked_by_asmen` dan role Admin ICT → set `progress_ppnk`

Rule role per status ada di:

- `app/Http/Controllers/ApprovalController.php` (lihat `canHandleIct()` dan `canUploadSignedPdf()`)

## Catatan UI

Button/aksi di halaman approvals tergantung status yang dianggap "pending" untuk user:

- `ApprovalController::pendingIctStatuses()`

