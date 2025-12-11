<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { header("Location: ../login.php"); exit; }

// Statistik
$count_members = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='member'"))['c'];
$count_events = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM events"))['c'];
// Hitung Total Uang Donasi yang CONFIRMED
$total_donasi_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM donation_transactions WHERE status='confirmed'"));
$total_donasi = $total_donasi_res['total'] ? $total_donasi_res['total'] : 0;

// --- NOTIFIKASI GABUNGAN (UNION Query) ---
// Mengambil 5 aktivitas terakhir (baik itu daftar event ATAU donasi)
$query_notif = "
    (SELECT 'event' as type, u.full_name as actor, e.title as item, r.registered_at as time, r.id as ref_id
     FROM event_registrations r
     JOIN users u ON r.user_id = u.id
     JOIN events e ON r.event_id = e.id)
    UNION
    (SELECT 'donation' as type, donor_name as actor, amount as item, created_at as time, status as ref_id
     FROM donation_transactions)
    ORDER BY time DESC LIMIT 6
";
$result_notif = mysqli_query($conn, $query_notif);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script> tailwind.config = { theme: { extend: { colors: { primary: '#2563eb', secondary: '#1e293b' } } } } </script>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    <?php include 'sidebar.php'; ?>

    <main class="md:ml-64 p-8 min-h-screen">
        <header class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Dashboard Overview</h1>
                <p class="text-slate-500">Pantau aktivitas komunitas secara real-time.</p>
            </div>
            <div class="flex items-center gap-3 bg-white px-4 py-2 rounded-full shadow-sm border border-slate-200">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-sm font-medium">System Online</span>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-medium">Total Anggota</p>
                    <h3 class="text-3xl font-bold text-slate-800 mt-1"><?php echo $count_members; ?></h3>
                </div>
                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl"><i data-feather="users"></i></div>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-medium">Event Aktif</p>
                    <h3 class="text-3xl font-bold text-slate-800 mt-1"><?php echo $count_events; ?></h3>
                </div>
                <div class="p-3 bg-purple-50 text-purple-600 rounded-xl"><i data-feather="calendar"></i></div>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-medium">Total Donasi</p>
                    <h3 class="text-2xl font-bold text-green-600 mt-1">Rp <?php echo number_format($total_donasi/1000, 0); ?>k</h3>
                </div>
                <div class="p-3 bg-green-50 text-green-600 rounded-xl"><i data-feather="dollar-sign"></i></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-6 border-b border-slate-100">
                <h2 class="font-bold text-lg text-slate-800">Aktivitas Terbaru</h2>
            </div>
            <div class="divide-y divide-slate-100">
                <?php if(mysqli_num_rows($result_notif) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result_notif)): ?>
                        <div class="p-5 flex items-start gap-4 hover:bg-slate-50 transition">
                            <?php if($row['type'] == 'event'): ?>
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                                    <i data-feather="user-plus" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-800">
                                        <span class="font-bold"><?php echo htmlspecialchars($row['actor']); ?></span> 
                                        mendaftar event 
                                        <span class="font-bold text-blue-600"><?php echo htmlspecialchars($row['item']); ?></span>
                                    </p>
                                    <p class="text-xs text-slate-400 mt-1"><?php echo date('d M Y, H:i', strtotime($row['time'])); ?></p>
                                </div>
                            <?php else: // Donation ?>
                                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 flex-shrink-0">
                                    <i data-feather="dollar-sign" class="w-5 h-5"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between">
                                        <p class="text-sm text-slate-800">
                                            <span class="font-bold"><?php echo htmlspecialchars($row['actor']); ?></span> 
                                            berdonasi 
                                            <span class="font-bold text-green-600">Rp <?php echo number_format($row['item'], 0, ',', '.'); ?></span>
                                        </p>
                                        <?php if($row['ref_id'] == 'pending'): ?>
                                            <span class="text-[10px] font-bold bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full uppercase">Pending</span>
                                        <?php elseif($row['ref_id'] == 'confirmed'): ?>
                                            <span class="text-[10px] font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded-full uppercase">Sukses</span>
                                        <?php else: ?>
                                            <span class="text-[10px] font-bold bg-red-100 text-red-700 px-2 py-0.5 rounded-full uppercase">Gagal</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1"><?php echo date('d M Y, H:i', strtotime($row['time'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="p-8 text-center text-slate-400">Belum ada aktivitas terbaru.</div>
                <?php endif; ?>
            </div>
        </div>

    </main>
    <script>feather.replace();</script>
</body>
</html>