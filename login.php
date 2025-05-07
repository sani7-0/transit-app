<?php
session_start();
$servername = "sql7.freesqldatabase.com";
$username = "sql7777349";
$password = "8Iib6bgQeK";  // Replace this once it finishes loading
$database = "sql7777349";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Both fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password, route FROM bus_drivers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $name, $db_password, $db_route);
            $stmt->fetch();

            if ($password === $db_password) {
                // ✅ Set required session values for update_location.php
                $_SESSION['driver_id'] = $id;
                $_SESSION['driver_name'] = $name;
                $_SESSION['driver_route'] = $db_route;

                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Driver not found.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transit Driver Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600&display=swap" rel="stylesheet">
    <link rel="manifest" href="/manifest.json">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #f0f5ff 0%, #f8f0ff 100%);
        }

        .login-container {
            background: white;
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 420px;
            position: relative;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        h2 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .gradient-bar {
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #4f46e5 0%, #8b5cf6 100%);
            margin: 0 auto 1.5rem;
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            color: #4a5568;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 0.9rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: #4f46e5;
        }

        button {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #4f46e5 0%, #8b5cf6 100%);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, opacity 0.2s ease;
        }

        button:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }

        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            text-align: center;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 1.5rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
        }
        .client-redirect {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }

        .client-redirect a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .client-redirect a:hover {
            color: #8b5cf6;
            text-decoration: underline;
        }

        .client-redirect svg {
            width: 14px;
            height: 14px;
            stroke: currentColor;
        }

    </style>
</head>
<body>
    <div class="login-container">
        <div class="header">
            <h2>Driver Portal</h2>
            <div class="gradient-bar"></div>
        </div>
        <script>
  if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register("/sw.js");
  }
</script>

        <?php if (!empty($error)) echo "<div class='error-message'>$error</div>"; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit">Sign In</button>
        </form>

        <div class="client-redirect">
            <a href="client.html">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Not a driver? Go to client side</span>
            </a>
        </div>
    </div>

</body>
</html>