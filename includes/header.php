<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPath = $_SERVER['PHP_SELF'] ?? '';

$isPublic = str_contains($currentPath, '/public/');
$isPages = str_contains($currentPath, '/pages/');
$isEmployee = str_contains($currentPath, '/employee/');


if ($isPublic) {

    $home = '../pages/menu.php';
    $login = 'login.php';
    $register = 'register.php';
    $logout = 'logout.php';

    $cartLink = '../pages/cart.php';
    $ordersLink = '../pages/orders.php';
    $employeeLink = '../employee/dashboard.php';

    $css = 'css/style.css';

} elseif ($isPages) {

    $home = 'menu.php';
    $login = '../public/login.php';
    $register = '../public/register.php';
    $logout = '../public/logout.php';

    $cartLink = 'cart.php';
    $ordersLink = 'orders.php';
    $employeeLink = '../employee/dashboard.php';

    $css = '../public/css/style.css';

} elseif ($isEmployee) {

    $home = '../pages/menu.php';
    $login = '../public/login.php';
    $register = '../public/register.php';
    $logout = '../public/logout.php';

    $cartLink = '../pages/cart.php';
    $ordersLink = '../pages/orders.php';
    $employeeLink = 'dashboard.php';

    $css = '../public/css/style.css';

} else {

    $home = '../pages/menu.php';
    $login = '../public/login.php';
    $register = '../public/register.php';
    $logout = '../public/logout.php';

    $cartLink = '../pages/cart.php';
    $ordersLink = '../pages/orders.php';
    $employeeLink = '../employee/dashboard.php';

    $css = '../public/css/style.css';
}

?>

<!DOCTYPE html>
<html lang="nl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>QuickBite</title>

    <link
        rel="stylesheet"
        href="<?= htmlspecialchars($css, ENT_QUOTES, 'UTF-8') ?>"
    >

</head>

<body>

<header class="site-header">

    <div class="container header-inner">

        <a
            href="<?= htmlspecialchars($home, ENT_QUOTES, 'UTF-8') ?>"
            class="logo"
        >
            QuickBite
        </a>

        <nav>

            <a href="<?= htmlspecialchars($home, ENT_QUOTES, 'UTF-8') ?>">
                Menu
            </a>

            <?php if (isset($_SESSION['user_id'])): ?>

                <?php if ($_SESSION['role'] === 'customer'): ?>

                    <a href="<?= htmlspecialchars($cartLink, ENT_QUOTES, 'UTF-8') ?>">
                        Mandje
                    </a>

                    <a href="<?= htmlspecialchars($ordersLink, ENT_QUOTES, 'UTF-8') ?>">
                        Mijn bestellingen
                    </a>

                <?php endif; ?>

                <?php if ($_SESSION['role'] === 'employee'): ?>

                    <a href="<?= htmlspecialchars($employeeLink, ENT_QUOTES, 'UTF-8') ?>">
                        Medewerker
                    </a>

                <?php endif; ?>

                <a href="<?= htmlspecialchars($logout, ENT_QUOTES, 'UTF-8') ?>">
                    Uitloggen
                </a>

            <?php else: ?>

                <a href="<?= htmlspecialchars($login, ENT_QUOTES, 'UTF-8') ?>">
                    Inloggen
                </a>

                <a href="<?= htmlspecialchars($register, ENT_QUOTES, 'UTF-8') ?>">
                    Account aanmaken
                </a>

            <?php endif; ?>

        </nav>

    </div>

</header>

<main class="container">