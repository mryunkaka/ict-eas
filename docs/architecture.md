# Arsitektur ICT EAS

## Stack
- Laravel 13
- Tailwind CSS v4 via Vite
- Alpine.js untuk interaksi ringan
- `barryvdh/laravel-dompdf`
- `maatwebsite/excel`

## Prinsip
- MVC Laravel penuh
- UI dibangun lewat komponen Blade, bukan styling acak di halaman
- Data transactional dipisah per domain: request ICT, email, repair, incident, asset, inventory, project
- Scoping unit diterapkan di query layer melalui `App\Support\UnitScope`
- Pagination dan eager loading dipakai pada list besar

## Struktur Modul
- `app/Models`: entitas domain
- `app/Http/Controllers/Form`: controller modul form
- `app/Http/Controllers/Tools`: tool admin dan utilitas
- `app/Http/Requests`: validasi per form
- `resources/views/components`: design system Blade
- `resources/views/forms`: halaman modul
- `resources/views/approvals`: approval center
- `resources/views/reports`: report dan export template
- `resources/views/tools`: user management dan ping server
- `docs`: dokumentasi, todo, checklist, status
- `storage/app/sop-text`: hasil ekstraksi teks SOP sumber

## Multi Unit
- `users.role` menentukan otorisasi global vs unit
- `users.unit_id` menentukan unit kerja user
- user biasa hanya membaca data unit sendiri
- `super_admin` melihat seluruh unit
- inventory dapat dilihat per scope `eas`, `unit`, atau `all`

## SOP Mapping
- SOP Email: approval atasan, verifikasi HRGA, proses ICT, pilihan akses internal/external
- SOP CCTV: incident type `cctv_outage`, BA wajib saat recorder timbang down
- SOP Standard Software/Hardware: asset menyimpan serial, spesifikasi, user, unit, lifecycle
- SOP Disposal: lifecycle asset disiapkan untuk redistribusi, transfer, disposal
- FMR-ICT: struktur form mengikuti permintaan fasilitas, perbaikan, berita acara, email

## Workflow Tambahan
- Approval center memakai role `unit_admin`, `hrga_approver`, `ict_admin`, `super_admin`
- Report module mendukung filter per modul dan export `xlsx` / `pdf`
- Asset lifecycle log menyimpan transfer, redistribusi, dan disposal
- CCTV maintenance log menyimpan aktivitas perbaikan sampai status `resolved`
