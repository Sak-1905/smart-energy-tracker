<?php

include 'includes/db.php';

$message = "";
$messageType = "";

// Handle Delete Operations
if (isset($_POST['delete_type'])) {
    $deleteType = $_POST['delete_type'];
    
    if ($deleteType === 'user') {
        $userId = $_POST['user_id'];
        $sql = "DELETE FROM users WHERE id='$userId'";
        if (mysqli_query($conn, $sql)) {
            $message = "User deleted successfully";
            $messageType = "success";
        } else {
            $message = "Error deleting user";
            $messageType = "error";
        }
    } elseif ($deleteType === 'usage') {
        $usageId = $_POST['usage_id'];
        $sql = "DELETE FROM energy_usage WHERE id='$usageId'";
        if (mysqli_query($conn, $sql)) {
            $message = "Usage record deleted successfully";
            $messageType = "success";
        } else {
            $message = "Error deleting usage record";
            $messageType = "error";
        }
    }
}

// Handle Update Operations
if (isset($_POST['update_type'])) {
    $updateType = $_POST['update_type'];
    
    if ($updateType === 'user') {
        $userId = $_POST['user_id'];
        $fullname = $_POST['fullname'];
        $email = $_POST['email'];
        $sql = "UPDATE users SET fullname='$fullname', email='$email' WHERE id='$userId'";
        if (mysqli_query($conn, $sql)) {
            $message = "User updated successfully";
            $messageType = "success";
        } else {
            $message = "Error updating user";
            $messageType = "error";
        }
    } elseif ($updateType === 'usage') {
        $usageId = $_POST['usage_id'];
        $appliance = $_POST['appliance'];
        $units = $_POST['units_consumed'];
        $usageDate = $_POST['usage_date'];
        $sql = "UPDATE energy_usage SET appliance='$appliance', units_consumed='$units', usage_date='$usageDate' WHERE id='$usageId'";
        if (mysqli_query($conn, $sql)) {
            $message = "Usage record updated successfully";
            $messageType = "success";
        } else {
            $message = "Error updating usage record";
            $messageType = "error";
        }
    }
}

// Search functionality
$usersSearch = isset($_POST['users_search']) ? $_POST['users_search'] : "";
$usageSearch = isset($_POST['usage_search']) ? $_POST['usage_search'] : "";
$activeTab = isset($_POST['tab']) ? $_POST['tab'] : "users";

// Get Users Data
$sqlUsers = "SELECT * FROM users";
if (!empty($usersSearch)) {
    $sqlUsers .= " WHERE fullname LIKE '%$usersSearch%' OR email LIKE '%$usersSearch%'";
}
$resultUsers = mysqli_query($conn, $sqlUsers);
$users = [];
while ($row = mysqli_fetch_assoc($resultUsers)) {
    $users[] = $row;
}

// Get Energy Usage Data
$sqlUsage = "SELECT * FROM energy_usage";
if (!empty($usageSearch)) {
    $sqlUsage .= " WHERE appliance LIKE '%$usageSearch%' OR user_id LIKE '%$usageSearch%'";
}
$sqlUsage .= " ORDER BY usage_date DESC";
$resultUsage = mysqli_query($conn, $sqlUsage);
$usageRecords = [];
while ($row = mysqli_fetch_assoc($resultUsage)) {
    $usageRecords[] = $row;
}

?>

<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        .admin-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .admin-header h1 {
            color: #0f172a;
            margin-bottom: 10px;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }

        .message.success {
            background: #d1f2eb;
            color: #047857;
            border-left: 4px solid #047857;
        }

        .message.error {
            background: #fee2e2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e2e8f0;
        }

        .tab-button {
            padding: 12px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #64748b;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }

        .tab-button.active {
            color: #3fae32;
            border-bottom-color: #3fae32;
        }

        .search-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .search-bar input {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }

        .search-bar button {
            padding: 10px 20px;
            background: #3fae32;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .search-bar button:hover {
            background: #2d8226;
        }

        .table-wrapper {
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background: #f8fafc;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #0f172a;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        tr:hover {
            background: #f8fafc;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .edit-btn, .delete-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .edit-btn {
            background: #dbeafe;
            color: #1e40af;
        }

        .edit-btn:hover {
            background: #bfdbfe;
        }

        .delete-btn {
            background: #fee2e2;
            color: #dc2626;
        }

        .delete-btn:hover {
            background: #fecaca;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #0f172a;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #0f172a;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #3fae32;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .modal-buttons button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .save-btn {
            background: #3fae32;
            color: white;
        }

        .save-btn:hover {
            background: #2d8226;
        }

        .cancel-btn {
            background: #e2e8f0;
            color: #0f172a;
        }

        .cancel-btn:hover {
            background: #cbd5e1;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #64748b;
        }

        .tab-content {
            display: none;
        }

        .tab-content.show {
            display: block;
        }
    </style>
</head>
<body>

<div class="admin-container">
    <div class="admin-header">
        <h1>Data Management</h1>
        <p>View, search, modify, and delete users & energy usage data</p>
    </div>

    <?php if (!empty($message)) : ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="tabs">
        <button class="tab-button <?php echo $activeTab === 'users' ? 'active' : ''; ?>" onclick="switchTab('users')">
            Users (<?php echo count($users); ?>)
        </button>
        <button class="tab-button <?php echo $activeTab === 'usage' ? 'active' : ''; ?>" onclick="switchTab('usage')">
            Energy Usage (<?php echo count($usageRecords); ?>)
        </button>
    </div>

    <!-- Users Tab -->
    <div id="users-tab" class="tab-content <?php echo $activeTab === 'users' ? 'show' : ''; ?>">
        <form method="POST" class="search-bar">
            <input type="hidden" name="tab" value="users">
            <input type="text" name="users_search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($usersSearch); ?>">
            <button type="submit">Search</button>
        </form>

        <?php if (count($users) > 0) : ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) : ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="edit-btn" onclick="openEditUserModal(<?php echo $user['id']; ?>, '<?php echo addslashes(htmlspecialchars($user['fullname'])); ?>', '<?php echo addslashes(htmlspecialchars($user['email'])); ?>')">Edit</button>
                                        <button class="delete-btn" onclick="deleteUser(<?php echo $user['id']; ?>)">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="no-data">No users found</div>
        <?php endif; ?>
    </div>

    <!-- Energy Usage Tab -->
    <div id="usage-tab" class="tab-content <?php echo $activeTab === 'usage' ? 'show' : ''; ?>">
        <form method="POST" class="search-bar">
            <input type="hidden" name="tab" value="usage">
            <input type="text" name="usage_search" placeholder="Search by appliance or user ID..." value="<?php echo htmlspecialchars($usageSearch); ?>">
            <button type="submit">Search</button>
        </form>

        <?php if (count($usageRecords) > 0) : ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User ID</th>
                            <th>Appliance</th>
                            <th>Units (kWh)</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usageRecords as $usage) : ?>
                            <tr>
                                <td><?php echo $usage['id']; ?></td>
                                <td><?php echo $usage['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($usage['appliance']); ?></td>
                                <td><?php echo $usage['units_consumed']; ?></td>
                                <td><?php echo $usage['usage_date']; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="edit-btn" onclick="openEditUsageModal(<?php echo $usage['id']; ?>, '<?php echo addslashes(htmlspecialchars($usage['appliance'])); ?>', <?php echo $usage['units_consumed']; ?>, '<?php echo $usage['usage_date']; ?>')">Edit</button>
                                        <button class="delete-btn" onclick="deleteUsage(<?php echo $usage['id']; ?>)">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="no-data">No usage records found</div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Edit User</div>
        <form method="POST">
            <input type="hidden" name="update_type" value="user">
            <input type="hidden" id="user_id" name="user_id">
            
            <div class="form-group">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="modal-buttons">
                <button type="submit" class="save-btn">Save Changes</button>
                <button type="button" class="cancel-btn" onclick="closeEditUserModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Usage Modal -->
<div id="editUsageModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Edit Energy Usage</div>
        <form method="POST">
            <input type="hidden" name="update_type" value="usage">
            <input type="hidden" id="usage_id" name="usage_id">
            
            <div class="form-group">
                <label for="appliance">Appliance</label>
                <input type="text" id="appliance" name="appliance" required>
            </div>
            
            <div class="form-group">
                <label for="units_consumed">Units (kWh)</label>
                <input type="number" id="units_consumed" name="units_consumed" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="usage_date">Date</label>
                <input type="date" id="usage_date" name="usage_date" required>
            </div>
            
            <div class="modal-buttons">
                <button type="submit" class="save-btn">Save Changes</button>
                <button type="button" class="cancel-btn" onclick="closeEditUsageModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Forms (hidden) -->
<form id="deleteUserForm" method="POST" style="display:none;">
    <input type="hidden" name="delete_type" value="user">
    <input type="hidden" name="user_id" id="deleteUserId">
</form>

<form id="deleteUsageForm" method="POST" style="display:none;">
    <input type="hidden" name="delete_type" value="usage">
    <input type="hidden" name="usage_id" id="deleteUsageId">
</form>

<script>
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('show');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('show');
    
    // Add active class to clicked button
    event.target.classList.add('active');
}

function openEditUserModal(userId, fullname, email) {
    document.getElementById('user_id').value = userId;
    document.getElementById('fullname').value = fullname;
    document.getElementById('email').value = email;
    document.getElementById('editUserModal').classList.add('show');
}

function closeEditUserModal() {
    document.getElementById('editUserModal').classList.remove('show');
}

function openEditUsageModal(usageId, appliance, units, date) {
    document.getElementById('usage_id').value = usageId;
    document.getElementById('appliance').value = appliance;
    document.getElementById('units_consumed').value = units;
    document.getElementById('usage_date').value = date;
    document.getElementById('editUsageModal').classList.add('show');
}

function closeEditUsageModal() {
    document.getElementById('editUsageModal').classList.remove('show');
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        document.getElementById('deleteUserId').value = userId;
        document.getElementById('deleteUserForm').submit();
    }
}

function deleteUsage(usageId) {
    if (confirm('Are you sure you want to delete this usage record?')) {
        document.getElementById('deleteUsageId').value = usageId;
        document.getElementById('deleteUsageForm').submit();
    }
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    let userModal = document.getElementById('editUserModal');
    let usageModal = document.getElementById('editUsageModal');
    
    if (event.target === userModal) {
        userModal.classList.remove('show');
    }
    if (event.target === usageModal) {
        usageModal.classList.remove('show');
    }
}
</script>

</body>
</html>

<?php include 'includes/footer.php'; ?>
