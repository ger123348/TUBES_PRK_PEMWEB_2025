<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $donation_id = $_POST['donation_id'];
    $amount = (int)str_replace('.', '', $_POST['amount']); // Hapus titik jika ada format ribuan
    
    // Tentukan Nama Donatur
    if (isset($_POST['is_anonymous'])) {
        $donor_name = "Hamba Allah";
    } else {
        $donor_name = mysqli_real_escape_string($conn, $_POST['name']);
    }
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NULL';
    $method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // INSERT TRANSAKSI (Status Default: Pending)
    $sql = "INSERT INTO donation_transactions (donation_id, user_id, donor_name, amount, payment_method, message, status) 
            VALUES ('$donation_id', " . ($user_id === 'NULL' ? "NULL" : "'$user_id'") . ", '$donor_name', '$amount', '$method', '$message', 'pending')";

    if (mysqli_query($conn, $sql)) {
        $status = "success";
    } else {
        echo "Error: " . mysqli_error($conn); exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instruksi Pembayaran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="bg-slate-50 font-sans min-h-screen flex items-center justify-center p-4">

    <div class="bg-white max-w-md w-full rounded-2xl shadow-xl border border-slate-200 overflow-hidden text-center">
        <div class="bg-blue-50 p-8">
            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm text-blue-500">
                <i data-feather="clock" class="w-10 h-10"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Menunggu Pembayaran</h1>
            <p class="text-slate-500 mt-2">Mohon selesaikan transfer Anda.</p>
        </div>

        <div class="p-8">
            <p class="text-sm text-slate-500 mb-2">Total Tagihan:</p>
            <div class="text-3xl font-bold text-blue-600 mb-6">
                Rp <?php echo number_format($amount, 0, ',', '.'); ?>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 mb-6 text-left">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-slate-500">Metode</span>
                    <span class="font-bold uppercase"><?php echo $method; ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-500">Nomor Rekening/VA</span>
                    <span class="font-bold flex items-center gap-2">
                        8800123456789 
                        <i data-feather="copy" class="w-3 h-3 text-blue-600 cursor-pointer"></i>
                    </span>
                </div>
            </div>
            
            <p class="text-xs text-slate-400 mb-6 leading-relaxed">
                Pembayaran Anda akan diverifikasi otomatis oleh sistem atau admin kami dalam waktu maksimal 1x24 jam.
            </p>

            <a href="index.php" class="block w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition">
                Kembali ke Beranda
            </a>
        </div>
    </div>

    <script>feather.replace();</script>
</body>
</html>