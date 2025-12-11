<?php
// --- LOGIKA HITUNG NOTIFIKASI ---
// Pastikan koneksi $conn sudah ada dari file induk (dashboard.php dll)
$unread_count = 0;
if (isset($conn)) {
    $q_notif = mysqli_query($conn, "SELECT COUNT(*) as total FROM notifications WHERE is_read = 0");
    if ($q_notif) {
        $d_notif = mysqli_fetch_assoc($q_notif);
        $unread_count = $d_notif['total'];
    }
}
?>

<style>
    @keyframes swing { 
        0%, 100% { transform: rotate(0deg); } 
        20% { transform: rotate(15deg); } 
        40% { transform: rotate(-10deg); } 
        60% { transform: rotate(5deg); } 
        80% { transform: rotate(-5deg); } 
    }
    .animate-swing { 
        animation: swing 1.5s ease-in-out infinite; 
        transform-origin: top center; 
    }
</style>

<aside class="hidden md:flex flex-col w-64 bg-slate-900 h-screen fixed left-0 top-0 text-white transition-all z-50 border-r border-slate-800">
    <div class="flex items-center justify-center h-20 border-b border-slate-800">
        <span class="font-bold text-xl tracking-wider flex items-center gap-2">
            <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-white text-sm">AP</div>
            ADMIN PANEL
        </span>
    </div>
    
    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2 custom-scrollbar">
        <p class="px-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-2 mt-2">Main Menu</p>
        
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 rounded-xl transition <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-primary text-white shadow-lg shadow-blue-900/20' : 'text-slate-400'; ?>">
            <i data-feather="home" class="w-5 h-5"></i> Dashboard
        </a>

        <a href="notifications.php" class="flex items-center justify-between px-4 py-3 hover:bg-slate-800 rounded-xl transition group <?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'bg-primary text-white shadow-lg shadow-blue-900/20' : 'text-slate-400'; ?>">
            <div class="flex items-center gap-3">
                <i data-feather="bell" class="w-5 h-5 <?php echo $unread_count > 0 ? 'animate-swing text-yellow-400' : ''; ?>"></i> 
                Notifikasi
            </div>
            
            <?php if($unread_count > 0): ?>
                <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm group-hover:bg-red-400 transition">
                    <?php echo $unread_count > 99 ? '99+' : $unread_count; ?>
                </span>
            <?php endif; ?>
        </a>
        
        <a href="events.php" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 rounded-xl transition <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' || basename($_SERVER['PHP_SELF']) == 'event_form.php' ? 'bg-primary text-white' : 'text-slate-400'; ?>">
            <i data-feather="calendar" class="w-5 h-5"></i> Kelola Event
        </a>

        <a href="news.php" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 rounded-xl transition <?php echo basename($_SERVER['PHP_SELF']) == 'news.php' || basename($_SERVER['PHP_SELF']) == 'news_form.php' ? 'bg-primary text-white' : 'text-slate-400'; ?>">
            <i data-feather="file-text" class="w-5 h-5"></i> Berita Acara
        </a>

        <a href="votings.php" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 rounded-xl transition <?php echo basename($_SERVER['PHP_SELF']) == 'votings.php' || basename($_SERVER['PHP_SELF']) == 'voting_manage.php' ? 'bg-primary text-white' : 'text-slate-400'; ?>">
            <i data-feather="check-circle" class="w-5 h-5"></i> Kelola Voting
        </a>

        <p class="px-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-2 mt-6">Donasi & Keuangan</p>

        <a href="campaigns.php" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 rounded-xl transition <?php echo basename($_SERVER['PHP_SELF']) == 'campaigns.php' ? 'bg-primary text-white' : 'text-slate-400'; ?>">
            <i data-feather="layers" class="w-5 h-5"></i> Donasi & Rekening
        </a>

        <a href="donations.php" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 rounded-xl transition <?php echo basename($_SERVER['PHP_SELF']) == 'donations.php' ? 'bg-primary text-white' : 'text-slate-400'; ?>">
            <i data-feather="dollar-sign" class="w-5 h-5"></i> Transaksi Masuk
        </a>

        <p class="px-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-2 mt-6">Pengguna & Sistem</p>
        
        <a href="members.php" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 rounded-xl transition <?php echo basename($_SERVER['PHP_SELF']) == 'members.php' ? 'bg-primary text-white' : 'text-slate-400'; ?>">
            <i data-feather="users" class="w-5 h-5"></i> Data Member
        </a>

        <a href="general.php" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 rounded-xl transition <?php echo basename($_SERVER['PHP_SELF']) == 'general.php' ? 'bg-primary text-white' : 'text-slate-400'; ?>">
            <i data-feather="settings" class="w-5 h-5"></i> Pengaturan Umum
        </a>
    </nav>

    <div class="p-4 border-t border-slate-800 bg-slate-900">
        <a href="../logout.php" class="flex items-center gap-3 px-4 py-3 text-red-400 hover:bg-slate-800 rounded-xl transition group">
            <i data-feather="log-out" class="w-5 h-5 group-hover:text-red-300"></i> Logout
        </a>
    </div>
</aside>