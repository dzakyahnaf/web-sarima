<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wana Wisata Kampoeng Ciherang - Bunihayu Forest</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|outfit:500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Style Override -->
    <style>
        :root {
            --primary: #2d6a4f;
            --primary-dark: #1b4332;
            --primary-light: #40916c;
            --secondary: #d8f3dc;
            --accent: #9ef01a;
            --text-main: #2d3e33;
            --text-muted: #52665a;
            --bg-light: #f8faf9;
        }
        
        * {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            background-color: var(--bg-light);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 5%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-main);
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.2s ease;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .btn-action {
            padding: 0.6rem 1.5rem;
            border-radius: 9999px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .btn-login {
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-register {
            background-color: var(--primary);
            color: white !important;
            box-shadow: 0 4px 12px rgba(45, 106, 79, 0.2);
        }

        .hero {
            position: relative;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0 5%;
            background: linear-gradient(rgba(27, 67, 50, 0.6), rgba(27, 67, 50, 0.3)), url('https://images.unsplash.com/photo-1542273917363-3b1817f69a2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80') center/cover no-repeat fixed;
        }

        .hero-content {
            max-width: 900px;
            color: white;
            z-index: 10;
        }

        .hero-content h1 {
            font-size: 4rem;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            font-weight: 800;
        }

        .hero-content p {
            font-size: 1.35rem;
            margin-bottom: 2.5rem;
            opacity: 0.95;
            font-weight: 400;
        }

        .btn-primary {
            display: inline-block;
            background-color: var(--accent);
            color: #1a2e05;
            padding: 1rem 2.5rem;
            border-radius: 9999px;
            font-size: 1.125rem;
            font-weight: 800;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(158, 240, 26, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(158, 240, 26, 0.4);
            filter: brightness(1.1);
        }

        section {
            padding: 8rem 5%;
        }

        .section-header {
            text-align: center;
            margin-bottom: 5rem;
        }

        .section-header span {
            color: var(--primary);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.85rem;
            margin-bottom: 1rem;
            display: block;
        }

        .section-header h2 {
            font-size: 3rem;
            color: var(--primary-dark);
            margin-bottom: 1.5rem;
        }

        .section-header p {
            color: var(--text-muted);
            font-size: 1.15rem;
            max-width: 700px;
            margin: 0 auto;
        }

        /* Facilities Experience Grid */
        .facility-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .facility-card {
            background: white;
            border-radius: 2rem;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            transition: all 0.4s ease;
            border: 1px solid rgba(0,0,0,0.02);
            position: relative;
        }

        .facility-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.1);
        }

        .facility-img {
            height: 250px;
            width: 100%;
            object-fit: cover;
        }

        .facility-info {
            padding: 2.5rem;
        }

        .facility-info h3 {
            font-size: 1.6rem;
            margin-bottom: 1rem;
            color: var(--primary-dark);
        }

        .facility-info p {
            color: var(--text-muted);
            line-height: 1.7;
            font-size: 1rem;
        }

        /* Basic Features Bar */
        .basic-features {
            background: white;
            padding: 3rem 5%;
            margin: 4rem auto 0;
            max-width: 1000px;
            border-radius: 2rem;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.02);
        }

        .basic-item {
            text-align: center;
        }

        .basic-item i {
            display: block;
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 0.75rem;
        }

        .basic-item span {
            font-weight: 600;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* Location Section */
        .location-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            max-width: 1200px;
            margin: 0 auto;
            align-items: center;
        }

        .map-wrapper {
            border-radius: 2rem;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            height: 450px;
        }

        .location-info h3 {
            font-size: 2.25rem;
            color: var(--primary-dark);
            margin-bottom: 1.5rem;
        }

        .location-info p {
            font-size: 1.1rem;
            color: var(--text-muted);
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .address-card {
            background: white;
            padding: 2rem;
            border-radius: 1.5rem;
            border-left: 5px solid var(--primary);
            box-shadow: 0 5px 15px rgba(0,0,0,0.02);
        }

        .address-card div {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .address-card i {
            color: var(--primary);
            margin-top: 5px;
        }

        /* Animations */
        [data-aos] {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }

        [data-aos].aos-animate {
            opacity: 1;
            transform: translateY(0);
        }

        footer {
            background: var(--primary-dark);
            color: white;
            padding: 5rem 5% 2rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 4rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-logo h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--accent);
        }

        .footer-links h4 {
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        .footer-links ul {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.75rem;
        }

        .footer-links a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: var(--accent);
        }

        .copyright {
            text-align: center;
            margin-top: 5rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.5);
            font-size: 0.9rem;
        }

        @media (max-width: 992px) {
            .location-container {
                grid-template-columns: 1fr;
            }
            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
            .hero-content h1 {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="/" class="navbar-brand">
            <i class="fas fa-tree text-primary"></i>
            Bunihayu Forest
        </a>
        <div class="nav-links">
            <a href="#tentang">Tentang</a>
            <a href="#fasilitas">Fasilitas</a>
            <a href="#lokasi">Lokasi</a>
            
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-action btn-register">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-action btn-login">Masuk</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-action btn-register">Daftar</a>
                    @endif
                @endauth
            @endif
        </div>
    </nav>

    <header class="hero">
        <div class="hero-content">
            <h1>Selamat Datang di Bunihayu Forest</h1>
            <p>Destinasi wisata alam keluarga terbaik di Subang dengan udara segar hutan pinus dan fasilitas rekreasi lengkap.</p>
            <a href="#fasilitas" class="btn-primary">Lihat Fasilitas</a>
        </div>
    </header>

    <section id="tentang">
        <div class="section-header" data-aos>
            <span>Wonderful Indonesia</span>
            <h2>Pesona Alam Bunihayu</h2>
            <p>Terletak di lereng pegunungan Subang, Bunihayu Forest menawarkan harmoni sempurna antara petualangan dan ketenangan untuk Anda dan keluarga.</p>
        </div>
        
        <div class="facility-grid">
            <div class="facility-card" data-aos>
                <img src="https://images.unsplash.com/photo-1549558549-415fe4c37b60?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" class="facility-img" alt="Pine Forest">
                <div class="facility-info">
                    <h3>Hutan Pinus</h3>
                    <p>Kawasan hutan pinus yang rindang sangat cocok untuk berpiknik, berfoto ria, maupun sekadar jalan-jalan santai menghirup udara bersih.</p>
                </div>
            </div>
            <div class="facility-card" data-aos>
                <img src="https://images.unsplash.com/photo-1523438885200-e635ba2c371e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" class="facility-img" alt="Glamping">
                <div class="facility-info">
                    <h3>Amphitheater</h3>
                    <p>Panggung terbuka dengan latar belakang barisan pohon pinus, memberikan pengalaman tak terlupakan untuk acara gathering maupun pertunjukan seni.</p>
                </div>
            </div>
            <div class="facility-card" data-aos>
                <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" class="facility-img" alt="Prediction">
                <div class="facility-info">
                    <h3>Smart Tourism</h3>
                    <p>Dilengkapi sistem prediksi pengunjung berbasis Machine Learning untuk memastikan Anda datang pada waktu ternyaman.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="fasilitas" style="background: #eff5f2;">
        <div class="section-header" data-aos>
            <span>Modern Experience</span>
            <h2>Fasilitas Rekreasi</h2>
            <p>Kami menyediakan berbagai fasilitas unggulan untuk melengkapi kenyamanan liburan Anda di tengah alam terbuka.</p>
        </div>

        <div class="facility-grid">
            <div class="facility-card" data-aos>
                <img src="https://images.unsplash.com/photo-1536431311719-398b6704d4cc?auto=format&fit=crop&w=800&q=80" class="facility-img" alt="Waterfall">
                <div class="facility-info">
                    <h3>Curug Kampoeng Ciherang</h3>
                    <p>Keindahan air terjun alami dengan air yang jernih dan segar, area terbaik untuk merilekskan pikiran dari kepenatan kota.</p>
                </div>
            </div>
            <div class="facility-card" data-aos>
                <img src="https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=800&q=80" class="facility-img" alt="Hot Spring">
                <div class="facility-info">
                    <h3>Kolam Air Hangat</h3>
                    <p>Berendam di kolam air hangat alami di tengah udara gunung yang sejuk memberikan sensasi kesegaran yang luar biasa.</p>
                </div>
            </div>
            <div class="facility-card" data-aos>
                <img src="https://images.unsplash.com/photo-1499696010180-025ef6e1a8f9?auto=format&fit=crop&w=800&q=80" class="facility-img" alt="Camping">
                <div class="facility-info">
                    <h3>Glamping & Camping Ground</h3>
                    <p>Nikmati malam yang syahdu dengan menginap di fasilitas glamping premium atau mendirikan tenda di camping ground yang aman.</p>
                </div>
            </div>
        </div>

        <div class="basic-features" data-aos>
            <div class="basic-item">
                <i class="fas fa-mosque"></i>
                <span>Musholla</span>
            </div>
            <div class="basic-item">
                <i class="fas fa-restroom"></i>
                <span>Toilet Bersih</span>
            </div>
            <div class="basic-item">
                <i class="fas fa-utensils"></i>
                <span>Warung Makan</span>
            </div>
            <div class="basic-item">
                <i class="fas fa-parking"></i>
                <span>Parkir Luas</span>
            </div>
            <div class="basic-item">
                <i class="fas fa-wifi"></i>
                <span>WiFi Corner</span>
            </div>
        </div>
    </section>

    <section id="lokasi">
        <div class="location-container">
            <div class="location-info" data-aos>
                <div class="section-header" style="text-align: left; margin-bottom: 2rem;">
                    <span>Visit Us</span>
                    <h2>Lokasi Kami</h2>
                </div>
                <p>Terletak secara strategis di Kabupaten Subang yang asri, Bunihayu Forest sangat mudah dijangkau baik dengan kendaraan roda dua maupun roda empat.</p>
                
                <div class="address-card">
                    <div>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Ds. Bunihayu, Kec. Jalancagak, Kabupaten Subang, Jawa Barat 41281</span>
                    </div>
                    <div>
                        <i class="fas fa-phone"></i>
                        <span>+62 821-xxxx-xxxx (Informasi & Reservasi)</span>
                    </div>
                    <div>
                        <i class="fas fa-clock"></i>
                        <span>Buka Setiap Hari: 08.00 - 17.00 WIB</span>
                    </div>
                </div>
            </div>
            <div class="map-wrapper" data-aos>
                <!-- Embedded Google Maps -->
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15852.793740266016!2d107.67759600000001!3d-6.622262199999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69225700000001%3A0xe5414f5933f3801e!2sKampoeng%20Ciherang!5e0!3m2!1sid!2sid!4v1713327000000!5m2!1sid!2sid" 
                    width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-grid">
            <div class="footer-logo">
                <h2>Bunihayu Forest</h2>
                <p>Wana Wisata Kampoeng Ciherang Bunihayu adalah bukti harmoni antara pelestarian alam dan pariwisata berkelanjutan.</p>
            </div>
            <div class="footer-links">
                <h4>Navigasi</h4>
                <ul>
                    <li><a href="#tentang">Tentang</a></li>
                    <li><a href="#fasilitas">Fasilitas</a></li>
                    <li><a href="#lokasi">Lokasi</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h4>Social Media</h4>
                <ul>
                    <li><a href="#"><i class="fab fa-instagram mr-2"></i> Instagram</a></li>
                    <li><a href="#"><i class="fab fa-facebook mr-2"></i> Facebook</a></li>
                    <li><a href="#"><i class="fab fa-youtube mr-2"></i> YouTube</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h4>Kontak</h4>
                <ul>
                    <li><a href="#">Email Kami</a></li>
                    <li><a href="#">WhatsApp</a></li>
                    <li><a href="#">Partner Kami</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; {{ date('Y') }} Bunihayu Forest Analytics System. Built with SARIMA Machine Learning.</p>
        </div>
    </footer>

    <script>
        // Simple Scroll Animation Observer
        document.addEventListener('DOMContentLoaded', () => {
            const observerOptions = {
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('aos-animate');
                    }
                });
            }, observerOptions);

            document.querySelectorAll('[data-aos]').forEach(el => {
                observer.observe(el);
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.padding = '0.8rem 5%';
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = '0 10px 30px rgba(0,0,0,0.1)';
            } else {
                navbar.style.padding = '1.25rem 5%';
                navbar.style.background = 'rgba(255, 255, 255, 0.9)';
                navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.03)';
            }
        });
    </script>
</body>
</html>
