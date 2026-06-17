
> # Design System: Jajan Pasar An-NaHL

## 1. Core Philosophy
Sistem desain ini mengusung tema natural, hangat, dan terpercaya. Tampilan utamanya memadukan warna krem/kuning pucat yang merepresentasikan Jajanan pasar dengan sentuhan modern, dengan aksen teks dan tombol berwarna merah marun gelap (earthy). Bagian footer menggunakan warna gelap yang kontras untuk memberikan kesan profesional dan solid.

## 2. Color Palette

### Brand Colors
- **Primary Brand (Text/Headings/Solid Button):** `#7A1A1A` (Merah Marun / Dark Red)
- **Background (Main Layout):** `#FEFBF4` (Krem / Warm Off-White)
- **Footer Background:** `#1B2433` (Biru Dongker Gelap / Dark Navy)

### Accent Colors
- **Article Tag (Kategori):** `#7A1A1A` (Merah Marun / Dark Red, digunakan untuk stroke (tanpa fill) pada label "Tips" dan "Edukasi")
- **Footer Outlined Buttons:** `#064E3B` (Hijau Teal tua, digunakan pada tombol WhatsApp, Instagram, dan Back to Top)

### Neutral Colors
- **Text Body:** `#333333` (Dark Gray)
- **Card Background:** `#FFFFFF` (White)
- **Product Image Background:** `#E5E7EB` (Light Gray, warna latar untuk area foto di dalam kartu produk)

## 3. Typography
- **Headings (H1, H2, H3):** Menggunakan font Sans-serif yang bersih, modern, dan sedikit membulat (rekomendasi: *Inter*, *Poppins*, atau *Montserrat*). Digunakan dominan dengan *weight* Bold (700) atau Semi-Bold (600).
- **Body Text:** Menggunakan font Sans-serif yang sangat *legible* untuk paragraf panjang (rekomendasi: *Inter*, *Poppins*, atau *Montserrat*). Digunakan dengan *weight* Regular (400) atau Medium (500).

## 4. Components & Styling

### Header
- **Logo:** Berupa logomark (gambar tanpa tulisan) dengan pembungkus lingkaran dengan panjang dan tingginya 85px. Letaknya di samping kiri navigation bar.

- **Navigation bar:**
	- **Style:** Floating / melayang (tidak menempel di sudut atas layar).
	- **Shape:** Pill-shaped (Border radius: 9999px).
	- **Background:** `#FFF9DB` dengan bayangan *subtle* (`box-shadow: 0 4px 10px rgba(0,0,0,0.05)`).
	- **Tinggi:** menyesuaikan tinggi dari logo di sebelahnya
	- **Text:** Berwarna `#7A1A1A`.
	- **Icon:** Berwarna  `#7A1A1A` dengan style stroke

### Buttons
- **Primary Button (Solid - CTA Utama):**
  - Background: `#7A1A1A`
  - Text Color: `#FFFFFF`
  - Border Radius: Pill-shaped (9999px).
- **Secondary Button (Outline):**
  - Background: Transparent
  - Border: 1px solid `#7A1A1A`
  - Text Color: `#7A1A1A`
  - Border Radius: Pill-shaped (9999px).
- **Tertiary Button (Secondary CTA):**
  - Background: Transparent
  - Border: 1px solid `#7A1A1A`
  - Text Color: `#7A1A1A`
  - Border Radius: 10px (sudut membulat kecil).

### Cards (Product, Features, Articles)
- **Background:** `#FFFFFF`
- **Border Radius:** 20px.
- **Shadow:** Lembut dan menyebar di sekeliling kartu (`box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03)`).
- **Image Border Radius (dalam card):** Membulat di bagian atas (menyesuaikan bentuk *container* kartu).

### Outlined Containers (Seperti pada halaman About)
- **Style:** Kotak dengan garis tepi murni tanpa *background* pekat.
- **Border:** 1px solid `#7A1A1A`.
- **Border Radius:** 40px.

### Footer
- **Background:** `#1B2433`
- **Text Color:** `#FFFFFF` (atau `#D1D5DB` untuk teks sekunder/deskripsi).
- **Divider:** Tidak menggunakan garis keras, pemisahan hierarki mengandalkan *white-space* / *padding* yang lega antar kolom.
