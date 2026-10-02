<?php

require_once '../config/database.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '') {
        $errors[] = 'Vul je naam in.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Vul een geldig e-mailadres in.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Je wachtwoord moet minimaal 8 tekens bevatten.';
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare(
            'SELECT id FROM users WHERE email = ?'
        );

        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = 'Dit e-mailadres is al in gebruik.';
        }
    }

    if (empty($errors)) {

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $stmt = $pdo->prepare(
            'INSERT INTO users (name, email, password, role)
             VALUES (?, ?, ?, ?)'
        );

        $stmt->execute([
            $name,
            $email,
            $hashedPassword,
            'customer'
        ]);

        $success = 'Je account is succesvol aangemaakt.';
    }
}

include '../includes/header.php';

?>

<h1>Account aanmaken</h1>

<?php if (!empty($errors)): ?>

    <div>
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>

<?php endif; ?>

<?php if ($success): ?>

    <p><?= htmlspecialchars($success) ?></p>

<?php endif; ?>

<form method="POST">

    <div>
        <label for="name">Naam</label>
        <input
            type="text"
            id="name"
            name="name"
            value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
            required
        >
    </div>

    <div>
        <label for="email">E-mailadres</label>
        <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
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

    <button type="submit">Account aanmaken</button>

</form>

<?php

include '../includes/footer.php';

?>