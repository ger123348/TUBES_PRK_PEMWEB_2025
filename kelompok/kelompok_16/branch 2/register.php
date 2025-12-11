<?php
session_start();
include 'config.php';

$message = ""; // Variabel untuk menampung pesan error/sukses

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Validasi Password
    if ($password !== $confirm_password) {
        $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Password dan Konfirmasi Password tidak sama!</div>";
    } else {
        // 2. Cek Email apakah sudah terdaftar
        $check_email = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check_email) > 0) {
            $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Email sudah terdaftar! Silakan login.</div>";
        } else {
            // 3. Enkripsi Password & Insert Data
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'member'; // Default role

            $insert = mysqli_query($conn, "INSERT INTO users (full_name, email, password, role) VALUES ('$name', '$email', '$hashed_password', '$role')");

            if ($insert) {
                $message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'>Pendaftaran berhasil! Silakan <a href='login.php' class='font-bold underline'>Login disini</a>.</div>";
            } else {
                $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Terjadi kesalahan sistem. Coba lagi nanti.</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Anggota | Komunitas Maju</title>
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
            <h2 class="text-xl font-bold text-slate-800 mt-2">Buat Akun Baru</h2>
            <p class="text-slate-500 text-sm">Bergabunglah bersama ribuan anggota lainnya.</p>
        </div>

        <div class="p-8">
            <?php echo $message; ?>

            <form action="" method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Contoh: Budi Santoso" 
                        class="w-full px-4 py-3 rounded-lg bg-slate-50 border border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition text-slate-700">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Alamat Email</label>
                    <input type="email" name="email" required placeholder="nama@email.com" 
                        class="w-full px-4 py-3 rounded-lg bg-slate-50 border border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition text-slate-700">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" required placeholder="••••••••" 
                        class="w-full px-4 py-3 rounded-lg bg-slate-50 border border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition text-slate-700">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" required placeholder="••••••••" 
                        class="w-full px-4 py-3 rounded-lg bg-slate-50 border border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition text-slate-700">
                </div>

                <div class="pt-2">
                    <button type="submit" name="register" class="w-full py-3.5 bg-primary hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg shadow-blue-500/30 transition transform hover:-translate-y-0.5">
                        Daftar Sekarang
                    </button>
                </div>
            </form>
        </div>

        <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 text-center">
            <p class="text-sm text-slate-600">Sudah punya akun? <a href="login.php" class="text-primary font-semibold hover:underline">Masuk disini</a></p>
        </div>
    </div>

</body>
</html>