<?php

require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();

$stmt = $pdo->query(
    "SELECT
        ts.id,
        ts.date,
        ts.start_time,
        ts.end_time,
        ts.max_orders,
        COUNT(o.id) AS current_orders
     FROM time_slots ts
     LEFT JOIN orders o
        ON ts.id = o.time_slot_id
     WHERE ts.date >= CURDATE()
     GROUP BY
        ts.id,
        ts.date,
        ts.start_time,
        ts.end_time,
        ts.max_orders
     ORDER BY ts.date, ts.start_time"
);

$timeSlots = $stmt->fetchAll();

include '../includes/header.php';

?>

<h1>Kies je afhaalmoment</h1>

<?php if (empty($timeSlots)): ?>

    <p>Er zijn momenteel geen beschikbare tijdsloten.</p>

<?php else: ?>

    <?php foreach ($timeSlots as $slot): ?>

        <?php
        $isFull = $slot['current_orders'] >= $slot['max_orders'];
        ?>

        <article>

            <h2>
                <?= htmlspecialchars($slot['date']) ?>
            </h2>

            <p>
                <?= htmlspecialchars(substr($slot['start_time'], 0, 5)) ?>
                -
                <?= htmlspecialchars(substr($slot['end_time'], 0, 5)) ?>
            </p>

            <p>
                Beschikbaar:
                <?= (int) $slot['current_orders'] ?>
                /
                <?= (int) $slot['max_orders'] ?>
            </p>

            <?php if ($isFull): ?>

                <p>
                    Dit tijdslot is vol. Kies een ander tijdstip.
                </p>

            <?php else: ?>

                <form method="POST" action="checkout.php">

                    <input
                        type="hidden"
                        name="time_slot_id"
                        value="<?= (int) $slot['id'] ?>"
                    >

                    <button type="submit">
                        Kies dit tijdstip
                    </button>

                </form>

            <?php endif; ?>

        </article>

        <hr>

    <?php endforeach; ?>

<?php endif; ?>

<p>
    <a href="cart.php">Terug naar mandje</a>
</p>

<?php

include '../includes/footer.php';

?>