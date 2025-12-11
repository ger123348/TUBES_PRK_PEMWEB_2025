<?php
session_start();
include '../config.php';

// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { header("Location: ../login.php"); exit; }

if (!isset($_GET['id'])) { header("Location: events.php"); exit; }
$event_id = mysqli_real_escape_string($conn, $_GET['id']);

// Ambil Info Event
$q_event = mysqli_query($conn, "SELECT * FROM events WHERE id='$event_id'");
$event = mysqli_fetch_assoc($q_event);

// Ambil Peserta
$q_participants = mysqli_query($conn, "SELECT r.*, u.full_name, u.email, u.phone as profile_phone 
                                       FROM event_registrations r 
                                       JOIN users u ON r.user_id = u.id 
                                       WHERE r.event_id='$event_id' 
                                       ORDER BY r.id DESC");

// --- LOGIKA EXPORT CSV (Tetap disimpan datanya jika butuh download) ---
if (isset($_GET['export'])) {
    $filename = "Peserta_" . preg_replace('/[^a-zA-Z0-9]/', '_', $event['title']) . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('No', 'Nama Lengkap', 'Email', 'No HP', 'Jawaban Form', 'Waktu Daftar'));
    
    $no = 1;
    mysqli_data_seek($q_participants, 0); 
    
    while ($row = mysqli_fetch_assoc($q_participants)) {
        $answers = json_decode($row['custom_answers'], true);
        $phone = $answers['Nomor_WhatsApp'] ?? $row['profile_phone'] ?? '-';
        
        $custom_text = "";
        if($answers && is_array($answers)) {
            foreach($answers as $k => $v) {
                if($k != 'Nomor_WhatsApp') $custom_text .= "$k: $v | ";
            }
        }

        $tgl = isset($row['created_at']) ? $row['created_at'] : '-';
        fputcsv($output, array($no++, $row['full_name'], $row['email'], $phone, $custom_text, $tgl));
    }
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Peserta: <?php echo htmlspecialchars($event['title'] ?? ''); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script> tailwind.config = { theme: { extend: { colors: { primary: '#2563eb', secondary: '#1e293b' } } } } </script>
</head>
<body class="bg-slate-50 font-sans text-slate-800">
    
    <?php include 'sidebar.php'; ?>

    <main class="md:ml-64 p-8 min-h-screen">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <div class="flex items-center gap-2 text-slate-500 text-sm mb-1">
                    <a href="events.php" class="hover:text-primary transition">Events</a>
                    <i data-feather="chevron-right" class="w-4 h-4"></i>
                    <span>Peserta</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900">Peserta: <?php echo htmlspecialchars($event['title'] ?? 'Event Tidak Ditemukan'); ?></h1>
                <p class="text-slate-500 text-sm mt-1">Total Pendaftar: <b><?php echo mysqli_num_rows($q_participants); ?></b> Orang</p>
            </div>
            
            <div class="flex gap-3">
                <a href="events.php" class="bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl text-sm font-bold hover:bg-slate-50 transition flex items-center gap-2">
                    <i data-feather="arrow-left" class="w-4 h-4"></i> Kembali
                </a>
                <a href="?id=<?php echo $event_id; ?>&export=true" class="bg-green-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-green-700 transition flex items-center gap-2 shadow-lg shadow-green-600/20">
                    <i data-feather="download" class="w-4 h-4"></i> Export CSV
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4 font-bold w-16">No</th>
                            <th class="px-6 py-4 font-bold">Nama Peserta</th>
                            <th class="px-6 py-4 font-bold">Kontak (Email & HP)</th>
                            <th class="px-6 py-4 font-bold">Waktu Daftar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php 
                        $no = 1;
                        mysqli_data_seek($q_participants, 0);
                        
                        if(mysqli_num_rows($q_participants) > 0):
                            while($row = mysqli_fetch_assoc($q_participants)): 
                                $answers = json_decode($row['custom_answers'], true);
                                $phone = $answers['Nomor_WhatsApp'] ?? $row['profile_phone'];
                                $phone_display = !empty($phone) ? htmlspecialchars($phone) : '<span class="text-slate-400 italic">Tidak ada</span>';
                        ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 text-slate-500"><?php echo $no++; ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                        <?php echo strtoupper(substr($row['full_name'], 0, 1)); ?>
                                    </div>
                                    <span class="font-bold text-slate-700"><?php echo htmlspecialchars($row['full_name'] ?? ''); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2 text-slate-600">
                                        <i data-feather="mail" class="w-3 h-3 text-slate-400"></i> 
                                        <?php echo htmlspecialchars($row['email'] ?? ''); ?>
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600">
                                        <i data-feather="smartphone" class="w-3 h-3 text-green-500"></i> 
                                        <span class="font-medium"><?php echo $phone_display; ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs">
                                <?php 
                                    if(isset($row['created_at'])) {
                                        echo date('d M Y, H:i', strtotime($row['created_at']));
                                    } else {
                                        echo '<span class="italic text-slate-400">Tidak tercatat</span>';
                                    }
                                ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic bg-slate-50">
                                    Belum ada peserta yang mendaftar.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>feather.replace();</script>
</body>
</html>