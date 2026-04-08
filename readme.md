# PC & AI Agent E-Commerce Store

Platform e-commerce untuk penjualan komponen PC dan PC Build yang dilengkapi dengan AI Agent yang dapat memberikan rekomendasi rakitan PC berdasarkan budget dan kebutuhan customer.

## Konsep Utama

**Tiga Fitur Produk Utama:**
1. **PC Parts** - Komponen individual (Processor, GPU, RAM, Storage, Motherboard, PSU, Case, dll)
2. **PC Builds** - Paket rakitan PC siap beli yang menggabungkan beberapa PC Parts
3. **AI Recommendation Engine** - Asisten AI yang membantu customer menemukan PC yang sesuai dengan budget & kebutuhan

**Aktor Sistem:**
- **Guest** - Pengunjung yang belum login, bisa browsing dan chat dengan AI tanpa login
- **Customer** - Pengguna terdaftar dengan fitur lengkap checkout, pembayaran QRIS, tracking order
- **Admin** - Pengelola toko dengan akses manajemen inventory, order, dan status pengiriman
- **AI Agent** - Sistem asisten yang membaca data real-time untuk memberikan rekomendasi akurat

## Teknologi Stack

- **Frontend**: HTML + CSS + JavaScript (index.php)
- **Backend REST API**: PHP (api.php)
- **Database**: MySQL (`database.sql`)
- **AI Engine**: Ollama lokal (`http://127.0.0.1:11434`)
- **Payment Integration**: QRIS Code Generator

## User Stories & Fitur

### 1️⃣ Guest (Pengunjung)
- **US-G1**: Melihat katalog semua PC Parts dan PC Builds
- **US-G2**: Search dan filter produk berdasarkan kategori, harga, spesifikasi
- **US-G3**: Chat dengan AI Assistant untuk rekomendasi PC (tanpa login)
- **US-G4**: Redirect otomatis ke Login saat klik "Beli"

### 2️⃣ Customer (Pengguna Terdaftar)
- **US-C1**: Login & Register dengan email
- **US-C2**: Tambah/ubah/hapus item ke keranjang
- **US-C3**: Validasi otomatis stok - tidak boleh melebihi stok tersedia
- **US-C4**: Checkout dan terima QRIS Code untuk pembayaran
- **US-C5**: Update status order jadi "Dibayar" setelah transfer QRIS
- **US-C6**: Lihat riwayat order dengan status: Menunggu Pembayaran → Dibayar → Diproses → Dikirim → Sampai → Selesai/Dibatalkan
- **US-C7**: Konfirmasi order jadi "Selesai" setelah barang aman

### 3️⃣ Admin (Pengelola)
- **US-A1**: Login admin ke dashboard
- **US-A2**: CRUD PC Parts & PC Builds + manage stok
- **US-A3**: Auto-label "stok habis" untuk stok = 0
- **US-A4**: Gabungkan PC Parts menjadi PC Build packages
- **US-A5**: Lihat daftar seluruh order customer + status
- **US-A6**: Update status order (Diproses → Dikirim + input resi → Sampai di Tujuan)
- **US-A7**: Lihat daftar customer terdaftar

### 4️⃣ AI Agent (Asisten Otomatis)
- **US-AI1**: Baca data stok, harga, spesifikasi dari DB → rekomendasi akurat & real-time
- **US-AI2**: Filter otomatis - jangan rekomendasikan produk "stok habis"

## Setup & Installation

### 1) Setup Laragon/XAMPP

Letakkan folder project di:
- **Laragon**: `C:\laragon\www\PABWA3-ECOMMERCE-GAME`
- **XAMPP**: `C:\xampp\htdocs\PABWA3-ECOMMERCE-GAME`

Jalankan Apache + MySQL.

### 2) Import Database

Import file `database.sql` ke MySQL melalui phpMyAdmin atau CLI:

```bash
mysql -u root -p < database.sql
```

Atau via phpMyAdmin:
1. Buka `http://localhost/phpmyadmin`
2. Klik **Import** → pilih `database.sql` → Execute

### 3) Konfigurasi Database

Edit `config.php` jika kredensial berbeda:

```php
define('DB_HOST', '127.0.0.1');    // Host MySQL
define('DB_PORT', '3306');         // Port MySQL
define('DB_NAME', 'pc_store_ai');  // Nama DB
define('DB_USER', 'root');         // User MySQL
define('DB_PASS', '');             // Password MySQL
```

### 4) Setup Ollama (AI Engine)

Install Ollama dan pull model:

```bash
ollama pull qwen:14b
```

Jalankan Ollama service (default berjalan di `http://127.0.0.1:11434`).

Edit endpoint di `config.php` jika berbeda:

```php
define('OLLAMA_URL', 'http://127.0.0.1:11434/api/generate');
define('OLLAMA_MODEL', 'qwen:14b');
define('OLLAMA_TIMEOUT', 300);
```

### 5) Run Aplikasi

Buka browser:

```
http://localhost/PABWA3-ECOMMERCE-GAME/index.php
```

## Database Schema

### Tabel Utama

| Tabel | Deskripsi |
|-------|-----------|
| `users` | User (id, name, email, password_hash, role, is_active) |
| `pc_parts` | Komponen PC individual |
| `pc_builds` | Paket rakitan PC siap jual |
| `pc_build_components` | Hubungan parts dalam build |
| `cart_items` | Keranjang belanja |
| `orders` | Order pembelian customer |
| `order_items` | Item detail per order |
| `ai_chat_history` | Riwayat chat dengan AI |

### Contoh Data Seed

Database sudah terisi dengan:
- **1 Admin** - Admin Store
- **2 Customer** - Budi Santoso, Siti Nurasia
- **7 PC Parts** - Processor, GPU, RAM, Storage, Motherboard, PSU, Case
- **3 PC Builds** - Gaming Pro 4K, Workstation Professional, Budget Gaming 1080p

## API Endpoints Utama

### Produk & Katalog
```bash
GET  /api.php?action=list_parts              # Daftar PC Parts
GET  /api.php?action=list_builds             # Daftar PC Builds
GET  /api.php?action=get_build&id={id}       # Detail build + komponen
GET  /api.php?action=search_parts&q={query}  # Search parts
```

### AI Recommendation
```bash
POST /api.php?action=ai_recommend            # Rekomendasi PC via AI
POST /api.php?action=ai_chat                 # Chat dengan AI Assistant
```

### Cart & Order
```bash
POST /api.php?action=add_to_cart             # Tambah ke keranjang
GET  /api.php?action=get_cart&user_id={id}   # Lihat cart
POST /api.php?action=checkout                # Checkout & generate QRIS
POST /api.php?action=clear_cart              # Kosongkan cart
```

### Order Management
```bash
GET  /api.php?action=list_orders&user_id={id}  # Daftar order customer
GET  /api.php?action=order_detail&id={id}      # Detail order
POST /api.php?action=update_order_status       # Update status (admin)
```

### Admin Operations
```bash
POST /api.php?action=admin_add_part            # Tambah PC Part
POST /api.php?action=admin_add_build           # Tambah PC Build
POST /api.php?action=admin_list_orders         # Lihat semua order
POST /api.php?action=admin_list_customers      # Lihat daftar customer
```

## User Seed Data

### Login Administrator
- **Email**: admin@pcstore.local
- **Password**: admin123
- **Role**: Admin

### Login Customer
- **Email**: budi@email.com / siti@email.com
- **Password**: pass123
- **Role**: Customer

## Fitur Pembayaran QRIS

Sistem otomatis:
1. Customer melakukan checkout
2. Sistem generate QRIS Code (simulasi)
3. Customer scan & transfer via e-wallet/m-banking
4. Status otomatis update ke "Dibayar" setelah verifikasi

## Alur Order Status

```
Menunggu Pembayaran 
    ↓ (customer bayar via QRIS)
Dibayar
    ↓ (admin ready & proses)
Diproses
    ↓ (admin input resi & kirim)
Dikirim
    ↓ (tracking update courier)
Sampai di Tujuan
    ↓ (customer verifikasi barang)
Selesai
    │
    └─→ Dibatalkan (jika ada masalah)
```

## File Structure

```
PABWA3-ECOMMERCE-GAME/
├── index.php          # Frontend UI
├── api.php            # REST API Backend
├── config.php         # Konfigurasi DB & Ollama
├── database.sql       # Schema & seed data
├── package.json       # Project info
└── readme.md          # Dokumentasi
```

## Development Notes

- Backend fully stateless (RESTful API)
- Frontend pure JavaScript (no frameworks)
- Database relations fully normalized
- AI responses cached untuk performa
- 404 errors untuk undefined endpoints

Selamat menggunakan PC & AI Agent E-Commerce Store! 🚀
- `POST /api.php?action=seller_add_game`
- `POST /api.php?action=seller_add_topup`
- `POST /api.php?action=seller_add_requirement`
- `GET /api.php?action=seller_requirements&seller_id={id}`
- `POST /api.php?action=seller_agent_command`
- `POST /api.php?action=seller_parse_prompt` (alias kompatibilitas)
- `GET /api.php?action=seller_objects&seller_id={id}`
- `POST /api.php?action=seller_delete_game`
- `POST /api.php?action=seller_delete_topup`
- `POST /api.php?action=seller_delete_requirement`

## Catatan

AgentAI-Assisten Penjual berjalan **strict via Ollama** (tanpa fallback lokal). Jika Ollama mati atau output AI tidak valid, perintah akan gagal dan tidak disimpan.

Troubleshooting cepat AgentAI-Assisten Penjual:
- Jika muncul `Ollama error`, cek service Ollama aktif dan model `qwen3:32b` tersedia.
- Jika muncul `Output AI tidak valid`, ulangi prompt dengan format lebih terstruktur, misalnya:
	- `Tambahkan game: nama: The Witcher 3, genre: Action RPG, platform: PC, harga: 449000, stok: 50, rating: 4.9`
	- `Tambah top up: nama game: Steam Wallet, paket: 60K, platform: PC, nominal: 60000, unit: IDR Wallet, harga: 59000, stok: 150`

Untuk database lama tanpa kolom `rating` pada tabel `games`, jalankan SQL berikut sekali:

```sql
ALTER TABLE games ADD COLUMN rating DECIMAL(2,1) NULL AFTER stock;
```

Untuk database lama agar fitur top up saldo PC aktif penuh, jalankan juga:

```sql
ALTER TABLE topup_products ADD COLUMN platform VARCHAR(80) NULL AFTER package_name;
ALTER TABLE topup_products ADD COLUMN value_amount INT NULL AFTER platform;
ALTER TABLE topup_products ADD COLUMN value_unit VARCHAR(60) NULL AFTER value_amount;
UPDATE topup_products SET platform = 'Mobile' WHERE platform IS NULL;
UPDATE topup_products SET value_amount = diamonds_amount WHERE value_amount IS NULL;
UPDATE topup_products SET value_unit = 'Diamonds' WHERE value_unit IS NULL;
```