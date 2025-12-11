<?php
// 1. Cek & Mulai Session (Agar tidak error "Session already started")
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Konfigurasi Database (BAGIAN INI WAJIB ADA)
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_komunitas";

// Membuat koneksi ke database
$conn = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$conn) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}

// 3. Set Timezone
date_default_timezone_set('Asia/Jakarta');

// 4. Logika Pelacak User Online
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $current_time = date("Y-m-d H:i:s");
    // Pastikan variabel $conn terbaca di sini
    mysqli_query($conn, "UPDATE users SET last_activity = '$current_time' WHERE id = '$uid'");
}

// 5. Fungsi Helper: Badge Role
function get_role_badge($position) {
    $pos = empty($position) ? 'Anggota' : $position;
    
    if (strpos($pos, 'Ketua') !== false) {
        return '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-700 border border-amber-200 shadow-sm ml-2"><i data-feather="award" class="w-3 h-3"></i> '.$pos.'</span>';
    } elseif (strpos($pos, 'Sekretaris') !== false || strpos($pos, 'Bendahara') !== false) {
        return '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-100 text-indigo-700 border border-indigo-200 ml-2"><i data-feather="briefcase" class="w-3 h-3"></i> '.$pos.'</span>';
    } elseif (strpos($pos, 'Divisi') !== false) {
        return '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-700 border border-emerald-200 ml-2"><i data-feather="layers" class="w-3 h-3"></i> '.$pos.'</span>';
    } else {
        return '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200 ml-2"><i data-feather="user" class="w-3 h-3"></i> '.$pos.'</span>';
    }
}

// --- FUNGSI KIRIM NOTIFIKASI KE ADMIN ---
function sendNotification($conn, $type, $message, $link = '#') {
    $type = mysqli_real_escape_string($conn, $type);
    $message = mysqli_real_escape_string($conn, $message);
    $link = mysqli_real_escape_string($conn, $link);
    
    $query = "INSERT INTO notifications (type, message, action_link) VALUES ('$type', '$message', '$link')";
    mysqli_query($conn, $query);
}

// --- FUNGSI HITUNG NOTIFIKASI BELUM DIBACA ---
function getUnreadCount($conn) {
    $q = mysqli_query($conn, "SELECT COUNT(*) as total FROM notifications WHERE is_read = 0");
    $d = mysqli_fetch_assoc($q);
    return $d['total'];
}


?>
