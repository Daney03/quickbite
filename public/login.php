<?php

require_once '../config/database.php';
require_once '../includes/auth.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Vul een geldig e-mailadres in.';
    }

    if ($password === '') {
        $errors[] = 'Vul je wachtwoord in.';
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare(
            'SELECT id, name, email, password, role
             FROM users
             WHERE email = ?'
        );

        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'E-mailadres of wachtwoord is niet juist.';
        } else {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'employee') {
                header('Location: ../employee/dashboard.php');
            } else {
                header('Location: ../pages/menu.php');
            }

            exit;
        }
    }
}

include '../includes/header.php';

?>

<h1>Inloggen</h1>

<?php if (!empty($errors)): ?>

    <div>
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>

<?php endif; ?>

<form method="POST">

    <div>
        <label for="email">E-mailadres</label>
        <input
            type="email"
            id="email"
            name="email"
            required
        >
    </div>

    <div>
        <label for="password">Wachtwoord</label>
        <input
            type="password"
            id="password"
            name="password"
            required
        >
    </div>

    <button type="submit">Inloggen</button>

</form>

<?php

include '../includes/footer.php';

?>