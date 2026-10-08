<?php

require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();
requireEmployee();

$errors = [];

/*
 * Product toevoegen, aanpassen of verwijderen
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    /*
     * Product verwijderen
     */
    if ($action === 'delete') {

        $productId = (int) ($_POST['product_id'] ?? 0);

        if ($productId > 0) {

            $stmt = $pdo->prepare(
                'DELETE FROM products
                 WHERE id = ?'
            );

            $stmt->execute([$productId]);
        }

        header('Location: products.php');
        exit;
    }

    /*
     * Product toevoegen
     */
    if ($action === 'add') {

        $name = trim($_POST['name'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $category = trim($_POST['category'] ?? '');
        $allergens = trim($_POST['allergens'] ?? '');
        $diet = trim($_POST['diet'] ?? '');
        $stock = (int) ($_POST['stock'] ?? 0);

        if ($name === '') {
            $errors[] = 'Vul de productnaam in.';
        }

        if ($price <= 0) {
            $errors[] = 'Vul een geldige prijs in.';
        }

        if ($category === '') {
            $errors[] = 'Vul de categorie in.';
        }

        if ($stock < 0) {
            $errors[] = 'Voorraad mag niet negatief zijn.';
        }

        if (empty($errors)) {

            $stmt = $pdo->prepare(
                'INSERT INTO products
                (name, price, category, allergens, diet, stock)
                VALUES (?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $name,
                $price,
                $category,
                $allergens,
                $diet,
                $stock
            ]);

            header('Location: products.php');
            exit;
        }
    }

    /*
     * Product aanpassen
     */
    if ($action === 'update') {

        $productId = (int) ($_POST['product_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $category = trim($_POST['category'] ?? '');
        $allergens = trim($_POST['allergens'] ?? '');
        $diet = trim($_POST['diet'] ?? '');
        $stock = (int) ($_POST['stock'] ?? 0);

        if ($productId <= 0) {
            $errors[] = 'Ongeldig product.';
        }

        if ($name === '') {
            $errors[] = 'Vul de productnaam in.';
        }

        if ($price <= 0) {
            $errors[] = 'Vul een geldige prijs in.';
        }

        if ($category === '') {
            $errors[] = 'Vul de categorie in.';
        }

        if ($stock < 0) {
            $errors[] = 'Voorraad mag niet negatief zijn.';
        }

        if (empty($errors)) {

            $stmt = $pdo->prepare(
                'UPDATE products
                 SET name = ?,
                     price = ?,
                     category = ?,
                     allergens = ?,
                     diet = ?,
                     stock = ?
                 WHERE id = ?'
            );

            $stmt->execute([
                $name,
                $price,
                $category,
                $allergens,
                $diet,
                $stock,
                $productId
            ]);

            header('Location: products.php');
            exit;
        }
    }
}

/*
 * Product om te bewerken ophalen
 */
$editProduct = null;

if (isset($_GET['edit'])) {

    $editId = (int) $_GET['edit'];

    $stmt = $pdo->prepare(
        'SELECT id, name, price, category, allergens, diet, stock
         FROM products
         WHERE id = ?'
    );

    $stmt->execute([$editId]);

    $editProduct = $stmt->fetch();
}

/*
 * Alle producten ophalen
 */
$stmt = $pdo->query(
    'SELECT id, name, price, category, allergens, diet, stock
     FROM products
     ORDER BY name'
);

$products = $stmt->fetchAll();

include '../includes/header.php';
?>

<h1>Productbeheer</h1>

<?php if (!empty($errors)): ?>

    <?php foreach ($errors as $error): ?>

        <p>
            <?= htmlspecialchars($error) ?>
        </p>

    <?php endforeach; ?>

<?php endif; ?>


<?php if ($editProduct): ?>

    <h2>Product aanpassen</h2>

    <form method="POST">

        <input
            type="hidden"
            name="action"
            value="update"
        >

        <input
            type="hidden"
            name="product_id"
            value="<?= (int) $editProduct['id'] ?>"
        >

        <div>
            <label for="edit-name">
                Productnaam
            </label>

            <input
                type="text"
                id="edit-name"
                name="name"
                value="<?= htmlspecialchars($editProduct['name']) ?>"
                required
            >
        </div>

        <div>
            <label for="edit-price">
                Prijs
            </label>

            <input
                type="number"
                id="edit-price"
                name="price"
                value="<?= htmlspecialchars($editProduct['price']) ?>"
                step="0.01"
                min="0.01"
                required
            >
        </div>

        <div>
            <label for="edit-category">
                Categorie
            </label>

            <input
                type="text"
                id="edit-category"
                name="category"
                value="<?= htmlspecialchars($editProduct['category']) ?>"
                required
            >
        </div>

        <div>
            <label for="edit-allergens">
                Allergenen
            </label>

            <input
                type="text"
                id="edit-allergens"
                name="allergens"
                value="<?= htmlspecialchars($editProduct['allergens'] ?? '') ?>"
            >
        </div>

        <div>
            <label for="edit-diet">
                Dieet
            </label>

            <input
                type="text"
                id="edit-diet"
                name="diet"
                value="<?= htmlspecialchars($editProduct['diet'] ?? '') ?>"
            >
        </div>

        <div>
            <label for="edit-stock">
                Voorraad
            </label>

            <input
                type="number"
                id="edit-stock"
                name="stock"
                value="<?= (int) $editProduct['stock'] ?>"
                min="0"
                required
            >
        </div>

        <button type="submit">
            Product opslaan
        </button>

    </form>

    <p>
        <a href="products.php">
            Annuleren
        </a>
    </p>

<?php else: ?>

    <h2>Nieuw product toevoegen</h2>

    <form method="POST">

        <input
            type="hidden"
            name="action"
            value="add"
        >

        <div>
            <label for="name">
                Productnaam
            </label>

            <input
                type="text"
                id="name"
                name="name"
                required
            >
        </div>

        <div>
            <label for="price">
                Prijs
            </label>

            <input
                type="number"
                id="price"
                name="price"
                step="0.01"
                min="0.01"
                required
            >
        </div>

        <div>
            <label for="category">
                Categorie
            </label>

            <input
                type="text"
                id="category"
                name="category"
                required
            >
        </div>

        <div>
            <label for="allergens">
                Allergenen
            </label>

            <input
                type="text"
                id="allergens"
                name="allergens"
            >
        </div>

        <div>
            <label for="diet">
                Dieet
            </label>

            <input
                type="text"
                id="diet"
                name="diet"
            >
        </div>

        <div>
            <label for="stock">
                Voorraad
            </label>

            <input
                type="number"
                id="stock"
                name="stock"
                min="0"
                value="0"
                required
            >
        </div>

        <button type="submit">
            Product toevoegen
        </button>

    </form>

<?php endif; ?>


<hr>

<h2>Bestaande producten</h2>

<?php if (empty($products)): ?>

    <p>Er zijn nog geen producten.</p>

<?php else: ?>

    <?php foreach ($products as $product): ?>

        <article>

            <h3>
                <?= htmlspecialchars($product['name']) ?>
            </h3>

            <p>
                Prijs:
                € <?= number_format($product['price'], 2, ',', '.') ?>
            </p>

            <p>
                Categorie:
                <?= htmlspecialchars($product['category']) ?>
            </p>

            <p>
                Allergenen:
                <?= htmlspecialchars($product['allergens'] ?? '') ?>
            </p>

            <p>
                Dieet:
                <?= htmlspecialchars($product['diet'] ?? '') ?>
            </p>

            <p>
                Voorraad:
                <?= (int) $product['stock'] ?>
            </p>

            <p>
                <a href="products.php?edit=<?= (int) $product['id'] ?>">
                    Bewerken
                </a>
            </p>

            <form method="POST">

                <input
                    type="hidden"
                    name="action"
                    value="delete"
                >

                <input
                    type="hidden"
                    name="product_id"
                    value="<?= (int) $product['id'] ?>"
                >

                <button type="submit">
                    Product verwijderen
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