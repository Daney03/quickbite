<?php

require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();

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
     WHERE o.user_id = ?
     ORDER BY o.created_at DESC'
);

$stmt->execute([
    $_SESSION['user_id']
]);

$orders = $stmt->fetchAll();

$statusLabels = [
    'received' => 'Ontvangen',
    'preparing' => 'In bereiding',
    'ready' => 'Klaar',
    'picked_up' => 'Afgehaald'
];

include '../includes/header.php';
?>

<h1>Mijn bestellingen</h1>

<?php if (empty($orders)): ?>

    <p>
        Je hebt nog geen bestellingen geplaatst.
    </p>

    <p>
        <a href="menu.php">
            Bekijk het menu
        </a>
    </p>

<?php else: ?>

    <?php foreach ($orders as $order): ?>

        <article>

            <h2>
                Bestelling #<?= (int) $order['id'] ?>
            </h2>

            <p>
                Datum:
                <?= htmlspecialchars($order['date']) ?>
            </p>

            <p>
                Afhaalmoment:
                <?= htmlspecialchars(substr($order['start_time'], 0, 5)) ?>
                -
                <?= htmlspecialchars(substr($order['end_time'], 0, 5)) ?>
            </p>

            <p>
                Totaal:
                € <?= number_format($order['total_price'], 2, ',', '.') ?>
            </p>

            <p>
                Status:
                <strong>
                    <?= htmlspecialchars(
                        $statusLabels[$order['status']] ?? 'Onbekend'
                    ) ?>
                </strong>
            </p>

            <p>
                Afhaalcode:
                <strong>
                    <?= htmlspecialchars($order['pickup_code']) ?>
                </strong>
            </p>

            <p>
                <a href="order.php?id=<?= (int) $order['id'] ?>">
                    Bestelling bekijken
                </a>
            </p>

            <hr>

        </article>

    <?php endforeach; ?>

<?php endif; ?>

<p>
    <a href="menu.php">
        Terug naar menu
    </a>
</p>

<?php include '../includes/footer.php'; ?>