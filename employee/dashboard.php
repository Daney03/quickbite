<?php

require_once '../includes/auth.php';

requireLogin();
requireEmployee();

include '../includes/header.php';
?>

<h1>Medewerker dashboard</h1>

<p>
    Welkom,
    <?= htmlspecialchars($_SESSION['name']) ?>!
</p>

<p>
    Je bent ingelogd als medewerker.
</p>

<h2>Beheer</h2>

<ul>
    <li>
        <a href="orders.php">
            Bestellingen beheren
        </a>
    </li>

    <li>
        <a href="stock.php">
            Voorraad beheren
        </a>
    </li>

    <li>
        <a href="products.php">
            Producten beheren
        </a>
    </li>

    <li>
        <a href="time-slots.php">
            Tijdsloten beheren
        </a>
    </li>
</ul>

<p>
    <a href="../public/logout.php">
        Uitloggen
    </a>
</p>

<?php include '../includes/footer.php'; ?>