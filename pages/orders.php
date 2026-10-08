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

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!is_array($orders)) {
    $orders = [];
}

include '../includes/header.php';
?>

<h1>Bestellingen beheren</h1>

<?php if (empty($orders)): ?>

    <p>Er zijn momenteel geen bestellingen.</p>

<?php else: ?>

    <?php foreach ($orders as $order): ?>

        <article class="order-card">

            <h2>
                Bestelling #<?= (int) $order['id'] ?>
            </h2>

            <p>
                <strong>Klant:</strong>
                <?= htmlspecialchars(
                    $order['name'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

            <p>
                <strong>E-mail:</strong>
                <?= htmlspecialchars(
                    $order['email'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

            <p>
                <strong>Afhaalmoment:</strong>
                <?= htmlspecialchars(
                    $order['date'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

                van

                <?= htmlspecialchars(
                    substr($order['start_time'], 0, 5),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

                tot

                <?= htmlspecialchars(
                    substr($order['end_time'], 0, 5),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

            <p>
                <strong>Totaal:</strong>
                € <?= number_format(
                    (float) $order['total_price'],
                    2,
                    ',',
                    '.'
                ) ?>
            </p>

            <p>
                <strong>Afhaalcode:</strong>
                <?= htmlspecialchars(
                    $order['pickup_code'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

            <p>
                <strong>Huidige status:</strong>

                <?php

                $statusLabels = [
                    'received' => 'Ontvangen',
                    'preparing' => 'In bereiding',
                    'ready' => 'Klaar',
                    'picked_up' => 'Afgehaald'
                ];

                $currentStatus = $statusLabels[$order['status']]
                    ?? $order['status'];

                echo htmlspecialchars(
                    $currentStatus,
                    ENT_QUOTES,
                    'UTF-8'
                );

                ?>

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
                        <?= $order['status'] === 'received'
                            ? 'selected'
                            : '' ?>
                    >
                        Ontvangen
                    </option>

                    <option
                        value="preparing"
                        <?= $order['status'] === 'preparing'
                            ? 'selected'
                            : '' ?>
                    >
                        In bereiding
                    </option>

                    <option
                        value="ready"
                        <?= $order['status'] === 'ready'
                            ? 'selected'
                            : '' ?>
                    >
                        Klaar
                    </option>

                    <option
                        value="picked_up"
                        <?= $order['status'] === 'picked_up'
                            ? 'selected'
                            : '' ?>
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
    <a href="dashboard.php">
        Terug naar dashboard
    </a>
</p>

<?php include '../includes/footer.php'; ?>