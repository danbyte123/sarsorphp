<?php
require_once 'db_conn.php';
require_once 'session.php';

if ($_POST['action'] ?? '' === 'register') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $service_type = $_POST['service_type'];
    $hourly_rate = $_POST['hourly_rate'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, address, service_type, hourly_rate) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $phone, $address, $service_type, $hourly_rate]);
        
        $success = "Registration successful! You can now login.";
    } catch(PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Email already exists!";
        } else {
            $error = "Registration failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Provider Registration - Home Services</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 0.5rem;
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
        
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.2s;
            margin-bottom: 1rem;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .links {
            text-align: center;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
        }
        
        .error {
            background: #ff6b6b;
            color: white;
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .success {
            background: #51cf66;
            color: white;
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Service Provider Registration</h1>
            <p>Join as a service provider to offer your services</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="action" value="register">
            
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" name="name" id="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" name="phone" id="phone">
            </div>
            
            <div class="form-group">
                <label for="service_type">Service Type:</label>
                <select name="service_type" id="service_type" required>
                    <option value="">Select Service Type</option>
                    <option value="Plumber">Plumber</option>
                    <option value="Electrician">Electrician</option>
                    <option value="Cleaner">Cleaner</option>
                    <option value="Handyman">Handyman</option>
                    <option value="Painter">Painter</option>
                    <option value="Gardener">Gardener</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="hourly_rate">Hourly Rate ($):</label>
                <input type="number" name="hourly_rate" id="hourly_rate" step="0.01" min="0">
            </div>
            
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea name="address" id="address"></textarea>
            </div>
            
            <button type="submit" class="btn">Register</button>
        </form>
        
        <div class="links">
            <a href="index.php">← Back to Login</a>
        </div>
    </div>
</body>
</html>