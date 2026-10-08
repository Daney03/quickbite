<?php

require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();
requireEmployee();

$errors = [];

/*
 * Tijdslot toevoegen of verwijderen
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    /*
     * Tijdslot verwijderen
     */
    if ($action === 'delete') {

        $slotId = (int) ($_POST['slot_id'] ?? 0);

        if ($slotId > 0) {

            $stmt = $pdo->prepare(
                'SELECT COUNT(*)
                 FROM orders
                 WHERE time_slot_id = ?'
            );

            $stmt->execute([$slotId]);

            $orderCount = (int) $stmt->fetchColumn();

            if ($orderCount === 0) {

                $stmt = $pdo->prepare(
                    'DELETE FROM time_slots
                     WHERE id = ?'
                );

                $stmt->execute([$slotId]);
            }
        }

        header('Location: time-slots.php');
        exit;
    }

    /*
     * Tijdslot toevoegen
     */
    if ($action === 'add') {

        $date = $_POST['date'] ?? '';
        $startTime = $_POST['start_time'] ?? '';
        $endTime = $_POST['end_time'] ?? '';
        $maxOrders = (int) ($_POST['max_orders'] ?? 0);

        if ($date === '') {
            $errors[] = 'Vul een datum in.';
        }

        if ($startTime === '') {
            $errors[] = 'Vul een begintijd in.';
        }

        if ($endTime === '') {
            $errors[] = 'Vul een eindtijd in.';
        }

        if ($maxOrders <= 0) {
            $errors[] = 'Het maximale aantal bestellingen moet groter zijn dan 0.';
        }

        if (
            $startTime !== '' &&
            $endTime !== '' &&
            $startTime >= $endTime
        ) {
            $errors[] = 'De eindtijd moet na de begintijd liggen.';
        }

        if (empty($errors)) {

            $stmt = $pdo->prepare(
                'INSERT INTO time_slots
                (date, start_time, end_time, max_orders)
                VALUES (?, ?, ?, ?)'
            );

            $stmt->execute([
                $date,
                $startTime,
                $endTime,
                $maxOrders
            ]);

            header('Location: time-slots.php');
            exit;
        }
    }
}

/*
 * Tijdsloten ophalen
 */
$stmt = $pdo->query(
    'SELECT
        ts.id,
        ts.date,
        ts.start_time,
        ts.end_time,
        ts.max_orders,
        COUNT(o.id) AS current_orders
     FROM time_slots ts
     LEFT JOIN orders o
        ON ts.id = o.time_slot_id
     GROUP BY
        ts.id,
        ts.date,
        ts.start_time,
        ts.end_time,
        ts.max_orders
     ORDER BY ts.date, ts.start_time'
);

$timeSlots = $stmt->fetchAll();

include '../includes/header.php';
?>

<h1>Tijdslotbeheer</h1>

<h2>Nieuw tijdslot toevoegen</h2>

<?php if (!empty($errors)): ?>

    <?php foreach ($errors as $error): ?>

        <p>
            <?= htmlspecialchars($error) ?>
        </p>

    <?php endforeach; ?>

<?php endif; ?>

<form method="POST">

    <input
        type="hidden"
        name="action"
        value="add"
    >

    <div>
        <label for="date">
            Datum
        </label>

        <input
            type="date"
            id="date"
            name="date"
            required
        >
    </div>

    <div>
        <label for="start_time">
            Begintijd
        </label>

        <input
            type="time"
            id="start_time"
            name="start_time"
            required
        >
    </div>

    <div>
        <label for="end_time">
            Eindtijd
        </label>

        <input
            type="time"
            id="end_time"
            name="end_time"
            required
        >
    </div>

    <div>
        <label for="max_orders">
            Maximaal aantal bestellingen
        </label>

        <input
            type="number"
            id="max_orders"
            name="max_orders"
            min="1"
            value="5"
            required
        >
    </div>

    <button type="submit">
        Tijdslot toevoegen
    </button>

</form>

<hr>

<h2>Bestaande tijdsloten</h2>

<?php if (empty($timeSlots)): ?>

    <p>Er zijn momenteel geen tijdsloten.</p>

<?php else: ?>

    <?php foreach ($timeSlots as $slot): ?>

        <article>

            <h3>
                <?= htmlspecialchars($slot['date']) ?>
            </h3>

            <p>
                <?= htmlspecialchars(substr($slot['start_time'], 0, 5)) ?>
                -
                <?= htmlspecialchars(substr($slot['end_time'], 0, 5)) ?>
            </p>

            <p>
                Bestellingen:
                <?= (int) $slot['current_orders'] ?>
                /
                <?= (int) $slot['max_orders'] ?>
            </p>

            <?php if ((int) $slot['current_orders'] === 0): ?>

                <form method="POST">

                    <input
                        type="hidden"
                        name="action"
                        value="delete"
                    >

                    <input
                        type="hidden"
                        name="slot_id"
                        value="<?= (int) $slot['id'] ?>"
                    >

                    <button type="submit">
                        Tijdslot verwijderen
                    </button>

                </form>

            <?php else: ?>

                <p>
                    Dit tijdslot kan niet verwijderd worden omdat
                    er al een bestelling aan gekoppeld is.
                </p>

            <?php endif; ?>

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