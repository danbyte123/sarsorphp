<?php
require_once 'db_conn.php';
require_once 'session.php';

if (!isClient()) {
    header("Location: index.php");
    exit();
}

$service_id = $_GET['service_id'] ?? 0;

// Get service details
$stmt = $pdo->prepare("
    SELECT s.*, u.name as provider_name, u.service_type 
    FROM services s 
    JOIN users u ON s.user_id = u.id 
    WHERE s.id = ?
");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    header("Location: client_dashboard.php");
    exit();
}

// Handle booking submission
if ($_POST['action'] ?? '' === 'book_service') {
    $booking_date = $_POST['booking_date'];
    $notes = $_POST['notes'];
    
    $stmt = $pdo->prepare("INSERT INTO bookings (client_id, service_id, user_id, booking_date, total_price, notes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([getClientId(), $service_id, $service['user_id'], $booking_date, $service['price'], $notes]);
    
    $success = "Booking request sent successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Service - Home Services</title>
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
        
        .btn {
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .service-details {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .service-details h2 {
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .service-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .info-item {
            padding: 0.5rem 0;
        }
        
        .info-label {
            font-weight: bold;
            color: #333;
        }
        
        .info-value {
            color: #666;
        }
        
        .price-highlight {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
            text-align: center;
            margin: 1rem 0;
        }
        
        .booking-form {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .booking-form h3 {
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: bold;
        }
        
        input, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        input:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        textarea {
            height: 100px;
            resize: vertical;
        }
        
        .btn-primary {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
        }
        
        .success {
            background: #51cf66;
            color: white;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: #667eea;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>Book Service</h1>
            <a href="client_dashboard.php" class="btn">← Back to Dashboard</a>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($success)): ?>
            <div class="success">
                <?php echo $success; ?>
                <br><br>
                <a href="client_dashboard.php" style="color: white; text-decoration: underline;">Return to Dashboard</a>
            </div>
        <?php endif; ?>
        
        <div class="service-details">
            <h2><?php echo htmlspecialchars($service['service_name']); ?></h2>
            
            <div class="service-info">
                <div class="info-item">
                    <div class="info-label">Provider:</div>
                    <div class="info-value"><?php echo htmlspecialchars($service['provider_name']); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Category:</div>
                    <div class="info-value"><?php echo htmlspecialchars($service['service_type']); ?></div>
                </div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Description:</div>
                <div class="info-value"><?php echo htmlspecialchars($service['description']); ?></div>
            </div>
            
            <div class="price-highlight">
                $<?php echo number_format($service['price'], 2); ?>
            </div>
        </div>
        
        <?php if (!isset($success)): ?>
        <div class="booking-form">
            <h3>Book This Service</h3>
            
            <form method="POST">
                <input type="hidden" name="action" value="book_service">
                
                <div class="form-group">
                    <label for="booking_date">Preferred Date & Time:</label>
                    <input type="datetime-local" name="booking_date" id="booking_date" required min="<?php echo date('Y-m-d\TH:i'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="notes">Additional Notes (Optional):</label>
                    <textarea name="notes" id="notes" placeholder="Any specific requirements or details..."></textarea>
                </div>
                
                <button type="submit" class="btn-primary">Send Booking Request</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>