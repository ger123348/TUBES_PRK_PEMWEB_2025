<?php
session_start();
include 'config.php';

// --- HAPUS LOGIKA FILTER, TAMPILKAN HANYA YANG MENDATANG ---
$current_date = date('Y-m-d H:i:s');
$page_title = "Agenda Kegiatan";
$page_desc = "Ikuti berbagai kegiatan positif untuk mengembangkan diri dan jaringan.";

// Ambil event yang belum lewat (Upcoming)
$query = mysqli_query($conn, "SELECT * FROM events WHERE event_date >= '$current_date' ORDER BY event_date ASC");

// Ambil ID user jika login (untuk cek status daftar)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda | Komunitas Maju Bersama</title>
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
    </script>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    <?php include 'navbar_include.php'; ?>
<script>const isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;</script>

    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 py-12 text-center">
            <h1 class="text-3xl font-bold text-slate-900 mb-2"><?php echo $page_title; ?></h1>
            <p class="text-slate-500 max-w-2xl mx-auto"><?php echo $page_desc; ?></p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-12">
        <?php if(mysqli_num_rows($query) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php while($row = mysqli_fetch_assoc($query)): 
                    // Formatting Tanggal
                    $dateObj = strtotime($row['event_date']);
                    $dateDay = date('d', $dateObj);
                    $dateMonth = date('M', $dateObj);
                    $time = date('H:i', $dateObj);
                    
                    // Warna Badge Tipe
                    $badgeColor = 'bg-blue-100 text-blue-800';
                    if($row['type'] == 'Lomba') $badgeColor = 'bg-orange-100 text-orange-800';
                    if($row['type'] == 'Rapat') $badgeColor = 'bg-slate-100 text-slate-800';

                    // --- LOGIKA CEK STATUS DAFTAR ---
                    $is_registered = false;
                    if ($user_id) {
                        $check = mysqli_query($conn, "SELECT * FROM event_registrations WHERE user_id='$user_id' AND event_id='".$row['id']."'");
                        if (mysqli_num_rows($check) > 0) {
                            $is_registered = true;
                        }
                    }
                ?>
                
                <div class="group bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-xl transition duration-300 flex flex-col h-full relative">
                    
                    <?php if(!empty($row['image_url'])): 
                        $file_ext = strtolower(pathinfo($row['image_url'], PATHINFO_EXTENSION));
                        $is_video = in_array($file_ext, ['mp4', 'mov', 'avi', 'webm']);
                    ?>
                        <a href="event_register.php?id=<?php echo $row['id']; ?>" class="block relative h-48 overflow-hidden bg-slate-100">
                            <?php if($is_video): ?>
                                <video src="<?php echo $row['image_url']; ?>" class="w-full h-full object-cover"></video>
                                <div class="absolute inset-0 flex items-center justify-center bg-black/20 group-hover:bg-black/10 transition">
                                    <i data-feather="play-circle" class="w-10 h-10 text-white opacity-80"></i>
                                </div>
                            <?php else: ?>
                                <img src="<?php echo $row['image_url']; ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-500">
                            <?php endif; ?>

                            <span class="absolute top-4 left-4 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/90 backdrop-blur text-slate-800 shadow-sm">
                                <?php echo $row['type']; ?>
                            </span>
                        </a>
                    <?php else: ?>
                        <div class="p-6 border-b border-slate-100 flex justify-between items-start bg-slate-50">
                            <div class="flex flex-col text-center bg-white rounded-lg p-2 min-w-[60px] border border-slate-200 shadow-sm">
                                <span class="text-2xl font-bold text-slate-800 leading-none"><?php echo $dateDay; ?></span>
                                <span class="text-xs font-bold text-slate-500 uppercase"><?php echo $dateMonth; ?></span>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider <?php echo $badgeColor; ?>">
                                <?php echo $row['type']; ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <div class="p-6 flex-1 flex flex-col">
                        <a href="event_register.php?id=<?php echo $row['id']; ?>">
                            <h3 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-primary transition line-clamp-2">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </h3>
                        </a>
                        
                        <div class="space-y-2 mb-6">
                            <?php if(!empty($row['image_url'])): ?>
                            <div class="flex items-center text-slate-500 text-sm">
                                <i data-feather="calendar" class="w-4 h-4 mr-2 text-slate-400"></i>
                                <?php echo date('d F Y', $dateObj); ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="flex items-center text-slate-500 text-sm">
                                <i data-feather="clock" class="w-4 h-4 mr-2 text-slate-400"></i>
                                <?php echo $time; ?> WIB
                            </div>
                            <div class="flex items-center text-slate-500 text-sm">
                                <i data-feather="map-pin" class="w-4 h-4 mr-2 text-slate-400"></i>
                                <?php echo htmlspecialchars($row['location']); ?>
                            </div>
                        </div>

                        <p class="text-slate-500 text-sm line-clamp-3 mb-6 flex-1">
                            <?php echo htmlspecialchars($row['description']); ?>
                        </p>

                        <div class="mt-auto">
                            <?php if ($is_registered): ?>
                                <a href="event_register.php?id=<?php echo $row['id']; ?>" class="flex items-center justify-center w-full py-3 text-center bg-green-50 text-green-700 font-bold rounded-xl transition border border-green-200 hover:bg-green-100">
                                    <i data-feather="check-circle" class="w-4 h-4 mr-2"></i> Telah Mendaftar
                                </a>
                            <?php else: ?>
                                <a href="event_register.php?id=<?php echo $row['id']; ?>" class="block w-full py-3 text-center bg-primary text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-500/20 transform hover:-translate-y-0.5">
                                    Daftar Sekarang
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

        <?php else: ?>
            <div class="text-center py-20 bg-white rounded-2xl border border-dashed border-slate-300">
                <div class="bg-slate-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                    <i data-feather="calendar" class="w-8 h-8"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700">Belum ada agenda aktif</h3>
                <p class="text-slate-500 mt-2">Nantikan kegiatan seru kami selanjutnya.</p>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-white border-t border-slate-200 py-8 text-center">
        <p class="text-sm text-slate-400">© 2025 Komunitas Maju Bersama.</p>
    </footer>

    <script>feather.replace();</script>
</body>
</html>