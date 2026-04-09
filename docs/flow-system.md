# Flow System

```text
User Unit -> Isi Form -> Draft tersimpan localStorage -> Submit
Submit -> Validasi Request -> Simpan ke DB -> Masuk antrian approval/proses ICT
Approval -> ICT Process -> Update status -> Selesai / ditindaklanjuti
```

## Approval Email
```text
Pemohon -> Atasan langsung -> HRGA -> ICT -> Akun aktif
```

## Approval ICT Request
```text
Pemohon -> Unit Admin -> HRGA -> ICT Admin -> Request approved
```

## CCTV Down
```text
Operator timbang cek CCTV -> CCTV OFF/tidak merekam -> Proses timbang dihentikan
-> Buat Berita Acara -> Lapor ICT -> Perbaikan -> Timbang dilanjutkan setelah normal
```

## Disposal / Transfer Asset
```text
ICT review asset -> Tentukan transfer / redistribusi / disposal
-> Simpan lifecycle log -> Update unit / status asset
```

## Diagram Mermaid
```mermaid
flowchart LR
    A[User] --> B[Input Form]
    B --> C[Validasi]
    C --> D[Approval]
    D --> E[Proses ICT]
    E --> F[Selesai]
```
