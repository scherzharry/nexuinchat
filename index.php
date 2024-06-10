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
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <title>NexuinChat - Home</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <meta property="og:title" content=Nexuin Chat>
<meta property="og:site_name" content="">
<meta property="og:url" content=http://nexuin.com>
<meta property="og:description" content=Nexuin Chat is a free web based chat app that fits all your needs, with an easy but powerful interface, no limitations what so ever and an android app as well! Register now!>
<meta property="og:type" content="">
<meta property="og:image" content="">
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

    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body id="body">
<div class="container">
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
                    <button class="login-btn" onclick="toggleDashboard()">Open Dashboard</button>
                    <button class="dropdown-item" onclick="logout()">Logout</button>
                </div>
            <?php else: ?>
                <button class="login-btn" onclick="location.href='login.php'">Login</button>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content animated">
            <h2>Welcome to NexuinChat</h2>
            <p>Finally, a Chat App that doesn't suck!</p>
            <button class="hero-btn" onclick="#">Get Started</button>
            <button class="hero-btn" onclick="location.href='changelog.html'">Go to Changelog</button>
        </div>
    </section>
<br>
<section class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2 text-center">
            <h2>Explore Our Blog</h2>
            <p>Discover insightful articles, news, and updates on NexuinChat.</p>
            <a href="blog.html" class="btn btn-primary">Visit Our Blog</a>
        </div>
    </div>
</section>
<br>
<div class="container mt-5">
    <div class="row">
        <div class="col-sm-4">
            <div class="card mb-3">
                <img src="easy.jpg" class="card-img-top" alt="Easy to use" style="height: 250px">
                <div class="card-body">
                    <h5 class="card-title">Easy to use</h5>
                    <p class="card-text">Simplify your communication with a user-friendly interface that anyone can navigate.</p>
                    <a href="#" class="btn btn-primary">Learn More</a>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card mb-3">
                <img src="coin.jpg" class="card-img-top" alt="Forever Free of charge" style="height: 250px">
                <div class="card-body">
                    <h5 class="card-title">Forever Free of charge</h5>
                    <p class="card-text">Enjoy all the features of NexuinChat without any cost. No hidden fees or subscriptions.</p>
                    <a href="#" class="btn btn-primary">Get Started</a>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card mb-3">
                <img src="android.jpg" class="card-img-top" alt="Android App" style="height: 250px">
                <div class="card-body">
                    <h5 class="card-title">Android App</h5>
                    <p class="card-text">Take NexuinChat with you on the go. Download our Android app for seamless mobile communication.</p>
                    <a href="#" class="btn btn-primary btn-team">Download Now</a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="container mt-5"> <div class="row"> <div class="col-md-8 offset-md-2 text-center team-section"> <h2>Our Amazing Team</h2> <p>Get to know the talented individuals behind our success!</p> <a href="about.html" class="btn btn-secondary">Meet the Team</a>
</div>
</div>
</section>


<style>

.team-section { background-color: #8b64b5; color: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }

.btn-team { display: inline-block; padding: 10px 20px; margin-top: 20px; color: white; background-color: #2c3e50; text-decoration: none; border-radius: 5px; }

.btn-team:hover { background-color: #34495e; }
</style>
<section class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2 text-center">
            <h2>Any Questions?</h2>
            <p>Send us an Email!</p>
            <a href="mailto:info@nexuin.com" class="btn btn-primary">Go to Mail</a>
        </div>
    </div>
</section>

<br>
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
        Nexuin Chat © <?php echo date("Y"); ?> All Rights Reserved
    </div>
</footer>


<!-- Bootstrap JS and Font Awesome JS for icons -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JavaScript -->
<script>
    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'index.php?logout';
        }
    }
</script>
</body>
</html>

<div class="site_loader" id="site_loader">
<div class="loading">Loading</div>

<!-- dribbble -->
<a class="dribbble" href="https://dribbble.com/shots/6616259-Loading-text-animation" target="_blank"><img src="https://cdn.dribbble.com/assets/dribbble-ball-mark-2bd45f09c2fb58dbbfb44766d5d1d07c5a12972d602ef8b32204d28fa3dda554.svg" alt=""></a>
</div>

<style>

.site_loader {
    position: fixed; /* Sit on top of the page content */
    display: flex; /* Hidden by default */
    width: 100%; /* Full width (cover the whole page) */
    height: 100%; /* Full height (cover the whole page) */
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgb(29, 29, 29); /* Black background with opacity */
    z-index: 200000000; /* Specify a stack order in case you're using a different order for other elements */
    cursor:progress; /* Add a pointer on hover */
    justify-content: center;
    align-items: center;
    flex-direction: column;
    transition: 0.7s;
    opacity: 1;
  }
  .loading {
  --color: #F5F9FF;
  --duration: 2000ms;
  font-family: Roboto, Arial;
  font-size: 24px;
  position: relative;
  white-space: nowrap;
  user-select: none;
  color: var(--color);
}
.loading span {
  --x: 0;
  --y: 0;
  --move-y: 0;
  --move-y-s: 0;
  --delay: 0ms;
  display: block;
  position: absolute;
  top: 0;
  left: 0;
  width: 1px;
  text-indent: calc(var(--x) * -1);
  overflow: hidden;
  transform: translate(var(--x), var(--y));
}
.loading.start div {
  opacity: 0;
}
.loading.start span {
  animation: move var(--duration) ease-in-out var(--delay);
}
@keyframes move {
  30% {
    transform: translate(var(--x), var(--move-y));
  }
  82% {
    transform: translate(var(--x), var(--move-y-s));
  }
}
html {
  box-sizing: border-box;
  -webkit-font-smoothing: antialiased;
}
* {
  box-sizing: inherit;
}
body {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #151924;
}
body .dribbble {
  position: fixed;
  display: block;
  right: 20px;
  bottom: 20px;
}
body .dribbble img {
  display: block;
  height: 28px;
}

  
</style>
<script>

$(document).ready(function() {

let loading = $('.loading').wrapInner('<div></div>'),
    min = 20,
    max = 70,
    minMove = 10,
    maxMove = 20;

startAnimation(loading);

loading.on('animationend webkitAnimationEnd oAnimationEnd', 'span:last-child', e => {
    startAnimation(loading);
});

//Set CSS vars & generate spans if needed
function setCSSVars(elem, min, max, minMove, maxMove) {
    let width = Math.ceil(elem.width()),
        text = elem.text();
    for(let i = 1; i < width; i++) {
        let num = Math.floor(Math.random() * (max - min + 1)) + min,
            numMove = Math.floor(Math.random() * (maxMove - minMove + 1)) + minMove,
            dir = (i % 2 == 0) ? 1 : -1,
            spanCurrent = elem.find('span:eq(' + i + ')'),
            span = spanCurrent.length ? spanCurrent : $('<span />');
        span.css({
            '--x': i - 1 + 'px',
            '--move-y': num * dir + 'px',
            '--move-y-s': ((i % 2 == 0) ? num * dir - numMove : num * dir + numMove) + 'px',
            '--delay': i * 10 + 'ms'
        });
        if(!spanCurrent.length) {
            elem.append(span.text(text));
        }
    }
}

//Start animation
function startAnimation(elem) {
    elem.removeClass('start');
    setCSSVars(elem, min, max, minMove, maxMove);
    void elem[0].offsetWidth;
    elem.addClass('start');
}

});


function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

window.addEventListener("load", async (event) => {
    document.getElementById("site_loader").style.opacity = 1;
    await sleep(3000); // Wait for 2 seconds
    document.getElementById("site_loader").style.opacity = 0;
    document.getElementById("site_loader").style.display = "none";
    document.getElementById("body").style = "background-color: #fff;";
    console.log("page is fully loaded");
});
</script>

    
    <!-- User Dashboard Overlay -->
    <div class="overlay" id="dashboardOverlay">
        <div class="dashboard">
            <h3>User Dashboard</h3>
            <?php if ($userData): ?>
                <p><strong>Username:</strong> <?php echo $userData['username']; ?></p>
                <p><strong>Profile Picture:</strong> <img src="<?php echo $userData['profile_picture']; ?>" alt="Profile Picture" style="width: 100px; height: 100px; border-radius: 50%;"></p>
                <?php if (!empty($userData['banner_url'])): ?>
                    <p><strong>Banner Image:</strong></p>
                    <img src="<?php echo $userData['banner_url']; ?>" alt="Banner Image" class="banner-image" height="150px">
                <?php endif; ?>
                <p><strong>About Me:</strong> <?php echo $userData['aboutme_text']; ?></p>
                <p><strong>Created At:</strong> <?php echo $userData['created_at']; ?></p>
                <p><strong>Staff:</strong> <?php echo ($userData['isStaff'] ? 'Yes' : 'No'); ?></p>
                <p><strong>Beta Tester:</strong> <?php echo ($userData['isBetaTester'] ? 'Yes' : 'No'); ?></p>
                <!-- Display Owner field based on username condition -->
                <p><strong>Owner:</strong> <?php echo ($userData['username'] === 'kirinaru' ? 'Yes' : 'No'); ?></p>
                <button onclick="toggleDashboard()">Close</button>
            <?php else: ?>
                <p>Please log in to view your dashboard.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function toggleDashboard() {
        const overlay = document.getElementById('dashboardOverlay');
        overlay.classList.toggle('active');
    }
</script>

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

    // Prepare and execute SQL query to fetch user data with extended fields
    $stmt = $conn->prepare("SELECT id, username, profile_picture, banner_url, aboutme_text, created_at, isStaff, isBetaTester FROM users WHERE id = ?");
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

<style>
/* Overlay Styles */
         .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s, visibility 0.3s;
        }

        .overlay.active {
            visibility: visible;
            opacity: 1;
        }

        .dashboard {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            text-align: center;
        }

        .dashboard h3 {
            margin-bottom: 20px;
        }

        .dashboard p {
            color: #666;
            margin-bottom: 10px;
        }

        .dashboard button {
            padding: 8px 16px;
            border-radius: 4px;
            border: none;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .dashboard button:hover {
            background-color: #0056b3;
        }
</style>