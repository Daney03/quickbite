<?php

require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();
requireEmployee();

/*
 * Voorraad aanpassen
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $productId = (int) ($_POST['product_id'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);

    if ($productId > 0 && $stock >= 0) {

        $stmt = $pdo->prepare(
            'UPDATE products
             SET stock = ?
             WHERE id = ?'
        );

        $stmt->execute([
            $stock,
            $productId
        ]);
    }

    header('Location: stock.php');
    exit;
}

/*
 * Producten ophalen
 */
$stmt = $pdo->query(
    'SELECT id, name, category, stock
     FROM products
     ORDER BY name'
);

$products = $stmt->fetchAll();

include '../includes/header.php';
?>

<h1>Voorraadbeheer</h1>

<?php if (empty($products)): ?>

    <p>Er zijn momenteel geen producten.</p>

<?php else: ?>

    <?php foreach ($products as $product): ?>

        <article>

            <h2>
                <?= htmlspecialchars($product['name']) ?>
            </h2>

            <p>
                Categorie:
                <?= htmlspecialchars($product['category']) ?>
            </p>

            <p>
                Huidige voorraad:
                <strong>
                    <?= (int) $product['stock'] ?>
                </strong>
            </p>

            <form method="POST">

                <input
                    type="hidden"
                    name="product_id"
                    value="<?= (int) $product['id'] ?>"
                >

                <label for="stock-<?= (int) $product['id'] ?>">
                    Nieuwe voorraad
                </label>

                <input
                    type="number"
                    id="stock-<?= (int) $product['id'] ?>"
                    name="stock"
                    value="<?= (int) $product['stock'] ?>"
                    min="0"
                    required
                >

                <button type="submit">
                    Voorraad aanpassen
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