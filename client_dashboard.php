<?php
require_once 'db_conn.php';
require_once 'session.php';

if (!isClient()) {
    header("Location: index.php");
    exit();
}

// Handle logout
if ($_GET['action'] ?? '' === 'logout') {
    logout();
}

// Get available services
$stmt = $pdo->prepare("
    SELECT s.*, u.name as provider_name, u.service_type, u.hourly_rate 
    FROM services s 
    JOIN users u ON s.user_id = u.id 
    ORDER BY s.created_at DESC
");
$stmt->execute();
$services = $stmt->fetchAll();

// Get client's bookings
$stmt = $pdo->prepare("
    SELECT b.*, s.service_name, u.name as provider_name 
    FROM bookings b 
    JOIN services s ON b.service_id = s.id 
    JOIN users u ON b.user_id = u.id 
    WHERE b.client_id = ? 
    ORDER BY b.created_at DESC
");
$stmt->execute([getClientId()]);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - Home Services</title>
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
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }
        
        .service-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1rem;
            transition: transform 0.2s;
        }
        
        .service-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
        
        .btn-book {
            background: #667eea;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 0.5rem;
        }
        
        .btn-book:hover {
            background: #5a6fd8;
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
            <h1>Welcome, <?php echo $_SESSION['client_name']; ?>!</h1>
            <div class="header-nav">
                <span>Client Dashboard</span>
                <a href="?action=logout" class="btn">Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="section">
            <h2>Available Services</h2>
            <?php if (empty($services)): ?>
                <div class="no-data">No services available at the moment.</div>
            <?php else: ?>
                <div class="services-grid">
                    <?php foreach ($services as $service): ?>
                        <div class="service-card">
                            <h3><?php echo htmlspecialchars($service['service_name']); ?></h3>
                            <p><strong>Provider:</strong> <?php echo htmlspecialchars($service['provider_name']); ?></p>
                            <p><strong>Category:</strong> <?php echo htmlspecialchars($service['service_type']); ?></p>
                            <p><?php echo htmlspecialchars($service['description']); ?></p>
                            <div class="service-price">$<?php echo number_format($service['price'], 2); ?></div>
                            <a href="book_service.php?service_id=<?php echo $service['id']; ?>" class="btn-book">Book Service</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>My Bookings</h2>
            <?php if (empty($bookings)): ?>
                <div class="no-data">You haven't made any bookings yet.</div>
            <?php else: ?>
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Provider</th>
                            <th>Date</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['service_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['provider_name']); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($booking['booking_date'])); ?></td>
                                <td>$<?php echo number_format($booking['total_price'], 2); ?></td>
                                <td><span class="status <?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>