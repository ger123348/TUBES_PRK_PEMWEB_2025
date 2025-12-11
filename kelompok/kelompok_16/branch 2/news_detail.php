<?php
session_start();
include 'config.php';

// 1. VALIDASI ID BERITA
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

// 2. AMBIL DATA BERITA UTAMA
$query = mysqli_query($conn, "SELECT * FROM news WHERE id = '$id'");
if (mysqli_num_rows($query) == 0) {
    echo "Berita tidak ditemukan.";
    exit;
}
$news = mysqli_fetch_assoc($query);

// 3. LOGIKA GAMBAR SLIDER (Carousel)
// Di sistem real, kita ambil dari tabel 'news_gallery'. 
// Disini saya buat simulasi array gambar agar fitur 'Bergerak' langsung terlihat.
$slider_images = [];

// Masukkan gambar utama ke slider
if (!empty($news['image_url'])) {
    $slider_images[] = $news['image_url'];
}

// Tambahkan gambar dummy tambahan agar slider bisa bergerak (Simulasi Galeri)
// Nanti bisa diganti query database: SELECT image FROM news_gallery WHERE news_id = $id
$slider_images[] = "https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&q=80&w=800";
$slider_images[] = "https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&q=80&w=800";

// 4. AMBIL BERITA LAINNYA (SIDEBAR)
// Ambil 3 berita terbaru KECUALI berita yang sedang dibuka
$query_other = mysqli_query($conn, "SELECT * FROM news WHERE id != '$id' ORDER BY created_at DESC LIMIT 3");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($news['title']); ?> | Komunitas Maju</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { primary: '#2563eb', secondary: '#1e293b' }
                }
            }
        }
    </script>
    <style>
        /* Hide scrollbar for gallery */
        .hide-scroll::-webkit-scrollbar {
            display: none;
        }
        .hide-scroll {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

  <?php include 'navbar_include.php'; ?>
<script>const isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;</script>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <div class="lg:col-span-2">
                
                <div class="mb-6">
                    <span class="inline-block px-3 py-1 bg-blue-100 text-primary text-xs font-bold rounded-full mb-3 uppercase tracking-wider">
                        Berita Terbaru
                    </span>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 leading-tight mb-4">
                        <?php echo htmlspecialchars($news['title']); ?>
                    </h1>
                    
                    <div class="flex items-center gap-4 text-sm text-slate-500 border-b border-slate-100 pb-6">
                        <div class="flex items-center gap-2">
                            <i data-feather="calendar" class="w-4 h-4"></i>
                            <?php echo date('d F Y', strtotime($news['created_at'])); ?>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-feather="clock" class="w-4 h-4"></i>
                            <?php echo date('H:i', strtotime($news['created_at'])); ?> WIB
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-feather="user" class="w-4 h-4"></i>
                            Admin
                        </div>
                    </div>
                </div>

                <div class="relative w-full aspect-video bg-slate-200 rounded-2xl overflow-hidden mb-8 group shadow-lg">
                    
                    <div id="carousel" class="flex transition-transform duration-700 ease-in-out h-full w-full">
                        <?php foreach($slider_images as $img): ?>
                        <div class="min-w-full h-full relative">
                            <img src="<?php echo $img; ?>" class="w-full h-full object-cover" alt="News Image">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-60"></div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <button onclick="prevSlide()" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/30 hover:bg-white/80 backdrop-blur-sm p-2 rounded-full text-white hover:text-slate-900 transition opacity-0 group-hover:opacity-100">
                        <i data-feather="chevron-left" class="w-6 h-6"></i>
                    </button>
                    <button onclick="nextSlide()" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/30 hover:bg-white/80 backdrop-blur-sm p-2 rounded-full text-white hover:text-slate-900 transition opacity-0 group-hover:opacity-100">
                        <i data-feather="chevron-right" class="w-6 h-6"></i>
                    </button>

                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2">
                        <?php foreach($slider_images as $index => $img): ?>
                        <button onclick="goToSlide(<?php echo $index; ?>)" id="dot-<?php echo $index; ?>" class="w-2.5 h-2.5 rounded-full transition-all duration-300 <?php echo $index === 0 ? 'bg-white w-6' : 'bg-white/50 hover:bg-white'; ?>"></button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <article class="prose prose-lg prose-slate max-w-none text-slate-700 leading-relaxed">
                    <?php echo nl2br(htmlspecialchars($news['content'])); ?>
                    
                    <p class="mt-4">
                        Kegiatan ini merupakan bagian dari komitmen jangka panjang komunitas untuk terus memberikan dampak positif bagi lingkungan sekitar. Kami mengundang seluruh elemen masyarakat untuk turut serta berpartisipasi dalam agenda-agenda selanjutnya.
                    </p>
                    <p class="mt-4">
                        Dukungan dari para anggota dan donatur sangat berarti bagi keberlangsungan program ini. Mari bersama-sama kita wujudkan visi misi komunitas untuk masa depan yang lebih baik dan inklusif.
                    </p>
                </article>

                <div class="mt-10 pt-8 border-t border-slate-200">
                    <p class="text-sm font-bold text-slate-900 mb-4">Bagikan Berita ini:</p>
                    <div class="flex gap-3">
                        <button class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                            <i data-feather="facebook" class="w-4 h-4"></i> Facebook
                        </button>
                        <button class="flex items-center gap-2 px-4 py-2 bg-sky-500 text-white rounded-lg text-sm font-medium hover:bg-sky-600 transition">
                            <i data-feather="twitter" class="w-4 h-4"></i> Twitter
                        </button>
                        <button class="flex items-center gap-2 px-4 py-2 bg-green-500 text-white rounded-lg text-sm font-medium hover:bg-green-600 transition">
                            <i data-feather="message-circle" class="w-4 h-4"></i> WhatsApp
                        </button>
                    </div>
                </div>

            </div>

            <div class="lg:col-span-1">
                <div class="sticky top-24 space-y-8">
                    
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                        <h3 class="font-bold text-lg text-slate-900 mb-6 border-b border-slate-100 pb-2">Berita Lainnya</h3>
                        <div class="space-y-6">
                            <?php while($row_other = mysqli_fetch_assoc($query_other)): ?>
                            <a href="news_detail.php?id=<?php echo $row_other['id']; ?>" class="flex gap-4 group">
                                <div class="w-20 h-20 flex-shrink-0 rounded-lg overflow-hidden bg-slate-200">
                                    <img src="<?php echo $row_other['image_url']; ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" alt="Thumb">
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800 line-clamp-2 group-hover:text-primary transition">
                                        <?php echo htmlspecialchars($row_other['title']); ?>
                                    </h4>
                                    <p class="text-xs text-slate-400 mt-1"><?php echo date('d M Y', strtotime($row_other['created_at'])); ?></p>
                                </div>
                            </a>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-secondary to-slate-900 rounded-xl p-6 text-white text-center relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-primary opacity-20 rounded-full blur-3xl -mr-10 -mt-10"></div>
                        <h3 class="font-bold text-lg mb-2 relative z-10">Bergabunglah Bersama Kami</h3>
                        <p class="text-slate-300 text-sm mb-6 relative z-10">Jadilah bagian dari perubahan positif.</p>
                        <a href="register.php" class="inline-block w-full py-3 bg-primary hover:bg-blue-600 text-white font-bold rounded-lg transition relative z-10">
                            Daftar Anggota
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <footer class="bg-white border-t border-slate-200 mt-12 py-8 text-center">
        <p class="text-sm text-slate-400">© 2025 Komunitas Maju Bersama.</p>
    </footer>

    <script>
        feather.replace();

        const carousel = document.getElementById('carousel');
        const totalSlides = <?php echo count($slider_images); ?>;
        let currentSlide = 0;
        let autoSlideInterval;

        // Fungsi Update Posisi Slide
        function updateSlide() {
            const offset = currentSlide * -100;
            carousel.style.transform = `translateX(${offset}%)`;
            
            // Update Dots
            for (let i = 0; i < totalSlides; i++) {
                const dot = document.getElementById(`dot-${i}`);
                if (i === currentSlide) {
                    dot.classList.remove('bg-white/50', 'w-2.5');
                    dot.classList.add('bg-white', 'w-6');
                } else {
                    dot.classList.remove('bg-white', 'w-6');
                    dot.classList.add('bg-white/50', 'w-2.5');
                }
            }
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateSlide();
            resetTimer();
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            updateSlide();
            resetTimer();
        }

        function goToSlide(index) {
            currentSlide = index;
            updateSlide();
            resetTimer();
        }

        // Fitur "Bergerak Otomatis" (Auto Play)
        function startTimer() {
            autoSlideInterval = setInterval(nextSlide, 5000); // Ganti gambar tiap 5 detik
        }

        function resetTimer() {
            clearInterval(autoSlideInterval);
            startTimer();
        }

        // Mulai saat halaman load
        startTimer();
    </script>
</body>
</html>