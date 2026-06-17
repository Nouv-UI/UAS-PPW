<?php
// pages/login.php
require_once __DIR__ . '/../includes/config.php';

// If already logged in, redirect to home page
if (isset($_SESSION['user_id'])) {
    header("Location: /jp-annahls/index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user) {
                $login_success = false;

                // Check hashed password
                if (password_verify($password, $user['password'])) {
                    $login_success = true;
                } 
                // Fallback: check if password matches plain text (for initial database dump users)
                else if ($password === $user['password']) {
                    // Migrasikan password ke versi hash aman
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    $update_stmt->execute([$hashed_password, $user['user_id']]);
                    
                    $login_success = true;
                }

                if ($login_success) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['nama_pemesan'] = $user['nama_pemesan'];
                    $_SESSION['role'] = $user['role'];

                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'text' => 'Selamat datang kembali, ' . esc($user['nama_pemesan']) . '!'
                    ];

                    if ($user['role'] === 'admin') {
                        header("Location: /jp-annahls/pages/admin_dashboard.php");
                    } else {
                        header("Location: /jp-annahls/index.php");
                    }
                    exit();
                } else {
                    $error = 'Password salah.';
                }
            } else {
                $error = 'Username tidak terdaftar.';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center py-5">
    <div class="col-md-5">
        <div class="card custom-card p-4 border border-1">
            <div class="text-center mb-4">
                <h3 class="fw-bold" style="color: var(--primary-color);">Masuk ke Akun</h3>
                <p class="text-muted small">Silakan masuk untuk berbelanja jajanan pasar favorit Anda</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="border-radius: 10px;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= esc($error) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="username" class="form-label fw-bold">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="border-right: none; border-radius: 10px 0 0 10px;"><i class="bi bi-person text-muted"></i></span>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" style="border-left: none; border-radius: 0 10px 10px 0;" required>
                        <div class="invalid-feedback">Username wajib diisi.</div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-bold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="border-right: none; border-radius: 10px 0 0 10px;"><i class="bi bi-lock text-muted"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" style="border-left: none; border-radius: 0 10px 10px 0;" required>
                        <div class="invalid-feedback">Password wajib diisi.</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-pill w-100 py-3 mb-3"><i class="bi bi-box-arrow-in-right me-2"></i> Masuk Sekarang</button>
            </form>

            <div class="text-center mt-3">
                <p class="small text-muted mb-0">Belum punya akun? <a href="/jp-annahls/pages/register.php" style="color: var(--primary-color); font-weight: 600;">Daftar Sekarang</a></p>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
