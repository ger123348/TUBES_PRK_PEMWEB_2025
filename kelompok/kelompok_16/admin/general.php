<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { header("Location: ../login.php"); exit; }

// --- 1. UPDATE TEKS ---
if (isset($_POST['update_text'])) {
    $title = mysqli_real_escape_string($conn, $_POST['hero_title']);
    $sub = mysqli_real_escape_string($conn, $_POST['hero_subtitle']);
    $desc = mysqli_real_escape_string($conn, $_POST['hero_description']);

    // Update baris pertama (id=1)
    $q = "UPDATE site_settings SET hero_title='$title', hero_subtitle='$sub', hero_description='$desc' WHERE id=1";
    if (mysqli_query($conn, $q)) {
        $msg_text = "Teks halaman depan berhasil diperbarui!";
    }
}

// --- 2. UPLOAD GAMBAR BARU ---
if (isset($_POST['upload_image'])) {
    if (!empty($_FILES['hero_image']['name'])) {
        $targetDir = "../uploads/hero/"; // Naik satu level dari admin
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . '_' . basename($_FILES['hero_image']['name']);
        $targetFile = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        if (in_array($fileType, ['jpg', 'jpeg', 'png', 'webp'])) {
            if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $targetFile)) {
                // Simpan nama file saja ke DB
                mysqli_query($conn, "INSERT INTO hero_images (image_path) VALUES ('$fileName')");
                $msg_img = "Gambar berhasil ditambahkan!";
            }
        } else {
            $err_img = "Format file harus JPG, PNG, atau WEBP.";
        }
    }
}

// --- 3. HAPUS GAMBAR ---
if (isset($_GET['del_img'])) {
    $id = $_GET['del_img'];
    $q = mysqli_query($conn, "SELECT image_path FROM hero_images WHERE id='$id'");
    $row = mysqli_fetch_assoc($q);
    
    if ($row) {
        $file = "../uploads/hero/" . $row['image_path'];
        if (file_exists($file)) unlink($file); // Hapus file fisik
        mysqli_query($conn, "DELETE FROM hero_images WHERE id='$id'"); // Hapus dari DB
        header("Location: general.php");
        exit;
    }
}

// AMBIL DATA SAAT INI
$settings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM site_settings WHERE id=1"));
$images = mysqli_query($conn, "SELECT * FROM hero_images ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pengaturan Umum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script> tailwind.config = { theme: { extend: { colors: { primary: '#2563eb', secondary: '#1e293b' } } } } </script>
    <style>
        /* Animasi untuk Drag Zone */
        .drag-active { border-color: #2563eb !important; background-color: #eff6ff !important; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">
    
    <?php include 'sidebar.php'; ?>

    <main class="md:ml-64 p-8 min-h-screen">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900">Pengaturan Umum</h1>
            <p class="text-slate-500 text-sm">Kelola tampilan halaman utama (Landing Page).</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 h-fit">
                <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <i data-feather="type" class="w-5 h-5 text-primary"></i> Konten Teks Hero
                </h3>
                
                <?php if(isset($msg_text)): ?>
                    <div class="bg-green-50 text-green-600 px-4 py-2 rounded-lg text-sm mb-4 flex items-center gap-2"><i data-feather="check"></i> <?php echo $msg_text; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Judul Utama (Baris 1)</label>
                            <input type="text" name="hero_title" value="<?php echo htmlspecialchars($settings['hero_title']); ?>" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Sub Judul (Gradient)</label>
                            <input type="text" name="hero_subtitle" value="<?php echo htmlspecialchars($settings['hero_subtitle']); ?>" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary outline-none text-blue-600 font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Deskripsi Singkat</label>
                            <textarea name="hero_description" rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary outline-none"><?php echo htmlspecialchars($settings['hero_description']); ?></textarea>
                        </div>
                        <button type="submit" name="update_text" class="w-full bg-slate-900 text-white py-2.5 rounded-lg font-bold text-sm hover:bg-slate-800 transition shadow-lg shadow-slate-900/20">Simpan Perubahan Teks</button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <i data-feather="image" class="w-5 h-5 text-primary"></i> Gambar Background
                </h3>

                <?php if(isset($msg_img)): ?>
                    <div class="bg-green-50 text-green-600 px-4 py-2 rounded-lg text-sm mb-4 flex items-center gap-2"><i data-feather="check"></i> <?php echo $msg_img; ?></div>
                <?php endif; ?>
                <?php if(isset($err_img)): ?>
                    <div class="bg-red-50 text-red-600 px-4 py-2 rounded-lg text-sm mb-4 flex items-center gap-2"><i data-feather="alert-circle"></i> <?php echo $err_img; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="mb-6">
                    <div id="drop-zone" class="border-2 border-dashed border-slate-300 rounded-2xl p-8 text-center transition-all duration-200 cursor-pointer hover:bg-slate-50 relative group">
                        
                        <input type="file" name="hero_image" id="file-input" class="hidden" accept="image/*">
                        
                        <div id="drop-content" class="pointer-events-none">
                            <div class="w-12 h-12 bg-blue-50 text-primary rounded-full flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition">
                                <i data-feather="upload-cloud" class="w-6 h-6"></i>
                            </div>
                            <p class="text-sm font-bold text-slate-700">Klik atau Drag gambar ke sini</p>
                            <p class="text-xs text-slate-400 mt-1">Format: JPG, PNG, WEBP (Max 2MB)</p>
                        </div>

                        <div id="preview-container" class="hidden absolute inset-0 bg-white rounded-2xl flex flex-col items-center justify-center z-10 p-2">
                            <img id="preview-image" src="" class="h-32 object-contain rounded-lg shadow-sm border border-slate-200">
                            <p id="file-name" class="text-xs text-slate-500 mt-2 truncate max-w-[80%]"></p>
                            <button type="button" id="remove-file" class="absolute top-2 right-2 bg-red-100 text-red-500 p-1 rounded-full hover:bg-red-200 transition">
                                <i data-feather="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" name="upload_image" id="btn-upload" class="mt-4 w-full bg-primary text-white py-2.5 rounded-lg font-bold text-sm hover:bg-blue-600 transition shadow-lg shadow-blue-500/30 opacity-50 cursor-not-allowed" disabled>
                        Upload Gambar Sekarang
                    </button>
                </form>

                <div class="grid grid-cols-2 gap-3 max-h-[350px] overflow-y-auto pr-1 custom-scrollbar">
                    <?php if(mysqli_num_rows($images) > 0): ?>
                        <?php while($img = mysqli_fetch_assoc($images)): ?>
                        <div class="group relative rounded-xl overflow-hidden border border-slate-200 aspect-video shadow-sm">
                            <img src="../uploads/hero/<?php echo $img['image_path']; ?>" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300">
                                <a href="?del_img=<?php echo $img['id']; ?>" onclick="return confirm('Hapus gambar ini?')" class="bg-white text-red-500 p-2 rounded-full hover:bg-red-50 transition transform hover:scale-110 shadow-lg">
                                    <i data-feather="trash-2" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-span-2 text-center py-10 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                            <p class="text-slate-400 text-sm">Belum ada gambar yang diupload.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <p class="text-[10px] text-slate-400 mt-4 text-center border-t border-slate-100 pt-3">
                    <i data-feather="info" class="w-3 h-3 inline mr-1"></i> Gambar di atas akan menjadi slideshow di halaman depan.
                </p>
            </div>

        </div>
    </main>

    <script>
        feather.replace();

        // --- DRAG AND DROP LOGIC ---
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');
        const dropContent = document.getElementById('drop-content');
        const previewContainer = document.getElementById('preview-container');
        const previewImage = document.getElementById('preview-image');
        const fileNameDisplay = document.getElementById('file-name');
        const removeFileBtn = document.getElementById('remove-file');
        const btnUpload = document.getElementById('btn-upload');

        // Klik Dropzone -> Buka File Dialog
        dropZone.addEventListener('click', () => fileInput.click());

        // Handle File Terpilih (Lewat Klik atau Drop)
        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

        // Event Drag Over (Ubah style kotak)
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('drag-active');
        });

        // Event Drag Leave (Kembalikan style)
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('drag-active');
        });

        // Event Drop (Terima File)
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-active');
            
            const files = e.dataTransfer.files;
            if (files.length) {
                fileInput.files = files; // Masukkan file ke input
                handleFiles(files);
            }
        });

        // Fungsi Tampilkan Preview
        function handleFiles(files) {
            const file = files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImage.src = e.target.result;
                    fileNameDisplay.textContent = file.name;
                    
                    // Tampilkan Preview, Sembunyikan Placeholder
                    previewContainer.classList.remove('hidden');
                    
                    // Aktifkan Tombol Upload
                    btnUpload.disabled = false;
                    btnUpload.classList.remove('opacity-50', 'cursor-not-allowed');
                }
                reader.readAsDataURL(file);
            }
        }

        // Tombol Hapus Preview (X)
        removeFileBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // Jangan trigger klik dropzone
            fileInput.value = ''; // Reset input
            previewContainer.classList.add('hidden'); // Sembunyikan preview
            
            // Matikan Tombol Upload
            btnUpload.disabled = true;
            btnUpload.classList.add('opacity-50', 'cursor-not-allowed');
        });
    </script>
</body>
</html>