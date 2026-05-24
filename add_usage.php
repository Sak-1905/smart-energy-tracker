<?php

include 'includes/auth.php';
include 'includes/db.php';

$message = "";

if (isset($_POST['submit'])) {
    $user_id = $_SESSION['user_id'];
    $appliance = $_POST['appliance'];
    $units = $_POST['units'];
    $usage_date = $_POST['usage_date'];

    $sql = "INSERT INTO energy_usage (
                user_id,
                appliance,
                units_consumed,
                usage_date
            ) VALUES (
                '$user_id',
                '$appliance',
                '$units',
                '$usage_date'
            )";

    if (mysqli_query($conn, $sql)) {
        $message = "Usage Added Successfully";
    }
}

?>

<?php include 'includes/header.php'; ?>

<?php $activePage = 'add'; ?>
<div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>
    <main class="content">
        <div class="page-header">
            <h1>Add Energy Usage</h1>
            <p>Record your energy consumption data</p>
        </div>

        <div class="form-container">
            <form method="POST">
                <p class="message"><?php echo $message; ?></p>

                <label for="appliance">Appliance</label>
                <input id="appliance" type="text" name="appliance" placeholder="e.g., Air Conditioner, Refrigerator" required>

                <label for="units">Energy Consumption (kWh)</label>
                <input id="units" type="number" step="0.01" name="units" placeholder="e.g., 15" required>

                <label for="usage_date">Date</label>
                <input id="usage_date" type="date" name="usage_date" required>

                <button type="submit" name="submit">Submit</button>
            </form>
        </div>
    </main>
</div>

<?php include 'includes/footer.php'; ?>