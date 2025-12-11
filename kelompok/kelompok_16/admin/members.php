<?php
session_start();
include '../config.php';

// 1. CEK AKSES ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { header("Location: ../login.php"); exit; }

// 2. LOGIKA HAPUS MEMBER
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    
    // Mencegah hapus akun sendiri
    if ($id_to_delete == $_SESSION['user_id']) {
        echo "<script>alert('Anda tidak bisa menghapus akun Anda sendiri!'); window.location='members.php';</script>";
    } else {
        // Ambil info gambar untuk dihapus
        $q_img = mysqli_query($conn, "SELECT avatar FROM users WHERE id='$id_to_delete'");
        $img = mysqli_fetch_assoc($q_img)['avatar'];
        if ($img && file_exists("../" . $img)) { unlink("../" . $img); }

        mysqli_query($conn, "DELETE FROM users WHERE id='$id_to_delete'");
        header("Location: members.php");
    }
}

// 3. LOGIKA PENCARIAN & PAGINATION
$search = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$where = "WHERE role != ''"; // Default where
if ($search) {
    $where .= " AND (full_name LIKE '%$search%' OR email LIKE '%$search%')";
}

// Pagination Setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Total Data
$total_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users $where"));
$total_rows = $total_res['c'];
$total_pages = ceil($total_rows / $limit);

// Query Data Utama
$query = mysqli_query($conn, "SELECT * FROM users $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");

// 4. STATISTIK RINGKAS
$count_admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='admin'"))['c'];
$count_member = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='member'"))['c'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Data Member</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script> tailwind.config = { theme: { extend: { colors: { primary: '#2563eb', secondary: '#1e293b' } } } } </script>
</head>
<body class="bg-slate-50 font-sans text-slate-800">
    <?php include 'sidebar.php'; ?>

    <main class="md:ml-64 p-8 min-h-screen">
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Data Anggota</h1>
                <p class="text-slate-500 text-sm">Kelola seluruh pengguna terdaftar di sistem.</p>
            </div>
            
            <button class="bg-white border border-slate-300 text-slate-600 px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 hover:bg-slate-50 transition">
                <i data-feather="download"></i> Export Excel
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total User</p>
                    <h3 class="text-2xl font-bold text-slate-800"><?php echo $total_rows; ?></h3>
                </div>
                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl"><i data-feather="users"></i></div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Member Biasa</p>
                    <h3 class="text-2xl font-bold text-slate-800"><?php echo $count_member; ?></h3>
                </div>
                <div class="p-3 bg-green-50 text-green-600 rounded-xl"><i data-feather="user"></i></div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Administrator</p>
                    <h3 class="text-2xl font-bold text-slate-800"><?php echo $count_admin; ?></h3>
                </div>
                <div class="p-3 bg-purple-50 text-purple-600 rounded-xl"><i data-feather="shield"></i></div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-t-2xl border border-slate-200 border-b-0 flex flex-col md:flex-row justify-between items-center gap-4">
            <form action="" method="GET" class="relative w-full md:w-96">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari nama atau email..." 
                    class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                <i data-feather="search" class="absolute left-3 top-2.5 w-4 h-4 text-slate-400"></i>
            </form>
            <div class="flex gap-2">
                <select class="border border-slate-300 rounded-lg text-sm px-3 py-2 text-slate-600 bg-white outline-none">
                    <option>Semua Role</option>
                    <option>Admin</option>
                    <option>Member</option>
                </select>
            </div>
        </div>

        <div class="bg-white border border-slate-200 shadow-sm overflow-hidden rounded-b-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold border-b border-slate-100">
                        <tr>
                            <th class="p-5 w-16">#</th>
                            <th class="p-5">Profil</th>
                            <th class="p-5">Kontak</th>
                            <th class="p-5">Role</th>
                            <th class="p-5">Bergabung</th>
                            <th class="p-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php if(mysqli_num_rows($query) > 0): ?>
                            <?php $no = $offset + 1; while($row = mysqli_fetch_assoc($query)): ?>
                            <tr class="hover:bg-slate-50 transition group">
                                <td class="p-5 text-slate-400 font-medium"><?php echo $no++; ?></td>
                                <td class="p-5">
                                    <div class="flex items-center gap-3">
                                        <?php if($row['avatar']): ?>
                                            <img src="../<?php echo $row['avatar']; ?>" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                                        <?php else: ?>
                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($row['full_name']); ?>&background=random&color=fff" class="w-10 h-10 rounded-full border border-slate-200">
                                        <?php endif; ?>
                                        
                                        <div>
                                            <p class="font-bold text-slate-800"><?php echo htmlspecialchars($row['full_name']); ?></p>
                                            <p class="text-xs text-slate-400 truncate max-w-[150px]">
                                                <?php echo $row['bio'] ? htmlspecialchars($row['bio']) : 'Belum ada bio'; ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-5">
                                    <div class="flex flex-col">
                                        <span class="text-slate-700 flex items-center gap-2"><i data-feather="mail" class="w-3 h-3 text-slate-400"></i> <?php echo htmlspecialchars($row['email']); ?></span>
                                        <span class="text-slate-500 text-xs mt-1 flex items-center gap-2"><i data-feather="phone" class="w-3 h-3 text-slate-400"></i> <?php echo $row['phone_number'] ? $row['phone_number'] : '-'; ?></span>
                                    </div>
                                </td>
                                <td class="p-5">
                                    <?php if($row['role'] == 'admin'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-700 border border-purple-200">
                                            <i data-feather="shield" class="w-3 h-3 mr-1"></i> Admin
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-600 border border-blue-100">
                                            Member
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-5 text-slate-500">
                                    <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                                </td>
                                <td class="p-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="member_detail.php?id=<?php echo $row['id']; ?>" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Lihat Detail">
                                            <i data-feather="eye" class="w-4 h-4"></i>
                                        </a>
                                        
                                        <?php if($row['id'] != $_SESSION['user_id']): ?>
                                            <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus user ini? Data tidak bisa dikembalikan.')" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus User">
                                                <i data-feather="trash-2" class="w-4 h-4"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="p-2 text-slate-200 cursor-not-allowed" title="Anda"><i data-feather="lock" class="w-4 h-4"></i></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="p-10 text-center">
                                    <div class="inline-flex bg-slate-50 p-4 rounded-full mb-3 text-slate-300">
                                        <i data-feather="search" class="w-8 h-8"></i>
                                    </div>
                                    <p class="text-slate-500 font-medium">Data member tidak ditemukan.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($total_pages > 1): ?>
            <div class="p-4 border-t border-slate-200 bg-slate-50 flex justify-between items-center">
                <span class="text-xs text-slate-500">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?></span>
                <div class="flex gap-2">
                    <?php if($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?>&q=<?php echo $search; ?>" class="px-3 py-1 border border-slate-300 rounded text-xs hover:bg-white transition">Sebelumnya</a>
                    <?php endif; ?>
                    
                    <?php if($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?>&q=<?php echo $search; ?>" class="px-3 py-1 border border-slate-300 rounded text-xs hover:bg-white transition">Selanjutnya</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </main>
    <script>feather.replace();</script>
</body>
</html>