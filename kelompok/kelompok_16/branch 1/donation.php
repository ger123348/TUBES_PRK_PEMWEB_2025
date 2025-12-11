<?php
session_start();
include 'config.php';

// Ambil semua campaign donasi
$query = mysqli_query($conn, "SELECT * FROM donations ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi | Komunitas Maju Bersama</title>
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
<body class="bg-slate-50 font-sans text-slate-800 antialiased selection:bg-blue-100 selection:text-blue-900">

  <?php include 'navbar_include.php'; ?>
<script>const isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;</script>

    <div class="bg-white border-b border-slate-100">
        <div class="max-w-4xl mx-auto px-4 py-16 text-center">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold uppercase tracking-wider mb-6">
                <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span> Program Kebaikan
            </span>
            <h1 class="text-3xl md:text-5xl font-bold text-slate-900 tracking-tight mb-4">
                Salurkan <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">Kebaikan Anda</span>
            </h1>
            <p class="text-lg text-slate-500 leading-relaxed">
                Pilih program donasi di bawah ini. Setiap kontribusi Anda, sekecil apapun, akan menciptakan dampak besar.
            </p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php if(mysqli_num_rows($query) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php while($row = mysqli_fetch_assoc($query)): 
                    // Hitung Persentase
                    $persen = 0;
                    if($row['target_amount'] > 0) {
                        $persen = ($row['current_amount'] / $row['target_amount']) * 100;
                    }
                    
                    // Cek Gambar
                    $imagePath = !empty($row['image']) ? 'admin/uploads/campaigns/' . $row['image'] : '';
                    $hasImage = (!empty($imagePath) && file_exists($imagePath));
                ?>
                
                <div class="group bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-blue-900/5 transition-all duration-300 flex flex-col h-full hover:-translate-y-1">
                    
                    <div class="h-56 bg-slate-100 relative overflow-hidden">
                        <?php if($hasImage): ?>
                            <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <?php else: ?>
                            <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 bg-slate-50">
                                <i data-feather="image" class="w-10 h-10 opacity-30 mb-2"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>

                    <div class="p-6 flex-1 flex flex-col">
                        <div class="mb-4">
                            <h3 class="text-lg font-bold text-slate-900 mb-2 leading-snug group-hover:text-primary transition-colors line-clamp-2">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </h3>
                            <p class="text-slate-500 text-sm leading-relaxed line-clamp-2">
                                <?php echo htmlspecialchars(strip_tags($row['description'] ?? '')); ?>
                            </p>
                        </div>
                        
                        <div class="mt-auto pt-4 border-t border-slate-50">
                            <div class="flex justify-between items-end mb-2">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Terkumpul</p>
                                    <p class="text-base font-bold text-primary">Rp <?php echo number_format($row['current_amount'], 0, ',', '.'); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-semibold text-slate-400">Target</p>
                                    <p class="text-sm font-semibold text-slate-600">Rp <?php echo number_format($row['target_amount'], 0, ',', '.'); ?></p>
                                </div>
                            </div>
                            
                            <div class="w-full bg-slate-100 rounded-full h-2 mb-6 overflow-hidden">
                                <div class="bg-primary h-2 rounded-full transition-all duration-1000 ease-out" style="width: <?php echo min($persen, 100); ?>%"></div>
                            </div>

                            <a href="donation_payment.php?id=<?php echo $row['id']; ?>" class="block w-full text-center py-3 bg-slate-900 text-white text-sm font-bold rounded-xl hover:bg-primary transition-colors duration-300 shadow-lg shadow-slate-900/10 hover:shadow-blue-600/20">
                                Donasi Sekarang
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

        <?php else: ?>
            <div class="max-w-lg mx-auto text-center py-16 bg-white rounded-2xl border-2 border-dashed border-slate-200">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-feather="inbox" class="w-8 h-8 text-slate-300"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Belum Ada Program</h3>
                <p class="text-slate-500 text-sm">Nantikan program kebaikan kami selanjutnya.</p>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-white border-t border-slate-200 py-10 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-sm text-slate-400 font-medium">© 2025 Komunitas Maju Bersama.</p>
        </div>
    </footer>

    <script>
        feather.replace();
    </script>
</body>
</html>