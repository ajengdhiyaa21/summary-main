<?php
$pageTitle = "Ganti Password";
include 'header.php';

$conn = new mysqli("localhost", "root", "", "kai");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$errors = [];
$success = '';
$step = 1; // 1 = login, 2 = change password
$user_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Step 1: Login form submitted
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $errors[] = "Username dan password wajib diisi.";
        } else {
            $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!$user || !password_verify($password, $user['password'])) {
                $errors[] = "Username atau password salah.";
            } else {
                $user_id = $user['id'];
                $step = 2;
            }
        }
    } elseif (isset($_POST['change_password'])) {
        // Step 2: Change password form submitted
        $user_id = $_POST['user_id'] ?? null;
        $password_lama = $_POST['password_lama'] ?? '';
        $password_baru = $_POST['password_baru'] ?? '';
        $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

        if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
            $errors[] = "Semua field wajib diisi.";
            $step = 2;
        } elseif ($password_baru !== $konfirmasi_password) {
            $errors[] = "Password baru dan konfirmasi password tidak sama.";
            $step = 2;
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!$user) {
                $errors[] = "User tidak ditemukan.";
                $step = 1;
            } elseif (!password_verify($password_lama, $user['password'])) {
                $errors[] = "Password lama salah.";
                $step = 2;
            } else {
                $password_baru_hash = password_hash($password_baru, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $password_baru_hash, $user_id);
                if ($stmt->execute()) {
                    $success = "Password berhasil diubah.";
                    $step = 1;
                } else {
                    $errors[] = "Gagal mengubah password.";
                    $step = 2;
                }
                $stmt->close();
            }
        }
    }
}
?>

<main id="main" class="main" style="margin-left: 300px; padding: 20px;">
    <div class="pagetitle">
        <h1>Ganti Password</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Ganti Password</li>
            </ol>
        </nav>
    </div>

    <div class="card p-3" style="padding: 30px; margin-bottom: 20px;">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <form method="POST" action="" novalidate>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary">Selanjutnya</button>
                <a href="dashboard.php" class="btn btn-secondary">Batal</a>
            </form>
        <?php elseif ($step === 2): ?>
            <form method="POST" action="" novalidate>
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
                <div class="mb-3">
                    <label for="password_lama" class="form-label">Password Lama</label>
                    <input type="password" class="form-control" id="password_lama" name="password_lama" required>
                </div>
                <div class="mb-3">
                    <label for="password_baru" class="form-label">Password Baru</label>
                    <input type="password" class="form-control" id="password_baru" name="password_baru" required>
                </div>
                <div class="mb-3">
                    <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-primary">Ganti Password</button>
                <a href="dashboard.php" class="btn btn-secondary">Batal</a>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>
