<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { header("Location: ../login.php"); exit; }

// --- LOGIKA SIMPAN DATA ---
if (isset($_POST['save_voting'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $type = $_POST['type'];
    $end_date = $_POST['end_date'];

    // 1. Simpan Header Voting
    $q_head = "INSERT INTO votings (title, description, type, start_date, end_date, status) VALUES ('$title', '$desc', '$type', NOW(), '$end_date', 'active')";
    
    if (mysqli_query($conn, $q_head)) {
        $voting_id = mysqli_insert_id($conn);

        // 2. Simpan Opsi/Kandidat (Looping)
        // Kita mengambil array dari input form
        $names = $_POST['candidate_name'];
        $descs = $_POST['candidate_desc'];
        
        // Loop setiap kandidat yang diinput
        for ($i = 0; $i < count($names); $i++) {
            $opt_name = mysqli_real_escape_string($conn, $names[$i]);
            $opt_desc = mysqli_real_escape_string($conn, $descs[$i]);
            $img_file = null;

            // Upload Foto Kandidat (Jika ada)
            if (!empty($_FILES['candidate_img']['name'][$i])) {
                $targetDir = "uploads/candidates/";
                if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
                
                $fileName = time() . "_" . $i . "_" . basename($_FILES['candidate_img']['name'][$i]);
                $targetFilePath = $targetDir . $fileName;
                
                if (move_uploaded_file($_FILES['candidate_img']['tmp_name'][$i], $targetFilePath)) {
                    $img_file = $fileName;
                }
            }

            // Insert ke tabel options
            if (!empty($opt_name)) {
                $q_opt = "INSERT INTO voting_options (voting_id, option_name, description, image) VALUES ('$voting_id', '$opt_name', '$opt_desc', '$img_file')";
                mysqli_query($conn, $q_opt);
            }
        }

        echo "<script>alert('Voting berhasil dibuat!'); window.location='votings.php';</script>";
    } else {
        echo "<script>alert('Gagal membuat voting.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Buat Voting Baru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script> tailwind.config = { theme: { extend: { colors: { primary: '#2563eb', secondary: '#1e293b' } } } } </script>
</head>
<body class="bg-slate-50 font-sans text-slate-800">
    
    <?php include 'sidebar.php'; ?>

    <main class="md:ml-64 p-8 min-h-screen">
        
        <div class="max-w-4xl mx-auto flex items-center gap-4 mb-8">
            <a href="votings.php" class="p-2 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 text-slate-500 transition">
                <i data-feather="arrow-left" class="w-5 h-5"></i>
            </a>
            <h1 class="text-2xl font-bold text-slate-900">Buat Voting Baru</h1>
        </div>

        <form method="POST" enctype="multipart/form-data" class="max-w-4xl mx-auto">
            
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 mb-8">
                <h2 class="text-lg font-bold text-slate-900 mb-6 border-b border-slate-100 pb-4">Informasi Dasar</h2>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Judul Voting</label>
                        <input type="text" name="title" required class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary outline-none transition" placeholder="Contoh: Pemilihan Ketua Komunitas 2025">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tipe Voting</label>
                            <select name="type" id="votingType" onchange="toggleFormType()" class="w-full border border-slate-200 rounded-xl px-4 py-3 bg-white focus:ring-2 focus:ring-primary outline-none transition cursor-pointer">
                                <option value="member">Pemilihan Anggota (Kandidat + Foto)</option>
                                <option value="event">Voting Acara / Umum (Opsi Text)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Berakhir</label>
                            <input type="date" name="end_date" required class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary outline-none transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi / Peraturan</label>
                        <textarea name="description" rows="3" class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary outline-none transition resize-none" placeholder="Jelaskan tujuan voting ini..."></textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 mb-8">
                <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-bold text-slate-900" id="sectionTitle">Daftar Kandidat</h2>
                    <button type="button" onclick="addCandidateRow()" class="text-sm font-bold text-primary hover:bg-blue-50 px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                        <i data-feather="plus-circle" class="w-4 h-4"></i> Tambah Baris
                    </button>
                </div>

                <div id="candidatesContainer" class="space-y-4">
                    <div class="candidate-row flex gap-4 items-start bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <div class="flex-1 space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama</label>
                                    <input type="text" name="candidate_name[]" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary outline-none" placeholder="Nama Kandidat / Opsi">
                                </div>
                                <div class="photo-input-group">
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Foto (Opsional)</label>
                                    <input type="file" name="candidate_img[]" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200">
                                </div>
                            </div>
                            <div>
                                <input type="text" name="candidate_desc[]" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary outline-none" placeholder="Keterangan singkat / Visi Misi (Opsional)">
                            </div>
                        </div>
                        <button type="button" onclick="removeRow(this)" class="mt-2 text-slate-400 hover:text-red-500 transition"><i data-feather="trash-2" class="w-5 h-5"></i></button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 mb-10">
                <a href="votings.php" class="px-6 py-3 rounded-xl text-slate-600 font-bold hover:bg-slate-200 transition">Batal</a>
                <button type="submit" name="save_voting" class="px-8 py-3 rounded-xl bg-primary text-white font-bold hover:bg-blue-600 shadow-lg shadow-blue-500/30 transition transform hover:-translate-y-0.5">
                    Simpan Voting
                </button>
            </div>

        </form>
    </main>

    <script>
        feather.replace();

        // Fungsi untuk menambah baris kandidat secara dinamis
        function addCandidateRow() {
            const container = document.getElementById('candidatesContainer');
            const div = document.createElement('div');
            div.className = 'candidate-row flex gap-4 items-start bg-slate-50 p-4 rounded-xl border border-slate-200 animate-fade-in';
            
            // Cek tipe saat ini untuk menampilkan/menyembunyikan input foto
            const isMemberType = document.getElementById('votingType').value === 'member';
            const displayStyle = isMemberType ? 'block' : 'none';

            div.innerHTML = `
                <div class="flex-1 space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama</label>
                            <input type="text" name="candidate_name[]" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary outline-none" placeholder="Nama Kandidat / Opsi">
                        </div>
                        <div class="photo-input-group" style="display: ${displayStyle};">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Foto (Opsional)</label>
                            <input type="file" name="candidate_img[]" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200">
                        </div>
                    </div>
                    <div>
                        <input type="text" name="candidate_desc[]" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-primary outline-none" placeholder="Keterangan singkat (Opsional)">
                    </div>
                </div>
                <button type="button" onclick="removeRow(this)" class="mt-2 text-slate-400 hover:text-red-500 transition"><i data-feather="trash-2" class="w-5 h-5"></i></button>
            `;
            
            container.appendChild(div);
            feather.replace();
        }

        // Hapus baris
        function removeRow(btn) {
            const rows = document.querySelectorAll('.candidate-row');
            if (rows.length > 1) {
                btn.closest('.candidate-row').remove();
            } else {
                alert("Minimal harus ada satu opsi!");
            }
        }

        // Ubah Tampilan berdasarkan Tipe Voting
        function toggleFormType() {
            const type = document.getElementById('votingType').value;
            const photoInputs = document.querySelectorAll('.photo-input-group');
            const title = document.getElementById('sectionTitle');

            if (type === 'member') {
                title.innerText = 'Daftar Kandidat (Nama & Foto)';
                photoInputs.forEach(el => el.style.display = 'block');
            } else {
                title.innerText = 'Daftar Pilihan Jawaban';
                photoInputs.forEach(el => el.style.display = 'none');
            }
        }
    </script>
</body>
</html>