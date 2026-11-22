<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$msg = '';
$msgType = 'success';

// ─────────────────────────────────────────────
// HELPER: Handle File Uploads Safely
// ─────────────────────────────────────────────
function handleUpload($fileInputName, $existingUrl = null) {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return $existingUrl; 
    }

    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $originalName = basename($_FILES[$fileInputName]['name']);
    $cleanName = preg_replace("/[^a-zA-Z0-9.\-_]/", "", str_replace(' ', '_', $originalName));
    $fileName = time() . '_' . $cleanName;
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $targetPath)) {
        return 'uploads/' . $fileName; 
    } else {
        return $existingUrl; 
    }
}

// ─────────────────────────────────────────────
// HANDLE ACTIONS
// ─────────────────────────────────────────────

if (isset($_POST['save_booking'])) {
    $id = $_POST['booking_id'] ?? null;
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $service = $_POST['service'];
    $date = $_POST['booking_date'];
    $time = $_POST['booking_time'];
    $total = $_POST['total_price'];
    $deposit = $_POST['deposit_paid'];
    $status = $_POST['payment_status'];

    if ($id) {
        $stmt = $pdo->prepare("UPDATE bookings SET full_name=?, email=?, phone=?, service=?, booking_date=?, booking_time=?, total_price=?, deposit_paid=?, payment_status=? WHERE id=?");
        $stmt->execute([$name, $email, $phone, $service, $date, $time, $total, $deposit, $status, $id]);
        $msg = "Booking updated.";
    } else {
        $ref = "DF-" . str_pad(rand(1000, 9999), 5, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("INSERT INTO bookings (full_name, email, phone, service, booking_date, booking_time, total_price, deposit_paid, payment_status, booking_reference) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$name, $email, $phone, $service, $date, $time, $total, $deposit, $status, $ref]);
        $msg = "Manual booking created.";
    }
}

if (isset($_POST['delete_booking'])) {
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->execute([(int)$_POST['booking_id']]);
    $msg = "Booking deleted.";
}

if (isset($_POST['save_service'])) {
    $id = $_POST['service_id'] ?? null;
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $dur = $_POST['duration'];
    $icon = $_POST['icon'];
    $active = isset($_POST['is_active']) ? 1 : 0;
    
    $existingMedia = $_POST['existing_media'] ?? null;
    $mediaUrl = handleUpload('media_file', $existingMedia);
    $mediaType = 'image';

    if ($mediaUrl) {
        $ext = strtolower(pathinfo($mediaUrl, PATHINFO_EXTENSION));
        if (in_array($ext, ['mp4', 'webm'])) $mediaType = 'video';
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE services SET name=?, description=?, price=?, duration=?, icon=?, is_active=?, media_url=?, media_type=? WHERE id=?");
        $stmt->execute([$name, $desc, $price, $dur, $icon, $active, $mediaUrl, $mediaType, $id]);
        $msg = "Service updated.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO services (name, description, price, duration, icon, is_active, media_url, media_type) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$name, $desc, $price, $dur, $icon, $active, $mediaUrl, $mediaType]);
        $msg = "Service added.";
    }
}

if (isset($_POST['delete_service'])) {
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([(int)$_POST['service_id']]);
    $msg = "Service deleted.";
}

if (isset($_POST['block_slot'])) {
    $date = $_POST['block_date'];
    $time = $_POST['block_time'] === 'full_day' ? null : $_POST['block_time'];
    $stmt = $pdo->prepare("INSERT INTO blocked_slots (block_date, block_time, reason) VALUES (?, ?, ?)");
    $stmt->execute([$date, $time, $_POST['reason']]);
    $msg = "Time slot blocked.";
}
if (isset($_POST['unblock_slot'])) {
    $stmt = $pdo->prepare("DELETE FROM blocked_slots WHERE id = ?");
    $stmt->execute([(int)$_POST['block_id']]);
    $msg = "Time slot unblocked.";
}

if (isset($_POST['save_content'])) {
    if (isset($_POST['content'])) {
        foreach ($_POST['content'] as $key => $value) {
            $stmt = $pdo->prepare("UPDATE site_content SET content_value = ? WHERE content_key = ?");
            $stmt->execute([$value, $key]);
        }
    }
    
    for ($i = 1; $i <= 6; $i++) {
        $currentGalleryUrl = $_POST['existing_gallery_' . $i] ?? null;
        $newUrl = handleUpload("gallery_$i", $currentGalleryUrl);
        
        if ($newUrl !== $currentGalleryUrl) {
            $stmt = $pdo->prepare("REPLACE INTO site_content (content_key, content_value) VALUES (?, ?)");
            $stmt->execute(["gallery_$i", $newUrl]);
        }
    }
    $msg = "Site content updated.";
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
            $msg = "Password updated successfully.";
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
    <link rel="icon" type="image/svg+xml" href="../assets/images/logo.svg">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* ─────────── ADMIN BASE STYLES ─────────── */
        body { background: #f4f6f9; display: flex; min-height: 100vh; margin: 0; }
        .sidebar { width: 260px; background: var(--black); color: white; padding: 30px 20px; min-height: 100vh; position: fixed; top:0; left:0; display: flex; flex-direction: column; }
        .sidebar h2 { font-family: 'Cinzel', serif; color: var(--gold); margin-bottom: 40px; text-align: center; font-size: 1.6rem; letter-spacing: 2px;}
        .sidebar a { display: flex; align-items: center; gap: 12px; color: #bbb; text-decoration: none; padding: 14px 18px; border-radius: 8px; margin-bottom: 8px; font-weight: 600; transition: all 0.2s; font-size: 0.95rem; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.08); color: var(--gold); }
        .sidebar a svg { width: 18px; height: 18px; stroke-width: 2; }
        
        .main-content { flex: 1; margin-left: 260px; padding: 40px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .top-bar h1 { font-family: 'Cinzel', serif; font-size: 2rem; color: var(--black); margin: 0; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 25px; font-weight: bold; font-size: 0.9rem; }
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
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--gold); box-shadow: 0 0 0 3px rgba(197, 160, 89, 0.1); }
        
        /* Service Cards in Admin */
        .svc-admin-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .svc-admin-card { border: 1px solid #eee; border-radius: 10px; padding: 20px; display: flex; flex-direction: column; gap: 10px; }
        .svc-admin-header { display: flex; justify-content: space-between; align-items: start; }
        
        /* Gallery Admin */
        .gallery-admin-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .gallery-upload-box { border: 2px dashed #ccc; border-radius: 10px; padding: 20px; text-align: center; }
        .gallery-upload-box img { width: 100%; height: 120px; object-fit: cover; border-radius: 6px; margin-bottom: 10px; }

        /* ─────────── BEAUTIFIED SETTINGS TAB ─────────── */
        .settings-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .settings-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border-top: 5px solid var(--gold);
        }
        .settings-card h3 {
            font-family: 'Cinzel', serif;
            font-size: 1.5rem;
            color: var(--black);
            margin-bottom: 10px;
            text-align: center;
        }
        .settings-card p.subtitle {
            text-align: center;
            color: var(--text-gray);
            font-size: 0.9rem;
            margin-bottom: 30px;
        }
        .settings-card .form-group {
            margin-bottom: 25px;
        }
        .settings-card .form-group label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--gold-dark);
        }
        .settings-card .btn-submit {
            width: 100%;
            background: var(--black);
            color: white;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            font-family: 'Cinzel', serif;
            letter-spacing: 1px;
            margin-top: 10px;
        }
        .settings-card .btn-submit:hover {
            background: var(--gold);
        }

        /* Mobile Admin Fix */
        @media (max-width: 768px) {
            body { flex-direction: column; }
            .sidebar { width: 100%; min-height: auto; position: relative; padding: 15px; flex-direction: row; flex-wrap: wrap; justify-content: center; }
            .sidebar h2 { display: none; }
            .sidebar a { margin: 5px; padding: 8px 12px; font-size: 0.8rem; }
            .main-content { margin-left: 0; padding: 15px; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>D'FACEEE</h2>
    
    <!-- SVG Icons Added to Sidebar -->
    <a href="?tab=bookings" class="<?= $activeTab==='bookings'?'active':'' ?>">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
        Bookings
    </a>
    <a href="?tab=availability" class="<?= $activeTab==='availability'?'active':'' ?>">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        Block Time
    </a>
    <a href="?tab=services" class="<?= $activeTab==='services'?'active':'' ?>">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
        Services
    </a>
    <a href="?tab=content" class="<?= $activeTab==='content'?'active':'' ?>">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
        Site Content
    </a>
    <a href="?tab=password" class="<?= $activeTab==='password'?'active':'' ?>">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
        Settings
    </a>
    
    <a href="../index.php" style="margin-top: auto;">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
        View Website
    </a>
    <a href="logout.php" style="color: #e74c3c;">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
        Logout
    </a>
</div>

<div class="main-content">
    <div class="top-bar">
        <h1><?= ucfirst($activeTab) ?></h1>
        <?php if ($activeTab === 'bookings'): ?>
            <button onclick="document.getElementById('addBookingForm').style.display='block'" class="btn btn-primary">+ Manual Booking</button>
        <?php elseif ($activeTab === 'services'): ?>
            <button onclick="document.getElementById('addSvcForm').style.display='block'" class="btn btn-primary">+ Add Service</button>
        <?php endif; ?>
    </div>

    <?php if ($msg): ?>
        <div class="alert <?= $msgType ?>"><?= $msg ?></div>
    <?php endif; ?>

    <!-- BOOKINGS TAB -->
    <?php if ($activeTab === 'bookings'): ?>
        <div class="card table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Ref</th><th>Client</th><th>Service</th><th>Schedule</th><th>Financials</th><th>Status</th><th>Actions</th>
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
                        <td><strong><?= htmlspecialchars($row['full_name']) ?></strong><br><small><?= htmlspecialchars($row['phone']) ?></small></td>
                        <td><?= htmlspecialchars($row['service']) ?></td>
                        <td><?= date('M d, Y', strtotime($row['booking_date'])) ?> <br><small><?= date('h:i A', strtotime($row['booking_time'])) ?></small></td>
                        <td>Total: $<?= $row['total_price'] ?><br><small style="color:#27ae60;">Dep: $<?= $row['deposit_paid'] ?></small></td>
                        <td><span class="status-badge <?= $statusClass ?>"><?= $row['payment_status'] ?></span></td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <button onclick='editBooking(<?= json_encode($row) ?>)' class="btn btn-sm" style="background:#eee;">Edit</button>
                                <?php if($row['payment_status'] !== 'Completed'): ?>
                                    <form method="POST"><input type="hidden" name="booking_id" value="<?= $row['id'] ?>"><input type="hidden" name="status" value="Completed"><button type="submit" name="save_booking" class="btn btn-sm btn-primary">Done</button></form>
                                <?php endif; ?>
                                <form method="POST" onsubmit="return confirm('DELETE permanently?');"><input type="hidden" name="booking_id" value="<?= $row['id'] ?>"><button type="submit" name="delete_booking" class="btn btn-sm btn-danger">X</button></form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="card" id="addBookingForm" style="display: none; border-left: 4px solid var(--gold);">
            <h3 style="margin-bottom: 20px;">Manual Booking</h3>
            <form method="POST">
                <input type="hidden" name="booking_id" id="b_id">
                <div class="form-grid">
                    <div class="form-group"><label>Client Name</label><input type="text" name="full_name" id="b_name" required></div>
                    <div class="form-group"><label>Phone</label><input type="text" name="phone" id="b_phone" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" id="b_email" required></div>
                    <div class="form-group"><label>Service</label>
                        <select name="service" id="b_service" required>
                            <?php 
                            $svcs = $pdo->query("SELECT name, price FROM services")->fetchAll();
                            foreach($svcs as $s) echo "<option value='{$s['name']}' data-price='{$s['price']}'>{$s['name']}</option>";
                            ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Date</label><input type="date" name="booking_date" id="b_date" required></div>
                    <div class="form-group"><label>Time</label><input type="time" name="booking_time" id="b_time" required></div>
                    <div class="form-group"><label>Total ($)</label><input type="number" step="0.01" name="total_price" id="b_total" required></div>
                    <div class="form-group"><label>Deposit ($)</label><input type="number" step="0.01" name="deposit_paid" id="b_dep" required></div>
                    <div class="form-group"><label>Status</label>
                        <select name="payment_status" id="b_status">
                            <option value="Paid">Paid</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="save_booking" class="btn btn-primary" style="margin-top: 15px;">Save Booking</button>
                <button type="button" onclick="document.getElementById('addBookingForm').style.display='none'" class="btn" style="background:#eee; margin-top: 15px;">Cancel</button>
            </form>
        </div>
        <script>
            function editBooking(data) {
                document.getElementById('addBookingForm').style.display = 'block';
                document.getElementById('b_id').value = data.id;
                document.getElementById('b_name').value = data.full_name;
                document.getElementById('b_phone').value = data.phone;
                document.getElementById('b_email').value = data.email;
                document.getElementById('b_service').value = data.service;
                document.getElementById('b_date').value = data.booking_date;
                document.getElementById('b_time').value = data.booking_time;
                document.getElementById('b_total').value = data.total_price;
                document.getElementById('b_dep').value = data.deposit_paid;
                document.getElementById('b_status').value = data.payment_status;
                document.getElementById('addBookingForm').scrollIntoView();
            }
        </script>
    <?php endif; ?>

    <!-- AVAILABILITY TAB -->
    <?php if ($activeTab === 'availability'): ?>
        <div class="card">
            <h3 style="margin-bottom: 20px;">Block a Date or Time</h3>
            <form method="POST" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 150px; margin:0;">
                    <label>Date to Block</label>
                    <input type="date" name="block_date" required>
                </div>
                <div class="form-group" style="flex: 1; min-width: 150px; margin:0;">
                    <label>Time (or Full Day)</label>
                    <select name="block_time">
                        <option value="full_day">Full Day</option>
                        <option value="05:00">5:00 AM</option>
                        <option value="06:00">6:00 AM</option>
                        <option value="07:00">7:00 AM</option>
                        <option value="15:00">3:00 PM</option>
                        <option value="16:00">4:00 PM</option>
                        <option value="17:00">5:00 PM</option>
                        <option value="18:00">6:00 PM</option>
                        <option value="19:00">7:00 PM</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 2; min-width: 200px; margin:0;">
                    <label>Reason (Internal)</label>
                    <input type="text" name="reason" placeholder="e.g., Personal appointment" required>
                </div>
                <button type="submit" name="block_slot" class="btn btn-primary" style="height: 42px;">Block Time</button>
            </form>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 20px;">Currently Blocked</h3>
            <table>
                <thead>
                    <tr><th>Date</th><th>Time</th><th>Reason</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM blocked_slots WHERE block_date >= CURDATE() ORDER BY block_date ASC, block_time ASC");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <tr>
                        <td><strong><?= date('M d, Y', strtotime($row['block_date'])) ?></strong></td>
                        <td>
                            <?php if($row['block_time'] === null): ?>
                                <span class="status-badge status-cancelled">Full Day</span>
                            <?php else: ?>
                                <?= date('h:i A', strtotime($row['block_time'])) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['reason']) ?></td>
                        <td>
                            <form method="POST"><input type="hidden" name="block_id" value="<?= $row['id'] ?>"><button type="submit" name="unblock_slot" class="btn btn-sm btn-danger">Unblock</button></form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- SERVICES TAB -->
    <?php if ($activeTab === 'services'): ?>
        <div class="card" id="addSvcForm" style="display: none; border-left: 4px solid var(--gold);">
            <h3 style="margin-bottom: 20px;">Add Service</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="service_id" id="s_id">
                <div class="form-grid">
                    <div class="form-group"><label>Service Name</label><input type="text" name="name" id="s_name" required></div>
                    <div class="form-group"><label>Price ($)</label><input type="number" step="0.01" name="price" id="s_price" required></div>
                    <div class="form-group"><label>Duration</label><input type="text" name="duration" id="s_dur" required></div>
                    <div class="form-group"><label>Icon Key (soft-glam, bridal-glam, etc.)</label><input type="text" name="icon" id="s_icon" placeholder="default" required></div>
                    <div class="form-group full"><label>Description</label><textarea name="description" id="s_desc" rows="3" required></textarea></div>
                    <div class="form-group full">
                        <label>Upload Media (Image or Video)</label>
                        <input type="file" name="media_file" accept="image/*,video/*">
                        <input type="hidden" name="existing_media" id="s_existing_media">
                        <div id="mediaPreview" style="margin-top: 10px;"></div>
                    </div>
                    <div class="form-group full"><label><input type="checkbox" name="is_active" id="s_active" checked> Active (visible on site)</label></div>
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
                    <span class="svc-admin-icon" style="color: var(--gold); width: 30px; height: 30px;">
                        <?php require_once '../includes/icons.php'; echo getServiceIcon($svc['icon']); ?>
                    </span>
                    <span style="font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; background: <?= $svc['is_active'] ? '#e8f5e9; color:#2e7d32' : '#eee; color:#888' ?>;">
                        <?= $svc['is_active'] ? 'Active' : 'Hidden' ?>
                    </span>
                </div>
                <h4 style="font-size: 1.1rem; margin: 0;"><?= htmlspecialchars($svc['name']) ?></h4>
                <?php if ($svc['media_url']): ?>
                    <small style="color: var(--gold);">✓ Media Uploaded</small>
                <?php else: ?>
                    <small style="color: #888;">No media</small>
                <?php endif; ?>
                <div style="font-weight: bold; color: var(--gold);">$<?= number_format($svc['price'], 2) ?></div>
                
                <div style="display: flex; gap: 10px; margin-top: 10px; border-top: 1px solid #eee; padding-top: 15px;">
                    <button onclick='editService(<?= json_encode($svc) ?>)' class="btn btn-sm btn-primary" style="flex: 1;">Edit</button>
                    <form method="POST" onsubmit="return confirm('Delete this service?');" style="flex: 1;">
                        <input type="hidden" name="service_id" value="<?= $svc['id'] ?>">
                        <button type="submit" name="delete_service" class="btn btn-sm btn-danger" style="width: 100%;">Delete</button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <script>
            function editService(svc) {
                document.getElementById('addSvcForm').style.display = 'block';
                document.getElementById('s_id').value = svc.id;
                document.getElementById('s_name').value = svc.name;
                document.getElementById('s_price').value = svc.price;
                document.getElementById('s_dur').value = svc.duration;
                document.getElementById('s_icon').value = svc.icon;
                document.getElementById('s_desc').value = svc.description;
                document.getElementById('s_active').checked = svc.is_active == 1;
                document.getElementById('s_existing_media').value = svc.media_url || '';
                
                let preview = document.getElementById('mediaPreview');
                if (svc.media_url) {
                    preview.innerHTML = `<img src="../${svc.media_url}" style="height: 60px; border-radius: 4px;"> <br><small>Current media</small>`;
                } else {
                    preview.innerHTML = '';
                }
                document.getElementById('addSvcForm').scrollIntoView({ behavior: 'smooth' });
            }
        </script>
    <?php endif; ?>

    <!-- CONTENT TAB -->
    <?php if ($activeTab === 'content'): ?>
        <?php
        $contentStmt = $pdo->query("SELECT * FROM site_content");
        $content = [];
        while ($row = $contentStmt->fetch(PDO::FETCH_ASSOC)) $content[$row['content_key']] = $row['content_value'];
        ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="card">
                <h3 style="margin-bottom: 20px;">Hero Section</h3>
                <div class="form-group">
                    <label>Hero Subtitle / Description</label>
                    <textarea name="content[hero_text]" rows="3"><?= htmlspecialchars($content['hero_text'] ?? 'Where looks says it all. Experience luxury beauty treatments tailored to your unique glow.') ?></textarea>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-bottom: 20px;">My Work Gallery (Upload up to 6 images)</h3>
                <div class="gallery-admin-grid">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <div class="gallery-upload-box">
                            <?php if (!empty($content["gallery_$i"])): ?>
                                <img src="../<?= htmlspecialchars($content["gallery_$i"]) ?>" alt="Gallery <?= $i ?>">
                                <input type="hidden" name="existing_gallery_<?= $i ?>" value="<?= htmlspecialchars($content["gallery_$i"]) ?>">
                            <?php else: ?>
                                <div style="height: 120px; display: flex; align-items: center; justify-content: center; background: #eee; border-radius: 6px; margin-bottom: 10px; color: #aaa;">Empty Slot</div>
                            <?php endif; ?>
                            <label style="font-size: 0.8rem; font-weight: bold;">Upload Image <?= $i ?></label>
                            <input type="file" name="gallery_<?= $i ?>" accept="image/*" style="font-size: 0.8rem; margin-top: 5px;">
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <button type="submit" name="save_content" class="btn btn-primary" style="padding: 15px 30px; font-size: 1rem;">Save All Content</button>
        </form>
    <?php endif; ?>

    <!-- SETTINGS TAB (BEAUTIFIED) -->
    <?php if ($activeTab === 'password'): ?>
        <div class="settings-container">
            <div class="settings-card">
                <h3>Security Settings</h3>
                <p class="subtitle">Update your admin password to keep your account secure.</p>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn-submit">Update Password</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>