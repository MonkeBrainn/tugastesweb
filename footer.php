</div> <!-- Close main-content div -->

    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="newsletter-container">
            <h2>Berlangganan Newsletter</h2>
            <p>Dapatkan informasi terbaru tentang produk dan promosi spesial!</p>
            <form class="newsletter-form">
                <input type="email" placeholder="Email Anda" required>
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </section>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Tentang Kami</h3>
                <p>Toko Online menyediakan berbagai produk berkualitas tinggi dengan harga terjangkau. Kepuasan pelanggan adalah prioritas utama kami.</p>
                <div class="social-links">
                    <a href="#"><i class="fa fa-facebook"></i></a>
                    <a href="#"><i class="fa fa-twitter"></i></a>
                    <a href="#"><i class="fa fa-instagram"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Kategori</h3>
                <ul class="footer-links">
                    <li><a href="#">Elektronik</a></li>
                    <li><a href="#">Fashion</a></li>
                    <li><a href="#">Kesehatan</a></li>
                    <li><a href="#">Rumah Tangga</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Informasi</h3>
                <ul class="footer-links">
                    <li><a href="#">Cara Pembelian</a></li>
                    <li><a href="#">Pengiriman</a></li>
                    <li><a href="#">Kebijakan Privasi</a></li>
                    <li><a href="#">Syarat dan Ketentuan</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Kontak</h3>
                <p>Email: info@tokoonline.com</p>
                <p>Telepon: +62 123 4567 890</p>
                <p>Alamat: Jl. Contoh No. 123, Jakarta, Indonesia</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 Toko Online. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- JavaScript for interactive elements -->
    <script>
        // Add scroll effect to header
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>