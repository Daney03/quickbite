<?php

require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();

$category = trim($_GET['category'] ?? '');
$diet = trim($_GET['diet'] ?? '');

$sql = 'SELECT * FROM products WHERE stock > 0';
$params = [];

if ($category !== '') {
    $sql .= ' AND category = ?';
    $params[] = $category;
}

if ($diet !== '') {
    $sql .= ' AND diet = ?';
    $params[] = $diet;
}

$sql .= ' ORDER BY category, name';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$products = $stmt->fetchAll();

$categories = $pdo->query(
    'SELECT DISTINCT category
     FROM products
     WHERE stock > 0
     ORDER BY category'
)->fetchAll();

$diets = $pdo->query(
    'SELECT DISTINCT diet
     FROM products
     WHERE stock > 0
     AND diet IS NOT NULL
     AND diet != ""
     ORDER BY diet'
)->fetchAll();

include '../includes/header.php';

?>

<h1>Menu</h1>

<p>
    Welkom, <?= htmlspecialchars($_SESSION['name']) ?>!
</p>

<h2>Producten filteren</h2>

<form method="GET">

    <label for="category">Categorie</label>

    <select name="category" id="category">

        <option value="">Alle categorieën</option>

        <?php foreach ($categories as $item): ?>

            <option
                value="<?= htmlspecialchars($item['category']) ?>"
                <?= $category === $item['category'] ? 'selected' : '' ?>
            >
                <?= htmlspecialchars($item['category']) ?>
            </option>

        <?php endforeach; ?>

    </select>

    <label for="diet">Dieet</label>

    <select name="diet" id="diet">

        <option value="">Alle dieetkenmerken</option>

        <?php foreach ($diets as $item): ?>

            <option
                value="<?= htmlspecialchars($item['diet']) ?>"
                <?= $diet === $item['diet'] ? 'selected' : '' ?>
            >
                <?= htmlspecialchars($item['diet']) ?>
            </option>

        <?php endforeach; ?>

    </select>

    <button type="submit">Filteren</button>

    <a href="menu.php">Filters wissen</a>

</form>

<hr>

<?php if (empty($products)): ?>

    <p>Er zijn geen producten gevonden met deze filters.</p>

<?php else: ?>

    <div class="products">

        <?php foreach ($products as $product): ?>

            <article class="product">

                <h2>
                    <?= htmlspecialchars($product['name']) ?>
                </h2>

                <p>
                    € <?= number_format($product['price'], 2, ',', '.') ?>
                </p>

                <p>
                    <strong>Categorie:</strong>
                    <?= htmlspecialchars($product['category']) ?>
                </p>

                <p>
                    <strong>Allergenen:</strong>
                    <?= htmlspecialchars($product['allergens'] ?: 'Geen') ?>
                </p>

                <p>
                    <strong>Dieet:</strong>
                    <?= htmlspecialchars($product['diet'] ?: 'Geen') ?>
                </p>

                <p>
                    <strong>Voorraad:</strong>
                    <?= (int) $product['stock'] ?>
                </p>
    <form method="POST" action="cart.php">
    <input
        type="hidden"
        name="product_id"
        value="<?= (int) $product['id'] ?>"
    >

    <label for="quantity-<?= (int) $product['id'] ?>">
        Aantal
    </label>

    <input
        type="number"
        id="quantity-<?= (int) $product['id'] ?>"
        name="quantity"
        value="1"
        min="1"
        max="<?= (int) $product['stock'] ?>"
    >

    <button type="submit">
        Toevoegen aan mandje
    </button>

</form>

            </article>

        <?php endforeach; ?>

    </div>

<?php endif; ?>

<p>
    <a href="../public/logout.php">Uitloggen</a>
</p>
<p>
    <a href="cart.php">🛒 Mijn mandje</a>
</p>
<?php

include '../includes/footer.php';


?>