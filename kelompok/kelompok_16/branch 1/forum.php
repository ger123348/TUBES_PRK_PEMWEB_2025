<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

// LOGIKA POSTING (INLINE)
if (isset($_POST['submit_topic'])) {
    $uid = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $cat = mysqli_real_escape_string($conn, $_POST['category']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);

    if (!empty($title) && !empty($content)) {
        mysqli_query($conn, "INSERT INTO forum_topics (user_id, category, title, content) VALUES ('$uid', '$cat', '$title', '$content')");
        header("Location: forum.php?status=posted");
        exit;
    }
}

// FUNGSI TIME AGO
function time_ago($datetime) {
    $diff = (new DateTime)->diff(new DateTime($datetime));
    if ($diff->y > 0) return $diff->y.' thn';
    if ($diff->m > 0) return $diff->m.' bln';
    if ($diff->d > 0) return $diff->d.' hr';
    if ($diff->h > 0) return $diff->h.' jam';
    if ($diff->i > 0) return $diff->i.' mnt';
    return 'baru saja';
}

// QUERY DATA
$cat_filter = $_GET['kategori'] ?? 'semua';
$where = ($cat_filter != 'semua') ? "WHERE category = '".mysqli_real_escape_string($conn, $cat_filter)."'" : "";

$query = "SELECT t.*, u.full_name, u.role, u.position, u.photo,
          (SELECT COUNT(*) FROM forum_comments c WHERE c.topic_id = t.id) as reply_count
          FROM forum_topics t 
          JOIN users u ON t.user_id = u.id 
          $where 
          ORDER BY t.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Forum Diskusi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] }, colors: { primary: '#2563eb', secondary: '#1e293b', surface: '#F8FAFC' } } } }</script>
</head>
<body class="bg-surface font-sans text-slate-800 antialiased">

    <?php include 'navbar_include.php'; ?>
<script>const isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;</script>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <div class="lg:col-span-1 hidden lg:block">
                <div class="bg-white rounded-2xl border border-slate-200 p-4 sticky top-24">
                    <h3 class="font-bold text-slate-400 text-xs uppercase tracking-wider mb-4 px-2">Filter Topik</h3>
                    <div class="space-y-1">
                        <?php 
                        $cats = ['semua'=>'Semua', 'umum'=>'Umum', 'tanya_jawab'=>'Tanya Jawab', 'pengumuman'=>'Pengumuman'];
                        foreach($cats as $k => $label): 
                            $active = ($cat_filter == $k) ? 'bg-primary/10 text-primary font-bold' : 'text-slate-600 hover:bg-slate-50';
                        ?>
                        <a href="?kategori=<?php echo $k; ?>" class="block px-4 py-2.5 rounded-xl text-sm transition <?php echo $active; ?>">
                            <?php echo $label; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3 space-y-6">
                
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i data-feather="edit-3" class="w-5 h-5 text-primary"></i> Mulai Diskusi Baru
                    </h3>
                    <form method="POST" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <input type="text" name="title" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-primary outline-none transition" placeholder="Judul topik yang menarik...">
                            </div>
                            <div>
                                <select name="category" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-primary outline-none transition cursor-pointer">
                                    <option value="umum">Diskusi Umum</option>
                                    <option value="tanya_jawab">Tanya Jawab</option>
                                    <option value="pengumuman">Pengumuman</option>
                                </select>
                            </div>
                        </div>
                        <textarea name="content" rows="3" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-primary outline-none transition resize-none" placeholder="Apa yang ingin Anda bahas hari ini?"></textarea>
                        <div class="flex justify-end pt-2">
                            <button type="submit" name="submit_topic" class="bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-blue-600 transition shadow-lg shadow-blue-500/20 flex items-center gap-2">
                                <i data-feather="send" class="w-4 h-4"></i> Posting
                            </button>
                        </div>
                    </form>
                </div>

                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): 
                        // Logic Foto Penulis
                        $pic = "https://ui-avatars.com/api/?name=".urlencode($row['full_name'])."&background=random&color=fff";
                        if (!empty($row['photo'])) {
                            if (file_exists("uploads/members/".$row['photo'])) $pic = "uploads/members/".$row['photo'];
                            elseif (file_exists("admin/uploads/members/".$row['photo'])) $pic = "admin/uploads/members/".$row['photo'];
                        }
                    ?>
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 hover:shadow-md transition duration-200 group">
                        <div class="flex gap-4">
                            <img src="<?php echo $pic; ?>" class="w-11 h-11 rounded-full object-cover border border-slate-100 flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold text-slate-900 text-sm"><?php echo htmlspecialchars($row['full_name']); ?></span>
                                    <?php if(function_exists('get_role_badge')) echo get_role_badge($row['position']); ?>
                                    <span class="text-xs text-slate-400">• <?php echo time_ago($row['created_at']); ?></span>
                                </div>
                                <div class="mb-2">
                                    <a href="forum_detail.php?id=<?php echo $row['id']; ?>" class="block">
                                        <h2 class="text-lg font-bold text-slate-900 group-hover:text-primary transition line-clamp-1 leading-snug mb-1">
                                            <?php echo htmlspecialchars($row['title']); ?>
                                        </h2>
                                    </a>
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-600">
                                        <?php echo str_replace('_',' ', $row['category']); ?>
                                    </span>
                                </div>
                                <p class="text-slate-500 text-sm line-clamp-2 mb-4"><?php echo htmlspecialchars(strip_tags($row['content'])); ?></p>
                                
                                <div class="flex items-center justify-between pt-3 border-t border-slate-50">
                                    <div class="flex gap-4 text-xs text-slate-400 font-medium">
                                        <span class="flex items-center gap-1"><i data-feather="message-square" class="w-3.5 h-3.5"></i> <?php echo $row['reply_count']; ?> Balasan</span>
                                        <span class="flex items-center gap-1"><i data-feather="eye" class="w-3.5 h-3.5"></i> <?php echo $row['views']; ?> Views</span>
                                    </div>
                                    <a href="forum_detail.php?id=<?php echo $row['id']; ?>" class="text-sm font-bold text-primary hover:underline">Lihat Diskusi &rarr;</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-16 bg-white rounded-2xl border border-dashed border-slate-300">
                        <i data-feather="message-circle" class="w-10 h-10 text-slate-300 mx-auto mb-2"></i>
                        <p class="text-slate-500 text-sm">Belum ada diskusi. Mulailah sekarang!</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script>feather.replace();</script>
</body>
</html>