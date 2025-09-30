<?php
session_start();

if (! isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$firstName = $_SESSION['firstname'] ?? '';
$lastName = $_SESSION['lastname'] ?? '';
$username = $_SESSION['username'] ?? '';

$pageTitle = 'Kontrol Paneli';
require_once __DIR__ . '/header.php';
?>
        <div class="card">
            <h2>Hoş Geldin!</h2>
            <p>
                <?php if ($firstName !== '' || $lastName !== '') : ?>
                    <?php echo htmlspecialchars(trim($firstName . ' ' . $lastName), ENT_QUOTES, 'UTF-8'); ?>
                <?php else : ?>
                    <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>
                <?php endif; ?>
                olarak giriş yaptınız.
            </p>
            <p>Hesap yönetimi ve içerik güncellemeleri yakında burada olacak.</p>
            <a class="button" href="index.php">Ana Sayfaya Dön</a>
        </div>
    </main>
</body>
</html>
