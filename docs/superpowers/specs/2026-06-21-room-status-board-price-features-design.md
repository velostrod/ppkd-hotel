# Room Status Board — Harga & Fitur Kamar

**Date:** 2026-06-21  
**Status:** Approved

## Ringkasan

Tambahkan tampilan harga dasar dan icon badges fitur kamar (kapasitas, breakfast, extra bed) ke setiap kartu kamar di Visual Peta Kamar pada FO Dashboard dan Admin Dashboard.

## Scope

Role yang melihat perubahan: Front Office, Admin, Manager.

### FO Dashboard (`dashboard/fo.blade.php`)

Sudah memiliki Visual Peta Kamar. Hanya perlu tambah harga dan icon badges ke kartu yang sudah ada. Tidak ada perubahan controller.

### Admin Dashboard (`dashboard/admin.blade.php` + `DashboardController::adminDashboard()`)

Saat ini **tidak memiliki** Visual Peta Kamar — hanya menampilkan aggregate stats dan activity log. Perlu dua perubahan:

1. **Controller** — `adminDashboard()` tambah query `$rooms` dengan eager load `roomType` dan reservasi aktif (identik dengan query di `foDashboard()`), lalu pass ke view.
2. **View** — Tambah section baru "Visual Peta Kamar" di bawah existing content, menggunakan grid kartu yang sama dengan FO (termasuk harga dan icon badges).

Manager mengakses via Admin Dashboard (routing sesuai role yang ada).

## Data Layer

Tidak ada perubahan model atau migration. Semua data yang dibutuhkan sudah ada di tabel `room_types`:

| Field                | Digunakan untuk               |
| -------------------- | ----------------------------- |
| `base_price`         | Tampilan harga dasar          |
| `capacity`           | Badge kapasitas               |
| `breakfast_included` | Badge breakfast (kondisional) |
| `extra_bed_allowed`  | Badge extra bed (kondisional) |

`DashboardController` harus memastikan eager load `roomType` tersedia saat mengambil data rooms agar tidak terjadi N+1 query.

## UI: Perubahan Kartu Kamar

Dua elemen ditambahkan di setiap kartu, di bawah nama room type dan info lantai:

### 1. Harga Dasar

Satu baris teks kecil menampilkan harga per malam dalam format Rupiah:

```
Rp 500.000 / malam
```

### 2. Icon Badges dengan CSS Tooltip

Row icon berisi 3 badge:

| Icon                   | Kondisi tampil              | Tooltip                |
| ---------------------- | --------------------------- | ---------------------- |
| Icon orang (kapasitas) | Selalu                      | `"Kapasitas: X orang"` |
| Icon sarapan           | `breakfast_included = true` | `"Breakfast Included"` |
| Icon extra bed         | `extra_bed_allowed = true`  | `"Extra Bed Tersedia"` |

Implementasi tooltip menggunakan Tailwind CSS `group` / `group-hover` (pure CSS, tanpa JS). Tooltip muncul saat hover, posisi `bottom-full` (di atas icon), background gelap (`bg-slate-800`), teks putih kecil.

## Pendekatan Teknis

**Opsi A: CSS-only Tooltip dengan Tailwind `group`** — dipilih karena:

- Zero JS overhead
- Konsisten dengan stack Tailwind yang sudah ada
- Cepat diimplementasi
- Cukup untuk internal tool

Format harga menggunakan `number_format()` PHP atau helper `CurrencyHelper` yang sudah ada di project.

## Yang Tidak Diubah

- Struktur kartu kamar (nomor, tipe, lantai, info tamu, tombol aksi) tidak berubah
- Tidak ada perubahan routing atau controller logic
- Tidak ada perubahan model atau database
