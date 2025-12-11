<?php
session_start();
include '../config.php';

if (!isset($_GET['id'])) { header("Location: votings.php"); exit; }
$id = $_GET['id'];

// Ambil Data Voting Utama
$voting = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM votings WHERE id='$id'"));

// --- FUNGSI UPLOAD ---
function uploadCandidatePhoto($file) {
    $targetDir = "uploads/candidates/";
    if (!file_exists($targetDir)) { mkdir($targetDir, 0777, true); }
    $fileName = time() . '_' . basename($file["name"]);
    if (move_uploaded_file($file["tmp_name"], $targetDir . $fileName)) return $fileName;
    return null;
}

// --- TAMBAH KANDIDAT/OPSI ---
if (isset($_POST['add_option'])) {
    $name = mysqli_real_escape_string($conn, $_POST['option_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['option_desc']);
    $image = null;

    if (!empty($_FILES['candidate_image']['name'])) {
        $image = uploadCandidatePhoto($_FILES['candidate_image']);
    }

    $q = "INSERT INTO voting_options (voting_id, option_name, description, image) VALUES ('$id', '$name', '$desc', '$image')";
    mysqli_query($conn, $q);
}

// --- HAPUS KANDIDAT ---
if (isset($_GET['del_opt'])) {
    $oid = $_GET['del_opt'];
    mysqli_query($conn, "DELETE FROM voting_options WHERE id='$oid'");
    header("Location: voting_manage.php?id=$id");
}

// Ambil Daftar Kandidat
$options = mysqli_query($conn, "SELECT * FROM voting_options WHERE voting_id='$id'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Kandidat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script> tailwind.config = { theme: { extend: { colors: { primary: '#2563eb', secondary: '#1e293b' } } } } </script>
</head>
<body class="bg-slate-50 font-sans text-slate-800">
    
    <?php include 'sidebar.php'; ?>

    <main class="md:ml-64 p-8 min-h-screen">
        
        <div class="flex items-center gap-4 mb-8">
            <a href="votings.php" class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-500">
                <i data-feather="arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900"><?php echo htmlspecialchars($voting['title']); ?></h1>
                <p class="text-slate-500 text-sm">Kelola pilihan jawaban atau kandidat.</p>
            </div>
            <div class="ml-auto">
                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold uppercase tracking-wider">
                    Tipe: <?php echo ucfirst($voting['type']); ?>
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm sticky top-8">
                    <h3 class="font-bold text-lg mb-4">Tambah Pilihan</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                                    <?php echo ($voting['type'] == 'member') ? 'Nama Kandidat' : 'Nama Opsi'; ?>
                                </label>
                                <input type="text" name="option_name" required class="w-full border border-slate-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-primary outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Deskripsi / Visi Misi</label>
                                <textarea name="option_desc" rows="3" class="w-full border border-slate-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-primary outline-none"></textarea>
                            </div>

                            <?php if($voting['type'] == 'member'): ?>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Foto Kandidat</label>
                                <div class="relative w-full border-2 border-dashed border-slate-300 rounded-lg p-4 text-center hover:bg-slate-50 transition cursor-pointer">
                                    <input type="file" name="candidate_image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*">
                                    <div class="text-slate-400">
                                        <i data-feather="image" class="mx-auto w-6 h-6 mb-1"></i>
                                        <span class="text-xs">Klik Upload Foto</span>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <button type="submit" name="add_option" class="w-full bg-slate-900 text-white py-2.5 rounded-lg font-bold hover:bg-slate-700 transition">
                                <i data-feather="plus-circle" class="w-4 h-4 inline mr-1"></i> Tambah
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="space-y-4">
                    <?php if(mysqli_num_rows($options) == 0): ?>
                        <div class="text-center py-12 bg-white rounded-2xl border border-dashed border-slate-300 text-slate-400">
                            <i data-feather="list" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                            <p>Belum ada kandidat/opsi yang ditambahkan.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php while($opt = mysqli_fetch_assoc($options)): 
                                $imgSrc = !empty($opt['image']) ? "uploads/candidates/".$opt['image'] : "https://via.placeholder.com/150?text=No+Img";
                            ?>
                            <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-start gap-4 hover:shadow-md transition group">
                                <?php if($voting['type'] == 'member'): ?>
                                    <img src="<?php echo $imgSrc; ?>" class="w-16 h-16 rounded-full object-cover border border-slate-100 shadow-sm flex-shrink-0">
                                <?php else: ?>
                                    <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 font-bold text-lg">
                                        <?php echo substr($opt['option_name'], 0, 1); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="flex-1">
                                    <h4 class="font-bold text-slate-800"><?php echo htmlspecialchars($opt['option_name']); ?></h4>
                                    <p class="text-xs text-slate-500 mt-1 line-clamp-2"><?php echo htmlspecialchars($opt['description']); ?></p>
                                    <p class="text-xs font-bold text-primary mt-2"><?php echo $opt['vote_count']; ?> Suara</p>
                                </div>

                                <a href="?id=<?php echo $id; ?>&del_opt=<?php echo $opt['id']; ?>" onclick="return confirm('Hapus opsi ini?')" class="text-slate-300 hover:text-red-500 p-1 opacity-0 group-hover:opacity-100 transition">
                                    <i data-feather="trash-2" class="w-4 h-4"></i>
                                </a>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

    <script>feather.replace();</script>
</body>
</html>