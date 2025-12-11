<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { header("Location: ../login.php"); exit; }

// 1. TANDAI SEMUA DIBACA
if (isset($_POST['mark_all_read'])) {
    mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE is_read = 0");
    header("Location: notifications.php"); exit;
}

// 2. HAPUS SATU
if (isset($_GET['del'])) {
    $id = $_GET['del'];
    mysqli_query($conn, "DELETE FROM notifications WHERE id='$id'");
    header("Location: notifications.php"); exit;
}

// 3. AMBIL DATA (Pagination bisa ditambahkan nanti jika data ribuan)
$query = mysqli_query($conn, "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 50");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pusat Notifikasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script> tailwind.config = { theme: { extend: { colors: { primary: '#2563eb', secondary: '#1e293b' } } } } </script>
</head>
<body class="bg-slate-50 font-sans text-slate-800">
    
    <?php include 'sidebar.php'; ?>

    <main class="md:ml-64 p-8 min-h-screen">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                    <i data-feather="bell" class="text-primary"></i> Pusat Notifikasi
                </h1>
                <p class="text-slate-500 text-sm">Pantau aktivitas terbaru anggota dan sistem.</p>
            </div>
            
            <?php if(getUnreadCount($conn) > 0): ?>
            <form method="POST">
                <button type="submit" name="mark_all_read" class="bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl text-sm font-bold hover:bg-slate-50 hover:text-primary transition flex items-center gap-2 shadow-sm">
                    <i data-feather="check-square" class="w-4 h-4"></i> Tandai Semua Dibaca
                </button>
            </form>
            <?php endif; ?>
        </div>

        <div class="space-y-3">
            <?php if(mysqli_num_rows($query) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($query)): 
                    // Styling beda untuk Read vs Unread
                    $bgClass = $row['is_read'] == 0 ? 'bg-white border-l-4 border-l-primary shadow-md' : 'bg-slate-100 border border-slate-200 opacity-70';
                    $textClass = $row['is_read'] == 0 ? 'text-slate-800' : 'text-slate-500';
                    
                    // Icon berdasarkan tipe
                    $icon = 'info';
                    $iconColor = 'text-blue-500 bg-blue-100';
                    if($row['type'] == 'event_registration') { $icon = 'calendar'; $iconColor = 'text-green-600 bg-green-100'; }
                    if($row['type'] == 'donation') { $icon = 'heart'; $iconColor = 'text-rose-600 bg-rose-100'; }
                ?>
                <div class="relative group p-4 rounded-xl flex items-start gap-4 transition-all hover:translate-x-1 <?php echo $bgClass; ?>">
                    
                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 <?php echo $iconColor; ?>">
                        <i data-feather="<?php echo $icon; ?>" class="w-5 h-5"></i>
                    </div>

                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <p class="text-sm <?php echo $textClass; ?> leading-relaxed">
                                <?php echo $row['message']; ?>
                            </p>
                            <span class="text-[10px] text-slate-400 whitespace-nowrap ml-4">
                                <?php echo date('d M H:i', strtotime($row['created_at'])); ?>
                            </span>
                        </div>
                        
                        <?php if(!empty($row['action_link']) && $row['action_link'] != '#'): ?>
                            <a href="<?php echo $row['action_link']; ?>" class="inline-flex items-center gap-1 text-xs font-bold text-primary mt-2 hover:underline">
                                Lihat Detail <i data-feather="arrow-right" class="w-3 h-3"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                    <a href="?del=<?php echo $row['id']; ?>" class="absolute top-2 right-2 p-1.5 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-full opacity-0 group-hover:opacity-100 transition" title="Hapus Notif">
                        <i data-feather="x" class="w-4 h-4"></i>
                    </a>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-20 bg-white rounded-2xl border border-dashed border-slate-300">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                        <i data-feather="bell-off" class="w-8 h-8"></i>
                    </div>
                    <p class="text-slate-500 font-medium">Tidak ada notifikasi saat ini.</p>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <script>feather.replace();</script>
</body>
</html>