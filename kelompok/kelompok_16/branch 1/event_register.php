<?php
session_start();
include 'config.php';

// 1. CEK LOGIN
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu untuk mendaftar.'); window.location='login.php';</script>";
    exit;
}

// 2. VALIDASI ID EVENT
if (!isset($_GET['id']) || empty($_GET['id'])) { header("Location: events.php"); exit; }

$event_id = mysqli_real_escape_string($conn, $_GET['id']);
$user_id = $_SESSION['user_id'];

// --- AMBIL DATA USER (UNTUK AUTO-FILL NO HP) ---
$q_user = mysqli_query($conn, "SELECT phone FROM users WHERE id = '$user_id'");
$d_user = mysqli_fetch_assoc($q_user);
$user_phone = $d_user['phone'] ?? ''; // Ambil no hp dari database user

// Ambil Detail Event
$query_event = mysqli_query($conn, "SELECT * FROM events WHERE id = '$event_id'");
if(mysqli_num_rows($query_event) == 0) {
    echo "Event tidak ditemukan."; exit;
}
$event = mysqli_fetch_assoc($query_event);

// Cek Kuota & Peserta
$q_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM event_registrations WHERE event_id = '$event_id'");
$current_participants = mysqli_fetch_assoc($q_count)['total'];
$quota_percent = ($event['quota'] > 0) ? ($current_participants / $event['quota']) * 100 : 0;
$seats_left = $event['quota'] - $current_participants;
$is_full = ($current_participants >= $event['quota']);

// Cek User Sudah Daftar?
$check_reg = mysqli_query($conn, "SELECT * FROM event_registrations WHERE user_id = '$user_id' AND event_id = '$event_id'");
$is_registered = (mysqli_num_rows($check_reg) > 0);
$registration_data = mysqli_fetch_assoc($check_reg);

// Parse Custom Fields
$custom_fields_array = [];
if (!empty($event['custom_fields'])) {
    $custom_fields_array = array_map('trim', explode(',', $event['custom_fields']));
}

// 3. PROSES DAFTAR
if (isset($_POST['register']) && !$is_registered && !$is_full) {
    
    $answers = [];
    
    // --- SIMPAN NO HP KE DATA JAWABAN ---
    if(isset($_POST['phone'])) {
        $phone_input = htmlspecialchars($_POST['phone']);
        $answers['Nomor_WhatsApp'] = $phone_input; // Simpan di JSON
        
        // Opsional: Update data user master juga agar profil terupdate
        mysqli_query($conn, "UPDATE users SET phone = '$phone_input' WHERE id = '$user_id'");
    }

    // Simpan Jawaban Custom Fields
    foreach ($custom_fields_array as $field) {
        $key = str_replace(' ', '_', $field);
        if (isset($_POST[$key])) {
            $answers[$field] = htmlspecialchars($_POST[$key]);
        }
    }
    
    $json_answers = mysqli_real_escape_string($conn, json_encode($answers));

    $insert = mysqli_query($conn, "INSERT INTO event_registrations (user_id, event_id, custom_answers) VALUES ('$user_id', '$event_id', '$json_answers')");
    
    if ($insert) {
        // --- KIRIM NOTIFIKASI KE ADMIN ---
        if (function_exists('sendNotification')) {
            $user_name = $_SESSION['user_name'];
            $event_title = $event['title'];
            $notif_msg = "Pendaftar Baru: <b>$user_name</b> telah mendaftar di event <b>$event_title</b>.";
            $link_detail = "event_participants.php?id=" . $event_id; 
            
            sendNotification($conn, 'event_registration', $notif_msg, $link_detail);
        }

        // Refresh page
        header("Location: event_register.php?id=$event_id&status=success");
        exit;
    } else {
        echo "<script>alert('Gagal mendaftar. Silakan coba lagi.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar | <?php echo htmlspecialchars($event['title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <script> tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] }, colors: { primary: '#2563eb', secondary: '#1e293b' } } } } </script>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

   <?php include 'navbar_include.php'; ?>
   <script>const isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;</script>

    <div class="max-w-5xl mx-auto px-4 py-8 pt-32">
        
        <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3 text-green-700 animate-pulse">
            <i data-feather="check-circle"></i>
            <span class="font-bold">Selamat! Pendaftaran Anda berhasil dikonfirmasi.</span>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                    <div class="w-full aspect-video bg-slate-200 relative">
                        <?php 
                            $img_url = $event['image_url'];
                            $display_img = "";
                            if(!empty($img_url)){
                                $fname = basename($img_url);
                                if(file_exists('uploads/events/'.$fname)) $display_img = 'uploads/events/'.$fname;
                                elseif(file_exists('admin/uploads/events/'.$fname)) $display_img = 'admin/uploads/events/'.$fname;
                                else $display_img = $img_url;
                            }

                            $file_ext = strtolower(pathinfo($display_img, PATHINFO_EXTENSION));
                            $is_video = in_array($file_ext, ['mp4', 'mov', 'avi', 'webm']);
                        ?>
                        
                        <?php if(!empty($display_img)): ?>
                            <?php if($is_video): ?>
                                <video src="<?php echo $display_img; ?>" controls class="w-full h-full object-cover"></video>
                            <?php else: ?>
                                <img src="<?php echo $display_img; ?>" class="w-full h-full object-cover">
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-slate-400 flex-col gap-2">
                                <i data-feather="image" class="w-12 h-12"></i>
                                <span class="text-sm">Tidak ada gambar</span>
                            </div>
                        <?php endif; ?>

                        <?php if($event['status'] == 'closed'): ?>
                            <div class="absolute top-4 right-4 bg-red-600 text-white px-4 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg">Pendaftaran Ditutup</div>
                        <?php elseif($is_full): ?>
                            <div class="absolute top-4 right-4 bg-orange-500 text-white px-4 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg">Kuota Penuh</div>
                        <?php else: ?>
                            <div class="absolute top-4 right-4 bg-green-500 text-white px-4 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg">Pendaftaran Buka</div>
                        <?php endif; ?>
                    </div>

                    <div class="p-6 md:p-8">
                        <span class="text-primary font-bold tracking-wide text-xs uppercase mb-2 block"><?php echo htmlspecialchars($event['type']); ?></span>
                        <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mb-4"><?php echo htmlspecialchars($event['title']); ?></h1>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 pb-6 border-b border-slate-100">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-blue-50 text-primary rounded-lg"><i data-feather="calendar" class="w-5 h-5"></i></div>
                                <div>
                                    <p class="text-xs text-slate-500 font-bold uppercase">Tanggal</p>
                                    <p class="text-sm font-semibold text-slate-800"><?php echo date('d F Y', strtotime($event['event_date'])); ?></p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-blue-50 text-primary rounded-lg"><i data-feather="clock" class="w-5 h-5"></i></div>
                                <div>
                                    <p class="text-xs text-slate-500 font-bold uppercase">Waktu</p>
                                    <p class="text-sm font-semibold text-slate-800"><?php echo date('H:i', strtotime($event['event_date'])); ?> WIB - Selesai</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-blue-50 text-primary rounded-lg"><i data-feather="map-pin" class="w-5 h-5"></i></div>
                                <div>
                                    <p class="text-xs text-slate-500 font-bold uppercase">Lokasi</p>
                                    <p class="text-sm font-semibold text-slate-800"><?php echo htmlspecialchars($event['location']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-blue-50 text-primary rounded-lg"><i data-feather="users" class="w-5 h-5"></i></div>
                                <div>
                                    <p class="text-xs text-slate-500 font-bold uppercase">Kuota</p>
                                    <p class="text-sm font-semibold text-slate-800"><?php echo $event['quota']; ?> Peserta</p>
                                </div>
                            </div>
                        </div>

                        <div class="prose prose-slate max-w-none text-sm leading-relaxed text-slate-600">
                            <h3 class="text-lg font-bold text-slate-800 mb-2">Tentang Acara Ini</h3>
                            <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="sticky top-24 space-y-6">
                    
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <h3 class="font-bold text-slate-800 mb-4">Status Pendaftaran</h3>
                        
                        <div class="mb-2 flex justify-between text-xs font-bold">
                            <span class="text-slate-500">Terisi: <?php echo $current_participants; ?></span>
                            <span class="text-primary"><?php echo round($quota_percent); ?>%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 mb-4">
                            <div class="bg-primary h-2.5 rounded-full transition-all duration-1000" style="width: <?php echo $quota_percent; ?>%"></div>
                        </div>

                        <?php if($is_full): ?>
                            <div class="bg-red-50 text-red-700 p-3 rounded-lg text-sm text-center font-bold">Maaf, Kuota Sudah Penuh :(</div>
                        <?php elseif($event['status'] == 'closed'): ?>
                            <div class="bg-slate-100 text-slate-600 p-3 rounded-lg text-sm text-center font-bold">Pendaftaran Telah Ditutup</div>
                        <?php else: ?>
                            <div class="bg-green-50 text-green-700 p-3 rounded-lg text-sm text-center font-bold mb-4">Tersisa <?php echo $seats_left; ?> Kursi Lagi!</div>
                        <?php endif; ?>
                    </div>

                    <?php if($is_registered): ?>
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden relative">
                            <div class="bg-slate-800 p-4 text-center">
                                <p class="text-xs text-slate-400 uppercase tracking-widest">E-Ticket Anda</p>
                            </div>
                            <div class="p-6 text-center">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=REG-<?php echo $event_id . '-' . $user_id; ?>" alt="QR Code" class="mx-auto border p-2 rounded-lg mb-4">
                                <p class="font-bold text-slate-800 text-lg"><?php echo $_SESSION['user_name']; ?></p>
                                <p class="text-xs text-slate-400 mt-1">Tunjukkan QR ini saat registrasi ulang.</p>
                            </div>
                        </div>

                    <?php elseif(!$is_full && $event['status'] == 'open'): ?>
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                            <h3 class="font-bold text-slate-800 mb-4">Formulir Peserta</h3>
                            <form action="" method="POST" class="space-y-4">
                                
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Lengkap</label>
                                    <input type="text" value="<?php echo $_SESSION['user_name']; ?>" readonly class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-500 cursor-not-allowed">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nomor WhatsApp / HP <span class="text-red-500">*</span></label>
                                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user_phone); ?>" required placeholder="Contoh: 0812xxxxxxxx" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:border-primary outline-none transition">
                                </div>

                                <?php foreach($custom_fields_array as $field): ?>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1"><?php echo $field; ?> <span class="text-red-500">*</span></label>
                                    <input type="text" name="<?php echo str_replace(' ', '_', $field); ?>" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:border-primary outline-none transition">
                                </div>
                                <?php endforeach; ?>

                                <div class="flex items-start gap-2 pt-2">
                                    <input type="checkbox" id="agree" required class="mt-1 w-4 h-4 text-primary rounded border-slate-300">
                                    <label for="agree" class="text-xs text-slate-500 leading-tight">Saya bersedia mengikuti aturan acara.</label>
                                </div>

                                <button type="submit" name="register" class="w-full py-3 bg-primary text-white font-bold rounded-xl shadow-lg hover:bg-blue-700 transition transform hover:-translate-y-0.5 mt-2">
                                    Daftar Sekarang
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>

    <script>feather.replace();</script>
</body>
</html>