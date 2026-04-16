# Maintenance

Dokumen di `docs/ai-handbook/generated/` adalah **hasil generator**.

## Kapan perlu update

Jalankan generator setiap kali ada perubahan yang berdampak ke navigasi:

- tambah/ubah route
- tambah/ubah controller action
- tambah/ubah halaman Blade
- tambah model/migration/test baru
- perubahan status workflow (contoh: ICT request statuses)

## Cara update

Command utama:

- `php artisan docs:generate`

Atau via composer:

- `composer docs:generate`

## Opsional: git hook (manual)

Kalau ingin otomatis saat commit, kamu bisa membuat pre-commit hook yang menjalankan generator lalu menambahkan outputnya ke staging.

Contoh file template ada di:

- `scripts/git-hooks/pre-commit.example.sh`

Aktifkan (sekali saja) dengan:

- `git config core.hooksPath scripts/git-hooks`
- rename `pre-commit.example.sh` → `pre-commit`

Catatan: hook bersifat opsional dan tergantung environment Git yang dipakai.

