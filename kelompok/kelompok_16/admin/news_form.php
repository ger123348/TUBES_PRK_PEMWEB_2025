<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { header("Location: ../login.php"); exit; }

$title = $content = "";
$is_edit = false;
$id = "";
$current_image = "";

// CEK MODE EDIT
if (isset($_GET['edit'])) {
    $is_edit = true;
    $id = $_GET['edit'];
    $query = mysqli_query($conn, "SELECT * FROM news WHERE id='$id'");
    $data = mysqli_fetch_assoc($query);
    
    $title = $data['title'];
    $content = $data['content'];
    $current_image = $data['image_url'];
}

// PROSES SIMPAN (TAMBAH / UPDATE)
if (isset($_POST['save_news'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))); // Auto Slug

    // Logika Upload Gambar
    $image_query = "";
    $db_path = "";

    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/news/";
        // Buat folder jika belum ada
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

        $filename = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $filename;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_type, $allowed)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $db_path = "uploads/news/" . $filename;
                $image_query = ", image_url='$db_path'";
                
                // Hapus gambar lama jika mode edit
                if ($is_edit && !empty($current_image) && file_exists("../" . $current_image)) {
                    unlink("../" . $current_image);
                }
            }
        } else {
            echo "<script>alert('Format gambar harus JPG, PNG, atau WEBP');</script>";
        }
    }

    if ($is_edit) {
        $sql = "UPDATE news SET title='$title', slug='$slug', content='$content' $image_query WHERE id='$id'";
    } else {
        // Jika tambah baru, gambar wajib ada (atau pakai placeholder)
        $final_img = !empty($db_path) ? $db_path : 'https://images.unsplash.com/photo-1504711434969-e33886168f5c'; 
        $sql = "INSERT INTO news (title, slug, content, image_url) VALUES ('$title', '$slug', '$content', '$final_img')";
    }

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Berita berhasil disimpan!'); window.location='news.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title><?php echo $is_edit ? 'Edit' : 'Tulis'; ?> Berita</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script> tailwind.config = { theme: { extend: { colors: { primary: '#2563eb', secondary: '#1e293b' } } } } </script>
</head>
<body class="bg-slate-50 font-sans text-slate-800">
    <?php include 'sidebar.php'; ?>

    <main class="md:ml-64 p-8 min-h-screen">
        <div class="max-w-4xl mx-auto">
            
            <div class="flex items-center gap-4 mb-8">
                <a href="news.php" class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-500 transition">
                    <i data-feather="arrow-left"></i>
                </a>
                <h1 class="text-2xl font-bold text-slate-900">
                    <?php echo $is_edit ? 'Edit Berita' : 'Tulis Berita Baru'; ?>
                </h1>
            </div>

            <form method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 space-y-8">
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Judul Berita</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" required placeholder="Masukkan judul yang menarik..." 
                        class="w-full border border-slate-300 p-4 rounded-xl focus:border-primary focus:ring-4 focus:ring-blue-50 outline-none transition text-lg font-medium">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Gambar Utama (Thumbnail)</label>
                    
                    <div class="flex items-start gap-6">
                        <?php if($is_edit && $current_image): ?>
                            <div class="w-32 h-24 bg-slate-100 rounded-lg overflow-hidden border border-slate-200 flex-shrink-0">
                                <img src="../<?php echo $current_image; ?>" class="w-full h-full object-cover">
                            </div>
                        <?php endif; ?>

                        <div class="flex-1">
                            <input type="file" name="image" accept="image/*" class="w-full border border-slate-300 p-3 rounded-xl text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-primary hover:file:bg-blue-100 cursor-pointer">
                            <p class="text-xs text-slate-400 mt-2">
                                <?php echo $is_edit ? 'Biarkan kosong jika tidak ingin mengubah gambar.' : 'Format: JPG, PNG, WEBP (Max 2MB).'; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Isi Berita</label>
                    <textarea name="content" rows="12" required placeholder="Tulis isi berita secara lengkap di sini..." 
                        class="w-full border border-slate-300 p-4 rounded-xl focus:border-primary outline-none transition leading-relaxed"><?php echo htmlspecialchars($content); ?></textarea>
                    <p class="text-xs text-slate-400 mt-2 text-right">Tip: Gunakan Enter untuk paragraf baru.</p>
                </div>

                <div class="pt-6 flex justify-end gap-4 border-t border-slate-100">
                    <a href="news.php" class="px-6 py-3 text-slate-600 font-bold hover:bg-slate-100 rounded-xl transition">Batal</a>
                    <button type="submit" name="save_news" class="px-8 py-3 bg-primary text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-500/30">
                        <?php echo $is_edit ? 'Simpan Perubahan' : 'Publish Berita'; ?>
                    </button>
                </div>

            </form>
        </div>
    </main>
    <script>feather.replace();</script>
</body>
</html>