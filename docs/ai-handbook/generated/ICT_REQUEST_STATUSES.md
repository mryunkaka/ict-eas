# ICT Request Status Labels

Generated at: `2026-04-15 08:29:06`

Source of truth: `App\\Models\\IctRequest::STATUS_LABELS`.

| Status | Label |
|---|---|
| `approved_by_manager` | Approved Manager ICT |
| `checked_by_asmen` | Validated Asmen ICT |
| `completed` | Barang Sudah Diterima |
| `drafted` | Draft Admin ICT |
| `needs_revision` | Perlu Revisi |
| `progress_po` | Progress PO |
| `progress_ppm` | Progress PPM |
| `progress_ppnk` | Progress PPNK |
| `progress_verifikasi_audit` | Progress Verifikasi Audit |
| `progress_waiting_goods` | Progress Menunggu Barang Diterima |
| `rejected` | Rejected |
| `ttd_in_progress` | Validated Staff ICT |

Notes:

- Special display label: if status is `checked_by_asmen` AND `print_count > 0` AND `final_signed_pdf_path` is empty, UI label becomes **Progress TTD**.
