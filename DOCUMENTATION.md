# Hikayat Teknis & Panduan Arsitektur Apexio

**Versi Dokumen:** 1.2.0 (Revisi Final)  
**Tanggal Penulisan:** 17 Desember 2025  
**Penulis:** Muhammad Yasyfi Alhafizh

---

## Daftar Isi

1. [Prolog: Filosofi & Arsitektur](#prolog-filosofi--arsitektur)
2. [Bab I: Para Tokoh (Model & Database)](#bab-i-para-tokoh-model--database)
3. [Bab II: Sang Penjaga Gerbang (Authentication & Policies)](#bab-ii-sang-penjaga-gerbang-authentication--policies)
4. [Bab III: Jantung Interaksi (Livewire Components)](#bab-iii-jantung-interaksi-livewire-components)
5. [Bab IV: Estetika & Tampilan (Bootstrap & SCSS)](#bab-iv-estetika--tampilan-bootstrap--scss)
6. [Epilog: Catatan Keamanan & Praktik Terbaik](#epilog-catatan-keamanan--praktik-terbaik)

---

## Prolog: Filosofi & Arsitektur

Selamat datang di dunia Apexio. Aplikasi ini bukan sekadar kumpulan kode, melainkan sebuah ekosistem manajemen proyek yang dibangun di atas pondasi **Laravel 11 & Livewire 3** yang dipadukan dengan ketangguhan **Bootstrap 5**.

Berbeda dengan tren TALL Stack yang menggunakan Tailwind, Apexio memilih jalan **Kestabilan Klasik**. Kami menggunakan Bootstrap yang dikelola melalui SCSS untuk memastikan desain yang konsisten, grid yang kokoh, dan komponen UI yang matang.

Filosofi utama kode ini adalah **Reaktivitas Tanpa Kompromi**. Kami menghindari page reload tradisional sebisa mungkin. Hampir seluruh interaksi dinamis—mulai dari membuat proyek, mengundang anggota tim, hingga menggeser kartu tugas—ditangani oleh Livewire, yang bertindak sebagai jembatan gaib antara browser dan server.

---

## Bab I: Para Tokoh (Model & Database)

Di dalam semesta data Apexio, terdapat empat entitas utama yang saling berinteraksi. Mereka hidup di `app/Models`.

### 1. Sang Pencipta: User

Segala sesuatu bermula dari User. Model ini mewarisi `Authenticatable` dari Laravel.

- **Identitas:** Memiliki nama, email, password, dan atribut `avatar_path` untuk menyimpan foto profil.
- **Peran:** Memiliki atribut `is_admin` untuk membedakan Super Admin (penguasa sistem) dengan pengguna biasa.
- **Relasi:** User bisa memiliki banyak Project (`projects()`) dan bisa menjadi anggota di banyak project lain (`belongsToMany` via pivot).

### 2. Sang Wadah: Project

Project adalah pusat gravitasi.

- **Kepemilikan:** Setiap project memiliki satu Owner mutlak yang dicatat dalam kolom `owner_id`. Ini adalah hukum yang tidak bisa diganggu gugat. Hanya Owner yang bisa menghancurkan project ini.
- **Siklus Hidup (The Cycle of Life):** Dalam method `booted()`, terdapat logika clean-up. Jika sebuah Project dihapus (`deleting`), maka secara otomatis:
  - Semua tasks di dalamnya akan musnah.
  - Semua hubungan keanggotaan (`members`) akan diputus (detach). Ini mencegah adanya data yatim piatu (orphan data) di database.

### 3. Sang Unit Kerja: Task

Task adalah atom terkecil dari pekerjaan.

- **Atribut:** Memiliki status (Todo, In Progress, Done), priority (Low, Medium, High), dan `due_date`.
- **Posisi:** Memiliki kolom `position` (atau `order`) yang krusial untuk fitur Drag & Drop di Kanban board.

### 4. Sang Penghubung: ProjectMember (Pivot)

Meskipun tidak memiliki Model eksplisit (menggunakan `belongsToMany` di User & Project), tabel pivot `project_members` adalah tempat di mana hierarki tim ditentukan.

- **Role:** Kolom `role` di tabel ini menentukan apakah seorang member adalah Admin (wakil) atau Member (rakyat biasa).

---

## Bab II: Sang Penjaga Gerbang (Authentication & Policies)

Keamanan di Apexio bukan sekadar fitur tambahan, melainkan dinding benteng yang kokoh.

### Authentication (Auth Controller)

Kami menggunakan starter kit (mirip Breeze) yang telah dimodifikasi. Controller otentikasi terletak di `app/Http/Controllers/Auth`.

### Authorization (Policies)

Di sinilah hukum ditegakkan. Terletak di `app/Policies`.

#### ProjectPolicy.php - Konstitusi Proyek

Ini adalah file paling sakral dalam manajemen akses.

- **View:** Siapa yang boleh melihat proyek? Hanya Owner ATAU mereka yang terdaftar di tabel `project_members`.
- **Update:** Siapa yang boleh mengedit proyek (ganti nama, tambah member)?
  - Owner (`owner_id`): Ya.
  - Admin Project (User dengan role 'Admin' di pivot): Ya.
  - Member biasa: TIDAK.
- **Delete (Pasal Kematian):** Siapa yang boleh menghapus proyek?
  - **HANYA OWNER** (`$user->id === $project->owner_id`).
  - Admin Project TIDAK memiliki kuasa ini. Ini adalah fitur keamanan absolut untuk mencegah "kudeta".

#### TaskPolicy.php

Mengatur siapa yang boleh memindah-mindahkan kartu tugas. Logikanya mirip dengan ProjectPolicy, namun lebih luwes agar kolaborasi bisa terjadi.

---

## Bab III: Jantung Interaksi (Livewire Components)

Di sinilah letak "sihir" aplikasi ini. `app/Livewire` adalah tempat di mana frontend bertemu backend secara real-time.

### Manajemen Proyek & Logika Pembuatan

**File:** `ManageProjects.php` & `AdminDashboard.php`

Komponen `ManageProjects` bertugas menampilkan daftar proyek di sidebar dan dashboard utama.

**Creation Logic (The Auto-Attach):**

Saat proyek baru dibuat via `saveProject()`, sistem melakukan dua langkah transaksional:

1. Membuat record Project dengan `owner_id`.
2. Secara otomatis mendaftarkan (attach) Owner tersebut sebagai 'Admin' di tabel pivot `project_members`.

**Alasan:** Ini memperbaiki celah logika di mana owner tidak bisa melihat proyeknya sendiri di dashboard karena query utama bergantung pada tabel keanggotaan.

**Keamanan Hapus:** Fungsi `deleteProject($id)` melakukan pengecekan ganda via `$this->authorize('delete', $project)` untuk menolak request ilegal.

### Keanggotaan & Peran (The Member Logic)

**File:** `ProjectMembers.php` (Backend) & `project-members.blade.php` (Frontend)

Ini adalah komponen yang paling kompleks secara logika sosial.

- **Proteksi Kudeta:** Di fungsi `updateRole` dan `removeMember`, terdapat pengecekan: `if ($userId === $this->project->owner_id) return;`. Owner tidak bisa diturunkan jabatannya atau di-kick oleh siapapun.
- **Styling Badge:** Menggunakan Inline Style pada badge status (Admin/Owner) untuk memastikan warna ungu dan emas muncul dengan kontras tinggi.

### Kanban & Task List

**File:** `TaskList.php` (Logic) & `task-list.blade.php` (View)

- Menggunakan perpustakaan sortable yang mengirimkan event ke Livewire saat kartu dipindah.
- **Description Persistence:** Berbeda dengan desain awal, metode `saveTask()` kini secara eksplisit menangkap dan menyimpan field `description`.
- **Visual Logic:** Tampilan kartu Kanban menggunakan CSS line-clamping (`-webkit-line-clamp: 2`) untuk menampilkan deskripsi tugas secara elegan tanpa merusak layout papan.

### Personal Workspace (My Tasks)

**File:** `MyTasks.php`

Komponen ini menangani agregasi tugas dari seluruh proyek.

**The "Ghost Task" Prevention:**

- **Masalah:** User yang sudah di-kick dari sebuah proyek masih bisa melihat tugas yang pernah diberikan kepadanya di halaman "My Tasks".
- **Solusi:** Logika query diperketat. Sistem kini mengecek: `where('assignee_id', $user)` DAN `whereHas('project', ...)` untuk memastikan user tersebut masih menjadi anggota aktif atau pemilik dari proyek induk tugas tersebut.

---

## Bab IV: Estetika & Tampilan (Bootstrap & SCSS)

Apexio tidak menggunakan CSS utility-first (Tailwind), melainkan pendekatan **Component-based** dengan SCSS yang terkompilasi.

### Struktur SCSS (`resources/scss`):

- **app.scss:** Jantung gaya aplikasi. File ini mengimpor Bootstrap Framework secara utuh, memberikan akses ke grid system, modal, dan utility classes.
- **_variables.scss:** Tempat mendefinisikan ulang variabel Bootstrap (seperti `$primary`, `$font-family`) agar sesuai dengan identitas brand Apexio.

### Integrasi JavaScript:

- **Bootstrap JS:** `resources/js/bootstrap.js` memuat library Bootstrap 5 JS dan Axios.
- **Profile Modals:** Kami memigrasikan modal "Delete Account" dari Alpine.js ke **Native Bootstrap 5 Data Attributes** (`data-bs-toggle`). Ini menjamin keandalan tombol dan konsistensi tema, memperbaiki masalah di mana tombol seringkali tidak merespons.

---

## Epilog: Catatan Keamanan & Praktik Terbaik

Sebagai penutup dokumentasi ini, berikut adalah "Mantra Keamanan" yang diterapkan di Apexio:

1. **Trust No One:** Jangan pernah percaya input dari browser. Selalu validasi di backend (`$this->validate()`).

2. **Verify Authority:** Jangan hanya sembunyikan tombol "Hapus". Pastikan fungsi di backend memanggil `$this->authorize()` sebelum mengeksekusi perintah berbahaya.

3. **Owner is King:** Pastikan logika kode selalu membedakan antara `user_id` (relasi pivot) dan `owner_id` (pemilik asli di tabel projects). Jangan sampai tertukar!

4. **No Ghost Data:** Pastikan setiap query list memverifikasi status keanggotaan user saat ini, bukan hanya riwayat penugasan (`assignee`).

---

Demikianlah dokumentasi teknis ini dibuat. Semoga menjadi pelita bagi pengembang yang meneruskan warisan kode Apexio.

**Akhir Dokumen**