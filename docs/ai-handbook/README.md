# AI Handbook (ict-eas)

Tujuan dokumen ini: mempercepat model AI (dan manusia) menemukan lokasi file yang perlu diedit saat **update fitur / tambah fitur / ganti halaman / tambah halaman**, tanpa perlu banyak pencarian via shell.

## Cara pakai (paling cepat)

1. Buka `docs/ai-handbook/modules/` untuk penjelasan fitur (alur, status, file yang terlibat).
2. Buka `docs/ai-handbook/generated/ROUTES.md` untuk mapping URL/route → controller action.
3. Buka `docs/ai-handbook/generated/VIEWS.md` untuk daftar halaman Blade.

## Update otomatis (disarankan)

Repo ini menyediakan generator markdown:

- Jalankan: `php artisan docs:generate`
- Output: `docs/ai-handbook/generated/*.md`

Praktik yang disarankan:

- Setelah menambah route/halaman/fitur, jalankan `php artisan docs:generate` lalu commit perubahan `docs/ai-handbook/generated/`.

## Index

- Struktur repo: `docs/ai-handbook/generated/STRUCTURE.md`
- Routes: `docs/ai-handbook/generated/ROUTES.md`
- Views (Blade): `docs/ai-handbook/generated/VIEWS.md`
- Models: `docs/ai-handbook/generated/MODELS.md`
- Migrations: `docs/ai-handbook/generated/MIGRATIONS.md`
- Tests: `docs/ai-handbook/generated/TESTS.md`
- Status label ICT Request: `docs/ai-handbook/generated/ICT_REQUEST_STATUSES.md`
- Modules (auto): `docs/ai-handbook/modules/generated/README.md`

## Modul (manual docs)

- ICT Requests: `docs/ai-handbook/modules/ict-requests.md`
- Approvals: `docs/ai-handbook/modules/approvals.md`
