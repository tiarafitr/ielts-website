# Mini IELTS Speaking Evaluation

Aplikasi latihan IELTS Speaking berbasis **Laravel 10 + Vue 3**. Pengguna menjawab
pertanyaan IELTS Speaking (Part 1, 2, dan 3) dalam bentuk teks, jawaban dikirim ke
**Google Gemini** untuk dievaluasi, lalu hasilnya ditampilkan sebagai band report
beserta riwayat latihan per pengguna.

---

## 1. Cara Menjalankan Project

### Prasyarat

- **PHP 8.1 – 8.3.** Laravel 10 belum mendukung PHP 8.5; pada versi tersebut
  `php artisan tinker` keluar sendiri karena *deprecation* di Sanctum.
  Periksa dengan `php -v`.
- Composer
- Node.js 18+ dan npm
- Database: PostgreSQL (mis. Supabase) **atau** SQLite

### Langkah 1 — Clone dan pasang dependency

Terminal pertama:

```bash
git clone https://github.com/USERNAME/REPOSITORY.git
cd REPOSITORY

composer install
```

### Langkah 2 — Siapkan environment

```bash
cp .env.example .env (copy isi .env yang telah diberikan)
php artisan key:generate
```

```env
DB_CONNECTION=pgsql
DB_HOST=db.xxxxxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=<password database Anda>
DB_SCHEMA=public
DB_SSLMODE=require
```

Gunakan port **6543**.

> Kredensial tidak disertakan di dalam repositori. Setiap orang yang melakukan
> *clone* harus mengisinya sendiri, atau memakai kredensial yang dibagikan
> pemilik project secara terpisah.

### Langkah 3 — Siapkan folder dan jalankan migrasi

```bash
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache      # lewati baris ini pada Windows

php artisan config:clear
php artisan migrate --seed
php artisan migrate:status                # opsional: memastikan 5 migration "Ran"
```

`--seed` mengisi bank soal dengan sembilan pertanyaan IELTS. Tanpa itu, tabel
`questions` kosong dan halaman Practice tidak menampilkan soal apa pun.

Apabila Anda memakai database yang sudah pernah dimigrasi dan di-*seed*
sebelumnya, perintah di atas akan menjawab `Nothing to migrate` dan bank soal
tidak diduplikasi karena seeder memakai `firstOrCreate`.

### Langkah 4 — Build frontend

Terminal kedua:

```bash
npm install
npm run build
```

Direktori `public/build` tidak ikut di-*commit*, sehingga langkah ini **wajib**
dijalankan setelah *clone*; tanpa itu halaman akan tampil kosong.

Sebagai alternatif saat mengembangkan tampilan, jalankan `npm run dev` dan
biarkan terminal tetap terbuka. Jika `npm run dev` tertutup paksa, berkas
`public/hot` dapat tersangkut dan halaman menjadi kosong; hapus dengan
`rm -f public/hot`.

### Langkah 5 — Jalankan aplikasi

Kembali ke terminal pertama:

```bash
php artisan serve
```

Buka **http://127.0.0.1:8000/dashboard**, lalu pilih **Create an account**.

Seeder hanya mengisi bank soal dan **tidak membuat user**, sehingga tidak ada
email maupun kata sandi bawaan. Silakan daftar melalui halaman tersebut atau mencoba user yang telah dibuat.

### Dummy User
email : demo@ielts-mini.dev
password : password123

### Ringkasan perintah

```bash
# Terminal 1
git clone https://github.com/USERNAME/REPOSITORY.git && cd REPOSITORY
composer install
cp .env.example .env          # sesuaikan konfigurasi database
php artisan key:generate
mkdir -p storage/framework/cache/data storage/framework/sessions \
         storage/framework/views storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache
php artisan config:clear
php artisan migrate --seed
php artisan serve

# Terminal 2
npm install
npm run build
```

### Bila terjadi masalah

| Gejala | Penyebab dan solusi |
|---|---|
| Halaman kosong / tanpa gaya | `npm run build` belum dijalankan, atau `public/hot` tersangkut (`rm -f public/hot`). |
| `SQLSTATE[08006] connection refused` | Kredensial database salah, atau memakai port selain 6543. |
| `Nothing to migrate` tetapi soal kosong | Database sudah bertabel tetapi belum di-*seed*. Jalankan `php artisan db:seed`. |
| `Please provide a valid cache path` | Folder `storage/framework/views` belum dibuat (lihat Langkah 3). |
| Laporan band bertanda *offline estimate* | `GEMINI_API_KEY` kosong atau panggilan gagal. Aplikasi tetap berfungsi; isi kunci untuk penilaian AI sungguhan. |
| `php artisan tinker` langsung keluar | PHP 8.5. Gunakan PHP 8.1–8.3. |

---
