<?php
session_start();
include '../config.php';

// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { header("Location: ../login.php"); exit; }

// --- FUNGSI UPLOAD ---
function uploadImage($file) {
    $targetDir = "uploads/campaigns/";
    if (!file_exists($targetDir)) { mkdir($targetDir, 0777, true); }
    
    $fileName = time() . '_' . basename($file["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    
    // Validasi sederhana
    $allowTypes = array('jpg', 'png', 'jpeg', 'webp');
    if (in_array($fileType, $allowTypes)) {
        if (move_uploaded_file($file["tmp_name"], $targetFilePath)) return $fileName;
    }
    return false;
}

// --- LOGIKA SIMPAN DONASI ---
if (isset($_POST['save_campaign'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $target = $_POST['target_amount'];
    // $deadline = $_POST['deadline']; // Aktifkan jika ada kolom deadline di database
    
    $image = 'default.jpg';
    if (!empty($_FILES["image"]["name"])) {
        $upl = uploadImage($_FILES["image"]);
        if ($upl) $image = $upl;
    }

    // Query Insert
    $query = "INSERT INTO donations (title, image, description, target_amount, current_amount, created_at) 
              VALUES ('$title', '$image', '$desc', '$target', 0, NOW())";
    
    if(mysqli_query($conn, $query)){
        echo "<script>alert('Program Donasi berhasil dibuat!'); window.location='campaigns.php';</script>";
    } else {
        echo "<script>alert('Gagal simpan: " . mysqli_error($conn) . "');</script>";
    }
}

// --- HAPUS DONASI ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM donations WHERE id='$id'");
    echo "<script>alert('Data dihapus!'); window.location='campaigns.php';</script>";
}

// --- NAVIGATION HANDLER ---
$view = isset($_GET['view']) ? $_GET['view'] : 'list'; // 'list' or 'create'
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Donasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

    <?php include 'sidebar.php'; ?>

    <main class="md:ml-64 p-8 min-h-screen">
        
        <?php if($view == 'list'): ?>
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Daftar Program Donasi</h1>
                    <p class="text-slate-500 text-sm">Pantau progres penggalangan dana aktif.</p>
                </div>
                <a href="?view=create" class="bg-blue-600 text-white px-5 py-2.5 rounded-xl hover:bg-blue-700 transition flex items-center gap-2 shadow-lg shadow-blue-500/30 font-medium">
                    <i data-feather="plus" class="w-4 h-4"></i> Buat Donasi Baru
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php 
                $q = mysqli_query($conn, "SELECT * FROM donations ORDER BY created_at DESC");
                while($row = mysqli_fetch_assoc($q)): 
                    $img = !empty($row['image']) ? "uploads/campaigns/".$row['image'] : "https://via.placeholder.com/600x400?text=No+Image";
                    
                    // Hitung Persentase
                    $target = $row['target_amount'];
                    $current = $row['current_amount'];
                    $percent = ($target > 0) ? ($current / $target) * 100 : 0;
                ?>
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-xl transition-all duration-300 group flex flex-col h-full">
                    
                    <div class="relative h-56 w-full overflow-hidden bg-slate-100">
                        <img src="<?php echo $img; ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Cover Donasi">
                        
                        <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold text-slate-700 shadow-sm">
                            <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                        </div>
                    </div>

                    <div class="p-6 flex-1 flex flex-col">
                        <h3 class="font-bold text-lg text-slate-900 mb-2 leading-snug line-clamp-2"><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p class="text-sm text-slate-500 mb-6 line-clamp-2 flex-1"><?php echo htmlspecialchars(strip_tags($row['description'] ?? '')); ?></p>
                        
                        <div class="mt-auto">
                            <div class="flex justify-between items-end mb-2">
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Terkumpul</p>
                                    <p class="text-base font-bold text-blue-600">Rp <?php echo number_format($current, 0, ',', '.'); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Target</p>
                                    <p class="text-sm font-semibold text-slate-700">Rp <?php echo number_format($target, 0, ',', '.'); ?></p>
                                </div>
                            </div>

                            <div class="relative w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                                <div class="absolute top-0 left-0 h-full bg-blue-500 rounded-full transition-all duration-1000 ease-out" style="width: <?php echo min(100, $percent); ?>%"></div>
                            </div>
                            
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-xs font-bold text-blue-600"><?php echo number_format($percent, 1); ?>%</span>
                                
                                <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus program donasi ini?')" class="text-xs text-red-400 hover:text-red-600 hover:bg-red-50 px-2 py-1 rounded transition">
                                    Hapus
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <?php if(mysqli_num_rows($q) == 0): ?>
                    <div class="col-span-full flex flex-col items-center justify-center py-16 text-slate-400 bg-white rounded-2xl border border-dashed border-slate-300">
                        <i data-feather="inbox" class="w-10 h-10 mb-3 opacity-50"></i>
                        <p>Belum ada program donasi aktif.</p>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif($view == 'create'): ?>
            <div class="max-w-3xl mx-auto">
                <div class="flex items-center gap-4 mb-6">
                    <a href="campaigns.php" class="p-2.5 rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-blue-600 transition shadow-sm">
                        <i data-feather="arrow-left" class="w-5 h-5"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Buat Donasi Baru</h1>
                        <p class="text-slate-500 text-sm">Isi detail program penggalangan dana di bawah ini.</p>
                    </div>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        
                        <div class="p-8">
                            <h2 class="text-lg font-bold text-slate-900 mb-6 border-b border-slate-100 pb-4">Informasi Program</h2>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Judul Donasi</label>
                                    <input type="text" name="title" required class="w-full border border-slate-200 rounded-xl px-4 py-3 text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition placeholder-slate-400" placeholder="Contoh: Bantuan Renovasi Masjid Al-Ikhlas">
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Target Dana (Rp)</label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-3.5 text-slate-400 font-bold text-sm">Rp</span>
                                            <input type="number" name="target_amount" required class="w-full border border-slate-200 rounded-xl pl-12 pr-4 py-3 text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none transition font-medium" placeholder="0">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Kategori Donasi</label>
                                        <select class="w-full border border-slate-200 rounded-xl px-4 py-3 text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none bg-white cursor-pointer">
                                            <option>Bencana Alam</option>
                                            <option>Pembangunan (Masjid/Sekolah)</option>
                                            <option>Kesehatan & Pengobatan</option>
                                            <option>Yatim & Dhuafa</option>
                                            <option>Kemanusiaan</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Foto Sampul (Cover)</label>
                                    <div class="relative w-full border-2 border-dashed border-slate-300 rounded-xl p-8 text-center hover:bg-slate-50 hover:border-blue-400 transition cursor-pointer group">
                                        <input type="file" name="image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewImage(event)">
                                        <div class="flex flex-col items-center justify-center" id="upload-placeholder">
                                            <div class="bg-blue-50 p-3 rounded-full mb-3 group-hover:bg-blue-100 transition">
                                                <i data-feather="image" class="w-6 h-6 text-blue-600"></i>
                                            </div>
                                            <p class="text-sm font-medium text-slate-700">Klik untuk upload foto</p>
                                            <p class="text-xs text-slate-400 mt-1">JPG, PNG, WEBP (Max 2MB)</p>
                                            <p class="text-[10px] text-slate-400 mt-1 italic">*Disarankan rasio landscape agar hasil maksimal</p>
                                        </div>
                                        <img id="img-preview" class="hidden w-full h-48 object-cover rounded-lg mt-2 mx-auto shadow-sm">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Cerita / Deskripsi</label>
                                    <textarea name="description" rows="6" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none transition resize-none leading-relaxed" placeholder="Ceritakan tujuan penggalangan dana ini secara detail..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 border-t border-slate-100 flex justify-end gap-3 bg-slate-50">
                            <a href="campaigns.php" class="px-6 py-3 rounded-xl text-slate-600 font-bold hover:bg-slate-200 transition text-sm">Batal</a>
                            <button type="submit" name="save_campaign" class="px-8 py-3 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition text-sm transform active:scale-95">
                                Terbitkan Donasi
                            </button>
                        </div>

                    </div>
                </form>
            </div>

        <?php endif; ?>

    </main>

    <script>
        feather.replace();

        // Script untuk Preview Gambar saat Upload
        function previewImage(event) {
            const input = event.target;
            const preview = document.getElementById('img-preview');
            const placeholder = document.getElementById('upload-placeholder');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    placeholder.classList.add('hidden'); // Sembunyikan teks placeholder
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>