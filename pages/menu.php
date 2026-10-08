<?php

require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();

include '../includes/header.php';

?>

<h1>Menu</h1>

<p>
    Welkom, <?= htmlspecialchars($_SESSION['name']) ?>!
</p>

<p>
    Je bent ingelogd als klant.
</p>

<a href="../public/logout.php">Uitloggen</a>

<?php

include '../includes/footer.php';

?>