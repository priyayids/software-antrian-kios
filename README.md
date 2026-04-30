# Aplikasi Antrian

Sistem manajemen antrian berbasis web untuk mengelola antrian pengunjung pada perusahaan atau instansi. Aplikasi ini membantu mencapai pelayanan yang efektif dan efisien melalui tampilan nomor antrian, panggilan, monitor publik, dan panel administrasi.

**Author:** Priyayi Dermawan Suandi

## Features

| Halaman | Deskripsi |
|---------|-----------|
| **Nomor Antrian** | Pengunjung mengambil nomor antrian secara mandiri (dengan dukungan cetak ke printer thermal) |
| **Panggilan Antrian** | Petugas loket memanggil antrian dengan dashboard real-time |
| **Monitor Antrian** | Tampilan layar publik dengan video, running text, dan suara pengumuman otomatis (TTS) |
| **Setting Antrian** | Admin panel untuk konfigurasi instansi, loket, warna tema, dan logo |

## Tech Stack

- **Backend:** PHP 8.2, PDO (Prepared Statements), MVC Architecture
- **Frontend:** Bootstrap 5, jQuery, DataTables, SweetAlert2, Inter Font
- **Database:** MySQL 8.0
- **Real-time:** Ratchet WebSocket
- **Printing:** mike42/escpos-php (ESC/POS thermal printer)
- **Voice:** ResponsiveVoice.js (Indonesian TTS)

## Directory Structure

```
├── public/                  # Web root (DocumentRoot)
│   ├── index.php            # Front controller & router
│   ├── .htaccess            # URL rewriting
│   ├── assets/              # Static assets (CSS, JS, images, audio)
│   └── storage/uploads/     # Uploaded logos
├── src/                     # Application source code
│   ├── Controllers/         # Request handlers
│   ├── Models/              # Database layer
│   ├── Core/                # Router, Database, App
│   └── Helpers/             # Utility functions
├── templates/               # View templates
│   ├── layouts/             # Base layouts
│   └── pages/               # Page templates
├── pages/nomor/             # Printer module (cetak.php)
├── config/                  # Legacy config (kept for compatibility)
├── database/                # SQL schema & Docker init script
├── docker/                  # Docker configuration files
├── composer.json            # PHP dependencies
├── docker-compose.yml       # Docker orchestration
├── server.php               # WebSocket server
└── .env                     # Environment variables
```

## Flow

```
1. Visitor → /nomor          → Click "Ambil Nomor" → Queue number created → (Optional: thermal print)
2. Staff   → /panggilan      → Select loket → Click "Call" on queue → Status updated → Call pushed to monitor
3. Monitor → /monitor        → Receives calls via polling → Plays bell + TTS announcement → Shows current/next queue
4. Admin   → /setting        → Login → Configure institution, counters, theme colors, logo
```

## Installation (Docker)

### Prerequisites
- Docker & Docker Compose

### Steps

```bash
# 1. Clone the repository
git clone <your-repo-url>
cd <project-directory>

# 2. Copy environment file
cp .env.example .env
# Edit .env if needed (DB credentials, admin credentials)

# 3. Build and start containers
docker-compose up --build -d

# 4. Access the application
# Web:        http://localhost:8080
# WebSocket:  ws://localhost:18081
# Database:   localhost:3307 (external), db:3306 (internal)
```

### Start WebSocket Server

```bash
docker exec -it antrian_app php server.php
```

## Installation (Local)

### Prerequisites
- PHP 8.2+ with PDO, intl, mbstring extensions
- MySQL 8.0
- Composer

### Steps

```bash
# 1. Copy environment file
cp .env.example .env

# 2. Install dependencies
composer install

# 3. Create database and import schema
mysql -u root -p < database/aplikasi_antrian.sql

# 4. Start PHP built-in server
php -S localhost:8080 -t public

# 5. (Optional) Start WebSocket server in another terminal
php server.php
```

## Default Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | superadmin | superadmin@123 |

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `DB_HOST` | db | Database host |
| `DB_USER` | root | Database username |
| `DB_PASS` | root | Database password |
| `DB_NAME` | aplikasi_antrian | Database name |
| `WS_PORT` | 8081 | WebSocket port |
| `ADMIN_USERNAME` | superadmin | Admin username |
| `ADMIN_PASSWORD` | superadmin@123 | Admin password |

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | Home dashboard |
| GET | `/nomor` | Queue number page |
| POST | `/api/nomor/insert` | Create new queue number |
| GET | `/api/nomor/antrian` | Get latest queue number |
| GET | `/panggilan` | Counter dashboard |
| GET | `/api/panggilan/antrian` | Get all queues today |
| GET | `/api/panggilan/sekarang` | Get currently served queue |
| GET | `/api/panggilan/selanjutnya` | Get next queue |
| GET | `/api/panggilan/jumlah` | Get total queue count |
| GET | `/api/panggilan/sisa` | Get remaining queue count |
| POST | `/api/panggilan/create` | Create a call |
| POST | `/api/panggilan/update` | Mark queue as served |
| GET | `/monitor` | Monitor display |
| GET/POST | `/api/monitor/panggilan` | Get pending calls |
| POST | `/api/monitor/panggilan/delete` | Delete a call after announcement |
| GET | `/setting` | Admin settings |
| POST | `/setting/login` | Admin login |
| POST | `/setting/logout` | Admin logout |
| POST | `/api/setting/save` | Save settings |

## License

Copyright © 2026 Priyayi Dermawan Suandi. All rights reserved.

This project is for personal/educational use. Redistribution or commercial use without permission from the author is prohibited.
