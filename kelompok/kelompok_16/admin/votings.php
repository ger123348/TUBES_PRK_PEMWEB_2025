<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { header("Location: ../login.php"); exit; }

// HAPUS VOTING
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM votings WHERE id='$id'");
    header("Location: votings.php");
}

// QUERY DATA VOTING + HITUNG SUARA
$votings = mysqli_query($conn, "SELECT v.*, 
    (SELECT COUNT(*) FROM voting_votes WHERE voting_id = v.id) as total_votes 
    FROM votings v ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>List Voting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script> tailwind.config = { theme: { extend: { colors: { primary: '#2563eb', secondary: '#1e293b' } } } } </script>
</head>
<body class="bg-slate-50 font-sans text-slate-800">
    
    <?php include 'sidebar.php'; ?>

    <main class="md:ml-64 p-8 min-h-screen">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Kelola Voting</h1>
                <p class="text-slate-500 text-sm">Monitor hasil dan buat pemungutan suara baru.</p>
            </div>
            <a href="voting_form.php" class="bg-blue-600 text-white px-5 py-2.5 rounded-xl hover:bg-blue-700 transition flex items-center gap-2 shadow-lg shadow-blue-500/30">
                <i data-feather="plus" class="w-4 h-4"></i> Buat Voting Baru
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while($row = mysqli_fetch_assoc($votings)): 
                $vid = $row['id'];
                $total = $row['total_votes'];
                $is_active = (strtotime($row['end_date']) > time() && $row['status'] == 'active');
                
                // Ambil Top Opsi (Untuk Preview Progress)
                $opsi_q = mysqli_query($conn, "SELECT option_name, vote_count FROM voting_options WHERE voting_id='$vid' ORDER BY vote_count DESC LIMIT 2");
            ?>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl transition flex flex-col overflow-hidden">
                <div class="p-6 flex-1">
                    <div class="flex justify-between items-start mb-4">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?php echo $row['type'] == 'member' ? 'bg-purple-100 text-purple-700' : 'bg-orange-100 text-orange-700'; ?>">
                            <?php echo $row['type'] == 'member' ? 'Pemilihan' : 'Event'; ?>
                        </span>
                        <span class="flex items-center gap-1 text-xs font-semibold <?php echo $is_active ? 'text-green-600' : 'text-slate-400'; ?>">
                            <div class="w-2 h-2 rounded-full <?php echo $is_active ? 'bg-green-500 animate-pulse' : 'bg-slate-300'; ?>"></div>
                            <?php echo $is_active ? 'Berjalan' : 'Selesai'; ?>
                        </span>
                    </div>

                    <h3 class="font-bold text-lg text-slate-800 mb-2 line-clamp-2"><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p class="text-xs text-slate-400 mb-4 flex items-center gap-1">
                        <i data-feather="calendar" class="w-3 h-3"></i> Berakhir: <?php echo date('d M Y', strtotime($row['end_date'])); ?>
                    </p>

                    <div class="space-y-3 pt-4 border-t border-slate-100">
                        <?php while($opt = mysqli_fetch_assoc($opsi_q)): 
                            $percent = ($total > 0) ? ($opt['vote_count'] / $total) * 100 : 0;
                        ?>
                        <div>
                            <div class="flex justify-between text-xs font-medium mb-1">
                                <span><?php echo $opt['option_name']; ?></span>
                                <span><?php echo round($percent); ?>%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5">
                                <div class="bg-primary h-1.5 rounded-full" style="width: <?php echo $percent; ?>%"></div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="bg-slate-50 p-4 border-t border-slate-100 flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-500">Total: <?php echo $total; ?> Suara</span>
                    <div class="flex gap-2">
                        <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Hapus voting ini?')" class="text-slate-400 hover:text-red-500 p-1">
                            <i data-feather="trash-2" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>

    <script>feather.replace();</script>
</body>
</html>