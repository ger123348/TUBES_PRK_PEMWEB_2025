<?php
session_start();
include 'config.php';

// 1. Validasi ID Donasi
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: donation.php");
    exit;
}

$donation_id = $_GET['id'];
$query = mysqli_query($conn, "SELECT * FROM donations WHERE id = '$donation_id'");

if (mysqli_num_rows($query) == 0) {
    echo "Campaign donasi tidak ditemukan.";
    exit;
}

$data = mysqli_fetch_assoc($query);

// Data User Otomatis jika Login
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
// Simulasi Email (karena di session index sebelumnya belum disimpan email, kosongkan saja atau tambah query user)
$user_email = ''; 

// --- PERBAIKAN: Ambil Path Gambar ---
$imagePath = !empty($data['image']) ? 'admin/uploads/campaigns/' . $data['image'] : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Donasi | Komunitas Maju</title>
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
    <style>
        /* Hide default radio */
        .payment-radio:checked + div {
            border-color: #2563eb;
            background-color: #eff6ff;
        }
        .payment-radio:checked + div .check-icon {
            display: block;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 pb-24">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-3xl mx-auto px-4 h-16 flex items-center gap-4">
            <a href="donation.php" class="p-2 hover:bg-slate-100 rounded-full transition">
                <i data-feather="arrow-left" class="w-5 h-5 text-slate-600"></i>
            </a>
            <h1 class="font-bold text-lg text-slate-800">Isi Nominal Donasi</h1>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 py-6">
        
        <div class="bg-white p-4 rounded-xl border border-slate-200 flex gap-4 mb-6 shadow-sm">
            <div class="w-20 h-20 bg-slate-100 rounded-lg flex items-center justify-center flex-shrink-0 text-slate-400 overflow-hidden">
                <?php if(!empty($imagePath) && file_exists($imagePath)): ?>
                    <img src="<?php echo $imagePath; ?>" alt="Thumbnail" class="w-full h-full object-cover">
                <?php else: ?>
                    <i data-feather="image" class="w-8 h-8"></i>
                <?php endif; ?>
            </div>
            <div>
                <p class="text-xs text-primary font-bold uppercase tracking-wide mb-1">Anda akan berdonasi untuk:</p>
                <h2 class="font-bold text-slate-900 line-clamp-2"><?php echo htmlspecialchars($data['title']); ?></h2>
            </div>
        </div>

        <form action="process_donation.php" method="POST" id="donationForm">
            <input type="hidden" name="donation_id" value="<?php echo $data['id']; ?>">
            
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm mb-6">
                <h3 class="font-bold text-lg mb-4">Mau donasi berapa?</h3>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                    <button type="button" onclick="setNominal(10000)" class="nominal-btn border border-slate-200 rounded-lg py-3 font-medium hover:border-primary hover:text-primary transition">Rp 10k</button>
                    <button type="button" onclick="setNominal(50000)" class="nominal-btn border border-slate-200 rounded-lg py-3 font-medium hover:border-primary hover:text-primary transition">Rp 50k</button>
                    <button type="button" onclick="setNominal(100000)" class="nominal-btn border border-slate-200 rounded-lg py-3 font-medium hover:border-primary hover:text-primary transition">Rp 100k</button>
                    <button type="button" onclick="setNominal(500000)" class="nominal-btn border border-slate-200 rounded-lg py-3 font-medium hover:border-primary hover:text-primary transition">Rp 500k</button>
                </div>

                <div class="relative">
                    <span class="absolute left-4 top-3.5 font-bold text-slate-400">Rp</span>
                    <input type="number" name="amount" id="amountInput" required min="1000" placeholder="0" class="w-full pl-12 pr-4 py-3 rounded-lg bg-slate-50 border border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition font-bold text-lg">
                </div>
                <p class="text-xs text-slate-400 mt-2">*Minimal donasi Rp 1.000</p>
            </div>

            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm mb-6">
                <h3 class="font-bold text-lg mb-4">Data Donatur</h3>
                
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <div class="bg-blue-50 text-blue-800 text-sm p-3 rounded-lg mb-4 flex gap-2">
                        <i data-feather="info" class="w-4 h-4 mt-0.5"></i>
                        <span><a href="login.php" class="font-bold underline">Masuk</a> agar riwayat donasi tercatat di akun Anda.</span>
                    </div>
                <?php endif; ?>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" value="<?php echo $user_name; ?>" required placeholder="Nama Anda (atau Hamba Allah)" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-primary outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email / WhatsApp</label>
                        <input type="text" name="contact" value="<?php echo $user_email; ?>" required placeholder="Untuk kirim bukti pembayaran" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-primary outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Dukungan / Doa (Opsional)</label>
                        <textarea name="message" rows="2" placeholder="Tulis doa atau dukungan untuk campaign ini..." class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-primary outline-none transition"></textarea>
                    </div>
                    
                    <div class="flex items-center gap-3 mt-2">
                        <input type="checkbox" name="is_anonymous" id="anon" class="w-5 h-5 text-primary rounded border-slate-300 focus:ring-primary">
                        <label for="anon" class="text-sm text-slate-600">Sembunyikan nama saya (Hamba Allah)</label>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm mb-6">
                <h3 class="font-bold text-lg mb-4">Pilih Pembayaran</h3>
                
                <div class="space-y-3">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">E-Wallet (Otomatis)</p>
                    
                    <label class="cursor-pointer block relative">
                        <input type="radio" name="payment_method" value="dana" class="payment-radio sr-only" required>
                        <div class="p-4 border border-slate-200 rounded-xl flex items-center justify-between hover:border-blue-300 transition">
                            <div class="flex items-center gap-4">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/72/Logo_dana_blue.svg/1200px-Logo_dana_blue.svg.png" class="h-6 w-auto object-contain" alt="DANA">
                                <span class="font-medium text-slate-700">DANA</span>
                            </div>
                            <div class="check-icon hidden text-primary">
                                <i data-feather="check-circle" class="w-5 h-5"></i>
                            </div>
                        </div>
                    </label>

                    <label class="cursor-pointer block relative">
                        <input type="radio" name="payment_method" value="gopay" class="payment-radio sr-only">
                        <div class="p-4 border border-slate-200 rounded-xl flex items-center justify-between hover:border-blue-300 transition">
                            <div class="flex items-center gap-4">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/86/Gopay_logo.svg/2560px-Gopay_logo.svg.png" class="h-6 w-auto object-contain" alt="GoPay">
                                <span class="font-medium text-slate-700">GoPay</span>
                            </div>
                            <div class="check-icon hidden text-primary">
                                <i data-feather="check-circle" class="w-5 h-5"></i>
                            </div>
                        </div>
                    </label>

                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-4 mb-2">Virtual Account (Verifikasi Otomatis)</p>
                    
                    <label class="cursor-pointer block relative">
                        <input type="radio" name="payment_method" value="bca" class="payment-radio sr-only">
                        <div class="p-4 border border-slate-200 rounded-xl flex items-center justify-between hover:border-blue-300 transition">
                            <div class="flex items-center gap-4">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Bank_Central_Asia.svg/1200px-Bank_Central_Asia.svg.png" class="h-8 w-auto object-contain" alt="BCA">
                                <div>
                                    <span class="block font-medium text-slate-700">BCA Virtual Account</span>
                                    <span class="text-xs text-slate-400">Biaya admin Rp 0</span>
                                </div>
                            </div>
                            <div class="check-icon hidden text-primary">
                                <i data-feather="check-circle" class="w-5 h-5"></i>
                            </div>
                        </div>
                    </label>

                    <label class="cursor-pointer block relative">
                        <input type="radio" name="payment_method" value="bri" class="payment-radio sr-only">
                        <div class="p-4 border border-slate-200 rounded-xl flex items-center justify-between hover:border-blue-300 transition">
                            <div class="flex items-center gap-4">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/BANK_BRI_logo.svg/1200px-BANK_BRI_logo.svg.png" class="h-8 w-auto object-contain" alt="BRI">
                                <div>
                                    <span class="block font-medium text-slate-700">BRI Virtual Account</span>
                                </div>
                            </div>
                            <div class="check-icon hidden text-primary">
                                <i data-feather="check-circle" class="w-5 h-5"></i>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="fixed bottom-0 left-0 w-full bg-white border-t border-slate-200 p-4 z-40">
                <div class="max-w-3xl mx-auto flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500">Total Pembayaran</p>
                        <p class="text-xl font-bold text-primary" id="totalDisplay">Rp 0</p>
                    </div>
                    <button type="submit" class="bg-primary text-white font-bold py-3 px-8 rounded-full shadow-lg hover:bg-blue-700 transition transform hover:-translate-y-0.5">
                        Lanjut Bayar
                    </button>
                </div>
            </div>

        </form>
    </div>

    <script>
        feather.replace();

        const amountInput = document.getElementById('amountInput');
        const totalDisplay = document.getElementById('totalDisplay');
        const buttons = document.querySelectorAll('.nominal-btn');

        // Fungsi Set Nominal dari Tombol
        function setNominal(amount) {
            amountInput.value = amount;
            updateTotal();
            
            // Visual feedback pada tombol
            buttons.forEach(btn => {
                btn.classList.remove('border-primary', 'bg-blue-50', 'text-primary');
                btn.classList.add('border-slate-200');
            });
            // Mencari tombol yang diklik (secara manual di kasus ini kita tidak pass 'this', jadi reset aja style-nya biar simple, user lihat input berubah)
        }

        // Fungsi Update Total Text
        function updateTotal() {
            const val = amountInput.value;
            if (val) {
                // Format Rupiah
                const formatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
                totalDisplay.innerText = formatted;
            } else {
                totalDisplay.innerText = 'Rp 0';
            }
        }

        // Event Listener saat user ketik manual
        amountInput.addEventListener('input', updateTotal);

        // Validasi Form sebelum submit
        document.getElementById('donationForm').addEventListener('submit', function(e) {
            if (!amountInput.value || amountInput.value < 1000) {
                e.preventDefault();
                alert('Mohon isi nominal donasi minimal Rp 1.000');
                amountInput.focus();
            }
        });
    </script>
</body>
</html>