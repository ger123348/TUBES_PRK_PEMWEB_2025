<?php
session_start();
include '../config.php';

// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { header("Location: ../login.php"); exit; }

// Inisialisasi Variabel
$title = $date = $location = $description = $type = $quota = $custom_fields = "";
$status = "open";
$is_edit = false;
$id = "";

// Jika Mode Edit
if (isset($_GET['edit'])) {
    $is_edit = true;
    $id = $_GET['edit'];
    $query = mysqli_query($conn, "SELECT * FROM events WHERE id='$id'");
    $data = mysqli_fetch_assoc($query);
    
    if ($data) {
        $title = $data['title'];
        $date = $data['event_date'];
        $location = $data['location'];
        $description = $data['description'];
        $type = $data['type'];
        $quota = $data['quota'];
        $status = $data['status'];
        $custom_fields = $data['custom_fields'];
    }
}

// PROSES SIMPAN DATA
if (isset($_POST['save_event'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $date = $_POST['date'];
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $type = $_POST['type'];
    $quota = (int)$_POST['quota'];
    $status = $_POST['status'];
    $custom_fields = mysqli_real_escape_string($conn, $_POST['custom_fields']);

    // --- LOGIKA UPLOAD GAMBAR (FIX PATH) ---
    $image_query = "";
    if (!empty($_FILES['image']['name'])) {
        // Path fisik folder (Naik satu level dari admin ke root -> uploads/events/)
        $target_dir = "../uploads/events/";
        
        // Buat folder jika belum ada
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov'];

        if (in_array($file_extension, $allowed_ext)) {
            // Nama file unik
            $new_filename = time() . "_" . uniqid() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Simpan PATH RELATIF untuk database (uploads/events/namafile.jpg)
                // Ini penting agar bisa dibaca dari admin (pakai ../) maupun index (langsung)
                $db_path = "uploads/events/" . $new_filename;
                $image_query = ", image_url='$db_path'";
            } else {
                echo "<script>alert('Gagal mengupload file ke server.');</script>";
            }
        } else {
            echo "<script>alert('Format file tidak didukung!');</script>";
        }
    }

    if ($is_edit) {
        $sql = "UPDATE events SET title='$title', event_date='$date', location='$location', description='$description', type='$type', quota='$quota', status='$status', custom_fields='$custom_fields' $image_query WHERE id='$id'";
    } else {
        // Jika insert baru, pastikan image_url terisi
        $final_img = isset($db_path) ? $db_path : ''; 
        $sql = "INSERT INTO events (title, event_date, location, description, type, quota, status, custom_fields, image_url) VALUES ('$title', '$date', '$location', '$description', '$type', '$quota', '$status', '$custom_fields', '$final_img')";
    }

    if (mysqli_query($conn, $sql)) {
        // Redirect kembali ke halaman list event
        header("Location: events.php?status=success");
        exit;
    } else {
        echo "Error Database: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title><?php echo $is_edit ? 'Edit' : 'Tambah'; ?> Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="bg-slate-50 font-sans text-slate-800">
    <?php include 'sidebar.php'; ?>

    <main class="md:ml-64 p-8 min-h-screen">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center gap-4 mb-8">
                <a href="events.php" class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-500"><i data-feather="arrow-left"></i></a>
                <h1 class="text-2xl font-bold text-slate-900"><?php echo $is_edit ? 'Edit Event' : 'Buat Event Baru'; ?></h1>
            </div>

            <form method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 space-y-8">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Informasi Dasar</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Nama Event</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" required class="w-full border p-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Tanggal & Waktu</label>
                            <input type="datetime-local" name="date" value="<?php echo $date ? date('Y-m-d\TH:i', strtotime($date)) : ''; ?>" required class="w-full border p-3 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Lokasi</label>
                            <input type="text" name="location" value="<?php echo htmlspecialchars($location); ?>" required class="w-full border p-3 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Kategori</label>
                            <select name="type" class="w-full border p-3 rounded-xl bg-white">
                                <option value="Seminar" <?php if($type=='Seminar') echo 'selected'; ?>>Seminar</option>
                                <option value="Workshop" <?php if($type=='Workshop') echo 'selected'; ?>>Workshop</option>
                                <option value="Lomba" <?php if($type=='Lomba') echo 'selected'; ?>>Lomba</option>
                                <option value="Rapat" <?php if($type=='Rapat') echo 'selected'; ?>>Rapat</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Media (Foto/Video)</label>
                            <input type="file" name="image" accept="image/*,video/*" class="w-full border p-2 rounded-xl text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-slate-400 mt-1">Support: JPG, PNG, MP4. (Biarkan kosong jika tidak ingin mengubah)</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Deskripsi</label>
                            <textarea name="description" rows="4" required class="w-full border p-3 rounded-xl"><?php echo htmlspecialchars($description); ?></textarea>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Pengaturan Pendaftaran</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Kuota</label>
                            <input type="number" name="quota" value="<?php echo $quota ? $quota : 100; ?>" class="w-full border p-3 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                            <select name="status" class="w-full border p-3 rounded-xl bg-white">
                                <option value="open" <?php if($status=='open') echo 'selected'; ?>>Buka</option>
                                <option value="closed" <?php if($status=='closed') echo 'selected'; ?>>Tutup</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Custom Fields (Opsional)</label>
                            <input type="text" name="custom_fields" value="<?php echo htmlspecialchars($custom_fields); ?>" placeholder="Contoh: Ukuran Kaos, Asal Instansi" class="w-full border p-3 rounded-xl">
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <a href="events.php" class="px-6 py-3 text-slate-600 font-bold hover:bg-slate-100 rounded-xl transition">Batal</a>
                    <button type="submit" name="save_event" class="px-8 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-500/30">
                        Simpan Event
                    </button>
                </div>
            </form>
        </div>
    </main>
    <script>feather.replace();</script>
</body>
</html>