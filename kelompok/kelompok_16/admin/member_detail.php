<?php
session_start();
include '../config.php';

// 1. CEK AKSES
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { header("Location: ../login.php"); exit; }

// 2. VALIDASI ID
if (!isset($_GET['id']) || empty($_GET['id'])) { header("Location: members.php"); exit; }
$id = $_GET['id'];

// 3. AMBIL DATA USER
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE id='$id'");
if (mysqli_num_rows($query_user) == 0) { echo "User tidak ditemukan."; exit; }
$user = mysqli_fetch_assoc($query_user);

// 4. AMBIL DATA EVENT YANG DIIKUTI
$query_events = mysqli_query($conn, "SELECT e.title, e.event_date, e.status, r.registered_at 
                                     FROM event_registrations r 
                                     JOIN events e ON r.event_id = e.id 
                                     WHERE r.user_id = '$id' 
                                     ORDER BY e.event_date DESC");

// 5. AMBIL DATA FORUM YANG DIBUAT
$query_forum = mysqli_query($conn, "SELECT title, category, views, created_at FROM forum_topics WHERE user_id = '$id' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Detail Member | <?php echo htmlspecialchars($user['full_name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script> tailwind.config = { theme: { extend: { colors: { primary: '#2563eb', secondary: '#1e293b' } } } } </script>
</head>
<body class="bg-slate-50 font-sans text-slate-800">
    <?php include 'sidebar.php'; ?>

    <main class="md:ml-64 p-8 min-h-screen">
        
        <div class="flex items-center gap-4 mb-8">
            <a href="members.php" class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-500 transition">
                <i data-feather="arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Detail Anggota</h1>
                <p class="text-slate-500 text-sm">Lihat profil dan aktivitas pengguna.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 text-center sticky top-8">
                    <div class="relative inline-block mb-4">
                        <?php if($user['avatar']): ?>
                            <img src="../<?php echo $user['avatar']; ?>" class="w-32 h-32 rounded-full object-cover border-4 border-slate-50 shadow-md mx-auto">
                        <?php else: ?>
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=random&color=fff&size=128" class="w-32 h-32 rounded-full border-4 border-slate-50 shadow-md mx-auto">
                        <?php endif; ?>
                        
                        <?php if($user['role'] == 'admin'): ?>
                            <span class="absolute bottom-0 right-2 bg-purple-600 text-white p-1.5 rounded-full border-2 border-white shadow-sm" title="Admin">
                                <i data-feather="shield" class="w-4 h-4"></i>
                            </span>
                        <?php endif; ?>
                    </div>

                    <h2 class="text-xl font-bold text-slate-900"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <p class="text-slate-500 text-sm mb-6">Bergabung: <?php echo date('d M Y', strtotime($user['created_at'])); ?></p>

                    <div class="text-left space-y-4 border-t border-slate-100 pt-6">
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase">Email</label>
                            <div class="flex items-center gap-2 text-slate-700 font-medium">
                                <i data-feather="mail" class="w-4 h-4 text-primary"></i> 
                                <?php echo htmlspecialchars($user['email']); ?>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase">No. Telepon</label>
                            <div class="flex items-center gap-2 text-slate-700 font-medium">
                                <i data-feather="phone" class="w-4 h-4 text-primary"></i> 
                                <?php echo $user['phone_number'] ? htmlspecialchars($user['phone_number']) : '-'; ?>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase">Bio</label>
                            <p class="text-sm text-slate-600 mt-1 leading-relaxed">
                                <?php echo $user['bio'] ? nl2br(htmlspecialchars($user['bio'])) : 'Belum ada bio.'; ?>
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-100 flex justify-center gap-3">
                        <a href="mailto:<?php echo $user['email']; ?>" class="px-4 py-2 bg-blue-50 text-primary font-bold rounded-lg text-sm hover:bg-blue-100 transition">
                            Kirim Email
                        </a>
                        <?php if($user['id'] != $_SESSION['user_id']): ?>
                            <a href="members.php?delete=<?php echo $user['id']; ?>" onclick="return confirm('Hapus permanen user ini?')" class="px-4 py-2 bg-red-50 text-red-600 font-bold rounded-lg text-sm hover:bg-red-100 transition">
                                Hapus User
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="font-bold text-lg text-slate-800">Riwayat Event</h3>
                        <span class="bg-blue-100 text-primary text-xs font-bold px-2 py-1 rounded-full"><?php echo mysqli_num_rows($query_events); ?> Event</span>
                    </div>
                    
                    <?php if(mysqli_num_rows($query_events) > 0): ?>
                        <div class="divide-y divide-slate-100">
                            <?php while($evt = mysqli_fetch_assoc($query_events)): ?>
                            <div class="p-5 hover:bg-slate-50 transition flex items-center justify-between">
                                <div>
                                    <h4 class="font-bold text-slate-800"><?php echo htmlspecialchars($evt['title']); ?></h4>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Acara: <?php echo date('d M Y', strtotime($evt['event_date'])); ?> • 
                                        Daftar: <?php echo date('d M Y', strtotime($evt['registered_at'])); ?>
                                    </p>
                                </div>
                                <div>
                                    <?php if($evt['status'] == 'open'): ?>
                                        <span class="text-green-600 bg-green-50 px-3 py-1 rounded-full text-xs font-bold">Aktif</span>
                                    <?php else: ?>
                                        <span class="text-slate-500 bg-slate-100 px-3 py-1 rounded-full text-xs font-bold">Selesai</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-8 text-center text-slate-400 text-sm">Belum pernah mengikuti event.</div>
                    <?php endif; ?>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="font-bold text-lg text-slate-800">Topik Forum</h3>
                        <span class="bg-orange-100 text-orange-600 text-xs font-bold px-2 py-1 rounded-full"><?php echo mysqli_num_rows($query_forum); ?> Post</span>
                    </div>

                    <?php if(mysqli_num_rows($query_forum) > 0): ?>
                        <div class="divide-y divide-slate-100">
                            <?php while($forum = mysqli_fetch_assoc($query_forum)): ?>
                            <div class="p-5 hover:bg-slate-50 transition">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider"><?php echo $forum['category']; ?></span>
                                    <span class="text-xs text-slate-400"><?php echo date('d/m/Y', strtotime($forum['created_at'])); ?></span>
                                </div>
                                <h4 class="font-bold text-slate-800"><?php echo htmlspecialchars($forum['title']); ?></h4>
                                <div class="mt-2 flex items-center gap-2 text-xs text-slate-500">
                                    <i data-feather="eye" class="w-3 h-3"></i> <?php echo $forum['views']; ?> Dilihat
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-8 text-center text-slate-400 text-sm">Belum pernah memposting topik.</div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>
    <script>feather.replace();</script>
</body>
</html>