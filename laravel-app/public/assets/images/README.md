# Struktur Gambar UI Storefront

Folder ini dipakai untuk semua gambar display pada halaman utama.

## Struktur

- `assets/images/display/`
- `assets/images/objects/categories/`
- `assets/images/objects/products/`
- `assets/images/objects/builds/`

## Penempatan Gambar Hero / Banner

Taruh gambar berikut di folder `display`:

- `hero-main.jpg` -> banner utama atas
- `hero-side-1.jpg` -> banner sisi kanan atas
- `hero-side-2.jpg` -> banner sisi kanan bawah
- `weekly-deal.jpg` -> banner promo mingguan

## Penempatan Gambar Kategori

Taruh ikon kategori di folder `objects/categories`:

- `phone-tablet.png`
- `laptop.png`
- `pc-aio.png`
- `smartwatch.png`
- `printer.png`
- `console.png`
- `network.png`
- `tv.png`

## Penempatan Gambar Produk dan Build

Gambar produk/build bisa dibaca dari:

1. Kolom `image_url` di database (prioritas utama)
2. Fallback lokal berdasarkan nama (slug)

Contoh fallback lokal:

- Produk `Intel Core i9-14900K` -> `assets/images/objects/products/intel-core-i9-14900k.jpg`
- Build `Gaming Build Pro 4K` -> `assets/images/objects/builds/gaming-build-pro-4k.jpg`

## Catatan

- Disarankan rasio gambar produk/build: `4:3`.
- Format yang digunakan frontend saat ini: `.jpg` untuk produk/build dan `.png` untuk ikon kategori.
- Jika gambar tidak ditemukan, UI otomatis menampilkan placeholder.
