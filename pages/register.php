<?php
// pages/register.php
require_once __DIR__ . '/../includes/config.php';

// If already logged in, redirect to home page
if (isset($_SESSION['user_id'])) {
    header("Location: /jp-annahls/index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $nama_pemesan = trim($_POST['nama_pemesan'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');

    if (empty($username) || empty($password) || empty($nama_pemesan) || empty($phone_number)) {
        $error = 'Semua kolom wajib diisi.';
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Username sudah digunakan oleh pengguna lain.';
            } else {
                // Hash password securely
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert into database
                $insert_stmt = $pdo->prepare("INSERT INTO users (username, password, nama_pemesan, phone_number, role) VALUES (?, ?, ?, ?, 'user')");
                $insert_stmt->execute([$username, $hashed_password, $nama_pemesan, $phone_number]);

                $success = 'Pendaftaran berhasil! Silakan masuk dengan akun baru Anda.';
                
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'text' => $success
                ];
                header("Location: /jp-annahls/pages/login.php");
                exit();
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center py-5">
    <div class="col-md-6">
        <div class="card custom-card p-4 border border-1">
            <div class="text-center mb-4">
                <h3 class="fw-bold" style="color: var(--primary-color);">Daftar Akun Baru</h3>
                <p class="text-muted small">Lengkapi formulir di bawah ini untuk membuat akun baru Anda</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="border-radius: 10px;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= esc($error) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="nama_pemesan" class="form-label fw-bold">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama_pemesan" name="nama_pemesan" value="<?= old('nama_pemesan') ?>" placeholder="Contoh: Budi Santoso" required>
                    <div class="invalid-feedback">Nama lengkap wajib diisi.</div>
                </div>

                <div class="mb-3">
                    <label for="phone_number" class="form-label fw-bold">Nomor Telepon/WA</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?= old('phone_number') ?>" placeholder="Contoh: 081234567890" required>
                    <div class="invalid-feedback">Nomor telepon wajib diisi.</div>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label fw-bold">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?= old('username') ?>" placeholder="Pilih username unik" required>
                    <div class="invalid-feedback">Username wajib diisi.</div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-bold">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Buat password minimal 6 karakter" minlength="6" required>
                    <div class="invalid-feedback">Password wajib diisi (minimal 6 karakter).</div>
                </div>

                <button type="submit" class="btn btn-primary-pill w-100 py-3 mb-3"><i class="bi bi-person-plus me-2"></i> Daftar Sekarang</button>
            </form>

            <div class="text-center mt-3">
                <p class="small text-muted mb-0">Sudah punya akun? <a href="/jp-annahls/pages/login.php" style="color: var(--primary-color); font-weight: 600;">Masuk di sini</a></p>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
