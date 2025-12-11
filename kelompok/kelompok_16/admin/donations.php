<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { header("Location: ../login.php"); exit; }

// LOGIKA KONFIRMASI DONASI
if (isset($_GET['confirm'])) {
    $trx_id = $_GET['confirm'];
    
    // 1. Ambil data transaksi
    $q = mysqli_query($conn, "SELECT * FROM donation_transactions WHERE id='$trx_id' AND status='pending'");
    
    if (mysqli_num_rows($q) > 0) {
        $trx = mysqli_fetch_assoc($q);
        $amount = $trx['amount'];
        $campaign_id = $trx['donation_id'];

        // 2. Update Status Transaksi jadi Confirmed
        mysqli_query($conn, "UPDATE donation_transactions SET status='confirmed' WHERE id='$trx_id'");

        // 3. Update Total Uang Terkumpul di Tabel Campaign (donations)
        mysqli_query($conn, "UPDATE donations SET current_amount = current_amount + $amount WHERE id='$campaign_id'");
        
        echo "<script>alert('Donasi berhasil dikonfirmasi!'); window.location='donations.php';</script>";
    }
}

// LOGIKA TOLAK DONASI
if (isset($_GET['reject'])) {
    $trx_id = $_GET['reject'];
    mysqli_query($conn, "UPDATE donation_transactions SET status='rejected' WHERE id='$trx_id'");
    header("Location: donations.php");
}

// AMBIL DATA TRANSAKSI
$query = mysqli_query($conn, "SELECT t.*, d.title as campaign_title 
                              FROM donation_transactions t 
                              JOIN donations d ON t.donation_id = d.id 
                              ORDER BY t.created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Donasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script> tailwind.config = { theme: { extend: { colors: { primary: '#2563eb', secondary: '#1e293b' } } } } </script>
</head>
<body class="bg-slate-50 font-sans text-slate-800">
    <?php include 'sidebar.php'; ?>

    <main class="md:ml-64 p-8 min-h-screen">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Donasi Masuk</h1>
                <p class="text-slate-500 text-sm">Verifikasi pembayaran dari donatur.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold border-b border-slate-100">
                    <tr>
                        <th class="p-5">Donatur</th>
                        <th class="p-5">Nominal</th>
                        <th class="p-5">Campaign</th>
                        <th class="p-5">Metode</th>
                        <th class="p-5">Status</th>
                        <th class="p-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php if(mysqli_num_rows($query) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($query)): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-5">
                                <p class="font-bold text-slate-800"><?php echo htmlspecialchars($row['donor_name']); ?></p>
                                <p class="text-xs text-slate-400"><?php echo date('d M Y H:i', strtotime($row['created_at'])); ?></p>
                                <?php if($row['message']): ?>
                                    <p class="text-xs text-slate-500 mt-1 italic">"<?php echo htmlspecialchars($row['message']); ?>"</p>
                                <?php endif; ?>
                            </td>
                            <td class="p-5 font-bold text-slate-800">
                                Rp <?php echo number_format($row['amount'], 0, ',', '.'); ?>
                            </td>
                            <td class="p-5 text-slate-600 max-w-xs truncate">
                                <?php echo htmlspecialchars($row['campaign_title']); ?>
                            </td>
                            <td class="p-5 uppercase text-xs font-bold text-slate-500">
                                <?php echo htmlspecialchars($row['payment_method']); ?>
                            </td>
                            <td class="p-5">
                                <?php if($row['status'] == 'confirmed'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                        Diterima
                                    </span>
                                <?php elseif($row['status'] == 'rejected'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                        Ditolak
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700 animate-pulse">
                                        Menunggu
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="p-5 text-right">
                                <?php if($row['status'] == 'pending'): ?>
                                    <div class="flex justify-end gap-2">
                                        <a href="?confirm=<?php echo $row['id']; ?>" onclick="return confirm('Konfirmasi dana ini sudah masuk?')" class="bg-green-600 text-white p-2 rounded-lg hover:bg-green-700 transition" title="Terima">
                                            <i data-feather="check" class="w-4 h-4"></i>
                                        </a>
                                        <a href="?reject=<?php echo $row['id']; ?>" onclick="return confirm('Tolak donasi ini?')" class="bg-red-100 text-red-600 p-2 rounded-lg hover:bg-red-200 transition" title="Tolak">
                                            <i data-feather="x" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="text-slate-300 text-xs italic">Selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="p-8 text-center text-slate-400">Belum ada donasi masuk.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script>feather.replace();</script>
</body>
</html>