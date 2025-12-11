# 🤝 Komunitas Maju Bersama (Web-Based Community Platform)

> *Tugas Besar Praktikum Pemrograman Web 2025*
>
> *Tema:* Community & Organization Management

Aplikasi ini adalah platform komunitas berbasis web yang dirancang untuk mendigitalkan interaksi sosial, pengambilan keputusan (voting), manajemen kegiatan, dan transparansi informasi dalam sebuah organisasi atau komunitas. Dibangun menggunakan PHP Native untuk performa yang ringan dan struktur yang mudah dipahami.

---

## 👥 Anggota Kelompok

| No  | Nama Lengkap | NPM | Role |
| :--- | :--- | :--- | :--- |
| 1 | *Gerhana Malik Ibrahim* | *231506032* | Frontend / Project Lead |
| 2 | *Daniel Ardiyansah* | *2315061124* | Backend / UI Designer |
| 3 | *Muhammad Abdul Hadi Amrul* | *2315061078* | Frontend |
| 4 | *Aisyah Rahma Hasan* | *2215061086* | Frontend |

---

## 📖 Gambaran Proyek

### Latar Belakang
Komunitas seringkali kesulitan dalam mengelola aspirasi anggota secara terpusat, mendata peserta kegiatan, dan menjaga transparansi keuangan donasi. Proyek ini hadir sebagai solusi all-in-one untuk manajemen komunitas modern.

### Fitur Utama
1.  *Forum Diskusi Interaktif:*
    * Member bisa membuat topik dan membalas komentar.
    * Mendukung upload foto dalam diskusi.
    * Fitur Time Ago dan penghitung Views.
2.  *E-Voting (Demokrasi Digital):*
    * Sistem pemungutan suara (Ketua/Acara) yang aman.
    * Validasi One User One Vote.
    * Visualisasi hasil real-time dengan progress bar.
3.  *Manajemen Event & Berita:*
    * Pendaftaran event otomatis (event_register.php).
    * Portal berita terupdate (news.php).
4.  *Program Donasi & Kemanusiaan:*
    * Halaman galang dana dengan tracking nominal terkumpul.
5.  *Admin Panel Lengkap:*
    * Dashboard statistik, manajemen member, verifikasi konten, dan laporan.

---

## 🛠 Teknologi yang Digunakan

Sesuai ketentuan tugas besar, aplikasi ini dibangun tanpa Framework PHP/JS (Native):

* *Frontend:* HTML5, *Tailwind CSS* (CDN), Feather Icons, JavaScript Native (AJAX/DOM).
* *Backend:* PHP Native (Procedural & Structured).
* *Database:* MySQL / MariaDB.
* *Tools:* VS Code, XAMPP/Laragon, Git.

---

## 🌳 Struktur Folder (Work Tree)

Struktur direktori disusun berdasarkan pemisahan hak akses (User vs Admin) sesuai screenshot proyek:

```bash
/komunitas-maju
├── /admin                      # PANEL ADMINISTRATOR
│   ├── /uploads                # File upload khusus admin (banner, kandidat)
│   ├── dashboard.php           # Statistik utama
│   ├── campaigns.php           # Manajemen donasi
│   ├── events.php              # Manajemen acara & peserta
│   ├── members.php             # Kelola data anggota
│   ├── news.php                # Kelola berita
│   ├── votings.php             # Kelola sesi voting
│   └── voting_manage.php       # Edit opsi/kandidat voting
│
├── /uploads                    # FILE UPLOAD USER
│   ├── /events                 # Banner event
│   ├── /forum                  # Foto lampiran diskusi
│   └── /hero                   # Slider halaman depan
│
├── config.php                  # Koneksi Database
├── navbar_include.php          # Navigasi Global
├── time_helper.php             # Helper waktu (time ago)
│
├── /auth                       # (Logika Login dipisah atau di root)
│   ├── login.php
│   ├── logout.php
│   └── register.php
│
├── /pages                      # HALAMAN USER (FRONTEND)
│   ├── index.php               # Homepage / Beranda
│   ├── forum.php               # List Diskusi
│   ├── forum_detail.php        # Detail & Komentar
│   ├── ajax_forum.php          # Proses background
│   ├── voting.php              # Halaman E-Voting
│   ├── events.php              # List Agenda
│   ├── event_register.php      # Form Daftar Event
│   ├── donation.php            # List Donasi
│   ├── donation_payment.php    # Konfirmasi Donasi
│   ├── news.php                # Portal Berita
│   └── news_detail.php         # Baca Berita
│
└── dashboard.php               # Dashboard User (Opsional)
