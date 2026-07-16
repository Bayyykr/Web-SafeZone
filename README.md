# SafeZone Web 🗺️🛡️

**SafeZone** adalah platform pemetaan dan pemantauan keamanan geografis berbasis framework Laravel (dengan Vite dan TailwindCSS). Aplikasi ini dirancang untuk memetakan area rawan kriminalitas, menentukan rute perjalanan teraman, serta memberikan notifikasi peringatan waktu nyata (real-time) bagi masyarakat di wilayah Kabupaten Lumajang. SafeZone bertindak sebagai web panel admin untuk manajemen data spasial sekaligus penyedia layanan RESTful API untuk diintegrasikan dengan aplikasi mobile client.

---

## 🌟 4 Fitur Unggulan

SafeZone dilengkapi dengan 4 fitur spasial utama yang memastikan keselamatan mobilitas pengguna secara optimal:

1. 🛰️ **Geofencing (Pembatasan Area Bahaya)**
   Memantau koordinat geografis pengguna (latitude & longitude) secara terus-menerus. Jika pengguna memasuki batas virtual wilayah yang berstatus **Rawan** atau **Sangat Rawan** (berdasarkan data polygon GeoJSON), sistem akan mendeteksi dan secara otomatis mengirimkan push notification peringatan secara real-time.

2. 🛣️ **Geo Corridor (Koridor Perjalanan Aman)**
   Fitur pelacakan aktif selama perjalanan sepanjang rute aman yang telah dipilih oleh pengguna. Sistem secara cerdas memantau posisi pengguna dan mendeteksi apabila menyimpang lebih dari 100 meter dari koridor rute. Jika pengguna menyimpang dan mendekati area kriminalitas aktif atau zona geofencing bahaya, notifikasi peringatan darurat akan segera dikirimkan.

3. 🛡️ **Safe Route (Skor Keamanan Rute)**
   Menghitung tingkat keamanan jalur perjalanan dari lokasi awal (*origin*) hingga tujuan (*destination*). Dengan memanfaatkan API routing OSRM (Open Source Routing Machine), sistem memindai laporan kejahatan terkonfirmasi (radius 200m) serta daerah rawan geofencing di sepanjang rute untuk menghitung **Safety Score** (0-100) dan mengklasifikasikan rute tersebut dalam indikator warna:
   *   🟢 **Green (Aman)**: Safety Score >= 80
   *   🟡 **Yellow (Waspada)**: Safety Score 50 - 79
   *   🔴 **Red (Bahaya)**: Safety Score < 50

4. 🏡 **Safe Zone (Deteksi Zona Aman)**
   Mendeteksi secara otomatis ketika pengguna memasuki zona yang dikategorikan sebagai daerah aman (misalnya wilayah berstatus **Aman** lainnya). Sistem akan mengirimkan pesan konfirmasi real-time bahwa pengguna berada di wilayah aman dan terlindungi.

---

## 📋 Prasyarat Sistem

Sebelum memulai instalasi, pastikan sistem komputer Anda telah memiliki aplikasi pendukung berikut:
*   **PHP** >= 8.3
*   **Composer** (Pengelola dependensi PHP)
*   **Node.js** & **NPM** (Versi LTS terbaru)
*   **Git**
*   **Database Engine** (SQLite, MySQL, atau PostgreSQL)

---

## 🚀 Langkah Instalasi Proyek

Ikuti panduan berikut untuk menjalankan proyek SafeZone di server lokal Anda:

### 1. Clone Repositori
Buka terminal dan unduh repositori proyek:
```bash
git clone https://github.com/Bayyykr/SafeZone-web.git
cd SafeZone-web
```

### 2. Instalasi Dependensi
Instal seluruh paket dependensi backend (PHP) dan frontend (NodeJS/Vite):

*   **Instal Dependensi Backend (Composer):**
    ```bash
    composer install
    ```
*   **Instal Dependensi Frontend (NPM):**
    ```bash
    npm install
    ```

### 3. Konfigurasi Environment File
1.  Salin file `.env.example` menjadi `.env`:
    ```bash
    cp .env.example .env
    ```
    *(Untuk pengguna Windows PowerShell, jalankan perintah: `copy .env.example .env`)*

2.  Buka file `.env` yang baru dibuat dan sesuaikan konfigurasi database.
    *   Jika menggunakan **SQLite** (bawaan default):
        ```env
        DB_CONNECTION=sqlite
        ```
        *(Pastikan untuk membuat file kosong bernama `database.sqlite` di dalam direktori `database/` jika belum tersedia)*
    *   Jika menggunakan **MySQL**:
        ```env
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=nama_database_anda
        DB_USERNAME=username_mysql_anda
        DB_PASSWORD=password_mysql_anda
        ```

3.  Generate kunci enkripsi aplikasi (App Key):
    ```bash
    php artisan key:generate
    ```

### 4. Migrasi & Seeding Database
Jalankan migrasi untuk membuat seluruh struktur tabel beserta data dummy awal (seperti data wilayah, laporan kriminal, dan akun pengguna):
```bash
php artisan migrate --seed
```

---

## 🔐 Integrasi & Sinkronisasi Firebase

Sistem pengiriman notifikasi SafeZone menggunakan **Firebase Cloud Messaging (FCM)** dan **Firebase Authentication** untuk mengelola autentikasi mobile client. Ikuti langkah di bawah ini untuk mengonfigurasinya:

1.  **Unduh File Kredensial Firebase:**
    *   Masuk ke [Firebase Console](https://console.firebase.google.com/).
    *   Pilih proyek Firebase Anda.
    *   Masuk ke **Project Settings** > **Service accounts**.
    *   Klik **Generate new private key** (Hasilkan kunci pribadi baru) dan simpan file JSON yang terunduh.

2.  **Tempatkan File Kredensial di Proyek:**
    *   Ubah nama file JSON tersebut menjadi `firebase-service-account.json` (atau nama lain pilihan Anda).
    *   Letakkan file tersebut di dalam direktori `storage/app/`.

3.  **Sesuaikan Konfigurasi `.env`:**
    *   Buka kembali file `.env` Anda dan pastikan nilai path kredensial telah sesuai:
        ```env
        FIREBASE_CREDENTIALS_PATH=storage/app/firebase-service-account.json
        ```

4.  **Jalankan Sinkronisasi Pengguna ke Firebase:**
    *   Untuk menyinkronkan seluruh akun dummy yang dibuat melalui proses seeder database lokal ke Firebase Authentication, jalankan perintah CLI berikut:
        ```bash
        php artisan firebase:sync-users
        ```

---

## 💻 Menjalankan Aplikasi di Lokal

Jalankan server pengembangan backend Laravel dan server kompilasi frontend Vite secara bersamaan menggunakan terminal terpisah:

### A. Jalankan Server Laravel (Backend)
```bash
php artisan serve
```
Aplikasi web SafeZone dapat diakses melalui browser di alamat [http://127.0.0.1:8000](http://127.0.0.1:8000).

### B. Jalankan Server Vite (Frontend)
Buka terminal baru di direktori proyek yang sama, lalu jalankan:
```bash
npm run dev
```
