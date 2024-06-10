<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexuinChat - Kontaktformular</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
        rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            color: #212529;
            padding-top: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .map-container {
            height: 300px; /* Höhe der Karte anpassen */
            border: 1px solid #ccc;
            margin-top: 20px;
        }

        h1 {
            color: white;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div>
            <h1>NexuinChat</h1>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <!-- Linke Spalte für das Kontaktformular -->
            <div class="col-md-6">
                <h2>Kontaktformular</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3">
                        <label for="i_name" class="form-label">Name:</label>
                        <input type="text" class="form-control" id="i_name" name="i_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="i_email" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="i_email" name="i_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="i_betreff" class="form-label">Betreff:</label>
                        <input type="text" class="form-control" id="i_betreff" name="i_betreff" required>
                    </div>
                    <div class="mb-3">
                        <label for="i_text" class="form-label">Nachricht:</label>
                        <textarea class="form-control" id="i_text" name="i_text" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" name="submit">Senden</button>
                </form>
                <?php
                // Verarbeitung des Formulars und Senden der E-Mail
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $emailTo = "info@nexuin.com";
                    $emailFrom = $_POST["i_email"];
                    $name = $_POST["i_name"];
                    $subject = $_POST["i_betreff"];
                    $message = $_POST["i_text"];
                    $headers = "From: $name <$emailFrom>\r\n";
                    $headers .= "Reply-To: $emailFrom\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
                    if (mail($emailTo, $subject, $message, $headers)) {
                        echo '<div class="alert alert-success mt-3" role="alert">E-Mail wurde erfolgreich versendet.</div>';
                    } else {
                        echo '<div class="alert alert-danger mt-3" role="alert">Fehler beim Versenden der E-Mail.</div>';
                    }
                }
                ?>
            </div>
            <!-- Rechte Spalte für die Google Maps-Karte -->
            <div class="col-md-6">
                <h2>Standort</h2>
                <div class="map-container">
                    <!-- Google Maps iframe hier einfügen -->
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d203321.19872128373!2d-115.869159!3d37.2264976!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80b81baaba3e8c81%3A0x970427e38e6237ae!2sArea%2051%2C%20Nevada%2C%20USA!5e0!3m2!1sde!2sat!4v1712941359253!5m2!1sde!2sat"
                        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
                <p class="mt-3">Adresse: Beispielstraße 123, 1234 Beispielstadt</p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Footer -->
    <footer style="background-color: #36393f; color: #fff; padding: 40px 20px; text-align: center; margin-top: 50px;">
        <div style="max-width: 1000px; margin: 0 auto;">
            <div style="font-size: 24px; font-weight: 300; margin-bottom: 30px;">
                Nexuin Chat, available everywhere you are
            </div>
            <button onclick="location.href='login.php'" class="btn btn-primary btn-lg">
                Try Nexuin Chat now, it's free
            </button>
            <div style="color: #8e9297; margin-top: 20px;">
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
