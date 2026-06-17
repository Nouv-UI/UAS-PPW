<?php
// includes/footer.php
?>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container" style="max-width: 1200px;">
            <div class="row g-4 mb-5">
                <!-- Col 1: Brand Info -->
                <div class="col-md-4">
                    <h5 class="text-uppercase tracking-wider" style="color: #FFF9DB;">Jajan Pasar An-NaHL</h5>
                    <p style="color: #D1D5DB; font-size: 0.9rem; line-height: 1.6;">
                        Menyajikan kelezatan cita rasa tradisional Indonesia dengan kualitas terbaik dan higienis. Pilihan utama untuk kebutuhan arisan, hajatan, rapat, dan camilan harian Anda.
                    </p>
                </div>
                <!-- Col 2: Navigation Links -->
                <div class="col-md-4">
                    <h5 style="color: #FFF9DB;">Navigasi</h5>
                    <ul class="list-unstyled d-flex flex-column gap-2" style="font-size: 0.95rem;">
                        <li><a href="/jp-annahls/index.php"><i class="bi bi-chevron-right me-1"></i> Beranda</a></li>
                        <li><a href="/jp-annahls/pages/katalog.php"><i class="bi bi-chevron-right me-1"></i> Katalog Produk</a></li>
                        <li><a href="/jp-annahls/pages/cart.php"><i class="bi bi-chevron-right me-1"></i> Keranjang Belanja</a></li>
                        <li><a href="/jp-annahls/pages/profile.php"><i class="bi bi-chevron-right me-1"></i> Profil Saya</a></li>
                    </ul>
                </div>
                <!-- Col 3: Outlined Accent Social Buttons -->
                <div class="col-md-4">
                    <h5 style="color: #FFF9DB;">Hubungi & Sosial Media</h5>
                    <p style="color: #D1D5DB; font-size: 0.9rem;" class="mb-3">
                        <i class="bi bi-geo-alt me-2"></i> Nogosaren, Sleman, D.I. Yogyakarta
                    </p>
                    <div class="d-flex flex-column gap-2" style="max-width: 250px;">
                        <a href="https://wa.me/6287738473677" target="_blank" class="btn btn-accent-outline text-start">
                            <i class="bi bi-whatsapp me-2"></i> Hubungi WhatsApp
                        </a>
                        <a href="https://instagram.com/jp_annahl" target="_blank" class="btn btn-accent-outline text-start">
                            <i class="bi bi-instagram me-2"></i> Instagram @jp_annahl
                        </a>
                        <button onclick="window.scrollTo({top: 0, behavior: 'smooth'});" class="btn btn-accent-outline text-start">
                            <i class="bi bi-arrow-up-circle me-2"></i> Kembali Ke Atas
                        </button>
                    </div>
                </div>
            </div>
            <hr style="border-color: rgba(255, 255, 255, 0.1);">
            <div class="text-center mt-4" style="color: #9CA3AF; font-size: 0.85rem;">
                <p class="mb-0">&copy; <?= date('Y') ?> Jajan Pasar An-NaHL. Proyek Pemrograman Web Akademik.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="/jp-annahls/assets/js/main.js"></script>
</body>
</html>
