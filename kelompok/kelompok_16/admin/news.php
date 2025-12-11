<?php
session_start();
include '../config.php';

// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { header("Location: ../login.php"); exit; }

// LOGIKA HAPUS BERITA
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Ambil info gambar dulu untuk dihapus dari folder
    $q = mysqli_query($conn, "SELECT image_url FROM news WHERE id='$id'");
    $data = mysqli_fetch_assoc($q);
    
    // Hapus file gambar jika ada
    if ($data['image_url'] && file_exists("../" . $data['image_url'])) {
        unlink("../" . $data['image_url']);
    }

    // Hapus data dari DB
    mysqli_query($conn, "DELETE FROM news WHERE id='$id'");
    header("Location: news.php");
}

// AMBIL DATA BERITA
$query = mysqli_query($conn, "SELECT * FROM news ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Berita Acara</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script> tailwind.config = { theme: { extend: { colors: { primary: '#2563eb', secondary: '#1e293b' } } } } </script>
</head>
<body class="bg-slate-50 font-sans text-slate-800">
    <?php include 'sidebar.php'; ?>

    <main class="md:ml-64 p-8 min-h-screen">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Berita Acara</h1>
                <p class="text-slate-500 text-sm">Publikasikan kegiatan dan informasi terbaru komunitas.</p>
            </div>
            <a href="news_form.php" class="bg-primary text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-blue-700 transition shadow-lg shadow-blue-500/30">
                <i data-feather="plus-circle"></i> Tulis Berita Baru
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if(mysqli_num_rows($query) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($query)): ?>
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition group">
                    
                    <div class="h-48 bg-slate-200 relative overflow-hidden">
                        <?php if($row['image_url']): ?>
                            <img src="../<?php echo $row['image_url']; ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-slate-400"><i data-feather="image" class="w-10 h-10"></i></div>
                        <?php endif; ?>
                        
                        <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold text-slate-800 shadow-sm">
                            <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                        </div>
                    </div>

                    <div class="p-5">
                        <h3 class="font-bold text-lg text-slate-800 mb-2 line-clamp-2 leading-tight">
                            <?php echo htmlspecialchars($row['title']); ?>
                        </h3>
                        <p class="text-sm text-slate-500 line-clamp-3 mb-4">
                            <?php echo substr(strip_tags($row['content']), 0, 150) . '...'; ?>
                        </p>
                        
                        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                            <a href="../news_detail.php?id=<?php echo $row['id']; ?>" target="_blank" class="text-xs font-bold text-slate-400 hover:text-primary flex items-center gap-1">
                                <i data-feather="external-link" class="w-3 h-3"></i> Preview
                            </a>
                            <div class="flex gap-2">
                                <a href="news_form.php?edit=<?php echo $row['id']; ?>" class="p-2 text-slate-500 hover:text-white hover:bg-orange-500 rounded-lg transition" title="Edit">
                                    <i data-feather="edit-2" class="w-4 h-4"></i>
                                </a>
                                <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus berita ini?')" class="p-2 text-slate-500 hover:text-white hover:bg-red-500 rounded-lg transition" title="Hapus">
                                    <i data-feather="trash-2" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-3 text-center py-20 bg-white rounded-2xl border border-dashed border-slate-300">
                    <div class="inline-flex p-4 bg-slate-50 rounded-full mb-4 text-slate-400">
                        <i data-feather="file-text" class="w-8 h-8"></i>
                    </div>
                    <p class="text-slate-500 font-medium">Belum ada berita yang dipublish.</p>
                </div>
            <?php endif; ?>
        </div>

    </main>
    <script>feather.replace();</script>
</body>
</html>