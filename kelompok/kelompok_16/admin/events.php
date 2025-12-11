<?php
session_start();
include '../config.php';

// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { header("Location: ../login.php"); exit; }

// Hapus Event
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM events WHERE id='$id'");
    header("Location: events.php");
}

// Ambil Data
$query = mysqli_query($conn, "SELECT e.*, (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id) as registered FROM events e ORDER BY event_date DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script> tailwind.config = { theme: { extend: { colors: { primary: '#2563eb', secondary: '#1e293b' } } } } </script>
</head>
<body class="bg-slate-50 font-sans text-slate-800">
    <?php include 'sidebar.php'; ?>

    <main class="md:ml-64 p-8 min-h-screen">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Manajemen Event</h1>
                <p class="text-slate-500 text-sm">Kelola jadwal, kuota, dan formulir pendaftaran.</p>
            </div>
            <a href="event_form.php" class="bg-primary text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-blue-700 transition shadow-lg shadow-blue-500/30">
                <i data-feather="plus-circle"></i> Buat Event Baru
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold border-b border-slate-100">
                    <tr>
                        <th class="p-5">Event</th>
                        <th class="p-5">Jadwal</th>
                        <th class="p-5">Kuota</th>
                        <th class="p-5">Status</th>
                        <th class="p-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php while($row = mysqli_fetch_assoc($query)): ?>
                    <tr class="hover:bg-slate-50 transition group">
                        <td class="p-5">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-lg bg-slate-200 overflow-hidden flex-shrink-0">
                                    <?php if($row['image_url']): ?>
                                        <img src="../<?php echo $row['image_url']; ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-slate-400"><i data-feather="image"></i></div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 text-base"><?php echo htmlspecialchars($row['title']); ?></p>
                                    <p class="text-xs text-slate-500 mt-1 truncate max-w-[200px]"><?php echo htmlspecialchars($row['location']); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="p-5">
                            <p class="font-medium text-slate-700"><?php echo date('d M Y', strtotime($row['event_date'])); ?></p>
                            <p class="text-xs text-slate-500"><?php echo date('H:i', strtotime($row['event_date'])); ?> WIB</p>
                        </td>
                        <td class="p-5">
                            <div class="flex items-center gap-2">
                                <div class="w-full bg-slate-100 rounded-full h-1.5 w-24">
                                    <div class="bg-primary h-1.5 rounded-full" style="width: <?php echo ($row['registered']/$row['quota'])*100; ?>%"></div>
                                </div>
                                <span class="text-xs font-bold text-slate-600"><?php echo $row['registered']; ?>/<?php echo $row['quota']; ?></span>
                            </div>
                        </td>
                        <td class="p-5">
                            <?php if($row['status'] == 'open'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span> Buka
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                    Tutup
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="p-5 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="event_participants.php?id=<?php echo $row['id']; ?>" class="p-2 text-slate-400 hover:text-primary hover:bg-blue-50 rounded-lg transition" title="Lihat Peserta">
                                    <i data-feather="users" class="w-4 h-4"></i>
                                </a>
                                <a href="event_form.php?edit=<?php echo $row['id']; ?>" class="p-2 text-slate-400 hover:text-orange-500 hover:bg-orange-50 rounded-lg transition" title="Edit">
                                    <i data-feather="edit-2" class="w-4 h-4"></i>
                                </a>
                                <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Hapus event ini?')" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                    <i data-feather="trash-2" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script>feather.replace();</script>
</body>
</html>