<?php
require_once 'db_conn.php';
require_once 'session.php';

if (!isUser()) {
    header("Location: index.php");
    exit();
}

// Handle logout
if ($_GET['action'] ?? '' === 'logout') {
    logout();
}

// Handle service addition
if ($_POST['action'] ?? '' === 'add_service') {
    $service_name = $_POST['service_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    
    $stmt = $pdo->prepare("INSERT INTO services (user_id, service_name, description, price) VALUES (?, ?, ?, ?)");
    $stmt->execute([getUserId(), $service_name, $description, $price]);
    
    $success = "Service added successfully!";
}

// Handle booking status update
if ($_POST['action'] ?? '' === 'update_booking') {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$status, $booking_id, getUserId()]);
    
    $success = "Booking status updated!";
}

// Get provider's services
$stmt = $pdo->prepare("SELECT * FROM services WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([getUserId()]);
$services = $stmt->fetchAll();

// Get provider's bookings
$stmt = $pdo->prepare("
    SELECT b.*, s.service_name, c.name as client_name 
    FROM bookings b 
    JOIN services s ON b.service_id = s.id 
    JOIN clients c ON b.client_id = c.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
");
$stmt->execute([getUserId()]);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Provider Dashboard - Home Services</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 1rem;
        }
        
        .header h1 {
            font-size: 1.5rem;
        }
        
        .header-nav {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .section {
            background: white;
            margin-bottom: 2rem;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section h2 {
            color: #333;
            margin-bottom: 1rem;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: bold;
        }
        
        input, textarea, select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        textarea {
            height: 80px;
            resize: vertical;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-primary:hover {
            background: #5a6fd8;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .service-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1rem;
            background: #f8f9fa;
        }
        
        .service-card h3 {
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .service-card p {
            color: #666;
            margin-bottom: 0.5rem;
        }
        
        .service-price {
            font-weight: bold;
            color: #333;
            font-size: 1.1rem;
        }
        
        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .bookings-table th,
        .bookings-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .bookings-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .status {
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .status.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status.confirmed {
            background: #d4edda;
            color: #155724;
        }
        
        .status.completed {
            background: #cce5ff;
            color: #004085;
        }
        
        .status.cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-select {
            padding: 0.25rem;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .btn-small {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .success {
            background: #51cf66;
            color: white;
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .no-data {
            text-align: center;
            color: #666;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>Welcome, <?php echo $_SESSION['user_name']; ?>!</h1>
            <div class="header-nav">
                <span>Service Provider Dashboard</span>
                <a href="?action=logout" class="btn">Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="section">
            <h2>Add New Service</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_service">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="service_name">Service Name:</label>
                        <input type="text" name="service_name" id="service_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price ($):</label>
                        <input type="number" name="price" id="price" step="0.01" min="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea name="description" id="description" required></textarea>
                </div>
                
                <button type="submit" class="btn-primary">Add Service</button>
            </form>
        </div>
        
        <div class="section">
            <h2>My Services</h2>
            <?php if (empty($services)): ?>
                <div class="no-data">You haven't added any services yet.</div>
            <?php else: ?>
                <div class="services-grid">
                    <?php foreach ($services as $service): ?>
                        <div class="service-card">
                            <h3><?php echo htmlspecialchars($service['service_name']); ?></h3>
                            <p><?php echo htmlspecialchars($service['description']); ?></p>
                            <div class="service-price">$<?php echo number_format($service['price'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>Booking Requests</h2>
            <?php if (empty($bookings)): ?>
                <div class="no-data">No booking requests yet.</div>
            <?php else: ?>
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['client_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['service_name']); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($booking['booking_date'])); ?></td>
                                <td>$<?php echo number_format($booking['total_price'], 2); ?></td>
                                <td><span class="status <?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_booking">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <select name="status" class="status-select">
                                            <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="completed" <?php echo $booking['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" class="btn-small">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>