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

// 6-Month Trend Data
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

// Yearly Report Data (All 12 months of current year)
$current_year = date('Y');
$yearlyMap = [];
$monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

for ($month = 1; $month <= 12; $month++) {
    $monthKey = sprintf("%04d-%02d", $current_year, $month);
    $yearlyMap[$monthKey] = [
        'label' => $monthNames[$month - 1],
        'units' => 0
    ];
}

$sql_yearly = "SELECT DATE_FORMAT(usage_date,'%Y-%m') AS month_key, SUM(units_consumed) AS total_units
    FROM energy_usage
    WHERE user_id='$user_id' AND YEAR(usage_date)='$current_year'
    GROUP BY month_key
    ORDER BY month_key";
$result_yearly = mysqli_query($conn, $sql_yearly);
while ($row = mysqli_fetch_assoc($result_yearly)) {
    if (isset($yearlyMap[$row['month_key']])) {
        $yearlyMap[$row['month_key']]['units'] = $row['total_units'];
    }
}

$yearly_labels = [];
$yearly_data = [];
foreach ($yearlyMap as $month) {
    $yearly_labels[] = $month['label'];
    $yearly_data[] = $month['units'];
}

// Calculate yearly statistics
$sql_yearly_total = "SELECT SUM(units_consumed) AS yearly_total FROM energy_usage WHERE user_id='$user_id' AND YEAR(usage_date)='$current_year'";
$result_yearly_total = mysqli_query($conn, $sql_yearly_total);
$yearly_total_data = mysqli_fetch_assoc($result_yearly_total);
$yearly_total = $yearly_total_data['yearly_total'] ?? 0;

$sql_yearly_avg = "SELECT AVG(monthly_total) AS yearly_average FROM (
    SELECT MONTH(usage_date) as month, SUM(units_consumed) AS monthly_total 
    FROM energy_usage 
    WHERE user_id='$user_id' AND YEAR(usage_date)='$current_year' AND units_consumed > 0
    GROUP BY MONTH(usage_date)
) AS monthly_totals";
$result_yearly_avg = mysqli_query($conn, $sql_yearly_avg);
$yearly_avg_data = mysqli_fetch_assoc($result_yearly_avg);
$yearly_average = round($yearly_avg_data['yearly_average'] ?? 0, 1);

?>

<div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>
    <main class="content">
        <div class="page-header">
            <h1>Reports</h1>
            <p>View your energy consumption trends and yearly analysis.</p>
        </div>

        <!-- Monthly Summary -->
        <div style="margin-bottom: 40px;">
            <h2 style="color: #0f172a; margin-bottom: 20px;">Monthly Summary</h2>
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
        </div>

        <!-- Yearly Summary -->
        <div style="margin-bottom: 40px;">
            <h2 style="color: #0f172a; margin-bottom: 20px;">Yearly Summary (<?php echo $current_year; ?>)</h2>
            <div class="summary-grid">
                <div class="summary-card">
                    <div>
                        <span>Total Yearly Usage</span>
                        <strong><?php echo $yearly_total; ?> kWh</strong>
                    </div>
                    <div class="badge"></div>
                </div>
                <div class="summary-card">
                    <div>
                        <span>Average Monthly Usage</span>
                        <strong><?php echo $yearly_average; ?> kWh</strong>
                    </div>
                    <div class="badge"></div>
                </div>
                <div class="summary-card">
                    <div>
                        <span>Average Daily Usage</span>
                        <strong><?php echo round($yearly_total / 365, 1); ?> kWh/day</strong>
                    </div>
                    <div class="badge"></div>
                </div>
            </div>

            <div class="chart-card">
                <h2>Monthly Comparison (<?php echo $current_year; ?>)</h2>
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="yearlyChart"></canvas>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// 6-Month Line Chart
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

// Yearly Bar Chart
const yearlyCtx = document.getElementById('yearlyChart');
new Chart(yearlyCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($yearly_labels); ?>,
        datasets: [{
            label: 'Monthly Usage (kWh)',
            data: <?php echo json_encode($yearly_data); ?>,
            backgroundColor: [
                'rgba(62, 185, 85, 0.8)',
                'rgba(62, 185, 85, 0.75)',
                'rgba(62, 185, 85, 0.7)',
                'rgba(62, 185, 85, 0.8)',
                'rgba(62, 185, 85, 0.75)',
                'rgba(62, 185, 85, 0.7)',
                'rgba(62, 185, 85, 0.8)',
                'rgba(62, 185, 85, 0.75)',
                'rgba(62, 185, 85, 0.7)',
                'rgba(62, 185, 85, 0.8)',
                'rgba(62, 185, 85, 0.75)',
                'rgba(62, 185, 85, 0.7)'
            ],
            borderColor: '#3fae32',
            borderWidth: 2,
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'x',
        scales: {
            y: {
                beginAtZero: true,
                grid: {color: 'rgba(226, 232, 240, 0.7)'}
            },
            x: {
                grid: {display: false}
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
