<?php
session_start();

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

// Initialize user data
$userData = null;

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Prepare and execute SQL query to fetch user data
    $stmt = $conn->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch user data from the result
    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
    }

    // Close statement
    $stmt->close();
}

// Handle logout
if (isset($_GET['logout'])) {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to the homepage
    header('Location: index.php');
    exit();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexuinChat - Impressum</title>
    <title>Document</title>
   <!-- Bootstrap CSS -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
   <!-- Font Awesome CSS for icons -->
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
   <!-- Custom CSS -->
   <style>
       body {
           background-color: #f8f9fa; /* Light mode background color */
           color: #212529; /* Light mode text color */
           padding-top: 20px;
       }

       .container {
           margin: 0 auto;
       }

       .navbar {
           display: flex;
           justify-content: space-between;
           align-items: center;
           padding: 10px 20px;
           background-color: #007bff; /* Bootstrap primary color */
           color: #fff;
       }

       .profile-info {
           display: flex;
           align-items: center;
           position: relative;
           cursor: pointer;
       }

       .profile-info img {
           width: 40px;
           height: 40px;
           border-radius: 50%;
           object-fit: cover;
           margin-right: 10px;
       }

       .dropdown-menu {
           position: absolute;
           top: 100%;
           right: 0;
           z-index: 1000;
           min-width: 150px;
           padding: 5px 0;
           text-align: center;
           background-color: #fff;
           box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
           border-radius: 4px;
           display: none;
       }

       .profile-info:hover .dropdown-menu {
           display: block;
       }

       .dropdown-menu button {
           width: 100%;
           padding: 8px;
           background-color: transparent;
           border: none;
           color: #212529;
           cursor: pointer;
           transition: background-color 0.3s;
       }

       .dropdown-menu button:hover {
           background-color: #f8f9fa;
       }

       .login-btn {
           background-color: #28a745; /* Bootstrap success color */
           border: none;
           color: #fff;
           padding: 8px 16px;
           border-radius: 4px;
           text-transform: uppercase;
           font-weight: bold;
           cursor: pointer;
       }

       .login-btn:hover {
           background-color: #218838; /* Darker shade of success color */
       }

       .images{
           max-height: 200px;
       }

       #carouselExampleAutoplaying .carousel-item img {
           max-height: 300px; /* Adjust the maximum height of carousel images */
           object-fit: cover;
       }


/* Hero Section Styles */
.hero {
           display: flex;
           justify-content: center;
           align-items: center;
           height: 80vh;
           background-color: #f8f9fa;
           color: #212529;
           text-align: center;
       }

       .hero-content {
           max-width: 800px;
           padding: 0 20px;
       }

       .hero h2 {
           font-size: 3rem;
           margin-bottom: 20px;
       }

       .hero p {
           font-size: 1.2rem;
           margin-bottom: 40px;
       }

       .hero-btn {
           padding: 10px 30px;
           font-size: 1.2rem;
           text-transform: uppercase;
           font-weight: bold;
           border: 2px solid #007bff;
           border-radius: 30px;
           color: #007bff;
           cursor: pointer;
           transition: all 0.3s ease;
       }

       .hero-btn:hover {
           background-color: #007bff;
           color: #fff;
       }

       /* Animation Styles */
       @keyframes fadeIn {
           0% { opacity: 0; transform: translateY(-20px); }
           100% { opacity: 1; transform: translateY(0); }
       }

       .animated {
           animation: fadeIn 1s ease forwards;
       }











       .test {
        padding: 10px 20px;
        align-items: left;
    }

    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body>

<nav class="navbar">
        <div>
            <h1>NexuinChat</h1>
        </div>
        <a href="Impressum.php" stlye="color: white;">Impressum</a>
        <div class="profile-info">
            <?php if ($userData): ?>
                <img src="<?php echo $userData['profile_picture']; ?>" alt="Profile Picture">
                <span><?php echo $userData['username']; ?></span>
                <div class="dropdown-menu">
                    <button class="dropdown-item" onclick="logout()">Logout</button>
                </div>
            <?php else: ?>
                <button class="login-btn" onclick="location.href='login.php'">Login</button>
            <?php endif; ?>
        </div>
    </nav>



       <div class="test">
        <h3>Impressum</h3>

        <p>Informationen und Offenlegung gemäß §5 (1) ECG, § 25 MedienG, § 63 GewO und § 14 UGB</p>
        
        <p>Webseitenbetreiber: Harry Scherz, Jonas Reisner, Julia Rußner</p>
        
        <p>Anschrift: Beispielstraße 1 1234 Beispielort</p>
        
        <p>UID-Nr: ATU12345678</p>
        
        <p>Gewerbeaufsichtbehörde: Bezirkshauptmannschaft Beispielort</p>
        
        <p>Mitgliedschaften: </p>
        
        <h3>Kontaktdaten:</h3>
        <p>Telefon: +4349900700696</p>
        <p>Email: info@nexuin.com</p>
        <p>Fax: +4349900700696</p>
        
        <p>Anwendbare Rechtsvorschrift: www.ris.bka.gv.at</p>
        
        <p>Berufsbezeichnung: Softwareunternehmen</p>
        
        <p>Online Streitbeilegung: Verbraucher, welche in Österreich oder in einem sonstigen Vertragsstaat der ODR-VO niedergelassen sind, haben die Möglichkeit Probleme bezüglich dem entgeltlichen Kauf von Waren oder Dienstleistungen im Rahmen einer Online-Streitbeilegung (nach OS, AStG) zu lösen. Die Europäische Kommission stellt eine Plattform hierfür bereit: <a href="https://ec.europa.eu/consumers/odr">https://ec.europa.eu/consumers/odr</a></p>
        
        <p>Urheberrecht: Die Inhalte dieser Webseite unterliegen, soweit dies rechtlich möglich ist, diversen Schutzrechten (z.B dem Urheberrecht). Jegliche Verwendung/Verbreitung von bereitgestelltem Material, welche urheberrechtlich untersagt ist, bedarf schriftlicher Zustimmung des Webseitenbetreibers.</p>
        
        <p>Haftungsausschluss: Trotz sorgfältiger inhaltlicher Kontrolle übernimmt der Webseitenbetreiber dieser Webseite keine Haftung für die Inhalte externer Links. Für den Inhalt der verlinkten Seiten sind ausschließlich deren Betreiber verantwortlich. Sollten Sie dennoch auf ausgehende Links aufmerksam werden, welche auf eine Webseite mit rechtswidriger Tätigkeit/Information verweisen, ersuchen wir um dementsprechenden Hinweis, um diese nach § 17 Abs. 2 ECG umgehend zu entfernen.
        Die Urheberrechte Dritter werden vom Betreiber dieser Webseite mit größter Sorgfalt beachtet. Sollten Sie trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, bitten wir um einen entsprechenden Hinweis. Bei Bekanntwerden derartiger Rechtsverletzungen werden wir den betroffenen Inhalt umgehend entfernen.</p>
        
        <p>Quelle: fairesRecht.at</p>
       </div> 

    

    <!-- Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    


    <!--footer-->
    <br>
    <footer style="background-color: #36393f; color: #fff; padding: 40px 20px; text-align: center;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <div style="font-size: 24px; font-weight: 300; margin-bottom: 30px;">
            Nexuin Chat, available everywhere you are
        </div>
            <button onclick="location.href='login.php'" style="background-color: #007bff; color: #fff; padding: 10px 20px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin-left: 10px;">
                Try Nexuin Chat now, it's free
            </button>
       
        <div style="color: #8e9297; margin-bottom: 20px;">
            No download required!
        </div>
        <div style="background-image: url('devices.png'); width: 100%; height: 192px; background-size: contain; background-repeat: no-repeat; background-position: center;"></div>
    </div>
    <nav style="border-top: 1px solid rgba(255, 255, 255, 0.1); margin-top: 40px; padding-top: 20px; font-size: 14px;">
        <a href="#" style="color: #8e9297; text-decoration: none; margin-right: 20px;">Help</a>
        <a href="#" style="color: #8e9297; text-decoration: none; margin-right: 20px;">Blog</a>
        <a href="#" style="color: #8e9297; text-decoration: none; margin-right: 20px;">Feedback</a>
        <a href="#" style="color: #8e9297; text-decoration: none; margin-right: 20px;">Terms of Service</a>
        <a href="#" style="color: #8e9297; text-decoration: none; margin-right: 20px;">Company</a>
        <a href="#" style="color: #8e9297; text-decoration: none;">Jobs</a>
    </nav>
    <ul style="display: flex; justify-content: center; margin-top: 20px;">
        <li style="opacity: 0.7; margin: 0 10px;">
            <a href="http://twitter.com/" style="color: #8e9297; text-decoration: none;" target="_blank">
                <i class="fab fa-twitter" style="margin-right: 5px;"></i> Twitter
            </a>
        </li>
        <li style="opacity: 0.7; margin: 0 10px;">
            <a href="http://facebook.com/" style="color: #8e9297; text-decoration: none;" target="_blank">
                <i class="fab fa-facebook-f" style="margin-right: 5px;"></i> Facebook
            </a>
        </li>
    </ul>
    <div style="font-size: 12px; text-align: center; margin-top: 20px;">
        Nexuin Chat © 2024 All Rights Reserved
    </div>
</footer>
</body>
</html>

<script>
    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'index.php?logout';
        }
    }
</script>