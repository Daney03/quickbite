<?php

require_once '../includes/auth.php';

requireLogin();
requireEmployee();

include '../includes/header.php';

?>

<h1>Medewerker dashboard</h1>

<p>
    Welkom, <?= htmlspecialchars($_SESSION['name']) ?>!
</p>

<p>
    Je bent ingelogd als medewerker.
</p>

<a href="../public/logout.php">Uitloggen</a>

<?php

include '../includes/footer.php';

?>