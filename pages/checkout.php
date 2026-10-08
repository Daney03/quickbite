<?php

require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();

if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$timeSlotId = (int) ($_POST['time_slot_id'] ?? 0);

if (isset($_POST['place_order'])) {

    // De rest van de bestelling wordt hieronder verwerkt.
}

if ($timeSlotId <= 0) {
    header('Location: pickup.php');
    exit;
}

/*
 * Tijdslot controleren
 */
$stmt = $pdo->prepare(
    'SELECT id, date, start_time, end_time, max_orders
     FROM time_slots
     WHERE id = ?'
);

$stmt->execute([$timeSlotId]);

$timeSlot = $stmt->fetch();

if (!$timeSlot) {
    die('Dit tijdslot bestaat niet.');
}

/*
 * Controleren hoeveel bestellingen al bestaan
 */
$stmt = $pdo->prepare(
    'SELECT COUNT(*)
     FROM orders
     WHERE time_slot_id = ?'
);

$stmt->execute([$timeSlotId]);

$currentOrders = (int) $stmt->fetchColumn();

if ($currentOrders >= $timeSlot['max_orders']) {
    die('Dit tijdslot is vol. Kies een ander tijdstip.');
}

/*
 * Producten uit winkelmandje ophalen
 */
$productIds = array_keys($_SESSION['cart']);

$placeholders = implode(
    ',',
    array_fill(0, count($productIds), '?')
);

$stmt = $pdo->prepare(
    "SELECT id, name, price, stock
     FROM products
     WHERE id IN ($placeholders)"
);

$stmt->execute($productIds);

$products = $stmt->fetchAll();

$total = 0;
$orderItems = [];

foreach ($products as $product) {

    $quantity = (int) $_SESSION['cart'][$product['id']];

    /*
     * Voorraad controleren
     */
    if ($quantity > $product['stock']) {
        die(
            'Het product "' .
            htmlspecialchars($product['name']) .
            '" is helaas niet voldoende op voorraad.'
        );
    }

    $subtotal = $product['price'] * $quantity;

    $total += $subtotal;

    $orderItems[] = [
        'product_id' => $product['id'],
        'quantity' => $quantity,
        'price' => $product['price']
    ];
}

include '../includes/header.php';

?>

<?php

if (isset($_POST['place_order'])) {

    try {

        $pdo->beginTransaction();

        /*
         * Tijdslot opnieuw controleren
         */
        $stmt = $pdo->prepare(
            'SELECT id, max_orders
             FROM time_slots
             WHERE id = ?
             FOR UPDATE'
        );

        $stmt->execute([$timeSlotId]);

        $slot = $stmt->fetch();

        if (!$slot) {
            throw new Exception('Ongeldig tijdslot.');
        }

        $stmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM orders
             WHERE time_slot_id = ?'
        );

        $stmt->execute([$timeSlotId]);

        $currentOrders = (int) $stmt->fetchColumn();

        if ($currentOrders >= $slot['max_orders']) {
            throw new Exception(
                'Dit tijdslot is vol. Kies een ander tijdstip.'
            );
        }

        /*
         * Voorraad opnieuw controleren
         */
        foreach ($orderItems as $item) {

            $stmt = $pdo->prepare(
                'SELECT stock
                 FROM products
                 WHERE id = ?
                 FOR UPDATE'
            );

            $stmt->execute([$item['product_id']]);

            $stock = (int) $stmt->fetchColumn();

            if ($item['quantity'] > $stock) {
                throw new Exception(
                    'Dit product is helaas uitverkocht.'
                );
            }
        }

        /*
         * Unieke pickup-code maken
         */
        $pickupCode = strtoupper(
            substr(bin2hex(random_bytes(4)), 0, 8)
        );

        /*
         * Bestelling opslaan
         */
        $stmt = $pdo->prepare(
            'INSERT INTO orders
                (user_id, time_slot_id, total_price, status, pickup_code)
             VALUES
                (?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $_SESSION['user_id'],
            $timeSlotId,
            $total,
            'received',
            $pickupCode
        ]);

        $orderId = $pdo->lastInsertId();

        /*
         * Bestelregels opslaan
         */
        foreach ($orderItems as $item) {

            $stmt = $pdo->prepare(
                'INSERT INTO order_items
                    (order_id, product_id, quantity, price)
                 VALUES
                    (?, ?, ?, ?)'
            );

            $stmt->execute([
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);

            /*
             * Voorraad verminderen
             */
            $stmt = $pdo->prepare(
                'UPDATE products
                 SET stock = stock - ?
                 WHERE id = ?'
            );

            $stmt->execute([
                $item['quantity'],
                $item['product_id']
            ]);
        }

        $pdo->commit();

        /*
         * Winkelmandje leegmaken
         */
        $_SESSION['cart'] = [];

        header(
            'Location: order.php?id=' . $orderId
        );

        exit;

    } catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $errors[] = 'Er is iets misgegaan bij het plaatsen van je bestelling. Probeer het opnieuw.';
}
}

include '../includes/header.php';

?>

<h1>Bestelling bevestigen</h1>

<?php if (!empty($orderError)): ?>

    <p>
        <?= htmlspecialchars($orderError) ?>
    </p>

<?php endif; ?>

<h2>Afhaalmoment</h2>

<p>
    <?= htmlspecialchars($timeSlot['date']) ?>
    van
    <?= htmlspecialchars(substr($timeSlot['start_time'], 0, 5)) ?>
    tot
    <?= htmlspecialchars(substr($timeSlot['end_time'], 0, 5)) ?>
</p>

<h2>Je bestelling</h2>

<?php foreach ($orderItems as $item): ?>

    <?php

    $productName = '';

    foreach ($products as $product) {

        if ($product['id'] == $item['product_id']) {
            $productName = $product['name'];
            break;
        }
    }

    ?>

    <p>
        <?= htmlspecialchars($productName) ?>
        × <?= (int) $item['quantity'] ?>
    </p>

<?php endforeach; ?>

<h2>
    Totaal:
    € <?= number_format($total, 2, ',', '.') ?>
</h2>

<form method="POST">

    <input
        type="hidden"
        name="time_slot_id"
        value="<?= (int) $timeSlotId ?>"
    >

    <button type="submit" name="place_order">
        Bestelling plaatsen
    </button>

</form>

<p>
    <a href="cart.php">Terug naar mandje</a>
</p>

<?php

include '../includes/footer.php';

?>