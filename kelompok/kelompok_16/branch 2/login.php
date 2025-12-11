<?php
session_start();
include 'config.php';

// Jika sudah login, lempar kembali sesuai role
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$message = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // 1. Cari user berdasarkan email
    $query = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    
    if (mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);
        
        // 2. Verifikasi Password Hash
        if (password_verify($password, $row['password'])) {
            // 3. Set Session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['full_name'];
            $_SESSION['user_role'] = $row['role'];
            $_SESSION['user_position'] = $row['position'];
            $_SESSION['user_photo'] = $row['photo'];

            // 4. Redirect Berdasarkan Role (UPDATE PENTING DISINI)
            if ($row['role'] == 'admin') {
                // Jika Admin, arahkan ke folder admin
                header("Location: admin/dashboard.php");
            } else {
                // Jika Member biasa, arahkan ke halaman utama
                header("Location: index.php");
            }
            exit;
        } else {
            $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Password salah!</div>";
        }
    } else {
        $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Email tidak ditemukan!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | Komunitas Maju</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
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
<body class="bg-slate-50 font-sans min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100">
        <div class="bg-slate-50 px-8 py-6 border-b border-slate-100 text-center">
            <a href="index.php" class="inline-flex items-center gap-2 mb-2 group">
                <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-white font-bold group-hover:bg-blue-700 transition">K</div>
                <span class="font-bold text-xl text-secondary">Komunitas<span class="text-primary">Maju</span></span>
            </a>
            <h2 class="text-xl font-bold text-slate-800 mt-2">Selamat Datang Kembali</h2>
            <p class="text-slate-500 text-sm">Masuk untuk mengakses dashboard.</p>
        </div>

        <div class="p-8">
            <?php echo $message; ?>

            <form action="" method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Alamat Email</label>
                    <input type="email" name="email" required placeholder="nama@email.com" 
                        class="w-full px-4 py-3 rounded-lg bg-slate-50 border border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition text-slate-700">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-sm font-medium text-slate-700">Password</label>
                        <a href="#" class="text-xs text-primary font-medium hover:underline">Lupa Password?</a>
                    </div>
                    <input type="password" name="password" required placeholder="••••••••" 
                        class="w-full px-4 py-3 rounded-lg bg-slate-50 border border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition text-slate-700">
                </div>

                <div class="pt-2">
                    <button type="submit" name="login" class="w-full py-3.5 bg-primary hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg shadow-blue-500/30 transition transform hover:-translate-y-0.5">
                        Masuk Akun
                    </button>
                </div>
            </form>
        </div>

        <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 text-center">
            <p class="text-sm text-slate-600">Belum jadi anggota? <a href="register.php" class="text-primary font-semibold hover:underline">Daftar sekarang</a></p>
        </div>
    </div>

</body>
</html>