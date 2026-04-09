# Database Schema

## Relasi Inti
- `units` 1..n `users`
- `units` 1..n `assets`
- `assets` 1..n `asset_lifecycle_logs`
- `units` 1..n `ict_requests`
- `ict_requests` 1..n `ict_request_items`
- `units` 1..n `email_requests`
- `units` 1..n `repair_requests`
- `units` 1..n `incident_reports`
- `incident_reports` 1..n `cctv_maintenance_logs`
- `units` 1..n `project_requests`
- `units` 1..n `inventory_items`

## Tabel Ringkas
- `units`: code, name, type, active flag
- `users`: unit, role, employee metadata, activation flag
- `assets`: uuid, asset number, serial, vendor, location, condition, lifecycle
- `ict_requests`: subject, category, priority, justification, approval columns
- `ict_request_items`: nama barang, merk/tipe, qty, estimasi harga
- `email_requests`: requested email, access level, approval chain
- `repair_requests`: asset optional, problem type, summary, priority, assignee
- `incident_reports`: incident type, description, follow up, repairable, occurred at
- `inventory_items`: code, scope, quantity, minimum quantity
- `project_requests`: title, background, scope, expected outcome, target date
- `asset_lifecycle_logs`: action, from unit, to unit, previous status, next status, processed at
- `cctv_maintenance_logs`: activity, description, status after, handled by, performed at
