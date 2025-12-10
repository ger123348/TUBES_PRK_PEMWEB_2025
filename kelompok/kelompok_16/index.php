<?php
session_start();
include 'config.php'; 

$q_settings = mysqli_query($conn, "SELECT * FROM site_settings WHERE id=1");
$site_setting = mysqli_fetch_assoc($q_settings);

$hero_title = $site_setting['hero_title'] ?? "Tumbuh Bersama,";
$hero_subtitle = $site_setting['hero_subtitle'] ?? "Berdampak Nyata.";
$hero_desc = $site_setting['hero_description'] ?? "Wadah kolaborasi untuk berbagi ilmu, mengembangkan potensi, dan memberikan kontribusi sosial bagi sesama.";

$q_hero_imgs = mysqli_query($conn, "SELECT * FROM hero_images ORDER BY id DESC");
$hero_images = [];
while($img = mysqli_fetch_assoc($q_hero_imgs)) {
    $filename = $img['image_path'];
    if (file_exists('uploads/hero/' . $filename)) {
        $hero_images[] = 'uploads/hero/' . $filename;
    } elseif (file_exists('admin/uploads/hero/' . $filename)) {
        $hero_images[] = 'admin/uploads/hero/' . $filename;
    }
}

if (empty($hero_images)) {
    $hero_images = [
        'https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&q=80',
        'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&q=80',
        'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&q=80'
    ];
}

$site_name = "Komunitas Maju Bersama";
$is_logged_in = isset($_SESSION['user_id']) ? 'true' : 'false';

$q_member = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='member'");
$total_member = ($q_member) ? mysqli_fetch_assoc($q_member)['total'] : 0;

$q_online = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE last_activity > (NOW() - INTERVAL 10 MINUTE)");
$total_online = ($q_online) ? mysqli_fetch_assoc($q_online)['total'] : 0;

$q_donasi = mysqli_query($conn, "SELECT SUM(current_amount) as total FROM donations");
$total_donasi_raw = ($q_donasi) ? mysqli_fetch_assoc($q_donasi)['total'] : 0;
$total_donasi_display = "Rp " . number_format((float)$total_donasi_raw, 0, ',', '.');

$stats = [
    ['label' => 'Anggota Bergabung', 'value' => number_format($total_member), 'icon' => 'users', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50'],
    ['label' => 'Sedang Online', 'value' => number_format($total_online), 'icon' => 'activity', 'color' => 'text-green-600', 'bg' => 'bg-green-50'],
    ['label' => 'Donasi Terkumpul', 'value' => $total_donasi_display, 'icon' => 'heart', 'color' => 'text-rose-600', 'bg' => 'bg-rose-50'],
];

$q_news = mysqli_query($conn, "SELECT * FROM news ORDER BY created_at DESC LIMIT 3");
$q_events_upcoming = mysqli_query($conn, "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 2");

$q_donation_main = mysqli_query($conn, "SELECT * FROM donations ORDER BY id DESC LIMIT 1");
$donation_data = ($q_donation_main) ? mysqli_fetch_assoc($q_donation_main) : null;

$persen_donasi = 0;
if($donation_data && $donation_data['target_amount'] > 0){
    $persen_donasi = ($donation_data['current_amount'] / $donation_data['target_amount']) * 100;
}
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda | <?php echo $site_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { primary: '#2563eb', secondary: '#1e293b' }
                }
            }
        }
        const isUserLoggedIn = <?php echo $is_logged_in; ?>;
    </script>
    <style>
        .carousel-item { position: absolute; inset: 0; opacity: 0; transition: opacity 1.5s ease-in-out; z-index: 0; }
        .carousel-item.active { opacity: 1; z-index: 1; }
        
        #loginModal { transition: opacity 0.3s ease, visibility 0.3s ease; }
        #loginModal.hidden-modal { opacity: 0; visibility: hidden; pointer-events: none; }
        #loginModal.show-modal { opacity: 1; visibility: visible; pointer-events: auto; }
        #loginModalContent { transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); transform: scale(0.9); }
        #loginModal.show-modal #loginModalContent { transform: scale(1); }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">

    <nav class="fixed w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-200 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="index.php" class="flex items-center gap-2 group">
                    <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-white font-bold shadow-lg shadow-blue-500/30 group-hover:scale-105 transition">K</div>
                    <span class="font-bold text-xl text-slate-900 tracking-tight">Komunitas<span class="text-primary">Maju</span></span>
                </a>

                <div class="hidden lg:flex space-x-8 items-center">
                    <a href="index.php" class="text-sm font-bold text-primary border-b-2 border-primary pb-0.5">Beranda</a>
                    <a href="#" class="text-sm font-medium text-slate-600 hover:text-primary transition">Tentang Kami</a>
                    <a href="events.php" class="text-sm font-medium text-slate-600 hover:text-primary transition">Agenda</a>
                    <a href="news.php" class="text-sm font-medium text-slate-600 hover:text-primary transition">Berita</a>
                    <a href="forum.php" onclick="checkAccess(event, 'forum.php')" class="text-sm font-medium text-slate-600 hover:text-primary transition cursor-pointer">Forum</a>
                    <a href="voting.php" onclick="checkAccess(event, 'voting.php')" class="text-sm font-medium text-slate-600 hover:text-primary transition cursor-pointer">E-Voting</a>
                    <a href="donation.php" class="text-sm font-medium text-slate-600 hover:text-primary transition">Donasi</a>
                </div>

                <div class="hidden lg:flex items-center">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="relative ml-3 group">
                            <button type="button" onclick="toggleUserMenu()" class="flex items-center gap-3 focus:outline-none p-1 rounded-full hover:bg-slate-50 transition">
                                <div class="hidden sm:flex flex-col items-end">
                                    <span class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars(strtok($_SESSION['user_name'], " ")); ?></span>
                                    <?php 
                                        $posisi = isset($_SESSION['user_position']) ? $_SESSION['user_position'] : 'Anggota';
                                        if (function_exists('get_role_badge')) echo get_role_badge($posisi); 
                                        else echo '<span class="text-[10px] text-slate-500">'.$posisi.'</span>';
                                    ?>
                                </div>
                                <?php
                                    $foto_profil = "https://ui-avatars.com/api/?name=" . urlencode($_SESSION['user_name']) . "&background=random&color=fff";
                                    if (isset($_SESSION['user_photo']) && !empty($_SESSION['user_photo'])) {
                                        if (file_exists("uploads/members/" . $_SESSION['user_photo'])) $foto_profil = "uploads/members/" . $_SESSION['user_photo'];
                                        elseif (file_exists("admin/uploads/members/" . $_SESSION['user_photo'])) $foto_profil = "admin/uploads/members/" . $_SESSION['user_photo'];
                                    }
                                ?>
                                <img class="h-10 w-10 rounded-full object-cover border-2 border-white shadow-sm" src="<?php echo $foto_profil; ?>">
                                <i data-feather="chevron-down" class="w-4 h-4 text-slate-400"></i>
                            </button>

                            <div id="user-menu-dropdown" class="hidden absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-xl bg-white py-1 shadow-2xl ring-1 ring-black ring-opacity-5">
                                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50 rounded-t-xl">
                                    <p class="text-xs text-slate-500">Masuk sebagai</p>
                                    <p class="text-sm font-bold text-slate-900 truncate"><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                                </div>
                                <div class="py-1">
                                    <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                                        <a href="admin/dashboard.php" class="group flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary transition"><i data-feather="grid" class="mr-3 h-4 w-4 text-slate-400 group-hover:text-primary"></i> Panel Admin</a>
                                    <?php endif; ?>
                                    <a href="dashboard.php" class="group flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary transition"><i data-feather="user" class="mr-3 h-4 w-4 text-slate-400 group-hover:text-primary"></i> Profil & Jabatan</a>
                                </div>
                                <div class="py-1 border-t border-slate-100">
                                    <a href="logout.php" class="group flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition"><i data-feather="log-out" class="mr-3 h-4 w-4 text-red-400"></i> Keluar</a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-3">
                            <a href="login.php" class="px-5 py-2.5 text-sm font-bold text-slate-600 hover:text-primary transition">Masuk</a>
                            <a href="register.php" class="px-5 py-2.5 text-sm font-bold bg-primary text-white rounded-full hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition transform hover:-translate-y-0.5">Daftar</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="lg:hidden flex items-center">
                    <button id="mobile-menu-btn" class="text-slate-600 hover:text-primary focus:outline-none"><i data-feather="menu"></i></button>
                </div>
            </div>
        </div>
        
        <div id="mobile-menu" class="hidden lg:hidden bg-white border-t border-slate-100 shadow-xl">
            <div class="px-4 pt-2 pb-6 space-y-1">
                <a href="index.php" class="block px-3 py-2 text-primary font-bold bg-blue-50 rounded-md">Beranda</a>
                <a href="forum.php" onclick="checkAccess(event, 'forum.php')" class="block px-3 py-2 text-slate-600 font-medium hover:bg-slate-50 rounded-md">Forum</a>
                <a href="voting.php" onclick="checkAccess(event, 'voting.php')" class="block px-3 py-2 text-slate-600 font-medium hover:bg-slate-50 rounded-md">E-Voting</a>
                <a href="donation.php" class="block px-3 py-2 text-slate-600 font-medium hover:bg-slate-50 rounded-md">Donasi</a>
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <div class="mt-4 pt-4 border-t border-slate-100 flex gap-3">
                        <a href="login.php" class="flex-1 text-center py-2 text-slate-600 border border-slate-200 rounded-lg">Masuk</a>
                        <a href="register.php" class="flex-1 text-center py-2 bg-primary text-white rounded-lg">Daftar</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <section class="relative pt-32 pb-20 lg:pt-40 lg:pb-28 overflow-hidden min-h-[600px] flex items-center justify-center">
        
        <div id="hero-carousel">
            <?php 
            $i = 0;
            foreach($hero_images as $img_src): 
                $activeClass = ($i == 0) ? 'active' : '';
            ?>
            <div class="carousel-item <?php echo $activeClass; ?>">
                <img src="<?php echo $img_src; ?>" class="w-full h-full object-cover blur-sm scale-110">
            </div>
            <?php $i++; endforeach; ?>
        </div>

        <div class="hero-overlay"></div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center hero-content">
            <span class="inline-block py-1.5 px-4 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white text-xs font-bold uppercase tracking-widest mb-6 shadow-lg">🚀 Portal Resmi Komunitas</span>
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold text-white tracking-tight mb-6 drop-shadow-sm">
                <?php echo htmlspecialchars($hero_title); ?><br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-300 to-cyan-300">
                    <?php echo htmlspecialchars($hero_subtitle); ?>
                </span>
            </h1>
            <p class="mt-4 text-lg text-blue-100 max-w-2xl mx-auto mb-10 font-medium leading-relaxed">
                <?php echo htmlspecialchars($hero_desc); ?>
            </p>
            <div class="flex justify-center gap-4">
                <a href="forum.php" onclick="checkAccess(event, 'forum.php')" class="group flex items-center gap-3 px-8 py-4 text-base font-bold text-slate-900 bg-white rounded-full shadow-xl hover:bg-slate-50 transition transform hover:-translate-y-1">
                    <i data-feather="message-circle" class="w-5 h-5 group-hover:text-primary transition"></i>
                    Forum Diskusi
                </a>
            </div>
        </div>
    </section>

    <section class="relative z-20 -mt-16 mb-20 px-4">
        <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach($stats as $stat): ?>
            <div class="bg-white p-6 rounded-3xl shadow-xl border border-slate-100 flex items-center gap-5 hover:shadow-2xl transition duration-300 transform hover:-translate-y-1">
                <div class="w-16 h-16 <?php echo $stat['bg']; ?> rounded-2xl flex items-center justify-center <?php echo $stat['color']; ?>">
                    <i data-feather="<?php echo $stat['icon']; ?>" class="w-8 h-8"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1"><?php echo $stat['label']; ?></p>
                    <h3 class="text-2xl font-black text-slate-900"><?php echo $stat['value']; ?></h3>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <h2 class="text-3xl font-bold text-slate-900 tracking-tight">Berita Terbaru</h2>
                    <p class="mt-2 text-slate-500">Informasi dan kegiatan terkini dari komunitas.</p>
                </div>
                <a href="news.php" class="hidden md:flex items-center text-sm font-bold text-primary hover:text-blue-700 transition">
                    Lihat Semua <i data-feather="arrow-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>

            <?php if(mysqli_num_rows($q_news) == 0): ?>
                <div class="text-center py-16 bg-slate-50 rounded-3xl border border-dashed border-slate-300">
                    <p class="text-slate-500 font-medium">Belum ada berita terbaru.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <?php while($row = mysqli_fetch_assoc($q_news)): 
                        $news_file = basename($row['image_url']);
                        $news_path = 'https://via.placeholder.com/600x400?text=No+Image';

                        if (!empty($news_file)) {
                            if (file_exists('uploads/news/' . $news_file)) {
                                $news_path = 'uploads/news/' . $news_file;
                            } 
                            elseif (file_exists('admin/uploads/news/' . $news_file)) {
                                $news_path = 'admin/uploads/news/' . $news_file;
                            }
                        }
                    ?>
                    <div class="group bg-white rounded-3xl overflow-hidden border border-slate-100 hover:shadow-2xl hover:shadow-blue-900/5 transition duration-500 flex flex-col h-full">
                        <a href="news_detail.php?id=<?php echo $row['id']; ?>" class="block relative h-60 overflow-hidden">
                            <img src="<?php echo $news_path; ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition duration-300"></div>
                            <div class="absolute top-4 left-4 bg-white/95 backdrop-blur px-3 py-1.5 rounded-full text-xs font-bold text-slate-800 shadow-md">
                                <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                            </div>
                        </a>
                        <div class="p-8 flex-1 flex flex-col">
                            <a href="news_detail.php?id=<?php echo $row['id']; ?>">
                                <h3 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-primary transition line-clamp-2 leading-snug"><?php echo $row['title']; ?></h3>
                            </a>
                            <p class="text-slate-500 text-sm line-clamp-3 mb-6 flex-1 leading-relaxed">
                                <?php echo htmlspecialchars(substr(strip_tags($row['content']), 0, 100)) . '...'; ?>
                            </p>
                            <a href="news_detail.php?id=<?php echo $row['id']; ?>" class="text-sm font-bold text-primary flex items-center gap-1 group-hover:gap-2 transition-all mt-auto">
                                Baca Selengkapnya <i data-feather="chevron-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="py-20 bg-slate-50/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
                
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-8 flex items-center gap-3">
                        <span class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center"><i data-feather="calendar"></i></span>
                        Agenda Mendatang
                    </h2>
                    <div class="space-y-5">
                        <?php if(mysqli_num_rows($q_events_upcoming) > 0): ?>
                            <?php while($evt = mysqli_fetch_assoc($q_events_upcoming)): 
                                $evt_file = basename($evt['image_url'] ?? '');
                                $evt_path = '';
                                if (!empty($evt_file)) {
                                    if (file_exists('uploads/events/' . $evt_file)) {
                                        $evt_path = 'uploads/events/' . $evt_file;
                                    } elseif (file_exists('admin/uploads/events/' . $evt_file)) {
                                        $evt_path = 'admin/uploads/events/' . $evt_file;
                                    }
                                }
                            ?>
                            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex flex-col sm:flex-row items-center gap-6 hover:shadow-lg transition duration-300 group overflow-hidden">
                                
                                <?php if($evt_path): ?>
                                    <div class="relative w-full sm:w-28 h-28 flex-shrink-0 rounded-2xl overflow-hidden">
                                        <img src="<?php echo $evt_path; ?>" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                                        <div class="absolute inset-0 bg-black/20"></div>
                                        <div class="absolute top-0 left-0 w-full h-full flex flex-col items-center justify-center text-white text-shadow">
                                            <span class="text-xl font-black"><?php echo date('d', strtotime($evt['event_date'])); ?></span>
                                            <span class="text-[10px] font-bold uppercase"><?php echo date('M', strtotime($evt['event_date'])); ?></span>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-slate-50 text-slate-700 rounded-2xl p-4 text-center min-w-[90px] border border-slate-100 group-hover:bg-primary group-hover:text-white transition h-28 flex flex-col justify-center">
                                        <span class="block text-2xl font-black mb-1"><?php echo date('d', strtotime($evt['event_date'])); ?></span>
                                        <span class="block text-xs font-bold uppercase tracking-wider"><?php echo date('M', strtotime($evt['event_date'])); ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="flex-1 text-center sm:text-left">
                                    <h4 class="text-lg font-bold text-slate-800 leading-tight mb-2"><?php echo htmlspecialchars($evt['title']); ?></h4>
                                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 text-xs text-slate-500 font-medium">
                                        <span class="flex items-center gap-1"><i data-feather="clock" class="w-3.5 h-3.5"></i> <?php echo date('H:i', strtotime($evt['event_date'])); ?></span>
                                        <span class="flex items-center gap-1"><i data-feather="map-pin" class="w-3.5 h-3.5"></i> <?php echo $evt['location']; ?></span>
                                    </div>
                                </div>
                                <a href="event_register.php?id=<?php echo $evt['id']; ?>" onclick="checkAccess(event, 'event_register.php?id=<?php echo $evt['id']; ?>')" class="px-6 py-3 text-sm font-bold text-slate-700 bg-slate-100 rounded-xl hover:bg-primary hover:text-white transition cursor-pointer">
                                    Detail
                                </a>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="p-8 bg-white rounded-3xl border border-dashed border-slate-300 text-center text-slate-400">Belum ada agenda dalam waktu dekat.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex flex-col h-full">
                    <h2 class="text-2xl font-bold text-slate-900 mb-8 flex items-center gap-3">
                        <span class="w-10 h-10 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center"><i data-feather="heart"></i></span>
                        Aksi Kebaikan
                    </h2>

                    <?php if($donation_data): 
                        $don_file = basename($donation_data['image'] ?? '');
                        $don_path = '';
                        if (!empty($don_file)) {
                            if (file_exists('uploads/campaigns/' . $don_file)) {
                                $don_path = 'uploads/campaigns/' . $don_file;
                            } elseif (file_exists('admin/uploads/campaigns/' . $don_file)) {
                                $don_path = 'admin/uploads/campaigns/' . $don_file;
                            }
                        }
                    ?>
                    <div class="relative flex-1 rounded-3xl overflow-hidden shadow-2xl group min-h-[320px]">
                        <?php if ($don_path): ?>
                            <img src="<?php echo $don_path; ?>" class="absolute inset-0 w-full h-full object-cover blur-sm transition duration-1000 group-hover:scale-110">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/60 to-slate-900/30"></div>
                        <?php else: ?>
                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 to-purple-700"></div>
                            <div class="absolute top-0 right-0 p-20 bg-white/10 rounded-full blur-3xl"></div>
                        <?php endif; ?>

                        <div class="relative z-10 h-full p-8 flex flex-col justify-end">
                            <span class="inline-flex items-center gap-2 self-start px-3 py-1 rounded-lg bg-white/20 backdrop-blur border border-white/20 text-white text-[10px] font-bold uppercase tracking-wider mb-4">
                                <i data-feather="zap" class="w-3 h-3 text-yellow-300"></i> Mendesak
                            </span>
                            <h3 class="text-3xl font-bold text-white mb-2 leading-tight"><?php echo htmlspecialchars($donation_data['title']); ?></h3>
                            <p class="text-slate-200 text-sm mb-6 line-clamp-2"><?php echo htmlspecialchars(strip_tags($donation_data['description'] ?? '')); ?></p>
                            
                            <div class="bg-slate-900/50 backdrop-blur rounded-2xl p-5 border border-white/10">
                                <div class="flex justify-between text-sm font-medium text-slate-300 mb-2"><span>Terkumpul</span><span>Target</span></div>
                                <div class="flex justify-between items-end mb-4">
                                    <span class="text-white text-xl font-bold">Rp <?php echo number_format($donation_data['current_amount'], 0, ',', '.'); ?></span>
                                    <span class="text-slate-400 text-sm">Rp <?php echo number_format($donation_data['target_amount'], 0, ',', '.'); ?></span>
                                </div>
                                <div class="w-full bg-white/10 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-gradient-to-r from-blue-400 to-cyan-300 h-2.5 rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(56,189,248,0.6)]" style="width: <?php echo min($persen_donasi, 100); ?>%"></div>
                                </div>
                            </div>

                            <a href="donation_payment.php?id=<?php echo $donation_data['id']; ?>" class="mt-6 w-full py-4 bg-white text-slate-900 font-bold text-center rounded-xl shadow-lg hover:bg-blue-50 transition transform hover:-translate-y-0.5">
                                Donasi Sekarang
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                        <div class="bg-white rounded-3xl p-10 text-center border-2 border-dashed border-slate-200 h-full flex flex-col justify-center items-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4"><i data-feather="heart" class="w-8 h-8 text-slate-300"></i></div>
                            <p class="text-slate-500 font-medium">Belum ada kampanye aktif saat ini.</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </section>

    <footer class="bg-white border-t border-slate-200 pt-16 pb-10 text-center">
        <p class="text-sm text-slate-400">© 2025 Komunitas Maju Bersama.</p>
    </footer>

    <div id="loginModal" class="hidden-modal fixed inset-0 z-[60] flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeLoginModal()"></div>
        <div id="loginModalContent" class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md p-10 text-center border border-slate-100">
            <button onclick="closeLoginModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 transition"><i data-feather="x" class="w-5 h-5"></i></button>
            <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6 text-primary border-4 border-blue-50/50 shadow-sm"><i data-feather="lock" class="w-8 h-8"></i></div>
            <h3 class="text-2xl font-bold text-slate-900 mb-3">Akses Terbatas</h3>
            <p class="text-slate-500 mb-8 leading-relaxed px-4">Fitur ini khusus untuk anggota. Silakan masuk atau daftar untuk bergabung.</p>
            <div class="grid grid-cols-2 gap-4">
                <a href="login.php" class="flex items-center justify-center py-3.5 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl hover:border-primary hover:text-primary transition hover:shadow-sm">Masuk</a>
                <a href="register.php" class="flex items-center justify-center py-3.5 bg-primary text-white font-bold rounded-xl hover:bg-blue-600 transition shadow-lg shadow-blue-500/30">Daftar</a>
            </div>
            <button onclick="closeLoginModal()" class="mt-8 text-sm text-slate-400 hover:text-slate-600 font-medium">Nanti saja</button>
        </div>
    </div>

    <script>
        feather.replace();
        function checkAccess(event, targetUrl) { if (!isUserLoggedIn) { event.preventDefault(); openLoginModal(); } else { window.location.href = targetUrl; } }
        const modal = document.getElementById('loginModal');
        function openLoginModal() { modal.classList.remove('hidden-modal'); modal.classList.add('show-modal'); }
        function closeLoginModal() { modal.classList.remove('show-modal'); modal.classList.add('hidden-modal'); }
        
        const items = document.querySelectorAll('.carousel-item');
        let currentItem = 0;
        function showNextImage() {
            items[currentItem].classList.remove('active');
            currentItem = (currentItem + 1) % items.length;
            items[currentItem].classList.add('active');
        }
        if (items.length > 0) setInterval(showNextImage, 5000);

        function toggleUserMenu() {
            const menu = document.getElementById('user-menu-dropdown');
            if (menu.classList.contains('hidden')) menu.classList.remove('hidden');
            else menu.classList.add('hidden');
        }
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        if(mobileMenuBtn){ mobileMenuBtn.addEventListener('click', () => { mobileMenu.classList.toggle('hidden'); }); }
    </script>
</body>
</html>