<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "nexuinco_root";
$password = "NexuinChat1234!!"; // Default password for XAMPP MySQL is empty
$database = "nexuinco_chatapp";

$site_key = 'f14fd478-9e4d-4fda-a352-a176d047346f';
$secret_key = 'ES_a7a9065963ee4ec996c2f385ce0eefe6';


// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize login and registration error messages
$login_error = "";
$registration_error = "";

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Sanitize input to prevent SQL injection (not needed for password since it's hashed)
    $username = $conn->real_escape_string($username);

    // Retrieve user record from database
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: chat.php'); // Redirect to chat page after successful login
            exit();
        } else {
            $login_error = "Invalid username or password";
        }
    } else {
        $login_error = "User not found";
    }
}

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    // Validate hCaptcha response
    if (isset($_POST['h-captcha-response'])) {
        $captcha_response = $_POST['h-captcha-response'];
        $url = 'https://hcaptcha.com/siteverify';
        $data = array(
            'secret' => $secret_key,
            'response' => $captcha_response
        );

        // Send POST request to verify hCaptcha response
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $verify = file_get_contents($url, false, $context);
        $captcha_success = json_decode($verify);

        if ($captcha_success->success) {
            // hCaptcha verification passed, continue with user registration
            $username = $_POST['reg_username'];
            $password = $_POST['reg_password'];

            // Validate inputs
            if (empty($username) || empty($password)) {
                $registration_error = "Username and password are required";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user into database with hashed password
                $sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";
                if ($conn->query($sql) === TRUE) {
                    $registration_success = "User registered successfully";
                } else {
                    $registration_error = "Error registering user: " . $conn->error;
                }
            }
        } else {
            // hCaptcha verification failed
            $registration_error = "Please complete the hCaptcha verification";
        }
    } else {
        // hCaptcha response not received
        $registration_error = "Please complete the hCaptcha verification";
    }
}

// Close connection
$conn->close();
?>
<script>
    
   
</script>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Registration</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS for dark mode and additional styling -->
    <style>
        body {
            background-color: #f8f9fa; /* Light mode background color */
            color: #fff; /* Light mode text color */
            transition: background-color 0.3s, color 0.3s;
        }

        body.dark-mode {
            background-color: #333; /* Dark mode background color */
            color: #fff; /* Dark mode text color */
            transition: background-color 0.3s, color 0.3s;
        }

        .form-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            background-color: #343a40; /* Dark mode form background color */
        }

        .form-container label,
        .form-container input,
        .form-container button {
            color: #fff; /* White text color for form elements in dark mode */
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .toggle-form {
            text-align: center;
            margin-top: 20px;
        }

        .black{
            color: black !important;
        }
    </style>
</head>
<body>
    <div class="container">
    
        <div class="form-container" id="loginForm">
        <center><h2>Nexuin<strong>Chat</strong></h2></center>
            
            <h2>Login</h2>
            <?php if ($login_error !== "") : ?>
                <div class="alert alert-danger"><?php echo $login_error; ?></div>
            <?php endif; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <input type="hidden" name="login">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control black" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div id="qrCodeContainer"></div> <!-- This will hold the QR code -->
                <img id="qrCodeImage" alt="QR Code">
                <!-- Hidden input field for session ID -->
                <input type="hidden" name="session_id" id="session_id">
                <button type="submit" class="btn btn-primary">Login with QR-Code</button>
            </form>
            <div class="toggle-form">
                <p>Don't have an account? <a href="#" onclick="toggleRegistration()">Register here</a></p>
            </div>
            
        </div>

        <div class="form-container" style="display: none;" id="registrationForm">
    <center><h2>Nexuin<strong>Chat</strong></h2></center>
    <h2>Register</h2>
    <?php if ($registration_error !== "") : ?>
        <div class="alert alert-danger"><?php echo $registration_error; ?></div>
    <?php endif; ?>
    <?php if (isset($registration_success)) : ?>
        <div class="alert alert-success"><?php echo $registration_success; ?></div>
    <?php endif; ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <input type="hidden" name="register">
        <div class="mb-3">
            <label for="reg_username" class="form-label">Username</label>
            <input type="text" class="form-control black" id="reg_username" name="reg_username" required>
        </div>
        <div class="mb-3">
            <label for="reg_password" class="form-label">Password</label>
            <input type="password" class="form-control black" id="reg_password" name="reg_password" required>
        </div>
        <!-- hCaptcha widget -->
        <div class="mb-3 h-captcha" id="h-captcha" data-sitekey="<?php echo $site_key; ?>"></div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
    <div class="toggle-form">
        <p>Already have an account? <a href="#" onclick="toggleRegistration()">Login here</a></p>
    </div>
</div>


<!-- hCaptcha JavaScript -->
<script src="https://hcaptcha.com/1/api.js" async defer></script>

    <!-- Bootstrap JS and Font Awesome JS for icons -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <!-- Custom JavaScript for dark mode toggle and form toggle -->
    <script>
        function toggleDarkMode() {
            const body = document.body;
            body.classList.toggle('dark-mode');

            // Save dark mode preference to localStorage
            const isDarkMode = body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDarkMode ? 'enabled' : 'disabled');
        }

        // Automatically set dark mode based on system preference
        const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (prefersDarkMode) {
            toggleDarkMode();
        }

        function toggleRegistration() {
            const loginForm = document.getElementById('loginForm');
            const registrationForm = document.getElementById('registrationForm');
            if (loginForm && registrationForm) {
                loginForm.style.display = loginForm.style.display === 'none' ? 'block' : 'none';
                registrationForm.style.display = registrationForm.style.display === 'none' ? 'block' : 'none';
            } else {
                console.error('Login form or registration form not found');
            }
        }



    </script>
</body>
</html>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Generate a random session ID (you can replace this with your own logic)
        var sessionId = Math.random().toString(36).substr(2, 8);

        // Create QR code instance
        var qr = new QRious({
            element: document.getElementById('qrCodeContainer'), // Container element for the QR code (optional)
            value: sessionId, // Text or URL to encode into QR code
            size: 200 // Size of the QR code (width and height)
        });

        // Update the <img> tag with the QR code image
        document.getElementById('qrCodeImage').src = qr.toDataURL();

       
        document.getElementById('session_id').value = sessionId;
        
    });
</script>


    <!-- Include qrious library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>

    <style>
     /* Two-column layout */
     .two-column-layout {
            display: flex;
            justify-content: space-between;
        }

        /* Left and right sections */
        .left-section {
            width: 60%; /* Adjust width of left section */
        }

        .right-section {
            width: 35%; /* Adjust width of right section */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .two-column-layout {
                flex-direction: column; /* Stack columns on small screens */
            }

            .left-section,
            .right-section {
                width: 100%; /* Full width on small screens */
            }
        }
</style>
<?php
// Database configuration
$servername = "localhost";
$username = "nexuinco_root";
$password = "NexuinChat1234!!"; // Default password for XAMPP MySQL is empty
$database = "nexuinco_chatapp";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize login error message
$login_error = "";

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['session_id'])) {
        $session_id = $_POST['session_id'];
        
        // Query the database to find the session ID
        $sql = "SELECT user_id FROM sessions WHERE session = ?";
        
        // Prepare SQL statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $session_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Session ID found, log in the user
            $session_data = $result->fetch_assoc();
            $_SESSION['user_id'] = $session_data['user_id'];
            $stmt->close();
            echo '<script>window.location.href = "chat.php";</script>';
            exit();
        } else {
            // Invalid session ID, display error message
            $login_error = "Invalid session ID";
        }
        $stmt->close();
    }
}
?>