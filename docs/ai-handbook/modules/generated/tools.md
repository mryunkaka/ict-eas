# Module: Tools

Generated at: `2026-04-15 08:29:06`

Module key: `tools`

## Routes

| Method | URI | Name | Action |
|---|---|---|---|
| `GET|HEAD` | `tools/ping-server` | `tools.ping.index` | `App\Http\Controllers\Tools\PingServerController@index` |
| `POST` | `tools/ping-server` | `tools.ping.check` | `App\Http\Controllers\Tools\PingServerController@check` |
| `GET|HEAD` | `tools/users` | `tools.users.index` | `App\Http\Controllers\Tools\UserManagementController@index` |
| `POST` | `tools/users` | `tools.users.store` | `App\Http\Controllers\Tools\UserManagementController@store` |
| `PUT` | `tools/users/{user}` | `tools.users.update` | `App\Http\Controllers\Tools\UserManagementController@update` |

## Controllers

- `App\Http\Controllers\Tools\PingServerController`
- `App\Http\Controllers\Tools\UserManagementController`

## Views

- `resources/views/ping/index.blade.php`
- `resources/views/users/index.blade.php`

## Tests (related)

- `tests/Feature/AdminToolsTest.php`
