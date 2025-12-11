<?php
session_start();
include 'config.php';

// --- LOGIKA PENCARIAN & PAGINATION ---
$limit = 9; // Tampilkan 9 berita per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';

// Query Dasar
$where_clause = "";
if (!empty($search)) {
    $where_clause = "WHERE title LIKE '%$search%' OR content LIKE '%$search%'";
}

// 1. Hitung Total Data (untuk Pagination)
$count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM news $where_clause");
$total_data = mysqli_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_data / $limit);

// 2. Ambil Data Berita
$query = mysqli_query($conn, "SELECT * FROM news $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Komunitas | Komunitas Maju Bersama</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
</head>
<body class="bg-slate-50 font-sans text-slate-800">

   <?php include 'navbar_include.php'; ?>
<script>const isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;</script>

    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 py-12 text-center">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Kabar Terbaru</h1>
            <p class="text-slate-500 max-w-2xl mx-auto mb-8">Informasi terkini seputar kegiatan, pencapaian, dan cerita inspiratif dari komunitas.</p>
            
            <form action="" method="GET" class="max-w-md mx-auto relative">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari berita..." 
                    class="w-full pl-12 pr-4 py-3 rounded-full border border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition shadow-sm">
                <i data-feather="search" class="absolute left-4 top-3.5 w-5 h-5 text-slate-400"></i>
            </form>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-12">
        
        <?php if(!empty($search)): ?>
            <p class="mb-6 text-slate-600">Menampilkan hasil pencarian untuk: <span class="font-bold text-slate-900">"<?php echo htmlspecialchars($search); ?>"</span></p>
        <?php endif; ?>

        <?php if(mysqli_num_rows($query) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php while($row = mysqli_fetch_assoc($query)): ?>
                <div class="group bg-white rounded-2xl overflow-hidden border border-slate-100 hover:shadow-2xl hover:shadow-blue-900/5 transition duration-300 flex flex-col h-full">
                    <a href="news_detail.php?id=<?php echo $row['id']; ?>" class="block relative h-56 overflow-hidden">
                        <img src="<?php echo $row['image_url']; ?>" alt="Berita" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-500">
                        <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold text-slate-800">
                            <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                        </div>
                    </a>
                    
                    <div class="p-6 flex-1 flex flex-col">
                        <a href="news_detail.php?id=<?php echo $row['id']; ?>">
                            <h3 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-primary transition line-clamp-2">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </h3>
                        </a>
                        <p class="text-slate-600 text-sm line-clamp-3 mb-4 flex-1">
                            <?php echo substr($row['content'], 0, 150) . '...'; ?>
                        </p>
                        <a href="news_detail.php?id=<?php echo $row['id']; ?>" class="text-sm font-semibold text-primary flex items-center hover:underline mt-auto">
                            Baca Selengkapnya <i data-feather="chevron-right" class="w-4 h-4 ml-1"></i>
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <?php if($total_pages > 1): ?>
            <div class="mt-12 flex justify-center gap-2">
                <?php if($page > 1): ?>
                    <a href="?page=<?php echo $page-1; ?>&q=<?php echo $search; ?>" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">Sebelumnya</a>
                <?php endif; ?>

                <?php for($i=1; $i<=$total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&q=<?php echo $search; ?>" class="px-4 py-2 border <?php echo ($i == $page) ? 'bg-primary text-white border-primary' : 'border-slate-300 text-slate-600 hover:bg-slate-50'; ?> rounded-lg transition">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?>&q=<?php echo $search; ?>" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">Selanjutnya</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center py-20 bg-white rounded-2xl border border-dashed border-slate-300">
                <div class="bg-slate-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                    <i data-feather="search" class="w-8 h-8"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700">Berita tidak ditemukan</h3>
                <p class="text-slate-500 mt-2">Coba kata kunci lain atau kembali ke halaman utama.</p>
                <a href="news.php" class="inline-block mt-4 text-primary font-medium hover:underline">Reset Pencarian</a>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-white border-t border-slate-200 py-8 text-center">
        <p class="text-sm text-slate-400">© 2025 Komunitas Maju Bersama.</p>
    </footer>

    <script>feather.replace();</script>
</body>
</html>