<?php
session_start();
include 'config.php';

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// --- AMBIL DATA USER AWAL ---
$q_user = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($q_user);

// --- UPDATE PROFIL & FOTO ---
if (isset($_POST['update_profile'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $position  = mysqli_real_escape_string($conn, $_POST['position']); 
    $bio       = mysqli_real_escape_string($conn, $_POST['bio']);
    $phone     = mysqli_real_escape_string($conn, $_POST['phone']);
    
    // Logika Upload Foto
    $photo_name = $user['photo']; // Default pakai foto lama
    
    if (!empty($_FILES['avatar']['name'])) {
        $target_dir = "uploads/members/";
        // Buat folder jika belum ada
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $file_extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        // Rename file agar unik (IDUser_Timestamp.jpg)
        $new_filename = $user_id . '_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Validasi tipe file
        $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array(strtolower($file_extension), $allowed_types)) {
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
                // Hapus foto lama jika ada (dan bukan default)
                if (!empty($user['photo']) && file_exists($target_dir . $user['photo'])) {
                    unlink($target_dir . $user['photo']);
                }
                $photo_name = $new_filename;
            }
        }
    }

    $query = "UPDATE users SET full_name='$full_name', position='$position', bio='$bio', phone='$phone', photo='$photo_name' WHERE id='$user_id'";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['user_name'] = $full_name;
        $_SESSION['user_position'] = $position;
        $_SESSION['user_photo'] = $photo_name;
        // Refresh halaman
        header("Location: dashboard.php?status=success");
        exit;
    }
}

// --- LOGIKA TAMPILAN KARTU (WARNA) ---
$pos = $user['position'] ?? 'Anggota';
$card_theme = 'bg-slate-800'; // Default
$icon_role = 'user';

if (strpos($pos, 'Ketua') !== false || strpos($pos, 'Wakil Ketua') !== false) {
    $card_theme = 'bg-gradient-to-br from-amber-400 via-orange-500 to-yellow-600 shadow-orange-500/30';
    $icon_role = 'award';
} elseif (strpos($pos, 'Sekretaris') !== false || strpos($pos, 'Bendahara') !== false) {
    $card_theme = 'bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-700 shadow-blue-500/30';
    $icon_role = 'briefcase';
} elseif (strpos($pos, 'Divisi') !== false) {
    $card_theme = 'bg-gradient-to-br from-emerald-500 via-teal-600 to-cyan-700 shadow-teal-500/30';
    $icon_role = 'layers';
}

$roles_list = [
    'Anggota', 'Ketua Umum', 'Wakil Ketua', 
    'Sekretaris', 'Wakil Sekretaris', 
    'Bendahara', 'Wakil Bendahara', 
    'Divisi Pendidikan', 'Divisi Pengembangan', 
    'Divisi Humas', 'Divisi Dokumentasi'
];

// Tentukan Sumber Foto Profil
$profile_pic = "https://ui-avatars.com/api/?name=".urlencode($user['full_name'])."&background=random&color=fff&size=256";
if (!empty($user['photo']) && file_exists("uploads/members/" . $user['photo'])) {
    $profile_pic = "uploads/members/" . $user['photo'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Anggota</title>
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

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 h-20 flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-2 group">
                <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-white font-bold shadow-lg shadow-blue-500/30">K</div>
                <span class="font-bold text-xl text-slate-900 tracking-tight">Komunitas<span class="text-primary">Maju</span></span>
            </a>
            <div class="flex items-center gap-6">
                <a href="index.php" class="text-sm font-medium text-slate-500 hover:text-primary transition">Beranda</a>
                <a href="logout.php" class="flex items-center gap-2 text-sm font-bold text-red-600 hover:bg-red-50 px-4 py-2 rounded-full transition">
                    <i data-feather="log-out" class="w-4 h-4"></i> Keluar
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 py-12">
        
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Dashboard Anggota</h1>
                <p class="text-slate-500 mt-2">Kelola identitas dan peran Anda dalam komunitas.</p>
            </div>
            <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div class="bg-green-100 text-green-700 px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 animate-bounce">
                    <i data-feather="check-circle" class="w-4 h-4"></i> Data berhasil disimpan!
                </div>
            <?php endif; ?>
        </div>

        <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <div class="lg:col-span-1 sticky top-28">
                <div class="relative w-full aspect-[3/4.5] rounded-3xl overflow-hidden shadow-2xl <?php echo $card_theme; ?> text-white p-8 flex flex-col items-center text-center transition-all duration-500 hover:scale-[1.02]">
                    
                    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 20px 20px;"></div>
                    
                    <div class="relative z-10 w-full h-full flex flex-col">
                        <div class="uppercase tracking-[0.2em] text-[10px] font-bold opacity-80 mb-8">Kartu Anggota Resmi</div>
                        
                        <div class="mx-auto relative group">
                            <div class="p-1.5 rounded-full border-2 border-white/30 backdrop-blur-sm">
                                <img id="previewImg" src="<?php echo $profile_pic; ?>" class="w-28 h-28 rounded-full border-4 border-white shadow-md bg-white object-cover">
                            </div>
                            
                            <input type="file" name="avatar" id="avatarInput" class="hidden" accept="image/*" onchange="previewImage(event)">
                            
                            <label for="avatarInput" class="absolute bottom-2 right-2 bg-white text-slate-800 p-2 rounded-full shadow-lg cursor-pointer hover:bg-slate-100 transition transform hover:scale-110">
                                <i data-feather="camera" class="w-4 h-4"></i>
                            </label>
                        </div>

                        <h2 class="text-2xl font-bold tracking-tight leading-tight mt-6 mb-2 text-shadow-sm">
                            <?php echo htmlspecialchars($user['full_name'] ?? 'Nama Anggota'); ?>
                        </h2>
                        <div class="inline-flex items-center justify-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-xs font-bold uppercase tracking-wider mx-auto">
                            <i data-feather="<?php echo $icon_role; ?>" class="w-3 h-3"></i> 
                            <?php echo htmlspecialchars($user['position'] ?? 'Anggota'); ?>
                        </div>

                        <div class="mt-auto pt-6 border-t border-white/20 w-full text-left space-y-3">
                            <div>
                                <p class="text-[10px] uppercase opacity-60 font-bold">Email</p>
                                <p class="text-xs font-medium truncate"><?php echo htmlspecialchars($user['email'] ?? '-'); ?></p>
                            </div>
                            <div class="flex justify-between items-end">
                                <div>
                                    <p class="text-[10px] uppercase opacity-60 font-bold">Status</p>
                                    <p class="text-xs font-bold text-emerald-300 flex items-center gap-1"><span class="w-1.5 h-1.5 bg-emerald-300 rounded-full animate-pulse"></span> Aktif</p>
                                </div>
                                <i data-feather="rss" class="w-6 h-6 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-center text-xs text-slate-400 mt-4">Klik ikon kamera untuk ganti foto.</p>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-8 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-lg font-bold text-slate-900">Perbarui Informasi</h3>
                        <p class="text-sm text-slate-500">Pastikan data Anda selalu up-to-date.</p>
                    </div>
                    
                    <div class="p-8 space-y-8">
                        
                        <div class="space-y-5">
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2">Identitas Dasar</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap</label>
                                    <div class="relative">
                                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" class="w-full pl-10 pr-4 py-3 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition" placeholder="Nama Anda">
                                        <i data-feather="user" class="absolute left-3.5 top-3.5 w-4 h-4 text-slate-400"></i>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
                                    <div class="relative">
                                        <input type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled class="w-full pl-10 pr-4 py-3 border border-slate-200 bg-slate-50 text-slate-500 rounded-xl text-sm cursor-not-allowed">
                                        <i data-feather="mail" class="absolute left-3.5 top-3.5 w-4 h-4 text-slate-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2">Peran & Kontak</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Jabatan / Divisi</label>
                                    <div class="relative">
                                        <select name="position" class="w-full pl-10 pr-10 py-3 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none appearance-none bg-white cursor-pointer">
                                            <?php foreach($roles_list as $role): ?>
                                                <option value="<?php echo $role; ?>" <?php echo (isset($user['position']) && $user['position'] == $role) ? 'selected' : ''; ?>>
                                                    <?php echo $role; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <i data-feather="briefcase" class="absolute left-3.5 top-3.5 w-4 h-4 text-slate-400"></i>
                                        <i data-feather="chevron-down" class="absolute right-3.5 top-3.5 w-4 h-4 text-slate-400 pointer-events-none"></i>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">WhatsApp</label>
                                    <div class="relative">
                                        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" class="w-full pl-10 pr-4 py-3 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none transition" placeholder="08xxxxxxxxxx">
                                        <i data-feather="smartphone" class="absolute left-3.5 top-3.5 w-4 h-4 text-slate-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2">Tentang Saya</h4>
                            <div class="relative">
                                <textarea name="bio" rows="4" class="w-full pl-10 pr-4 py-3 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none transition resize-none leading-relaxed" placeholder="Ceritakan sedikit tentang diri Anda..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                <i data-feather="align-left" class="absolute left-3.5 top-3.5 w-4 h-4 text-slate-400"></i>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex justify-end">
                            <button type="submit" name="update_profile" class="bg-slate-900 text-white px-8 py-3 rounded-xl font-bold text-sm hover:bg-primary transition shadow-lg shadow-slate-900/20 transform hover:-translate-y-0.5 flex items-center gap-2">
                                <i data-feather="save" class="w-4 h-4"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <script>
        feather.replace();

        // Fitur Heuristik: Live Preview Image
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('previewImg');
                output.src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>