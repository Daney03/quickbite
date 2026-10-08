<?php

require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/*
 * Aantal aanpassen
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $productId = (int) ($_POST['product_id'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 1);

    /*
     * Product toevoegen
     */
    if ($action === 'add') {

        $stmt = $pdo->prepare(
            'SELECT id, stock
             FROM products
             WHERE id = ?'
        );

        $stmt->execute([$productId]);

        $product = $stmt->fetch();

        if ($product && $quantity > 0 && $quantity <= $product['stock']) {
            $_SESSION['cart'][$productId] = $quantity;
        }

    /*
     * Aantal aanpassen
     */
    } elseif ($action === 'update') {

        $stmt = $pdo->prepare(
            'SELECT id, stock
             FROM products
             WHERE id = ?'
        );

        $stmt->execute([$productId]);

        $product = $stmt->fetch();

        if ($product && $quantity > 0 && $quantity <= $product['stock']) {
            $_SESSION['cart'][$productId] = $quantity;
        }

    /*
     * Product verwijderen
     */
    } elseif ($action === 'remove') {

        unset($_SESSION['cart'][$productId]);
    }

    header('Location: cart.php');
    exit;
}

/*
 * Producten uit het mandje ophalen
 */
$cartItems = [];
$total = 0;

if (!empty($_SESSION['cart'])) {

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

    foreach ($products as $product) {

        $quantity = $_SESSION['cart'][$product['id']];

        $subtotal = $product['price'] * $quantity;

        $cartItems[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];

        $total += $subtotal;
    }
}

include '../includes/header.php';

?>

<h1>Mijn mandje</h1>

<?php if (empty($cartItems)): ?>

    <p>Je mandje is momenteel leeg.</p>

    <a href="menu.php">Bekijk het menu</a>

<?php else: ?>

    <?php foreach ($cartItems as $item): ?>

        <article>

            <h2>
                <?= htmlspecialchars($item['name']) ?>
            </h2>

            <p>
                Prijs:
                € <?= number_format($item['price'], 2, ',', '.') ?>
            </p>

            <form method="POST">

                <input
                    type="hidden"
                    name="product_id"
                    value="<?= (int) $item['id'] ?>"
                >

                <label for="quantity-<?= (int) $item['id'] ?>">
                    Aantal
                </label>

                <input
                    type="number"
                    id="quantity-<?= (int) $item['id'] ?>"
                    name="quantity"
                    value="<?= (int) $item['quantity'] ?>"
                    min="1"
                >

                <button type="submit" name="action" value="update">
                    Aantal aanpassen
                </button>

                <button type="submit" name="action" value="remove">
                    Verwijderen
                </button>

            </form>

            <p>
                Subtotaal:
                € <?= number_format($item['subtotal'], 2, ',', '.') ?>
            </p>

        </article>

        <hr>

    <?php endforeach; ?>

    <h2>
        Totaal:
        € <?= number_format($total, 2, ',', '.') ?>
    </h2>

<?php endif; ?>

<p>
    <a href="menu.php">Terug naar menu</a>
    <?php if (!empty($cartItems)): ?>

    <p>
        <a href="pickup.php">
            Kies een afhaalmoment
        </a>
    </p>

<?php endif; ?>
</p>

<p>
    <a href="../public/logout.php">Uitloggen</a>
</p>

<?php

include '../includes/footer.php';

?>