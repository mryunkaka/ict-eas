# Module: ICT Requests

Halaman utama:

- Index: `GET /forms/ict-requests` → route `forms.ict-requests.index` → view `resources/views/forms/ict-requests/index.blade.php`
- Create: `GET /forms/ict-requests/create` → route `forms.ict-requests.create` → view `resources/views/forms/ict-requests/create.blade.php`
- Store: `POST /forms/ict-requests` → route `forms.ict-requests.store` → controller `app/Http/Controllers/Form/IctRequestController.php`

Generator PDF:

- `GET /forms/ict-requests/{ictRequest}/pdf` → `forms.ict-requests.pdf`
- Print (tracking original/copy): `POST /forms/ict-requests/{ictRequest}/print` → `forms.ict-requests.print`

## Status utama (state machine)

Sumber label status: `app/Models/IctRequest.php` (lihat `IctRequest::STATUS_LABELS`).

Flow normal (ringkas):

1. `drafted`
2. `ttd_in_progress`
3. `checked_by_asmen`
4. `progress_ppnk`
5. `progress_verifikasi_audit`
6. `progress_ppm`
7. `progress_po`
8. `progress_waiting_goods`
9. `completed` (tampil sebagai "Barang Sudah Diterima")

Catatan:

- Jika status `checked_by_asmen` tapi sudah pernah print (`print_count > 0`) dan belum upload PDF signed (`final_signed_pdf_path` null), label UI menjadi **Progress TTD**.

## Siapa yang bisa memproses apa (ringkas)

Role helper ada di `app/Models/User.php`:

- Unit user / requester: buat & lihat request unitnya
- Staff ICT: approve tahap `drafted` → `ttd_in_progress`
- Asmen ICT: approve tahap `ttd_in_progress` → `checked_by_asmen`
- Admin ICT: upload PDF signed, dan proses procurement (PPNK → audit → PPM → PO → penerimaan barang)

Logic approval stage (manual sign) ada di:

- `app/Http/Controllers/ApprovalController.php` (route `approvals.ict.update`)

## Procurement steps (file + route)

Semua step berikut umumnya hanya untuk Admin ICT dan ada guard status di controller:

1. Upload PDF TTD lengkap
   - Route: `POST /approvals/ict-requests/{ictRequest}` action `upload_signed_pdf`
   - Mengubah status: `checked_by_asmen` → `progress_ppnk`

2. PPNK/PPK per item
   - Route: `POST /forms/ict-requests/{ictRequest}/ppnk` → `forms.ict-requests.ppnk.store`
   - Mengubah status: `progress_ppnk` → `progress_verifikasi_audit`

3. Verifikasi audit
   - Route: `POST /forms/ict-requests/{ictRequest}/verify-audit` → `forms.ict-requests.verify-audit`
   - Mengubah status: `progress_verifikasi_audit` → `progress_ppm`

4. PPM
   - Route: `POST /forms/ict-requests/{ictRequest}/ppm` → `forms.ict-requests.ppm.store`
   - Mengubah status: `progress_ppm` → `progress_po`

5. PO
   - Route: `POST /forms/ict-requests/{ictRequest}/po` → `forms.ict-requests.po.store`
   - Mengubah status: `progress_po` → `progress_waiting_goods`

6. Penerimaan barang (Goods Receipt)
   - Route: `POST /forms/ict-requests/{ictRequest}/goods-receipt` → `forms.ict-requests.goods-receipt.store`
   - Guard status: wajib `progress_waiting_goods`
   - Mengubah status: `progress_waiting_goods` → `completed` (label: "Barang Sudah Diterima")
   - Side effects:
     - Membuat `assets` jika `handover_type=asset`
     - Membuat `asset_handovers`
     - Generate PDF Berita Acara di `storage/app/public/ict-handover-reports/*`

## UI: modal Penerimaan Barang

Lokasi: `resources/views/forms/ict-requests/index.blade.php`

Hal yang sering diubah:

- Text konfirmasi submit: cari `submitGoodsReceiptForm()` (confirm dialog).
- Hint teks status setelah submit: cari teks "Setelah submit, status berubah ..."
- Endpoint form submit: pakai `goods_receipt_url` dari data row.

## Test yang memvalidasi end-to-end

- `tests/Feature/IctRequestProcurementWorkflowTest.php` (drafted → approvals → signed PDF → PPNK → audit → PPM → PO → goods receipt → completed)

