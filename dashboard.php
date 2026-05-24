<?php

include 'includes/auth.php';
include 'includes/db.php';
include 'includes/header.php';
$activePage = 'dashboard';

$user_id = $_SESSION['user_id'];

$sql_total = "SELECT SUM(units_consumed) AS total_usage FROM energy_usage WHERE user_id='$user_id'";
$result_total = mysqli_query($conn, $sql_total);
$data_total = mysqli_fetch_assoc($result_total);
$total_usage = $data_total['total_usage'] ?? 0;

$sql_weekly = "SELECT SUM(units_consumed) AS weekly_usage FROM energy_usage WHERE user_id='$user_id' AND usage_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$result_weekly = mysqli_query($conn, $sql_weekly);
$data_weekly = mysqli_fetch_assoc($result_weekly);
$weekly_usage = $data_weekly['weekly_usage'] ?? 0;

$sql_monthly = "SELECT SUM(units_consumed) AS monthly_usage FROM energy_usage WHERE user_id='$user_id' AND MONTH(usage_date)=MONTH(CURDATE()) AND YEAR(usage_date)=YEAR(CURDATE())";
$result_monthly = mysqli_query($conn, $sql_monthly);
$data_monthly = mysqli_fetch_assoc($result_monthly);
$monthly_usage = $data_monthly['monthly_usage'] ?? 0;

$weekDates = [];
$weeklyValues = [];
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-{$i} days"));
    $weekDates[$day] = date('D', strtotime($day));
    $weeklyValues[$day] = 0;
}

$sql_weekly_chart = "SELECT DATE_FORMAT(usage_date,'%Y-%m-%d') AS usage_date, SUM(units_consumed) AS units
    FROM energy_usage
    WHERE user_id='$user_id' AND usage_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY usage_date
    ORDER BY usage_date";
$result_chart = mysqli_query($conn, $sql_weekly_chart);
while ($row = mysqli_fetch_assoc($result_chart)) {
    if (isset($weeklyValues[$row['usage_date']])) {
        $weeklyValues[$row['usage_date']] = $row['units'];
    }
}

$chart_labels = array_values($weekDates);
$chart_data = array_values($weeklyValues);

?>

<div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>
    <main class="content">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Monitor your energy consumption</p>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <div>
                    <span>Total Usage</span>
                    <strong><?php echo $total_usage ?: 0; ?> kWh</strong>
                </div>
                <div class="badge"></div>
            </div>
            <div class="summary-card">
                <div>
                    <span>Weekly Usage</span>
                    <strong><?php echo $weekly_usage ?: 0; ?> kWh</strong>
                </div>
                <div class="badge"></div>
            </div>
            <div class="summary-card">
                <div>
                    <span>Monthly Usage</span>
                    <strong><?php echo $monthly_usage ?: 0; ?> kWh</strong>
                </div>
                <div class="badge"></div>
            </div>
        </div>

        <div class="chart-card">
            <h2>Weekly Energy Usage</h2>
            <canvas id="energyChart"></canvas>
        </div>
    </main>
</div>

<script>
const ctx = document.getElementById('energyChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [{
            label: 'kWh',
            data: <?php echo json_encode($chart_data); ?>,
            backgroundColor: 'rgba(62, 185, 85, 0.15)',
            borderColor: '#3fae32',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 5,
            pointBackgroundColor: '#3fae32'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                grid: {color: 'rgba(226, 232, 240, 0.7)'}
            },
            x: {
                grid: {display: false}
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>