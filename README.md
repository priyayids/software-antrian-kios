# Aplikasi Antrian

Sistem manajemen antrian berbasis web untuk mengelola antrian pengunjung pada perusahaan atau instansi. Aplikasi ini membantu mencapai pelayanan yang efektif dan efisien melalui tampilan nomor antrian, panggilan, monitor publik, dan panel administrasi.

**Author:** Priyayi Dermawan Suandi

## Features

| Halaman | Deskripsi |
|---------|-----------|
| **Nomor Antrian** | Pengunjung mengambil nomor antrian secara mandiri (dengan dukungan cetak ke printer thermal) |
| **Panggilan Antrian** | Petugas loket memanggil antrian dengan dashboard real-time |
| **Monitor Antrian** | Tampilan layar publik dengan video YouTube, running text, dan suara pengumuman otomatis (TTS) |
| **Setting Antrian** | Admin panel untuk konfigurasi instansi, loket, warna tema, logo, dan reset harian |

### Keamanan

| Fitur | Keterangan |
|-------|------------|
| **Prepared Statements** | Semua query SQL menggunakan PDO prepared statements — tidak ada string interpolation langsung |
| **CSRF Protection** | Setiap form POST dilindungi token CSRF berbasis session |
| **Session Security** | Session dikelola terpusat dengan cookie `HttpOnly`, `SameSite=Lax`, regenerasi ID setelah login |
| **Rate Limiting** | Pembatasan 3 detik per pengambilan nomor antrian (berdasarkan IP) |
| **File Upload** | Nama file logo digenerate acak (`bin2hex(random_bytes(16))`) — tidak menggunakan nama asli |
| **CORS** | `Access-Control-Allow-Origin` dibatasi sesuai `APP_URL` (tidak wildcard) |
| **Error Handling** | Database `die()` diganti dengan exception + halaman error 500 yang graceful. 404/500 dengan template terpisah |

### Fitur Lainnya

| Fitur | Keterangan |
|-------|------------|
| **Soft-Delete** | Reset harian menggunakan soft-delete (`deleted = 1`) — data tetap tersimpan di database |
| **Auto-Reset Terjadwal** | Reset otomatis pada jam tertentu via konfigurasi `.env` (`RESET_TIME=HH:MM`). Jika perangkat mati saat jam reset, reset akan tetap dijalankan pada request pertama setelah menyala (marker file mencegah duplikasi dalam hari yang sama) |
| **Race Condition Protection** | Generate nomor antrian menggunakan transaksi database dengan `FOR UPDATE` lock — dua pengguna bersamaan tidak akan mendapat nomor yang sama |
| **Timezone** | Timezone `Asia/Jakarta` diterapkan global di front controller — tidak ada offset manual |

## Tech Stack

- **Backend:** PHP 8.2+, PDO (Prepared Statements), MVC Architecture
- **Frontend:** Bootstrap 5, jQuery, SweetAlert2, Inter Font
- **Database:** MySQL 8.0
- **Real-time:** Ratchet WebSocket
- **Printing:** mike42/escpos-php (ESC/POS thermal printer)
- **Voice:** ResponsiveVoice.js (Indonesian TTS)

## Directory Structure

```
├── public/                      # Web root (DocumentRoot)
│   ├── index.php                # Front controller, router, session init, global error handler
│   ├── .htaccess                # URL rewriting (Apache fallback)
│   ├── assets/                  # Static assets (CSS, JS, images, audio)
│   └── storage/uploads/         # Uploaded logos
├── src/
│   ├── Controllers/             # Request handlers
│   │   ├── HomeController.php
│   │   ├── NomorController.php  # Queue number + printer
│   │   ├── PanggilanController.php
│   │   ├── MonitorController.php
│   │   ├── SettingController.php # Admin settings + login/logout
│   │   └── ResetController.php  # Manual reset (auth + CSRF protected)
│   ├── Models/
│   │   ├── Queue.php            # Queue CRUD, soft-delete, reset, race-condition-safe create
│   │   ├── Panggilan.php        # Call CRUD, soft-delete, reset
│   │   └── Setting.php          # Institution config, loket list, colors
│   ├── Core/
│   │   ├── App.php              # App bootstrap, route registration, scheduled reset check
│   │   ├── Router.php           # Path matching + dispatch
│   │   └── Database.php         # PDO singleton, exception on failure
│   └── Helpers/
│       └── functions.php        # view(), jsonResponse(), sanitize(), getToday(), etc.
├── templates/
│   ├── layouts/
│   │   ├── main.php             # Default layout (CSS, JS, footer)
│   │   ├── monitor.php          # Monitor-specific layout
│   │   └── error.php            # 404 error page
│   ├── pages/
│   │   ├── home.php
│   │   ├── nomor/index.php
│   │   ├── panggilan/index.php
│   │   ├── monitor/index.php    # YouTube video, queue display, TTS, bell
│   │   └── setting/index.php    # Admin form, CSRF, reset button with SweetAlert
│   └── errors/
│       └── 500.php              # Graceful 500 error page
├── database/
│   ├── aplikasi_antrian.sql     # Full schema with seed data
│   ├── migration.sql            # Idempotent migration (utf8mb4, FK, soft-delete column)
│   └── docker-init.sh           # First-run DB initialization script
├── docker/
│   └── nginx/                   # Nginx Docker config
├── server.php                   # WebSocket server (Ratchet)
├── docker-compose.yml           # Docker orchestration (nginx, php, db)
├── composer.json
├── Dockerfile
└── .env
```

## Flow

```
1. Visitor → /nomor          → Click "Ambil Nomor" → Queue number created (transaction-safe) → (Optional: thermal print)
2. Staff   → /panggilan      → Select loket → Click "Call" on queue → Status updated → Call pushed to monitor
3. Monitor → /monitor        → Receives calls via polling → Plays bell + TTS announcement → Shows current/next queue
4. Admin   → /setting        → Login → Configure institution, counters, theme, logo, reset queue
5. Reset   → Scheduled       → At RESET_TIME (or first request after) → Soft-delete all records → Marker file prevents duplicate
```

## Installation (Docker)

### Prerequisites
- Docker & Docker Compose

### Steps

```bash
# 1. Clone and enter project
git clone <your-repo-url>
cd <project-directory>

# 2. Configure environment
cp .env.example .env
# Edit .env as needed (credentials, RESET_TIME, printer)

# 3. Build and start
docker-compose up --build -d

# 4. Access
# Web:        http://localhost
# WebSocket:  ws://localhost:8081
# Database:   localhost:3307 (external), db:3306 (internal)
```

### WebSocket Server (Automatic)

The WebSocket server is automatically started when Docker containers launch via Supervisor.

```bash
# Check status
docker exec antrian_app supervisorctl status

# View WebSocket logs
docker exec antrian_app tail -f /var/log/supervisor/websocket.out.log
```

## Installation (Local)

### Prerequisites
- PHP 8.2+ with PDO, intl, mbstring extensions
- MySQL 8.0
- Composer

### Steps

```bash
cp .env.example .env
composer install
mysql -u root -p < database/aplikasi_antrian.sql
php -S localhost:8080 -t public
# Optional: php server.php  (WebSocket in another terminal)
```

## Default Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | superadmin | superadmin@123 |

Ubah di `.env`:
```
ADMIN_USERNAME=superadmin
ADMIN_PASSWORD=superadmin@123
```

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `DB_HOST` | db | Database host |
| `DB_PORT` | 3306 | Database port |
| `DB_NAME` | aplikasi_antrian | Database name |
| `DB_USER` | root | Database username |
| `DB_PASS` | root | Database password |
| `APP_ENV` | development | Application environment |
| `APP_DEBUG` | true | Debug mode |
| `APP_URL` | http://localhost | Application URL (used for CORS) |
| `WS_PORT` | 8081 | WebSocket port |
| `ADMIN_USERNAME` | superadmin | Admin login username |
| `ADMIN_PASSWORD` | superadmin@123 | Admin login password |
| `RESET_TIME` | false | Auto-reset time (`HH:MM`) or `false` untuk manual |
| `PRINTER_HOST` | *(empty)* | Network printer IP (prioritas dibanding SMB) |
| `PRINTER_PORT` | *(empty)* | Network printer port (default: 9100) |
| `PRINTER_SHARE` | thermal | Windows SMB printer share name |
| `PRINTER_USER` | printeruser | SMB username |
| `PRINTER_PASSWORD` | asdasd | SMB password |
| `PRINTER_REQUIREMENT` | false | `true` = cetak wajib berhasil, `false` = cetak opsional |
| `ANDROID_KIOS_IP` | 192.168.1.20 | IP Android kios printer |
| `PRINTER_HOST_ANDROID` | 192.168.1.20 | Android kios printer host |
| `PRINTER_PORT_ANDROID` | 9100 | Android kios printer port |

## API Endpoints

### Web Pages

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/` | Home dashboard |
| GET | `/nomor` | Queue number page (self-service kiosk) |
| GET | `/panggilan` | Counter staff dashboard |
| GET | `/monitor` | Public monitor display (video + running text) |
| GET | `/setting` | Admin settings panel (login required) |

### Authentication

| Method | Route | Description |
|--------|-------|-------------|
| POST | `/setting/login` | Admin login (session-based, CSRF regeneration) |
| POST | `/setting/logout` | Admin logout |

### Queue Numbers

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/api/nomor/antrian` | Get next queue number (without creating) |
| POST | `/api/nomor/insert` | Create new queue number (rate-limited 3s, printer optional) |

### Queue Management

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/api/panggilan/antrian` | Get all queues for today |
| GET | `/api/panggilan/sekarang` | Get currently served queue |
| GET | `/api/panggilan/selanjutnya` | Get next queue to be served |
| GET | `/api/panggilan/jumlah` | Get total queue count today |
| GET | `/api/panggilan/sisa` | Get remaining unserved queue count |
| POST | `/api/panggilan/create` | Create a call (staff calls a queue) |
| POST | `/api/panggilan/update` | Mark queue as served |

### Monitor

| Method | Route | Description |
|--------|-------|-------------|
| GET/POST | `/api/monitor/panggilan` | Get pending calls (polled by monitor) |
| POST | `/api/monitor/panggilan/delete` | Delete call after announcement |

### Settings & Reset

| Method | Route | Description |
|--------|-------|-------------|
| POST | `/api/setting/save` | Save settings (auth + CSRF required) |
| POST | `/api/reset` | Reset all queues today (auth + CSRF required) |

## License

Copyright © 2026 Priyayi Dermawan Suandi. All rights reserved.

This project is for personal/educational use. Redistribution or commercial use without permission from the author is prohibited.
