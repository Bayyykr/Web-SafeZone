# Panduan Lengkap Deploy GeoCrime-Web ke Railway 🚀

Panduan ini memandu Anda langkah demi langkah untuk men-deploy aplikasi Laravel **GeoCrime-Web** ke platform cloud **Railway** (https://railway.com) menggunakan **FrankenPHP Dockerfile** produksi.

---

## 📋 Fitur Deployment yang Sudah Disiapkan

1. **FrankenPHP & Caddy Server**: Server web modern berkecepatan tinggi tanpa perlu repot konfigurasi Nginx dan PHP-FPM terpisah.
2. **Multi-Stage Docker Build**: Otomatis mengompilasi aset Vite (`npm run build`) dan aset CSS/JS siap pakai.
3. **Migrasi Otomatis**: Database migrations dapat berjalan otomatis setiap kali container menyala (`RUN_MIGRATIONS=true`).
4. **Storage Link Otomatis**: Folder storage publik otomatis di-link saat booting.
5. **Dukungan Otomatis Database Railway**: Langsung membaca variabel `MYSQLHOST`, `MYSQLPORT`, `PGHOST`, dll. yang disediakan Railway.
6. **Force HTTPS**: Menghilangkan error *Mixed Content* pada aset Leaflet, Chart, dan stylesheet.

---

## 🛠️ Langkah-Langkah Deployment

### Langkah 1: Push Perubahan ke GitHub

Pastikan seluruh file konfigurasi baru (`Dockerfile`, `docker/`, `config/database.php`, dll.) sudah di-commit dan di-push ke repository GitHub Anda:

```bash
git add .
git commit -m "feat: setup railway deployment with frankenphp and docker"
git push origin main
```
*(Ganti `main` dengan branch aktif Anda jika menggunakan branch lain)*

---

### Langkah 2: Buat Project Baru di Railway

1. Buka [https://railway.com](https://railway.com) dan login (disarankan menggunakan akun GitHub Anda).
2. Di Dashboard Railway, klik tombol **"+ New Project"**.
3. Pilih **"Deploy from GitHub repo"**.
4. Pilih repository **GeoCrime-web** (atau `Web-SafeZone`).
5. Railway akan otomatis mendeteksi `Dockerfile` yang telah kita siapkan.

---

### Langkah 3: Tambahkan Database MySQL di Railway

GeoCrime-Web membutuhkan database relasional (MySQL / PostgreSQL). Cara termudah di Railway:

1. Di dalam canvas project Anda di Railway, klik tombol **"+ Create"** atau **"+ New"** di pojok kanan atas.
2. Pilih **"Database"** -> klik **"Add MySQL"** (atau PostgreSQL jika Anda lebih memilih Postgres).
3. Railway akan membuat satu service database MySQL khusus secara instan.

---

### Langkah 4: Konfigurasi Environment Variables (Variabel Lingkungan)

1. Klik service **GeoCrime-web** (service aplikasi web Anda).
2. Buka tab **"Variables"**.
3. Klik tombol **"RAW Editor"** di pojok kanan atas untuk memasukkan variabel secara cepat.
4. Salin dan tempel konfigurasi berikut (sesuaikan `APP_KEY` Anda):

```ini
APP_NAME=GeoCrime
APP_ENV=production
APP_DEBUG=false
APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}
APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_TIMEZONE=Asia/Jakarta

LOG_CHANNEL=stderr
LOG_LEVEL=error

# Database
DB_CONNECTION=mysql

# Mengikat variabel MySQL Railway secara otomatis:
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

# Otomatisasi saat deploy
RUN_MIGRATIONS=true

# Aktifkan seeder ke 'true' pada deploy pertama agar akun admin terbuat
RUN_SEEDER=true

# Driver sesi & cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
FORCE_HTTPS=true
```

5. **Penting - Isi APP_KEY:**
   - Ambil nilai `APP_KEY` dari file `.env` lokal Anda saat ini (contoh: `base64:xxxx...`).
   - Atau buat key baru di terminal lokal dengan:
     ```bash
     php artisan key:generate --show
     ```
   - Tambahkan variabel `APP_KEY=base64:xxxx...` pada tab Variables di Railway.

6. Klik **"Update Variables"**. Railway akan otomatis melakukan *Redeploy* dengan variabel baru.

---

### Langkah 5: Buat Domain Publik (URL Website)

Agar website Anda bisa diakses dari browser:

1. Masih di service **GeoCrime-web**, buka tab **"Settings"**.
2. Scroll ke bagian **"Networking"**.
3. Di bagian **Public Networking**, klik tombol **"Generate Domain"**.
4. Railway akan memberikan domain publik gratis dengan akhiran `.up.railway.app` (misal: `geocrime-production.up.railway.app`).
5. Klik domain tersebut untuk membuka aplikasi Anda!

---

## 🔑 Kredensial Akun Administrator Awal

Jika Anda menyetel `RUN_SEEDER=true` pada deploy pertama, database akan terisi dengan akun bawaan berikut:

### Akun Super Admin:
- **Email:** `admin.kominfo@lumajang.go.id`
- **Password:** `KominfoLumajang2026!`
- **Role:** `super_admin`

### Akun Admin Patroli:
- **Email:** `admin.patroli@polri.go.id`
- **Password:** `PolriPatroli2026!`
- **Role:** `admin`

*(Setelah deploy pertama sukses dan akun sudah ada, Anda dapat mengubah kembali `RUN_SEEDER=false` di tab Variables agar seeder tidak dijalankan berulang setiap kali restart).*

---

## 🔍 Cara Memeriksa Log & Debugging

Jika terjadi kendala saat build atau runtime:
1. Klik service aplikasi Anda di Railway.
2. Buka tab **"Deployments"** lalu klik deployment yang sedang aktif.
3. Buka tab **"View Logs"** (atau **"Build Logs"** jika gagal saat build).
4. Anda dapat melihat pesan error Laravel secara *real-time*.

---

## 💡 Menjalankan Perintah Artisan Manual (Opsional)

Jika Anda ingin menjalankan perintah seperti `php artisan migrate:status` atau `php artisan tinker`:
1. Anda bisa menggunakan **Railway CLI** di komputer lokal:
   ```bash
   npm i -g @railway/cli
   railway login
   railway link
   railway run php artisan migrate:status
   ```
2. Atau jika mengaktifkan tab **"Web Terminal"** di Railway Dashboard.
