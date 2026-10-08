<?php

require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();

$orderId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT
        o.id,
        o.total_price,
        o.status,
        o.pickup_code,
        o.created_at,
        ts.date,
        ts.start_time,
        ts.end_time
     FROM orders o
     INNER JOIN time_slots ts
        ON o.time_slot_id = ts.id
     WHERE o.id = ?
     AND o.user_id = ?'
);

$stmt->execute([
    $orderId,
    $_SESSION['user_id']
]);

$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    die('Bestelling niet gevonden.');
}

include '../includes/header.php';

?>

<h1>Bestelling</h1>

<p>
    Bestelnummer: <?= (int) $order['id'] ?>
</p>

<p>
<?php

$statusLabels = [
    'received' => 'Ontvangen',
    'preparing' => 'In bereiding',
    'ready' => 'Klaar',
    'picked_up' => 'Afgehaald'
];

?>

<p>
    Status:
    <strong>
        <?= htmlspecialchars(
            $statusLabels[$order['status']] ?? 'Onbekend'
        ) ?>
    </strong>
</p>
</p>

<p>
    Totaal:
    € <?= number_format($order['total_price'], 2, ',', '.') ?>
</p>

<p>
    Afhalen op:
    <?= htmlspecialchars($order['date']) ?>
    van
    <?= htmlspecialchars(substr($order['start_time'], 0, 5)) ?>
    tot
    <?= htmlspecialchars(substr($order['end_time'], 0, 5)) ?>
</p>

<p>
    Je afhaalcode:
    <strong><?= htmlspecialchars($order['pickup_code']) ?></strong>
</p>

<p>
    <a href="menu.php">Terug naar menu</a>
</p>

<?php

include '../includes/footer.php';

?>