<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Handle Actions
$msg = '';
$msgType = 'success';

if (isset($_POST['update_status'])) {
    $stmt = $pdo->prepare("UPDATE bookings SET payment_status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], (int)$_POST['booking_id']]);
    $msg = "Booking status updated.";
}

if (isset($_POST['delete_booking'])) {
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->execute([(int)$_POST['booking_id']]);
    $msg = "Booking deleted permanently.";
}

if (isset($_POST['save_service'])) {
    $id = $_POST['service_id'] ?? null;
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $dur = $_POST['duration'];
    $icon = $_POST['icon'];
    $active = isset($_POST['is_active']) ? 1 : 0;

    if ($id) {
        $stmt = $pdo->prepare("UPDATE services SET name=?, description=?, price=?, duration=?, icon=?, is_active=? WHERE id=?");
        $stmt->execute([$name, $desc, $price, $dur, $icon, $active, $id]);
        $msg = "Service updated.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO services (name, description, price, duration, icon, is_active) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$name, $desc, $price, $dur, $icon, $active]);
        $msg = "Service added.";
    }
}

if (isset($_POST['delete_service'])) {
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([(int)$_POST['service_id']]);
    $msg = "Service deleted.";
}

if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($new !== $confirm) {
        $msg = "New passwords do not match."; $msgType = 'error';
    } elseif (strlen($new) < 6) {
        $msg = "Password must be at least 6 characters."; $msgType = 'error';
    } else {
        $stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE username = ?");
        $stmt->execute([$_SESSION['admin_username']]);
        $user = $stmt->fetch();
        if ($user && password_verify($current, $user['password_hash'])) {
            $update = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE username = ?");
            $update->execute([password_hash($new, PASSWORD_DEFAULT), $_SESSION['admin_username']]);
            $msg = "Password updated.";
        } else {
            $msg = "Current password incorrect."; $msgType = 'error';
        }
    }
}

$activeTab = $_GET['tab'] ?? 'bookings';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | D'FACEEE</title>
    
    <!-- FIXED: Added the favicon here -->
    <link rel="icon" type="image/svg+xml" href="../assets/images/logo.svg">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        /* Admin Specific Styles */
        body { background: #f4f6f9; display: flex; min-height: 100vh; margin: 0; }
        .sidebar { width: 260px; background: var(--black); color: white; padding: 30px 20px; min-height: 100vh; position: fixed; top:0; left:0; }
        .sidebar h2 { font-family: 'Cinzel', serif; color: var(--gold); margin-bottom: 40px; text-align: center; font-size: 1.6rem; }
        .sidebar a { display: flex; align-items: center; gap: 12px; color: #bbb; text-decoration: none; padding: 14px 18px; border-radius: 8px; margin-bottom: 8px; font-weight: 600; transition: all 0.2s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.08); color: var(--gold); }
        .main-content { flex: 1; margin-left: 260px; padding: 40px; }
        
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .top-bar h1 { font-family: 'Cinzel', serif; font-size: 2rem; color: var(--black); margin: 0; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 25px; font-weight: bold; }
        .alert.success { background: #d4edda; color: #155724; border-left: 4px solid #27ae60; }
        .alert.error { background: #f8d7da; color: #721c24; border-left: 4px solid #c0392b; }

        .card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 30px; margin-bottom: 30px; }
        
        /* Tables */
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; font-size: 0.8rem; text-transform: uppercase; color: #888; border-bottom: 2px solid #eee; }
        td { padding: 18px 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        tr:hover { background: #fafafa; }
        
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-block; text-transform: uppercase; }
        .status-paid { background: #e3f2fd; color: #1976d2; }
        .status-completed { background: #e8f5e9; color: #2e7d32; }
        .status-cancelled { background: #ffebee; color: #c62828; }
        
        /* Buttons */
        .btn { padding: 10px 18px; border-radius: 6px; border: none; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: 0.2s; display: inline-block; text-decoration: none; }
        .btn-primary { background: var(--gold); color: white; }
        .btn-primary:hover { background: var(--gold-dark); }
        .btn-danger { background: #ffebee; color: #c62828; }
        .btn-danger:hover { background: #c62828; color: white; }
        .btn-sm { padding: 6px 12px; font-size: 0.75rem; }
        
        /* Forms */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-grid .full { grid-column: 1 / -1; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 700; font-size: 0.85rem; color: #444; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-family: inherit; font-size: 0.95rem; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--gold); }
        
        /* Service Cards in Admin */
        .svc-admin-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .svc-admin-card { border: 1px solid #eee; border-radius: 10px; padding: 20px; display: flex; flex-direction: column; gap: 10px; }
        .svc-admin-header { display: flex; justify-content: space-between; align-items: start; }
        .svc-admin-icon { font-size: 2rem; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>D'FACEEE</h2>
    <a href="?tab=bookings" class="<?= $activeTab==='bookings'?'active':'' ?>">📅 Bookings</a>
    <a href="?tab=services" class="<?= $activeTab==='services'?'active':'' ?>">💎 Manage Services</a>
    <a href="?tab=password" class="<?= $activeTab==='password'?'active':'' ?>">⚙️ Settings</a>
    <a href="../index.php" target="_blank">🌐 View Website</a>
    <a href="logout.php" style="color: #e74c3c; margin-top: auto;">🚪 Logout</a>
</div>

<div class="main-content">
    <div class="top-bar">
        <h1><?= $activeTab === 'bookings' ? 'Bookings' : ($activeTab === 'services' ? 'Services' : 'Settings') ?></h1>
        <?php if ($activeTab === 'services'): ?>
            <button onclick="document.getElementById('addSvcForm').style.display='block'" class="btn btn-primary">+ Add New Service</button>
        <?php endif; ?>
    </div>

    <?php if ($msg): ?>
        <div class="alert <?= $msgType ?>"><?= $msg ?></div>
    <?php endif; ?>

    <!-- ═══════════ BOOKINGS TAB ═══════════ -->
    <?php if ($activeTab === 'bookings'): ?>
        <div class="card table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Ref</th><th>Client Details</th><th>Service</th><th>Schedule</th><th>Financials</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM bookings ORDER BY booking_date ASC, booking_time ASC");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                        $statusClass = 'status-paid';
                        if ($row['payment_status'] == 'Completed') $statusClass = 'status-completed';
                        if ($row['payment_status'] == 'Cancelled') $statusClass = 'status-cancelled';
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['booking_reference'] ?? 'DF-'.$row['id']) ?></strong></td>
                        <td>
                            <strong><?= htmlspecialchars($row['full_name']) ?></strong><br>
                            <small><?= htmlspecialchars($row['phone']) ?></small><br>
                            <small><?= htmlspecialchars($row['email']) ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($row['service']) ?><br>
                            <small>Stylist: <?= htmlspecialchars($row['stylist_preference']) ?></small>
                            <?php if($row['special_notes']): ?>
                                <br><small style="color: #888; font-style: italic;">"<?= htmlspecialchars($row['special_notes']) ?>"</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= date('M d, Y', strtotime($row['booking_date'])) ?><br>
                            <small><?= date('h:i A', strtotime($row['booking_time'])) ?></small>
                        </td>
                        <td>
                            Total: $<?= $row['total_price'] ?><br>
                            <small style="color:#27ae60;">Dep: $<?= $row['deposit_paid'] ?></small>
                        </td>
                        <td><span class="status-badge <?= $statusClass ?>"><?= $row['payment_status'] ?></span></td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <?php if($row['payment_status'] !== 'Completed'): ?>
                                    <form method="POST"><input type="hidden" name="booking_id" value="<?= $row['id'] ?>"><input type="hidden" name="status" value="Completed"><button type="submit" name="update_status" class="btn btn-sm btn-primary">Mark Done</button></form>
                                <?php endif; ?>
                                <?php if($row['payment_status'] !== 'Cancelled'): ?>
                                    <form method="POST" onsubmit="return confirm('Cancel this booking?');"><input type="hidden" name="booking_id" value="<?= $row['id'] ?>"><input type="hidden" name="status" value="Cancelled"><button type="submit" name="update_status" class="btn btn-sm" style="background:#eee; color:#666;">Cancel</button></form>
                                <?php endif; ?>
                                <form method="POST" onsubmit="return confirm('DELETE permanently? This cannot be undone.');"><input type="hidden" name="booking_id" value="<?= $row['id'] ?>"><button type="submit" name="delete_booking" class="btn btn-sm btn-danger">Delete</button></form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- ═══════════ SERVICES TAB ═══════════ -->
    <?php if ($activeTab === 'services'): ?>
        <div class="card" id="addSvcForm" style="display: none; border-left: 4px solid var(--gold);">
            <h3 style="margin-bottom: 20px;">Add New Service</h3>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group"><label>Service Name</label><input type="text" name="name" required></div>
                    <div class="form-group"><label>Price ($)</label><input type="number" step="0.01" name="price" required></div>
                    <div class="form-group"><label>Duration (e.g. 60 mins)</label><input type="text" name="duration" required></div>
                    <div class="form-group"><label>Emoji Icon</label><input type="text" name="icon" placeholder="💄" maxlength="5"></div>
                    <div class="form-group full"><label>Description</label><textarea name="description" rows="3" required></textarea></div>
                    <div class="form-group full"><label><input type="checkbox" name="is_active" checked> Active (visible on site)</label></div>
                </div>
                <button type="submit" name="save_service" class="btn btn-primary" style="margin-top: 15px;">Save Service</button>
                <button type="button" onclick="document.getElementById('addSvcForm').style.display='none'" class="btn" style="background:#eee; margin-top: 15px;">Cancel</button>
            </form>
        </div>

        <div class="svc-admin-grid">
            <?php
            $svcStmt = $pdo->query("SELECT * FROM services ORDER BY id ASC");
            while ($svc = $svcStmt->fetch(PDO::FETCH_ASSOC)):
            ?>
            <div class="svc-admin-card">
                <div class="svc-admin-header">
                    <span class="svc-admin-icon"><?= $svc['icon'] ?></span>
                    <span style="font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; background: <?= $svc['is_active'] ? '#e8f5e9; color:#2e7d32' : '#eee; color:#888' ?>;">
                        <?= $svc['is_active'] ? 'Active' : 'Hidden' ?>
                    </span>
                </div>
                <h4 style="font-size: 1.1rem; margin: 0;"><?= htmlspecialchars($svc['name']) ?></h4>
                <p style="font-size: 0.85rem; color: #666; margin: 0; flex-grow: 1;"><?= htmlspecialchars($svc['description']) ?></p>
                <div style="font-weight: bold; color: var(--gold);">$<?= number_format($svc['price'], 2) ?> &middot; <?= $svc['duration'] ?></div>
                
                <div style="display: flex; gap: 10px; margin-top: 10px; border-top: 1px solid #eee; padding-top: 15px;">
                    <button onclick="editService(<?= htmlspecialchars(json_encode($svc)) ?>)" class="btn btn-sm btn-primary" style="flex: 1;">Edit</button>
                    <form method="POST" onsubmit="return confirm('Delete this service?');" style="flex: 1;">
                        <input type="hidden" name="service_id" value="<?= $svc['id'] ?>">
                        <button type="submit" name="delete_service" class="btn btn-sm btn-danger" style="width: 100%;">Delete</button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="card" id="editSvcForm" style="display: none; border-left: 4px solid var(--gold); margin-top: 30px;">
            <h3 style="margin-bottom: 20px;">Edit Service</h3>
            <form method="POST">
                <input type="hidden" name="service_id" id="edit_id">
                <div class="form-grid">
                    <div class="form-group"><label>Service Name</label><input type="text" name="name" id="edit_name" required></div>
                    <div class="form-group"><label>Price ($)</label><input type="number" step="0.01" name="price" id="edit_price" required></div>
                    <div class="form-group"><label>Duration</label><input type="text" name="duration" id="edit_duration" required></div>
                    <div class="form-group"><label>Emoji Icon</label><input type="text" name="icon" id="edit_icon" maxlength="5"></div>
                    <div class="form-group full"><label>Description</label><textarea name="description" id="edit_description" rows="3" required></textarea></div>
                    <div class="form-group full"><label><input type="checkbox" name="is_active" id="edit_active"> Active (visible on site)</label></div>
                </div>
                <button type="submit" name="save_service" class="btn btn-primary" style="margin-top: 15px;">Update Service</button>
                <button type="button" onclick="document.getElementById('editSvcForm').style.display='none'" class="btn" style="background:#eee; margin-top: 15px;">Cancel</button>
            </form>
        </div>

        <script>
            function editService(svc) {
                document.getElementById('edit_id').value = svc.id;
                document.getElementById('edit_name').value = svc.name;
                document.getElementById('edit_price').value = svc.price;
                document.getElementById('edit_duration').value = svc.duration;
                document.getElementById('edit_icon').value = svc.icon;
                document.getElementById('edit_description').value = svc.description;
                document.getElementById('edit_active').checked = svc.is_active == 1;
                document.getElementById('editSvcForm').style.display = 'block';
                document.getElementById('editSvcForm').scrollIntoView({ behavior: 'smooth' });
            }
        </script>
    <?php endif; ?>

    <!-- ═══════════ SETTINGS TAB ═══════════ -->
    <?php if ($activeTab === 'password'): ?>
        <div class="card" style="max-width: 500px;">
            <h3 style="margin-bottom: 20px;">Change Password</h3>
            <form method="POST">
                <div class="form-group"><label>Current Password</label><input type="password" name="current_password" required></div>
                <div class="form-group"><label>New Password</label><input type="password" name="new_password" required></div>
                <div class="form-group"><label>Confirm New Password</label><input type="password" name="confirm_password" required></div>
                <button type="submit" name="change_password" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Update Password</button>
            </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>