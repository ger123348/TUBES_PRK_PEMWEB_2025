<?php
session_start();
include 'config.php';

// --- 1. PROTEKSI HALAMAN (WAJIB LOGIN) ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$site_name = "Komunitas Maju Bersama";

// --- 2. PROSES SUBMIT VOTE ---
if (isset($_POST['cast_vote'])) {
    $voting_id = mysqli_real_escape_string($conn, $_POST['voting_id']);
    $option_id = mysqli_real_escape_string($conn, $_POST['option_id']);

    // Cek apakah user sudah pernah vote di topik ini?
    $check = mysqli_query($conn, "SELECT id FROM voting_votes WHERE voting_id='$voting_id' AND user_id='$user_id'");
    
    if (mysqli_num_rows($check) == 0) {
        // Simpan Vote
        $save_vote = "INSERT INTO voting_votes (voting_id, user_id, option_id, voted_at) VALUES ('$voting_id', '$user_id', '$option_id', NOW())";
        // Update Counter
        $update_count = "UPDATE voting_options SET vote_count = vote_count + 1 WHERE id='$option_id'";

        if (mysqli_query($conn, $save_vote) && mysqli_query($conn, $update_count)) {
            echo "<script>alert('Suara Anda berhasil direkam!'); window.location='voting.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan sistem.');</script>";
        }
    } else {
        echo "<script>alert('Anda sudah melakukan voting pada sesi ini.');</script>";
    }
}

// --- 3. AMBIL DATA VOTING AKTIF ---
$query_votings = mysqli_query($conn, "SELECT * FROM votings WHERE status = 'active' AND end_date >= CURDATE() ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Voting | <?php echo $site_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { primary: '#2563eb', secondary: '#1e293b', surface: '#F8FAFC' }
                }
            }
        }
    </script>
    <style>
        /* Custom Radio Style */
        input[type="radio"]:checked + .option-card {
            border-color: #2563eb;
            background-color: #eff6ff;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
            transform: translateY(-2px);
        }
        input[type="radio"]:checked + .option-card .check-badge {
            opacity: 1;
            transform: scale(1);
        }
        .option-card { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .check-badge { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body class="bg-surface font-sans text-slate-800 antialiased">

    <?php include 'navbar_include.php'; ?>
<script>const isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;</script>

    <div class="bg-white border-b border-slate-200">
        <div class="max-w-5xl mx-auto px-4 py-12 text-center">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-[11px] font-bold uppercase tracking-wider mb-4 border border-indigo-100">
                <i data-feather="box" class="w-3 h-3"></i> Portal Demokrasi
            </span>
            <h1 class="text-3xl md:text-4xl font-bold text-slate-900 mb-3 tracking-tight">Suara Anda, Masa Depan Kita</h1>
            <p class="text-slate-500 max-w-2xl mx-auto leading-relaxed">Berpartisipasilah dalam pengambilan keputusan komunitas secara transparan dan adil.</p>
        </div>
    </div>

    <main class="max-w-5xl mx-auto px-4 py-12 space-y-12">
        
        <?php if (mysqli_num_rows($query_votings) > 0): ?>
            <?php while($voting = mysqli_fetch_assoc($query_votings)): 
                $vid = $voting['id'];
                
                // 1. Cek User Sudah Vote Belum
                $my_vote_q = mysqli_query($conn, "SELECT * FROM voting_votes WHERE voting_id='$vid' AND user_id='$user_id'");
                $has_voted = (mysqli_num_rows($my_vote_q) > 0);
                
                // 2. Hitung Total Suara Global
                $total_votes_q = mysqli_query($conn, "SELECT COUNT(*) as c FROM voting_votes WHERE voting_id='$vid'");
                $total_votes = mysqli_fetch_assoc($total_votes_q)['c'];

                // 3. Ambil Opsi Kandidat
                $options_q = mysqli_query($conn, "SELECT * FROM voting_options WHERE voting_id='$vid'");
            ?>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden scroll-mt-24">
                
                <div class="p-8 border-b border-slate-100">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold uppercase tracking-wide">
                                <?php echo ($voting['type'] == 'member') ? '👤 Pemilihan Ketua' : '📅 Voting Acara'; ?>
                            </span>
                            <?php if($has_voted): ?>
                                <span class="flex items-center gap-1 text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded-lg">
                                    <i data-feather="check-circle" class="w-3 h-3"></i> Sudah Memilih
                                </span>
                            <?php else: ?>
                                <span class="flex items-center gap-1 text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-lg">
                                    <i data-feather="clock" class="w-3 h-3"></i> Berakhir: <?php echo date('d M Y', strtotime($voting['end_date'])); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-2 leading-tight"><?php echo htmlspecialchars($voting['title']); ?></h2>
                    <p class="text-slate-500 leading-relaxed"><?php echo nl2br(htmlspecialchars($voting['description'])); ?></p>
                </div>

                <div class="p-8 bg-slate-50/50">
                    
                    <?php if ($has_voted): ?>
                        <div class="space-y-5">
                            <?php while($opt = mysqli_fetch_assoc($options_q)): 
                                $percent = ($total_votes > 0) ? ($opt['vote_count'] / $total_votes) * 100 : 0;
                            ?>
                            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="font-bold text-slate-800"><?php echo htmlspecialchars($opt['option_name']); ?></h4>
                                    <span class="font-bold text-slate-900"><?php echo number_format($percent, 1); ?>%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2.5 mb-2 overflow-hidden">
                                    <div class="bg-primary h-2.5 rounded-full transition-all duration-1000" style="width: <?php echo $percent; ?>%"></div>
                                </div>
                                <p class="text-xs text-slate-400 text-right"><?php echo $opt['vote_count']; ?> Suara</p>
                            </div>
                            <?php endwhile; ?>
                            
                            <div class="text-center pt-4">
                                <p class="text-sm text-slate-500">Terima kasih atas partisipasi Anda!</p>
                            </div>
                        </div>

                    <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="voting_id" value="<?php echo $vid; ?>">
                            
                            <div class="grid grid-cols-1 <?php echo ($voting['type'] == 'member') ? 'md:grid-cols-2 lg:grid-cols-3' : 'md:grid-cols-2'; ?> gap-6">
                                <?php 
                                // Reset pointer data opsi agar bisa di-loop ulang
                                mysqli_data_seek($options_q, 0);
                                while($opt = mysqli_fetch_assoc($options_q)): 
                                    
                                    // --- SMART IMAGE PATH (Sama seperti index.php) ---
                                    $raw_img = basename($opt['image']);
                                    $img_path = '';
                                    
                                    if (!empty($raw_img)) {
                                        // Cek di folder admin/uploads/candidates/
                                        if (file_exists('admin/uploads/candidates/' . $raw_img)) {
                                            $img_path = 'admin/uploads/candidates/' . $raw_img;
                                        } 
                                        // Cek di folder root uploads/candidates/ (Jaga-jaga)
                                        elseif (file_exists('uploads/candidates/' . $raw_img)) {
                                            $img_path = 'uploads/candidates/' . $raw_img;
                                        }
                                    }
                                ?>
                                <label class="cursor-pointer relative group">
                                    <input type="radio" name="option_id" value="<?php echo $opt['id']; ?>" class="sr-only" required>
                                    
                                    <div class="option-card h-full bg-white p-6 rounded-2xl border-2 border-slate-200 hover:border-blue-300 flex flex-col items-center text-center relative shadow-sm hover:shadow-md">
                                        
                                        <div class="check-badge absolute top-4 right-4 bg-primary text-white p-1.5 rounded-full shadow-md opacity-0 transform scale-50">
                                            <i data-feather="check" class="w-4 h-4"></i>
                                        </div>

                                        <?php if($voting['type'] == 'member'): ?>
                                            <div class="w-28 h-28 mb-5 rounded-full overflow-hidden border-4 border-slate-50 shadow-inner group-hover:scale-105 transition-transform duration-300 bg-slate-100">
                                                <?php if($img_path): ?>
                                                    <img src="<?php echo $img_path; ?>" class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <div class="w-full h-full flex items-center justify-center text-slate-300 font-bold text-3xl">
                                                        <?php echo substr($opt['option_name'], 0, 1); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                        <h4 class="text-lg font-bold text-slate-900 mb-2"><?php echo htmlspecialchars($opt['option_name']); ?></h4>
                                        
                                        <?php if(!empty($opt['description'])): ?>
                                            <p class="text-sm text-slate-500 leading-relaxed mb-4 line-clamp-3"><?php echo htmlspecialchars($opt['description']); ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="mt-auto pt-4 w-full">
                                            <span class="block w-full py-2 rounded-lg bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                                Pilih Ini
                                            </span>
                                        </div>
                                    </div>
                                </label>
                                <?php endwhile; ?>
                            </div>

                            <div class="mt-10 flex justify-end border-t border-slate-200 pt-6">
                                <button type="submit" name="cast_vote" onclick="return confirm('Pilihan Anda tidak dapat diubah. Lanjutkan?')" class="px-8 py-3 bg-primary text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition transform active:scale-95 flex items-center gap-2">
                                    <i data-feather="send" class="w-4 h-4"></i> Kirim Suara
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>

                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            
            <div class="max-w-lg mx-auto text-center py-20">
                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm border border-slate-100">
                    <i data-feather="inbox" class="w-10 h-10 text-slate-300"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Belum Ada Pemungutan Suara</h3>
                <p class="text-slate-500 leading-relaxed">
                    Saat ini belum ada sesi voting yang dibuka oleh admin. Silakan kembali lagi nanti untuk berpartisipasi.
                </p>
                <a href="index.php" class="inline-flex items-center gap-2 mt-8 px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 hover:text-primary transition">
                    <i data-feather="arrow-left" class="w-4 h-4"></i> Kembali ke Beranda
                </a>
            </div>

        <?php endif; ?>

    </main>

    <script>
        feather.replace();
    </script>
</body>
</html>