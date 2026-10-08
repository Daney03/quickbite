<?php

session_start();

require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {

        $error = 'Vul dit veld in.';

    } else {

        $stmt = $pdo->prepare(
            'SELECT id, name, email, password, role
             FROM users
             WHERE email = ?'
        );

        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'employee') {

                header('Location: ../employee/dashboard.php');
                exit;

            } else {

                header('Location: ../pages/menu.php');
                exit;
            }

        } else {

            $error = 'E-mailadres of wachtwoord is niet juist.';
        }
    }
}

require_once '../includes/header.php';

?>

<h1>Inloggen</h1>

<?php if ($error !== ''): ?>

    <div class="alert error">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>

<?php endif; ?>

<form method="POST">

    <div class="form-group">

        <label for="email">
            E-mailadres
        </label>

        <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            required
        >

    </div>

    <div class="form-group">

        <label for="password">
            Wachtwoord
        </label>

        <input
            type="password"
            id="password"
            name="password"
            required
        >

    </div>

    <button type="submit">
        Inloggen
    </button>

</form>

<p>
    Nog geen account?
    <a href="register.php">
        Account aanmaken
    </a>
</p>

<?php require_once '../includes/footer.php'; ?>