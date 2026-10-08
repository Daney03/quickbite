<?php

require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();
requireEmployee();

/*
 * Status van een bestelling aanpassen
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $orderId = (int) ($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    $allowedStatuses = [
        'received',
        'preparing',
        'ready',
        'picked_up'
    ];

    if (
        $orderId > 0 &&
        in_array($status, $allowedStatuses, true)
    ) {

        $stmt = $pdo->prepare(
            'UPDATE orders
             SET status = ?
             WHERE id = ?'
        );

        $stmt->execute([
            $status,
            $orderId
        ]);
    }

    header('Location: orders.php');
    exit;
}

/*
 * Alle bestellingen ophalen
 */
$stmt = $pdo->query(
    'SELECT
        o.id,
        o.total_price,
        o.status,
        o.pickup_code,
        o.created_at,
        u.name,
        u.email,
        ts.date,
        ts.start_time,
        ts.end_time
     FROM orders o
     INNER JOIN users u
        ON o.user_id = u.id
     INNER JOIN time_slots ts
        ON o.time_slot_id = ts.id
     ORDER BY o.created_at DESC'
);

$orders = $stmt->fetchAll();

include '../includes/header.php';
?>

<h1>Bestellingen</h1>

<?php if (empty($orders)): ?>

    <p>Er zijn momenteel geen bestellingen.</p>

<?php else: ?>

    <?php foreach ($orders as $order): ?>

        <article>

            <h2>
                Bestelling #<?= (int) $order['id'] ?>
            </h2>

            <p>
                Klant:
                <?= htmlspecialchars($order['name']) ?>
            </p>

            <p>
                E-mail:
                <?= htmlspecialchars($order['email']) ?>
            </p>

            <p>
                Afhaalmoment:
                <?= htmlspecialchars($order['date']) ?>
                van
                <?= htmlspecialchars(substr($order['start_time'], 0, 5)) ?>
                tot
                <?= htmlspecialchars(substr($order['end_time'], 0, 5)) ?>
            </p>

            <p>
                Totaal:
                € <?= number_format($order['total_price'], 2, ',', '.') ?>
            </p>

            <p>
                Afhaalcode:
                <strong>
                    <?= htmlspecialchars($order['pickup_code']) ?>
                </strong>
            </p>

            <p>
                Huidige status:
                <?= htmlspecialchars($order['status']) ?>
            </p>

            <form method="POST">

                <input
                    type="hidden"
                    name="order_id"
                    value="<?= (int) $order['id'] ?>"
                >

                <label for="status-<?= (int) $order['id'] ?>">
                    Nieuwe status
                </label>

                <select
                    id="status-<?= (int) $order['id'] ?>"
                    name="status"
                >
                    <option
                        value="received"
                        <?= $order['status'] === 'received' ? 'selected' : '' ?>
                    >
                        Ontvangen
                    </option>

                    <option
                        value="preparing"
                        <?= $order['status'] === 'preparing' ? 'selected' : '' ?>
                    >
                        In bereiding
                    </option>

                    <option
                        value="ready"
                        <?= $order['status'] === 'ready' ? 'selected' : '' ?>
                    >
                        Klaar
                    </option>

                    <option
                        value="picked_up"
                        <?= $order['status'] === 'picked_up' ? 'selected' : '' ?>
                    >
                        Afgehaald
                    </option>
                </select>

                <button type="submit">
                    Status aanpassen
                </button>

            </form>

            <hr>

        </article>

    <?php endforeach; ?>

<?php endif; ?>

<p>
    <a href="dashboard.php">Terug naar dashboard</a>
</p>

<?php include '../includes/footer.php'; ?>