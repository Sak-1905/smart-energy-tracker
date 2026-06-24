<?php include 'includes/header.php'; ?>

<?php $activePage = 'tips'; ?>
<div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>
    <main class="content">
        <div class="page-header">
            <h1>Energy Saving Tips</h1>
            <p>Smart ways to reduce your energy consumption</p>
        </div>

        <div class="tips-grid">
            <div class="tip-card">
                <div class="tip-card-title">
                    <span class="tip-dot"></span>
                    <h3>Turn off unused devices</h3>
                </div>
                <p>Unplug chargers and electronics when not in use. Even in standby mode, they consume energy.</p>
                <div class="progress"><span class="progress-bar" style="width:85%;"></span></div>
            </div>
            <div class="tip-card">
                <div class="tip-card-title">
                    <span class="tip-dot"></span>
                    <h3>Use LED lighting</h3>
                </div>
                <p>Replace traditional bulbs with LED lights. They use less energy and last longer.</p>
                <div class="progress"><span class="progress-bar" style="width:75%;"></span></div>
            </div>
            <div class="tip-card">
                <div class="tip-card-title">
                    <span class="tip-dot"></span>
                    <h3>Optimize AC usage</h3>
                </div>
                <p>Set your thermostat to a moderate temperature to reduce energy consumption.</p>
                <div class="progress"><span class="progress-bar" style="width:70%;"></span></div>
            </div>
            <div class="tip-card">
                <div class="tip-card-title">
                    <span class="tip-dot"></span>
                    <h3>Use natural light</h3>
                </div>
                <p>Open curtains during the day to reduce reliance on artificial lighting.</p>
                <div class="progress"><span class="progress-bar" style="width:80%;"></span></div>
            </div>
            <div class="tip-card">
                <div class="tip-card-title">
                    <span class="tip-dot"></span>
                    <h3>Maintain appliances</h3>
                </div>
                <p>Clean and service appliances regularly for better efficiency.</p>
                <div class="progress"><span class="progress-bar" style="width:72%;"></span></div>
            </div>
            <div class="tip-card">
                <div class="tip-card-title">
                    <span class="tip-dot"></span>
                    <h3>Fix water leaks</h3>
                </div>
                <p>Repair leaky faucets and water heaters to prevent waste.</p>
                <div class="progress"><span class="progress-bar" style="width:68%;"></span></div>
            </div>
        </div>
    </main>
</div>

<style>
    .tips-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
    }

    .tip-card {
        background: #ffffff;
        border-radius: 28px;
        padding: 26px 28px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .tip-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 28px 70px rgba(15, 23, 42, 0.12);
    }

    .tip-card-title {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .tip-dot {
        width: 20px;
        height: 20px;
        background: #3fae32;
        border-radius: 50%;
        box-shadow: 0 0 0 8px rgba(62, 185, 85, 0.12);
    }

    .tip-card h3 {
        margin: 0;
        font-size: 1.1rem;
    }

    .tip-card p {
        color: #64748b;
        line-height: 1.8;
        margin: 0;
    }

    .progress {
        height: 12px;
        background: #f1f5f9;
        border-radius: 999px;
        overflow: hidden;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.08);
    }

    .progress-bar {
        display: block;
        height: 100%;
        background: linear-gradient(90deg, #3fae32, #22c55e);
        border-radius: 999px;
        transition: width 0.4s ease;
    }

    @media (max-width: 1000px) {
        .tips-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>