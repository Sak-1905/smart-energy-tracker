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

// Appliance usage chart data
$appliance_labels = [];
$appliance_data = [];
$sql_appliance = "SELECT appliance, SUM(units_consumed) AS total_units FROM energy_usage WHERE user_id='$user_id' GROUP BY appliance ORDER BY total_units DESC LIMIT 6";
$result_appliance = mysqli_query($conn, $sql_appliance);
while ($row = mysqli_fetch_assoc($result_appliance)) {
    $appliance_labels[] = $row['appliance'];
    $appliance_data[] = $row['total_units'];
}

?>

<style>
    .dashboard-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .dashboard-card {
        padding: 24px 26px;
        border-radius: 24px;
        background: #ffffff;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
    }

    .dashboard-card span {
        color: #64748b;
        font-size: 0.95rem;
    }

    .dashboard-card strong {
        display: block;
        font-size: 2.1rem;
        color: #0f172a;
        margin-top: 8px;
    }

    .dashboard-card .badge {
        width: 14px;
        height: 14px;
        border-radius: 50%;
    }

    .dashboard-card.total .badge { background: #3fae32; }
    .dashboard-card.weekly .badge { background: #2563eb; }
    .dashboard-card.monthly .badge { background: #f59e0b; }

    .report-grid {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 24px;
    }

    .report-grid .chart-card {
        padding: 28px;
    }

    .chart-card h2 {
        font-size: 1.15rem;
        margin-bottom: 20px;
        color: #0f172a;
    }

    @media (max-width: 1100px) {
        .report-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .dashboard-summary {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>
    <main class="content">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Monitor your energy consumption</p>
        </div>

        <div class="dashboard-summary">
            <div class="dashboard-card total">
                <div>
                    <span>Total Usage</span>
                    <strong><?php echo $total_usage ?: 0; ?> kWh</strong>
                </div>
                <div class="badge"></div>
            </div>
            <div class="dashboard-card weekly">
                <div>
                    <span>Weekly Usage</span>
                    <strong><?php echo $weekly_usage ?: 0; ?> kWh</strong>
                </div>
                <div class="badge"></div>
            </div>
            <div class="dashboard-card monthly">
                <div>
                    <span>Monthly Usage</span>
                    <strong><?php echo $monthly_usage ?: 0; ?> kWh</strong>
                </div>
                <div class="badge"></div>
            </div>
        </div>

        <div class="report-grid">
            <div class="chart-card">
                <h2>Weekly Energy Usage</h2>
                <div style="position: relative; height: 340px; width: 100%;">
                    <canvas id="energyChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <h2>Usage by Appliance</h2>
                <div style="position: relative; height: 340px; width: 100%;">
                    <canvas id="applianceChart"></canvas>
                </div>
            </div>
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
            backgroundColor: 'rgba(62, 185, 85, 0.18)',
            borderColor: '#16a34a',
            borderWidth: 3,
            fill: true,
            tension: 0.35,
            pointRadius: 5,
            pointBackgroundColor: '#16a34a',
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(226, 232, 240, 0.7)' },
                ticks: { color: '#475569' }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#475569' }
            }
        }
    }
});

const appCtx = document.getElementById('applianceChart');
new Chart(appCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($appliance_labels); ?>,
        datasets: [{
            label: 'kWh',
            data: <?php echo json_encode($appliance_data); ?>,
            backgroundColor: 'rgba(37, 99, 235, 0.85)',
            borderColor: 'rgba(37, 99, 235, 1)',
            borderWidth: 1,
            borderRadius: 10,
            maxBarThickness: 50
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(226, 232, 240, 0.7)' },
                ticks: { color: '#475569' }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#475569' }
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>