# Hotel Management System — PPKD Hotel

## Product Requirements Document (PRD) + Entity Relationship Design (ERD)

### Versi Final — Merged Document

---

# 1. Ringkasan Project

| Atribut          | Detail                                                                                                            |
| ---------------- | ----------------------------------------------------------------------------------------------------------------- |
| **Nama Project** | Hotel Management System — PPKD Hotel                                                                              |
| **Tujuan**       | Membangun sistem manajemen hotel untuk mengelola operasional hotel secara terpusat, real-time, dan terdokumentasi |
| **Platform**     | Web Application (Internal Staff)                                                                                  |
| **Tech Stack**   | Laravel 13, MySQL, Tailwind CSS, Laravel Breeze                                                                   |

**Pengguna Sistem:**

- Administrator
- Front Office (FO)
- Housekeeping (HK)
- Food and Beverage (FnB)
- Management Hotel _(read-only access ke laporan dan dashboard)_

---

# 2. Latar Belakang Masalah

Operasional hotel melibatkan banyak aktivitas yang saling berkaitan, seperti:

- Reservasi dan manajemen tamu
- Check-in dan check-out
- Inspeksi kamar dan housekeeping
- Request laundry
- Pesanan makanan dan minuman
- Pembayaran dan invoice

Jika dilakukan secara manual atau menggunakan file terpisah, masalah yang muncul antara lain:

- Data tamu sulit dilacak
- Status kamar tidak real-time dan sering tidak sinkron
- Request housekeeping, laundry, dan FnB tidak terdokumentasi
- Riwayat inspeksi kamar tercecer
- Perhitungan invoice dan pendapatan tidak konsisten
- Request tamu antar divisi sulit dikomunikasikan
- Manajemen sulit memantau performa hotel secara keseluruhan

Sistem ini dirancang untuk menyatukan seluruh proses tersebut dalam satu webapps internal.

---

# 3. Tujuan Sistem

1. Mengelola reservasi tamu secara terstruktur
2. Mengatur proses check-in dan check-out
3. Memantau status kamar secara real-time
4. Mengelola request housekeeping (stayover cleaning, checkout cleaning, linen replacement, dll)
5. Mengelola request laundry dari tamu
6. Mengelola pesanan makanan dan minuman (FnB)
7. Mencatat hasil inspeksi kamar saat checkout
8. Menghitung charge tambahan (kerusakan kamar, extra bed, minibar, dll)
9. Menyediakan invoice yang siap printout
10. Menyediakan laporan operasional dan pendapatan
11. Memberikan hak akses sesuai role masing-masing divisi

---

# 4. Role dan Divisi

## 4.1 Deskripsi Role

### 4.1.1 Administrator

**Tugas utama:**

- Akses semua menu sistem
- Mengatur hak akses staff lainnya
- Mengelola master data (kamar, harga, menu, charge, metode pembayaran)
- Melihat semua laporan dan pengaturan sistem

### 4.1.2 Front Office (FO)

**Tugas utama:**

- Mengelola data tamu
- Membuat dan mengelola reservasi
- Melakukan check-in dan checkout
- Memantau status kamar
- Membuat request cleaning, laundry, dan FnB atas permintaan tamu
- Mengelola pembayaran dan invoice

### 4.1.3 Housekeeping (HK)

**Tugas utama:**

- Menerima dan memproses request cleaning kamar
- Melakukan room inspection saat checkout
- Mengelola request laundry
- Memperbarui status kamar setelah cleaning/inspeksi
- Melihat riwayat cleaning dan inspeksi

### 4.1.4 Food and Beverage (FnB)

**Tugas utama:**

- Menerima daftar order dari FO
- Memproses pesanan makanan dan minuman
- Menandai pesanan selesai dan diantar ke kamar
- Melihat riwayat order

---

## 4.2 Hak Akses per Role

### 4.2.1 Administrator

- CRUD user / staff
- CRUD role & permission
- CRUD jenis kamar (room types)
- CRUD data kamar (rooms)
- CRUD menu makanan dan minuman
- CRUD kategori menu FnB
- CRUD metode pembayaran
- CRUD jenis charge tambahan
- CRUD pengaturan hotel (tax, service charge, dll)
- Melihat semua laporan

### 4.2.2 Front Office

- Data tamu (CRUD)
- Reservasi (CRUD)
- Check-in
- Checkout
- Status kamar (view)
- Cleaning request (buat dan pantau)
- Laundry request (buat dan pantau)
- FnB order (buat dan pantau)
- Pembayaran (input dan konfirmasi)
- Invoice (lihat, cetak)
- Dashboard

### 4.2.3 Housekeeping

- Cleaning request (lihat, terima, update status)
- Room inspection (input hasil inspeksi)
- Riwayat inspeksi kamar
- Laundry request (lihat, proses, update status)
- Update status kamar
- Riwayat cleaning
- Dashboard housekeeping

### 4.2.4 Food and Beverage

- Daftar pesanan masuk (view)
- Proses pesanan (update status)
- Riwayat pesanan
- Dashboard FnB

---

# 5. Metode Pembayaran

Sistem mendukung 4 metode pembayaran:

1. **Cash**
2. **Transfer Bank**
3. **QRIS**
4. **Credit Card**

### Kebutuhan Sistem untuk Pembayaran

- Setiap transaksi dapat memiliki satu atau lebih record pembayaran
- Mendukung pembayaran penuh (full payment)
- Mendukung pembayaran sebagian (partial payment)
- Mendukung split payment (bayar dengan lebih dari satu metode)
- Mendukung koreksi dan refund pembayaran
- Menyimpan nominal pembayaran
- Menyimpan nomor referensi transaksi (nomor transfer, nomor approval, dsb)
- Menyimpan tanggal pembayaran
- Menyimpan catatan transaksi

### Status Pembayaran

| Status     | Keterangan             |
| ---------- | ---------------------- |
| `unpaid`   | Belum ada pembayaran   |
| `partial`  | Sudah dibayar sebagian |
| `paid`     | Lunas                  |
| `refunded` | Dikembalikan           |

---

# 6. Jenis Kamar

### 6.1 Standard Room

**Karakteristik:** Kamar ekonomis dan paling dasar.

**Fasilitas:**

- Tempat tidur single / queen
- AC
- TV
- Wi-Fi
- Kamar mandi dalam
- Air mineral

**Kapasitas:** 1–2 orang
**Cocok untuk:** Tamu singkat dan budget terbatas

---

### 6.2 Deluxe Room

**Karakteristik:** Lebih luas dari standard, lebih nyaman.

**Fasilitas:**

- Tempat tidur queen / king
- AC
- TV
- Wi-Fi
- Meja kerja
- Kamar mandi lebih lengkap
- Lemari pakaian

**Kapasitas:** 2 orang
**Cocok untuk:** Tamu bisnis atau pasangan

---

### 6.3 Superior Room

**Karakteristik:** Level di atas standard dengan kenyamanan lebih baik.

**Fasilitas:**

- Tempat tidur queen / king
- AC
- TV
- Wi-Fi
- Meja kerja
- Amenities lebih lengkap

**Kapasitas:** 2 orang
**Cocok untuk:** Tamu yang ingin kenyamanan menengah

---

### 6.4 Studio Room

**Karakteristik:** Kamar modern dengan layout multifungsi.

**Fasilitas:**

- Area tidur dan area duduk menyatu
- AC
- TV
- Wi-Fi
- Meja kerja
- Kamar mandi dalam
- Beberapa unit dilengkapi pantry kecil

**Kapasitas:** 2 orang
**Cocok untuk:** Tamu bisnis atau menginap jangka menengah

---

### 6.5 Suite Room

**Karakteristik:** Kamar premium dengan ruang lebih luas.

**Fasilitas:**

- Ruang tidur dan ruang duduk terpisah
- AC
- TV
- Wi-Fi
- Sofa
- Kamar mandi premium
- Amenities lengkap
- Minibar

**Kapasitas:** 2–3 orang
**Cocok untuk:** Tamu VIP dan keluarga kecil

---

### 6.6 Connecting Room

**Karakteristik:** Dua kamar yang terhubung dengan pintu penghubung.

**Fasilitas:**

- Dua kamar terpisah dengan pintu penghubung
- AC
- TV
- Wi-Fi
- Kamar mandi masing-masing

**Kapasitas:** 4 orang atau lebih
**Cocok untuk:** Keluarga atau rombongan kecil

---

# 7. Aturan Kamar

### 7.1 Breakfast

- Kamar dengan harga di atas **Rp600.000** mendapatkan breakfast included
- Breakfast dapat dikonfigurasi dari sistem (per room type)
- Pengaturan breakfast:
    - Otomatis included berdasarkan harga kamar
    - Optional add-on
    - Berdasarkan room type / rate policy yang diset admin

### 7.2 Extra Bed

- Extra bed tersedia sebagai layanan tambahan
- Dikenakan charge tambahan yang dikonfigurasi admin
- Jumlah extra bed harus tercatat di booking dan invoice

---

# 8. Status Kamar

Sistem menggunakan 8 status kamar untuk mencerminkan kondisi aktual kamar secara real-time.

| Status         | Keterangan                                                 |
| -------------- | ---------------------------------------------------------- |
| `available`    | Kamar siap dijual / siap digunakan tamu                    |
| `reserved`     | Kamar sudah dibooking, tamu belum check-in                 |
| `occupied`     | Kamar sedang ditempati tamu                                |
| `dirty`        | Kamar perlu dibersihkan (setelah checkout atau permintaan) |
| `cleaning`     | Housekeeping sedang membersihkan kamar                     |
| `inspected`    | Kamar sudah dibersihkan dan sudah dicek supervisor HK      |
| `maintenance`  | Kamar sedang perbaikan, tidak bisa dijual                  |
| `out_of_order` | Kamar tidak dapat digunakan sementara (kerusakan berat)    |

**Alur umum status kamar:**

```
available → reserved → occupied → dirty → cleaning → inspected → available
                                        → maintenance → available
```

---

# 9. Alur Sistem

## A. Reservasi

1. FO menerima tamu
2. FO mengecek kamar tersedia
3. FO memilih kamar yang sesuai
4. FO membuat reservasi dan mengisi data tamu
5. Sistem menyimpan data booking dengan booking number unik
6. Status kamar berubah menjadi `reserved`

**Hasil:**

- Booking tersimpan dengan booking number
- Status kamar terupdate ke `reserved`

---

## B. Check-in

1. Tamu tiba di hotel
2. FO mencari data reservasi
3. FO melakukan konfirmasi check-in
4. Status kamar berubah menjadi `occupied`

**Hasil:**

- Tamu dianggap aktif menginap
- Record checkin tersimpan

---

## C. Stayover Cleaning

_(Berlaku jika tamu menginap lebih dari 1 hari dan meminta kamar dibersihkan)_

1. Tamu menghubungi FO
2. FO membuat cleaning request (tipe: `stayover_cleaning`)
3. Request masuk ke dashboard Housekeeping
4. HK menerima dan memproses request
5. Status cleaning request berubah menjadi `in_progress`
6. HK menyelesaikan cleaning
7. Status request berubah menjadi `completed`
8. Riwayat cleaning tersimpan

**Hasil:**

- Request cleaning tercatat dan terdokumentasi
- Status kamar sementara berubah ke `cleaning`, lalu kembali ke `occupied`

---

## D. Laundry

1. Tamu menghubungi FO untuk request laundry
2. FO input request laundry ke sistem
3. Housekeeping menerima request
4. Laundry diproses
5. Laundry dikembalikan ke tamu
6. Status request berubah menjadi `delivered`

**Hasil:**

- Request laundry tercatat dan terpantau
- Charge laundry masuk ke invoice reservasi

---

## E. Food and Beverage

1. Tamu memesan makanan / minuman ke FO
2. FO input order ke sistem
3. Order masuk ke dashboard FnB
4. FnB memproses pesanan
5. Status order berubah menjadi `preparing`
6. Pesanan diantar ke kamar tamu
7. Status order berubah menjadi `delivered`

**Hasil:**

- Order tercatat dengan status real-time
- Charge FnB masuk ke invoice reservasi

---

## F. Checkout

1. Tamu menuju FO untuk checkout
2. FO melakukan proses checkout
3. FO meminta HK melakukan inspeksi kamar
4. HK mengecek kondisi kamar:
    - Barang hilang
    - Kerusakan fasilitas
    - Kondisi umum kamar
5. Jika ada kerusakan → tambah charge ke invoice
6. Status kamar berubah menjadi `dirty`
7. HK melakukan cleaning kamar
8. Status kamar berubah menjadi `cleaning`
9. Supervisor HK melakukan inspeksi akhir
10. Status kamar berubah menjadi `inspected` → `available`

**Hasil:**

- Checkout tercatat
- Semua charge tambahan masuk invoice
- Invoice final dapat dicetak
- Status kamar mengikuti kondisi aktual

---

# 10. Jenis Housekeeping Request

Sistem mendukung 5 jenis request housekeeping:

| Tipe                | Keterangan                                    |
| ------------------- | --------------------------------------------- |
| `stayover_cleaning` | Pembersihan kamar saat tamu masih menginap    |
| `checkout_cleaning` | Pembersihan setelah tamu checkout             |
| `deep_cleaning`     | Pembersihan menyeluruh dan mendetail          |
| `maintenance`       | Permintaan perbaikan fasilitas kamar          |
| `linen_replacement` | Penggantian sprei, sarung bantal, atau handuk |

### Status Housekeeping Request

| Status        | Keterangan                                  |
| ------------- | ------------------------------------------- |
| `pending`     | Request baru dibuat, belum diproses         |
| `assigned`    | Request sudah diterima dan ditugaskan ke HK |
| `in_progress` | HK sedang mengerjakan request               |
| `completed`   | Request selesai dikerjakan                  |
| `cancelled`   | Request dibatalkan                          |

---

# 11. Invoice dan Booking Number

### 11.1 Invoice

Invoice harus:

- Dapat ditampilkan di sistem
- Dapat dicetak / printout langsung dari browser
- Memuat rincian biaya lengkap (room charge, FnB, laundry, extra bed, kerusakan)
- Memuat riwayat pembayaran
- Memuat metode pembayaran yang digunakan
- Memuat status pembayaran saat ini
- Memuat informasi tamu dan periode menginap

### 11.2 Booking Number

Format: **`BK-ROOMNO-YYYYMMDD-XXXX`**

Contoh:

- `BK-101-20260616-0001`
- `BK-205-20260617-0002`

Keterangan:

- `BK` = prefix booking
- `ROOMNO` = nomor kamar yang dipesan
- `YYYYMMDD` = tanggal booking dibuat
- `XXXX` = nomor urut unik harian

### 11.3 Invoice Number

Format: **`INV-YYYYMMDD-XXXX`**

Contoh:

- `INV-20260616-0001`

### 11.4 Relasi Booking dan Invoice

- Satu reservasi memiliki **satu** invoice utama
- Satu invoice dapat memiliki banyak charge tambahan
- Satu invoice dapat memiliki banyak payment record (partial payment)

---

# 12. Menu Sistem per Role

## 12.1 Administrator

- Dashboard
- User Management
- Role & Permission
- Room Type (Tipe Kamar)
- Room Management (Data Kamar)
- Food Menu (Menu FnB + Kategori)
- Payment Method
- Charge Types
- Reports (semua laporan)
- Settings (pengaturan hotel)

## 12.2 Front Office

- Dashboard
- Data Tamu
- Reservasi
- Check-in
- Checkout
- Status Kamar
- Cleaning Request
- Laundry Request
- FnB Order
- Pembayaran
- Invoice

## 12.3 Housekeeping

- Dashboard
- Cleaning Request (terima & proses)
- Room Inspection
- Laundry Request
- Update Room Status
- Cleaning History
- Inspection History

## 12.4 Food and Beverage

- Dashboard
- Incoming Orders
- Process Orders
- Order History

---

# 13. Menu Laporan

## 13.1 Laporan Reservasi

Berisi:

- Daftar booking beserta booking number
- Tanggal reservasi
- Nama tamu
- Kamar yang dipesan
- Status reservasi
- Total biaya

## 13.2 Laporan Occupancy

Berisi:

- Tingkat aktivitas kamar (harian / periodik)
- Jumlah kamar per status (available, reserved, occupied, dirty, maintenance)
- Occupancy rate (%)

## 13.3 Laporan FnB

Berisi:

- Daftar order makanan dan minuman
- Jumlah order per menu
- Total pendapatan FnB
- Menu terlaris
- Status order (pending, preparing, delivered, cancelled)

## 13.4 Laporan Pendapatan

Berisi:

- Total revenue hotel (keseluruhan)
- Revenue dari room charge
- Revenue extra bed
- Revenue laundry
- Revenue FnB
- Revenue charge kerusakan kamar

Pendapatan per jenis kamar:

- Standard
- Deluxe
- Superior
- Studio
- Suite
- Connecting

## 13.5 Laporan Summary

Berisi ringkasan operasional hotel:

- Total reservasi
- Total check-in
- Total checkout
- Occupancy rate
- Total pendapatan
- Total transaksi FnB
- Total request laundry
- Total charge tambahan
- Total invoice paid / unpaid

---

# 14. Database / Tabel

---

## 14.1 Authentication dan Akses

### `users`

Data staff hotel untuk login sistem.

| Field      | Tipe           | Keterangan        |
| ---------- | -------------- | ----------------- |
| id         | bigint PK      | Primary key       |
| name       | varchar        | Nama staff        |
| email      | varchar unique | Email login       |
| password   | varchar        | Password (hashed) |
| role_id    | bigint FK      | Relasi ke roles   |
| status     | enum           | active / inactive |
| created_at | timestamp      |                   |
| updated_at | timestamp      |                   |

---

### `roles`

Daftar role yang tersedia.

Nilai: `admin`, `front_office`, `housekeeping`, `fnb`

---

### `permissions`

Daftar izin akses spesifik ke tiap fitur/menu.

---

### `role_permissions`

Relasi many-to-many antara role dan permission.

| Field         | Tipe      |
| ------------- | --------- |
| role_id       | bigint FK |
| permission_id | bigint FK |

---

## 14.2 Master Data

### `guests`

Data tamu hotel.

| Field       | Tipe      | Keterangan          |
| ----------- | --------- | ------------------- |
| id          | bigint PK |                     |
| full_name   | varchar   | Nama lengkap        |
| phone       | varchar   | Nomor telepon       |
| email       | varchar   | Email tamu          |
| address     | text      | Alamat              |
| id_number   | varchar   | Nomor KTP / Paspor  |
| nationality | varchar   | Kewarganegaraan     |
| gender      | enum      | male / female       |
| notes       | text      | Catatan khusus tamu |

---

### `room_types`

Data tipe / kategori kamar.

| Field              | Tipe      | Keterangan                  |
| ------------------ | --------- | --------------------------- |
| id                 | bigint PK |                             |
| name               | varchar   | Nama tipe kamar             |
| description        | text      | Deskripsi                   |
| base_price         | decimal   | Harga dasar per malam       |
| capacity           | int       | Kapasitas orang             |
| breakfast_included | boolean   | Apakah breakfast included   |
| breakfast_price    | decimal   | Harga breakfast jika add-on |
| extra_bed_allowed  | boolean   | Boleh extra bed atau tidak  |
| extra_bed_price    | decimal   | Harga extra bed             |
| is_active          | boolean   | Status aktif/nonaktif       |

---

### `rooms`

Data kamar fisik.

| Field        | Tipe      | Keterangan                     |
| ------------ | --------- | ------------------------------ |
| id           | bigint PK |                                |
| room_number  | varchar   | Nomor kamar (contoh: 101)      |
| room_type_id | bigint FK | Relasi ke room_types           |
| floor        | int       | Lantai                         |
| status       | enum      | Status kamar (lihat section 8) |
| notes        | text      | Catatan kamar                  |
| is_active    | boolean   | Status aktif/nonaktif          |

**Status enum:** `available`, `reserved`, `occupied`, `dirty`, `cleaning`, `inspected`, `maintenance`, `out_of_order`

---

### `food_categories`

Kategori menu FnB.

Contoh: `makanan`, `minuman`, `snack`, `dessert`

---

### `food_items`

Daftar menu makanan dan minuman.

| Field            | Tipe      | Keterangan                |
| ---------------- | --------- | ------------------------- |
| id               | bigint PK |                           |
| food_category_id | bigint FK | Relasi ke food_categories |
| name             | varchar   | Nama menu                 |
| price            | decimal   | Harga                     |
| description      | text      | Deskripsi menu            |
| is_available     | boolean   | Tersedia atau tidak       |

---

### `payment_methods`

Metode pembayaran yang aktif.

Contoh: `cash`, `transfer_bank`, `qris`, `credit_card`

---

### `charge_types`

Jenis charge tambahan di luar harga kamar.

Contoh: `extra_bed`, `damage`, `laundry`, `minibar`, `late_checkout`, `fnb`

---

### `hotel_settings`

Pengaturan umum sistem hotel.

| Field               | Keterangan                                   |
| ------------------- | -------------------------------------------- |
| name                | Nama hotel                                   |
| address             | Alamat hotel                                 |
| phone               | Nomor telepon hotel                          |
| tax_rate            | Persentase pajak (%)                         |
| service_charge_rate | Persentase service charge (%)                |
| breakfast_threshold | Harga minimum kamar untuk breakfast included |
| invoice_prefix      | Prefix invoice number (default: INV)         |
| booking_prefix      | Prefix booking number (default: BK)          |

---

## 14.3 Transaksi Utama

### `reservations`

Data reservasi tamu.

| Field            | Tipe           | Keterangan                            |
| ---------------- | -------------- | ------------------------------------- |
| id               | bigint PK      |                                       |
| reservation_code | varchar unique | Booking number (BK-101-20260616-0001) |
| guest_id         | bigint FK      | Relasi ke guests                      |
| room_id          | bigint FK      | Relasi ke rooms                       |
| checkin_date     | date           | Tanggal check-in                      |
| checkout_date    | date           | Tanggal checkout                      |
| adults           | int            | Jumlah tamu dewasa                    |
| children         | int            | Jumlah anak-anak                      |
| status           | enum           | Status reservasi                      |
| subtotal         | decimal        | Total sebelum pajak                   |
| discount         | decimal        | Diskon                                |
| tax              | decimal        | Pajak                                 |
| service_charge   | decimal        | Service charge                        |
| total            | decimal        | Total akhir                           |
| created_by       | bigint FK      | User (FO) yang membuat                |
| created_at       | timestamp      |                                       |

**Status enum:** `pending`, `confirmed`, `checked_in`, `checked_out`, `cancelled`

---

### `reservation_details`

Detail tambahan per reservasi.

| Field          | Tipe      | Keterangan                                  |
| -------------- | --------- | ------------------------------------------- |
| id             | bigint PK |                                             |
| reservation_id | bigint FK |                                             |
| type           | enum      | `extra_bed`, `breakfast`, `special_request` |
| qty            | int       | Jumlah                                      |
| price          | decimal   | Harga satuan                                |
| notes          | text      | Catatan                                     |

---

### `checkins`

Record proses check-in.

| Field           | Tipe      | Keterangan              |
| --------------- | --------- | ----------------------- |
| id              | bigint PK |                         |
| reservation_id  | bigint FK |                         |
| checked_in_at   | datetime  | Waktu check-in aktual   |
| front_office_id | bigint FK | Staff FO yang memproses |
| notes           | text      | Catatan check-in        |

---

### `checkouts`

Record proses checkout.

| Field            | Tipe      | Keterangan              |
| ---------------- | --------- | ----------------------- |
| id               | bigint PK |                         |
| reservation_id   | bigint FK |                         |
| checked_out_at   | datetime  | Waktu checkout aktual   |
| front_office_id  | bigint FK | Staff FO yang memproses |
| notes            | text      | Catatan checkout        |
| final_bill_total | decimal   | Total tagihan final     |

---

### `invoices`

Data invoice per reservasi.

| Field          | Tipe           | Keterangan                        |
| -------------- | -------------- | --------------------------------- |
| id             | bigint PK      |                                   |
| invoice_number | varchar unique | Nomor invoice (INV-YYYYMMDD-XXXX) |
| reservation_id | bigint FK      |                                   |
| invoice_date   | date           | Tanggal invoice                   |
| subtotal       | decimal        | Total sebelum pajak dan charge    |
| tax            | decimal        | Pajak                             |
| service_charge | decimal        | Service charge                    |
| discount       | decimal        | Diskon                            |
| total_amount   | decimal        | Total akhir                       |
| paid_amount    | decimal        | Total yang sudah dibayar          |
| balance_due    | decimal        | Sisa tagihan                      |
| status         | enum           | Status pembayaran                 |

**Status enum:** `unpaid`, `partial`, `paid`, `refunded`

---

### `payments`

Record setiap pembayaran yang dilakukan.

| Field             | Tipe      | Keterangan                                 |
| ----------------- | --------- | ------------------------------------------ |
| id                | bigint PK |                                            |
| invoice_id        | bigint FK |                                            |
| payment_method_id | bigint FK |                                            |
| payment_date      | datetime  | Waktu pembayaran                           |
| amount            | decimal   | Nominal yang dibayar                       |
| reference_number  | varchar   | Nomor referensi transfer / approval        |
| notes             | text      | Catatan                                    |
| status            | enum      | `success`, `pending`, `failed`, `refunded` |
| created_by        | bigint FK | Staff yang mencatat                        |

---

### `charges`

Catatan charge tambahan per reservasi.

| Field          | Tipe      | Keterangan          |
| -------------- | --------- | ------------------- |
| id             | bigint PK |                     |
| reservation_id | bigint FK |                     |
| charge_type_id | bigint FK |                     |
| amount         | decimal   | Nominal charge      |
| description    | text      | Deskripsi detail    |
| created_by     | bigint FK | Staff yang mencatat |
| created_at     | timestamp |                     |

---

## 14.4 Housekeeping

### `housekeeping_requests`

Request cleaning dan perawatan kamar.

| Field          | Tipe      | Keterangan                                     |
| -------------- | --------- | ---------------------------------------------- |
| id             | bigint PK |                                                |
| reservation_id | bigint FK | Reservasi terkait (nullable untuk maintenance) |
| room_id        | bigint FK | Kamar yang diminta                             |
| requested_by   | bigint FK | User (FO) yang membuat request                 |
| assigned_to    | bigint FK | Staff HK yang ditugaskan                       |
| request_type   | enum      | Jenis request (lihat section 10)               |
| priority       | enum      | `low`, `normal`, `high`, `urgent`              |
| status         | enum      | Status request (lihat section 10)              |
| request_time   | datetime  | Waktu request dibuat                           |
| completed_time | datetime  | Waktu request selesai                          |
| notes          | text      | Catatan tambahan                               |

**request_type enum:** `stayover_cleaning`, `checkout_cleaning`, `deep_cleaning`, `maintenance`, `linen_replacement`

**status enum:** `pending`, `assigned`, `in_progress`, `completed`, `cancelled`

---

### `housekeeping_request_items`

Detail item yang perlu ditangani per request.

| Field                   | Tipe      | Keterangan                 |
| ----------------------- | --------- | -------------------------- |
| id                      | bigint PK |                            |
| housekeeping_request_id | bigint FK |                            |
| item_name               | varchar   | Nama item / area           |
| description             | text      | Detail kondisi / instruksi |
| is_done                 | boolean   | Status penyelesaian item   |

---

### `room_inspections`

Hasil inspeksi kamar saat checkout atau pemeriksaan berkala.

| Field           | Tipe      | Keterangan                          |
| --------------- | --------- | ----------------------------------- |
| id              | bigint PK |                                     |
| room_id         | bigint FK |                                     |
| reservation_id  | bigint FK |                                     |
| inspected_by    | bigint FK | Staff HK yang inspeksi              |
| inspection_date | datetime  | Waktu inspeksi                      |
| room_condition  | enum      | `good`, `needs_cleaning`, `damaged` |
| damage_found    | boolean   | Ada kerusakan atau tidak            |
| damage_cost     | decimal   | Estimasi biaya kerusakan            |
| notes           | text      | Catatan inspeksi                    |
| status          | enum      | `pending`, `completed`              |

---

### `room_inspection_items`

Detail temuan per inspeksi kamar.

| Field              | Tipe      | Keterangan                   |
| ------------------ | --------- | ---------------------------- |
| id                 | bigint PK |                              |
| room_inspection_id | bigint FK |                              |
| item_name          | varchar   | Nama item yang diperiksa     |
| condition          | enum      | `good`, `damaged`, `missing` |
| charge_amount      | decimal   | Biaya kerusakan / kehilangan |
| notes              | text      | Keterangan detail            |

---

## 14.5 Laundry

### `laundry_requests`

Request laundry dari tamu.

| Field          | Tipe      | Keterangan                   |
| -------------- | --------- | ---------------------------- |
| id             | bigint PK |                              |
| reservation_id | bigint FK |                              |
| guest_id       | bigint FK |                              |
| requested_by   | bigint FK | Staff FO yang input          |
| handled_by     | bigint FK | Staff HK yang menangani      |
| request_date   | datetime  | Waktu request                |
| status         | enum      | Status request               |
| notes          | text      | Catatan (jenis pakaian, dsb) |
| total_charge   | decimal   | Total biaya laundry          |

**status enum:** `requested`, `picked_up`, `processing`, `ready`, `delivered`, `cancelled`

---

## 14.6 Food and Beverage

### `fnb_orders`

Order makanan dan minuman dari tamu.

| Field          | Tipe      | Keterangan            |
| -------------- | --------- | --------------------- |
| id             | bigint PK |                       |
| reservation_id | bigint FK |                       |
| guest_id       | bigint FK |                       |
| requested_by   | bigint FK | Staff FO yang input   |
| handled_by     | bigint FK | Staff FnB yang proses |
| order_time     | datetime  | Waktu order           |
| status         | enum      | Status order          |
| total_price    | decimal   | Total harga           |
| notes          | text      | Catatan khusus        |

**status enum:** `pending`, `confirmed`, `preparing`, `delivered`, `cancelled`

---

### `fnb_order_items`

Detail item per order FnB.

| Field        | Tipe      | Keterangan                      |
| ------------ | --------- | ------------------------------- |
| id           | bigint PK |                                 |
| fnb_order_id | bigint FK |                                 |
| food_item_id | bigint FK |                                 |
| qty          | int       | Jumlah                          |
| price        | decimal   | Harga satuan saat order         |
| subtotal     | decimal   | Subtotal item                   |
| notes        | text      | Catatan item (tidak pedas, dsb) |

---

## 14.7 Logging

### `activity_logs`

Log seluruh aktivitas user untuk keperluan audit.

| Field       | Tipe      | Keterangan                              |
| ----------- | --------- | --------------------------------------- |
| id          | bigint PK |                                         |
| user_id     | bigint FK | User yang melakukan aksi                |
| action      | varchar   | Nama aksi (create, update, delete, dll) |
| module      | varchar   | Modul / tabel yang diubah               |
| description | text      | Detail aksi                             |
| ip_address  | varchar   | IP address user                         |
| created_at  | timestamp | Waktu aksi                              |

---

# 15. ERD Konseptual

Relasi lengkap antar tabel:

```
Authentication:
roles              1..n  users

Master Data:
room_types         1..n  rooms
food_categories    1..n  food_items
payment_methods    1..n  payments

Reservasi:
guests             1..n  reservations
rooms              1..n  reservations
users              1..n  reservations (created_by)
reservations       1..n  reservation_details
reservations       1..1  checkins
reservations       1..1  checkouts
reservations       1..1  invoices

Housekeeping:
reservations       1..n  housekeeping_requests
rooms              1..n  housekeeping_requests
housekeeping_requests 1..n housekeeping_request_items
reservations       1..n  room_inspections
rooms              1..n  room_inspections
room_inspections   1..n  room_inspection_items

Laundry:
reservations       1..n  laundry_requests

FnB:
reservations       1..n  fnb_orders
fnb_orders         1..n  fnb_order_items
food_items         1..n  fnb_order_items

Charges:
charge_types       1..n  charges
reservations       1..n  charges

Pembayaran:
invoices           1..n  payments
payment_methods    1..n  payments

Logging:
users              1..n  activity_logs
```

---

# 16. Tech Stack

| Komponen             | Teknologi                    |
| -------------------- | ---------------------------- |
| **Backend**          | Laravel 13                   |
| **Database**         | MySQL                        |
| **Frontend Styling** | Tailwind CSS                 |
| **Authentication**   | Laravel Breeze               |
| **Print Invoice**    | Browser print / PDF via HTML |

---

# 17. Catatan Implementasi

### Backend (Laravel)

- Gunakan migration untuk semua tabel (urutan migration harus mengikuti relasi FK)
- Gunakan enum atau constant class untuk semua nilai status
- Definisikan relasi Eloquent secara jelas di tiap Model
- Gunakan Policy dan/atau Middleware untuk access control per role
- Gunakan Observer atau Event/Listener untuk otomasi (contoh: perubahan status kamar saat checkout)
- Gunakan Database Transaction untuk operasi yang melibatkan banyak tabel (checkout, pembayaran)

### Frontend (Tailwind CSS)

- Gunakan layout terpisah per role (admin layout, fo layout, hk layout, fnb layout)
- Gunakan tabel data dengan pagination untuk daftar reservasi, order, dan laporan
- Gunakan modal form untuk input yang tidak perlu halaman baru
- Gunakan badge / label berwarna untuk menampilkan status (kamar, reservasi, order)
- Gunakan filter tanggal pada seluruh halaman laporan
- Gunakan komponen invoice yang print-friendly (CSS print media query)

### Operasional / Business Rules

- Status kamar **harus selalu sinkron** dengan kondisi aktual
- Checkout tidak langsung membuat kamar `available` — harus melalui inspeksi dan cleaning
- **Semua charge tambahan** (laundry, FnB, kerusakan, extra bed) harus masuk invoice sebelum checkout dikonfirmasi
- Partial payment harus mencatat sisa tagihan (`balance_due`) secara akurat
- Booking number dan invoice number harus di-generate otomatis oleh sistem

---

# 18. Rekomendasi Pengembangan Lanjutan

Fitur-fitur berikut direkomendasikan untuk pengembangan selanjutnya:

1. **Tax dan Service Charge** — Konfigurasi persentase dari hotel_settings, dihitung otomatis di invoice
2. **Cancellation Policy** — Aturan pembatalan dan biaya denda
3. **Late Check-out / Early Check-in** — Charge tambahan yang dikonfigurasi admin
4. **Notification Internal** — Notifikasi antar divisi saat request baru masuk (HK, FnB)
5. **Export Laporan PDF / Excel** — Untuk kebutuhan management dan arsip
6. **Print Invoice** — Langsung dari browser dengan layout yang rapi
7. **Guest History** — Profil tamu beserta riwayat menginap dan preferensi
8. **Room Maintenance Management** — Modul lengkap untuk penjadwalan maintenance berkala
9. **Booking Status Lifecycle yang Jelas** — Diagram alur status dan validasi transisi
10. **Daily Housekeeping Report** — Laporan otomatis cleaning dan inspeksi per hari
11. **Auto Occupancy Calculation** — Kalkulasi occupancy rate harian dan bulanan otomatis
12. **Dashboard Analytics Real-time** — Grafik pendapatan, occupancy, dan top menu FnB
13. **Multi-room Booking** — Satu tamu dapat memesan lebih dari satu kamar dalam satu reservasi
14. **Audit Trail** — Detail lengkap perubahan data siapa, kapan, dari nilai apa ke nilai apa
15. **Laundry Item Detail** — Tabel `laundry_request_items` untuk mencatat jenis dan jumlah item laundry

---

# 19. Kesimpulan

Hotel Management System — Hotel Kejora dirancang untuk mengintegrasikan seluruh operasional hotel ke dalam satu sistem terpusat. Fokus utama sistem:

- **Efisiensi kerja staf** — setiap divisi bekerja dari sistem yang sama
- **Keteraturan data** — semua transaksi dan request tercatat dan terdokumentasi
- **Transparansi pembayaran** — invoice jelas, histori pembayaran terlacak
- **Kontrol status kamar** — status real-time yang selalu sinkron antar divisi
- **Laporan yang akurat** — data operasional dan pendapatan tersedia kapan saja
- **Invoice siap cetak** — invoice lengkap dan profesional

Dengan struktur PRD dan ERD ini, sistem siap dikembangkan menggunakan Laravel 13 sebagai webapps internal hotel yang profesional, terstruktur, dan scalable.

---

_Dokumen ini merupakan hasil merge dari PRD_ERD v1 dan v2. Seluruh konten terbaik dari kedua versi telah digabungkan dan distrukturkan ulang._
