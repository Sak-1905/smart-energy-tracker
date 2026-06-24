<?php

include 'includes/auth.php';
include 'includes/db.php';
include 'includes/header.php';
$activePage = 'reports';

$user_id = $_SESSION['user_id'];

$sql_monthly = "SELECT SUM(units_consumed) AS current_month_total FROM energy_usage WHERE user_id='$user_id' AND MONTH(usage_date)=MONTH(CURDATE()) AND YEAR(usage_date)=YEAR(CURDATE())";
$result_monthly = mysqli_query($conn, $sql_monthly);
$data_monthly = mysqli_fetch_assoc($result_monthly);
$current_month_total = $data_monthly['current_month_total'] ?? 0;

$average_usage = round($current_month_total / max(1, date('j')), 1);

$sql_highest = "SELECT usage_date, SUM(units_consumed) AS total_units FROM energy_usage WHERE user_id='$user_id' GROUP BY usage_date ORDER BY total_units DESC LIMIT 1";
$result_highest = mysqli_query($conn, $sql_highest);
$highest_data = mysqli_fetch_assoc($result_highest);
$highest_day = $highest_data['usage_date'] ?? 'N/A';
$highest_units = $highest_data['total_units'] ?? 0;

$monthMap = [];
for ($i = 5; $i >= 0; $i--) {
    $monthKey = date('Y-m', strtotime("-{$i} months"));
    $monthLabel = date('M', strtotime("-{$i} months"));
    $monthMap[$monthKey] = [
        'label' => $monthLabel,
        'units' => 0
    ];
}

$sql_trend = "SELECT DATE_FORMAT(usage_date,'%Y-%m') AS month_key, SUM(units_consumed) AS total_units
    FROM energy_usage
    WHERE user_id='$user_id' AND usage_date >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
    GROUP BY month_key
    ORDER BY month_key";
$result_trend = mysqli_query($conn, $sql_trend);
while ($row = mysqli_fetch_assoc($result_trend)) {
    if (isset($monthMap[$row['month_key']])) {
        $monthMap[$row['month_key']]['units'] = $row['total_units'];
    }
}

$report_labels = [];
$report_data = [];
foreach ($monthMap as $month) {
    $report_labels[] = $month['label'];
    $report_data[] = $month['units'];
}

?>

<div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>
    <main class="content">
        <div class="page-header">
            <h1>Monthly Reports</h1>
            <p>View your energy consumption trends.</p>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <div>
                    <span>Total Usage</span>
                    <strong><?php echo $current_month_total; ?> kWh</strong>
                </div>
                <div class="badge"></div>
            </div>
            <div class="summary-card">
                <div>
                    <span>Average Usage</span>
                    <strong><?php echo $average_usage; ?> kWh/day</strong>
                </div>
                <div class="badge"></div>
            </div>
            <div class="summary-card">
                <div>
                    <span>Highest Day</span>
                    <strong><?php echo $highest_day; ?> (<?php echo $highest_units; ?> kWh)</strong>
                </div>
                <div class="badge"></div>
            </div>
        </div>

        <div class="chart-card">
            <h2>6-Month Energy Trend</h2>
            <div style="position: relative; height: 320px; width: 100%;">
                <canvas id="reportsChart"></canvas>
            </div>
        </div>
    </main>
</div>

<script>
const reportCtx = document.getElementById('reportsChart');
new Chart(reportCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($report_labels); ?>,
        datasets: [{
            label: 'Usage',
            data: <?php echo json_encode($report_data); ?>,
            backgroundColor: 'rgba(62, 185, 85, 0.18)',
            borderColor: '#3fae32',
            borderWidth: 3,
            fill: true,
            tension: 0.35,
            pointRadius: 4,
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
