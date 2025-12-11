<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$uid = $_SESSION['user_id'];
$role = $_SESSION['user_role'] ?? 'member';

if (!isset($_GET['id'])) { header("Location: forum.php"); exit; }
$tid = mysqli_real_escape_string($conn, $_GET['id']);

// --- TIME AGO ---
function time_ago($datetime) {
    $diff = (new DateTime)->diff(new DateTime($datetime));
    if ($diff->y > 0) return $diff->y.' thn';
    if ($diff->m > 0) return $diff->m.' bln';
    if ($diff->d > 0) return $diff->d.' hr';
    if ($diff->h > 0) return $diff->h.' jam';
    if ($diff->i > 0) return $diff->i.' mnt';
    return 'baru saja';
}

// --- LOGIKA HAPUS ---
if (isset($_GET['del_com'])) {
    $cid = $_GET['del_com'];
    $cek = mysqli_query($conn, "SELECT user_id FROM forum_comments WHERE id='$cid'");
    $d = mysqli_fetch_assoc($cek);
    if ($d && ($d['user_id'] == $uid || $role == 'admin')) {
        mysqli_query($conn, "DELETE FROM forum_comments WHERE id='$cid'"); // Karena ada ON DELETE CASCADE di DB, anak otomatis terhapus
        header("Location: forum_detail.php?id=$tid"); exit;
    }
}

// --- LOGIKA BALAS / KOMENTAR (FIX PARENT_ID) ---
if (isset($_POST['post_reply'])) {
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    // Cek apakah ini balasan untuk komentar lain?
    $parent_id = !empty($_POST['parent_id']) ? "'" . mysqli_real_escape_string($conn, $_POST['parent_id']) . "'" : "NULL";
    
    if (!empty($content)) {
        $q = "INSERT INTO forum_comments (topic_id, user_id, content, parent_id) VALUES ('$tid', '$uid', '$content', $parent_id)";
        if(mysqli_query($conn, $q)) {
            header("Location: forum_detail.php?id=$tid"); 
            exit;
        }
    }
}

// UPDATE VIEW
mysqli_query($conn, "UPDATE forum_topics SET views = views + 1 WHERE id = '$tid'");

// AMBIL DATA TOPIK
$q_top = mysqli_query($conn, "SELECT t.*, u.full_name, u.photo, u.position FROM forum_topics t JOIN users u ON t.user_id = u.id WHERE t.id = '$tid'");
$topic = mysqli_fetch_assoc($q_top);
if (!$topic) { header("Location: forum.php"); exit; }

// AMBIL SEMUA KOMENTAR (Flat List)
$q_com = mysqli_query($conn, "SELECT c.*, u.full_name, u.photo, u.position FROM forum_comments c JOIN users u ON c.user_id = u.id WHERE c.topic_id = '$tid' ORDER BY c.created_at ASC");
$comments = [];
while ($row = mysqli_fetch_assoc($q_com)) { $comments[] = $row; }

// --- FUNGSI REKURSIF RENDER KOMENTAR ---
function render_comments($comments, $parent_id = null, $level = 0) {
    foreach ($comments as $c) {
        if ($c['parent_id'] == $parent_id) {
            // Styling Indentasi (Nested Look)
            $margin = ($level > 0) ? 'ml-8 md:ml-12 border-l-2 border-slate-200 pl-4' : '';
            $bg = ($level > 0) ? 'bg-slate-50/50' : 'bg-white border border-slate-200 shadow-sm';
            
            // Foto Profil
            $c_pic = "https://ui-avatars.com/api/?name=".urlencode($c['full_name'])."&background=random&color=fff";
            if (!empty($c['photo'])) {
                if (file_exists("uploads/members/".$c['photo'])) $c_pic = "uploads/members/".$c['photo'];
                elseif (file_exists("admin/uploads/members/".$c['photo'])) $c_pic = "admin/uploads/members/".$c['photo'];
            }

            echo '<div class="mb-3 '.$margin.'">';
            echo '  <div class="'.$bg.' p-4 rounded-xl relative group">';
            
            // Hapus (Jika punya akses)
            global $uid, $role, $tid;
            if ($uid == $c['user_id'] || $role == 'admin') {
                echo '<a href="?id='.$tid.'&del_com='.$c['id'].'" onclick="return confirm(\'Hapus komentar?\')" class="absolute top-3 right-3 text-slate-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition"><i data-feather="trash-2" class="w-3.5 h-3.5"></i></a>';
            }

            // Header Komentar
            echo '    <div class="flex gap-3">';
            echo '      <img src="'.$c_pic.'" class="w-8 h-8 rounded-full object-cover mt-1">';
            echo '      <div class="flex-1">';
            echo '        <div class="flex items-center gap-2 mb-0.5">';
            echo '          <span class="font-bold text-sm text-slate-900">'.htmlspecialchars($c['full_name']).'</span>';
            if(function_exists('get_role_badge')) echo get_role_badge($c['position']);
            echo '          <span class="text-xs text-slate-400">• '.time_ago($c['created_at']).'</span>';
            echo '        </div>';
            echo '        <div class="text-sm text-slate-600 leading-relaxed mb-2">'.nl2br(htmlspecialchars($c['content'])).'</div>';
            
            // Tombol Balas
            echo '        <button onclick="toggleReply('.$c['id'].')" class="text-xs font-bold text-slate-400 hover:text-primary flex items-center gap-1 transition"><i data-feather="message-circle" class="w-3 h-3"></i> Balas</button>';
            
            // Form Balas (Hidden)
            echo '        <form id="reply-form-'.$c['id'].'" method="POST" class="hidden mt-3 pt-2 animate-fade-in">';
            echo '          <input type="hidden" name="parent_id" value="'.$c['id'].'">';
            echo '          <div class="flex gap-2">';
            echo '            <textarea name="content" rows="1" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary outline-none resize-none" placeholder="Tulis balasan..."></textarea>';
            echo '            <button type="submit" name="post_reply" class="bg-primary text-white px-4 rounded-lg text-xs font-bold hover:bg-blue-600 transition">Kirim</button>';
            echo '          </div>';
            echo '        </form>';
            
            echo '      </div>';
            echo '    </div>';
            echo '  </div>';
            
            // Panggil Anak (Rekursif)
            render_comments($comments, $c['id'], $level + 1);
            echo '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($topic['title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] }, colors: { primary: '#2563eb', secondary: '#1e293b', surface: '#F8FAFC' } } } }</script>
</head>
<body class="bg-surface font-sans text-slate-800 antialiased">

   <?php include 'navbar_include.php'; ?>
<script>const isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;</script>

    <div class="max-w-4xl mx-auto px-4 py-8">
        
        <div class="bg-white rounded-2xl border border-slate-200 p-6 md:p-8 shadow-sm mb-8">
            <div class="flex items-start gap-4 mb-6 border-b border-slate-50 pb-6">
                <?php 
                    $t_pic = "https://ui-avatars.com/api/?name=".urlencode($topic['full_name'])."&background=random&color=fff";
                    if (!empty($topic['photo'])) {
                        if (file_exists("uploads/members/".$topic['photo'])) $t_pic = "uploads/members/".$topic['photo'];
                        elseif (file_exists("admin/uploads/members/".$topic['photo'])) $t_pic = "admin/uploads/members/".$topic['photo'];
                    }
                ?>
                <img src="<?php echo $t_pic; ?>" class="w-14 h-14 rounded-full object-cover border border-slate-100 flex-shrink-0">
                <div class="flex-1">
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900 leading-tight mb-2"><?php echo htmlspecialchars($topic['title']); ?></h1>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="font-semibold text-slate-700"><?php echo htmlspecialchars($topic['full_name']); ?></span>
                        <?php if(function_exists('get_role_badge')) echo get_role_badge($topic['position']); ?>
                        <span class="text-slate-400 text-xs">• <?php echo time_ago($topic['created_at']); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed mb-6">
                <?php echo nl2br(htmlspecialchars($topic['content'])); ?>
            </div>
            
            <div class="flex items-center gap-6 text-sm text-slate-500 font-medium pt-2">
                <span><i data-feather="message-circle" class="w-4 h-4 inline mr-1"></i> <?php echo count($comments); ?> Komentar</span>
                <span><i data-feather="eye" class="w-4 h-4 inline mr-1"></i> <?php echo $topic['views']; ?> Dilihat</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8 shadow-sm">
            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Tulis Tanggapan Anda</h3>
            <form method="POST">
                <textarea name="content" rows="3" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-primary outline-none transition text-sm resize-none" placeholder="Bagikan pendapat Anda..."></textarea>
                <div class="text-right mt-3">
                    <button type="submit" name="post_reply" class="bg-primary text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-blue-600 transition shadow-lg shadow-blue-500/20">Kirim Komentar</button>
                </div>
            </form>
        </div>

        <div class="space-y-2">
            <?php 
            if (count($comments) > 0) {
                render_comments($comments); 
            } else {
                echo '<div class="text-center py-12 text-slate-400 italic">Belum ada diskusi. Jadilah yang pertama!</div>';
            }
            ?>
        </div>

    </div>

    <script>
        feather.replace();
        function toggleReply(id) {
            // Tutup semua form dulu
            document.querySelectorAll('form[id^="reply-form-"]').forEach(el => el.classList.add('hidden'));
            
            // Buka form yang dituju
            const form = document.getElementById('reply-form-' + id);
            if (form) {
                form.classList.remove('hidden');
                form.querySelector('textarea').focus();
            }
        }
    </script>
</body>
</html>