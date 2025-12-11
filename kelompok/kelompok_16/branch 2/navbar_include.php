<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isActive($page) {
    return basename($_SERVER['PHP_SELF']) == $page ? 'text-primary font-bold border-b-2 border-primary pb-0.5' : 'text-slate-600 font-medium hover:text-primary transition';
}
?>

<style>
    /* FIX GLOBAL SCROLL */
    html, body {
        overflow-x: hidden;
        /* Tambahkan padding agar konten paling atas tidak tertutup navbar saat pertama load */
        padding-top: 0px; 
    }

    /* CSS NAVBAR */
    #main-navbar {
        transition: transform 0.4s ease-in-out; /* Animasi naik turun halus */
        top: 0;
        left: 0;
        z-index: 9999; /* Pastikan selalu di atas elemen lain */
    }
    
    /* Kelas Hilang */
    .navbar-hidden {
        transform: translateY(-100%) !important;
        box-shadow: none !important;
    }

    /* Kelas Muncul */
    .navbar-visible {
        transform: translateY(0) !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
</style>

<nav id="main-navbar" class="fixed w-full bg-white/95 backdrop-blur-md border-b border-slate-200 navbar-visible">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <a href="index.php" class="flex items-center gap-2 group">
                <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-white font-bold shadow-lg shadow-blue-500/30 group-hover:scale-105 transition">K</div>
                <span class="font-bold text-xl text-slate-900 tracking-tight">Komunitas<span class="text-primary">Maju</span></span>
            </a>

            <div class="hidden lg:flex space-x-8 items-center">
                <a href="index.php" class="text-sm <?php echo isActive('index.php'); ?>">Beranda</a>
                <a href="#" class="text-sm font-medium text-slate-600 hover:text-primary transition">Tentang Kami</a>
                <a href="events.php" class="text-sm <?php echo isActive('events.php'); ?>">Agenda</a>
                <a href="news.php" class="text-sm <?php echo isActive('news.php'); ?>">Berita</a>
                <a href="forum.php" onclick="checkAccess(event, 'forum.php')" class="text-sm cursor-pointer <?php echo isActive('forum.php'); ?>">Forum</a>
                <a href="voting.php" onclick="checkAccess(event, 'voting.php')" class="text-sm cursor-pointer <?php echo isActive('voting.php'); ?>">E-Voting</a>
                <a href="donation.php" class="text-sm <?php echo isActive('donation.php'); ?>">Donasi</a>
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
            <a href="index.php" class="block px-3 py-2 rounded-md <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-primary font-bold bg-blue-50' : 'text-slate-600 font-medium hover:bg-slate-50'; ?>">Beranda</a>
            <a href="forum.php" onclick="checkAccess(event, 'forum.php')" class="block px-3 py-2 rounded-md <?php echo basename($_SERVER['PHP_SELF']) == 'forum.php' ? 'text-primary font-bold bg-blue-50' : 'text-slate-600 font-medium hover:bg-slate-50'; ?>">Forum</a>
            <a href="voting.php" onclick="checkAccess(event, 'voting.php')" class="block px-3 py-2 rounded-md <?php echo basename($_SERVER['PHP_SELF']) == 'voting.php' ? 'text-primary font-bold bg-blue-50' : 'text-slate-600 font-medium hover:bg-slate-50'; ?>">E-Voting</a>
            <a href="donation.php" class="block px-3 py-2 rounded-md <?php echo basename($_SERVER['PHP_SELF']) == 'donation.php' ? 'text-primary font-bold bg-blue-50' : 'text-slate-600 font-medium hover:bg-slate-50'; ?>">Donasi</a>
            
            <?php if(!isset($_SESSION['user_id'])): ?>
                <div class="mt-4 pt-4 border-t border-slate-100 flex gap-3">
                    <a href="login.php" class="flex-1 text-center py-2 text-slate-600 border border-slate-200 rounded-lg">Masuk</a>
                    <a href="register.php" class="flex-1 text-center py-2 bg-primary text-white rounded-lg">Daftar</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div id="loginModal" class="hidden-modal fixed inset-0 z-[60] flex items-center justify-center px-4 hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeLoginModal()"></div>
    <div id="loginModalContent" class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md p-10 text-center border border-slate-100">
        <button onclick="closeLoginModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 transition p-2 rounded-full hover:bg-slate-100"><i data-feather="x" class="w-5 h-5"></i></button>
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
    // --- SCRIPT AUTO-HIDE NAVBAR (TANPA SYARAT SCROLL) ---
    const navbar = document.getElementById('main-navbar');
    let idleTimer;

    // Fungsi: Munculkan Navbar
    function showNav() {
        navbar.classList.remove('navbar-hidden');
        navbar.classList.add('navbar-visible');
        
        // Reset timer setiap kali ada aktivitas
        clearTimeout(idleTimer);
        idleTimer = setTimeout(hideNav, 1500); // 1.5 detik diam -> Sembunyi
    }

    // Fungsi: Sembunyikan Navbar
    function hideNav() {
        // Jangan sembunyi kalau mouse sedang di atas navbar
        if (!navbar.matches(':hover')) {
            navbar.classList.add('navbar-hidden');
            navbar.classList.remove('navbar-visible');
        }
    }

    // Event Listener: Munculkan saat ada aktivitas apapun
    window.addEventListener('scroll', showNav);
    window.addEventListener('mousemove', showNav);
    window.addEventListener('keydown', showNav);
    window.addEventListener('click', showNav);

    // Khusus: Jika mouse masuk ke navbar, hentikan timer (biar gak ilang pas mau diklik)
    navbar.addEventListener('mouseenter', () => {
        clearTimeout(idleTimer);
        navbar.classList.remove('navbar-hidden');
        navbar.classList.add('navbar-visible');
    });

    // Jika mouse keluar navbar, mulai timer lagi
    navbar.addEventListener('mouseleave', () => {
        idleTimer = setTimeout(hideNav, 1500);
    });

    // Mulai timer saat pertama load
    idleTimer = setTimeout(hideNav, 1500);


    // --- FUNGSI GLOBAL LAINNYA ---
    function checkAccess(event, targetUrl) {
        if (typeof isUserLoggedIn !== 'undefined' && !isUserLoggedIn) { 
            event.preventDefault(); 
            openLoginModal(); 
        } else if (typeof isUserLoggedIn === 'undefined') {
             window.location.href = targetUrl;
        } else { 
            window.location.href = targetUrl; 
        }
    }

    const modal = document.getElementById('loginModal');
    function openLoginModal() { if(modal) { modal.classList.remove('hidden'); modal.classList.remove('hidden-modal'); modal.classList.add('show-modal'); } }
    function closeLoginModal() { if(modal) { modal.classList.add('hidden'); modal.classList.add('hidden-modal'); modal.classList.remove('show-modal'); } }

    function toggleUserMenu() {
        const menu = document.getElementById('user-menu-dropdown');
        if (menu) {
            if (menu.classList.contains('hidden')) menu.classList.remove('hidden');
            else menu.classList.add('hidden');
        }
    }
    
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    if(mobileMenuBtn && mobileMenu){ 
        mobileMenuBtn.addEventListener('click', () => { mobileMenu.classList.toggle('hidden'); }); 
    }
</script>