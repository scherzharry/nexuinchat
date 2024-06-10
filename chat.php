<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

/*

*/

// Database configuration
$servername = "localhost";
$username = "nope";
$password = "no-password-for-u"; // Default password for XAMPP MySQL is empty
$database = "nexuinco_chatapp";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Process sending friend request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_request'])) {
    $receiver_username = $_POST['receiver_username'];

    // Check if receiver username exists
    $sql_check_user = "SELECT id FROM users WHERE username = '$receiver_username'";
    $result_check_user = $conn->query($sql_check_user);
    
    if ($result_check_user && $result_check_user->num_rows > 0) {
        $receiver_id = $result_check_user->fetch_assoc()['id'];

        // Check if friend request already exists
        $sql_check_request = "SELECT id FROM friend_requests WHERE sender_id = $user_id AND receiver_id = $receiver_id AND status = 'pending'";
        $result_check_request = $conn->query($sql_check_request);

        if ($result_check_request && $result_check_request->num_rows > 0) {
            $error_message = "A pending friend request already exists for $receiver_username.";
        } else {
            // Create friend request
            $sql_create_request = "INSERT INTO friend_requests (sender_id, receiver_id, status) VALUES ($user_id, $receiver_id, 'pending')";
            $conn->query($sql_create_request);

            // Display success message
            $success_message = "Friend request sent successfully to $receiver_username.";
        }
    } else {
        $error_message = "User not found with username '$receiver_username'.";
    }
}

// Function to get logged-in user details
function getUserDetails($conn, $user_id) {
    $sql = "SELECT * FROM users WHERE id = $user_id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Function to get user's friends list with profile pictures
function getFriendsListWithPfp($conn, $user_id) {
    $sql = "SELECT users.id, users.username, users.profile_pic, users.banner_url, users.aboutme_text, cover_image, users.created_at, users.isStaff, users.isBetaTester
            FROM friends 
            JOIN users ON friends.friend_id = users.id 
            WHERE friends.user_id = $user_id";
    $result = $conn->query($sql);
    $friends = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Check if banner_url and aboutme_text keys exist in the row array
            if (isset($row['banner_url']) && isset($row['aboutme_text'])) {
                $row['banner_url'] = htmlspecialchars($row['banner_url']);
                $row['aboutme_text'] = htmlspecialchars($row['aboutme_text']);
            } else {
                // Set default values if keys are not set
                $row['banner_url'] = ''; // Default banner URL
                $row['aboutme_text'] = ''; // Default about me text
            }
            $friends[] = $row;
        }
    }
    return $friends;
}

function getUserData($conn, $user_id) {
    $sql = "SELECT id, username, profile_pic, banner_url, aboutme_text, cover_image, created_at, isStaff, isBetaTester
            FROM users
            WHERE id = $user_id";
    $result = $conn->query($sql);
    $userData = null;
    if ($result && $result->num_rows > 0) {
        $userData = $result->fetch_assoc();
        // Check if banner_url and aboutme_text keys exist in the userData array
        if (isset($userData['banner_url']) && isset($userData['aboutme_text'])) {
            $userData['banner_url'] = htmlspecialchars($userData['banner_url']);
            $userData['aboutme_text'] = htmlspecialchars($userData['aboutme_text']);
        } else {
            // Set default values if keys are not set
            $userData['banner_url'] = ''; // Default banner URL
            $userData['aboutme_text'] = ''; // Default about me text
        }
    }
    return $userData;
}



// Function to get pending friend requests for both sender and receiver
function getPendingRequests($conn, $user_id) {
    $sql = "SELECT fr.id, u.username, u.profile_pic, fr.status 
            FROM friend_requests fr
            JOIN users u ON fr.sender_id = u.id
            WHERE fr.receiver_id = $user_id AND fr.status = 'pending'";
    $result = $conn->query($sql);
    $requests = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
    }

    // Also fetch pending requests sent by the current user
    $sql_sent_requests = "SELECT fr.id, u.username, u.profile_pic, fr.status 
                          FROM friend_requests fr
                          JOIN users u ON fr.receiver_id = u.id
                          WHERE fr.sender_id = $user_id AND fr.status = 'pending'";
    $result_sent_requests = $conn->query($sql_sent_requests);
    if ($result_sent_requests && $result_sent_requests->num_rows > 0) {
        while ($row = $result_sent_requests->fetch_assoc()) {
            $requests[] = $row;
        }
    }

    return $requests;
}

// Process responding to friend request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['respond_request'])) {
    $request_id = $_POST['request_id'];
    $response = $_POST['respond_request'];

    // Update friend request status
    $sql_update_request = "UPDATE friend_requests SET status = '$response' WHERE id = $request_id";
    $conn->query($sql_update_request);

    // If the request was accepted, establish friendship
    if ($response === 'accepted') {
        $sql_get_request = "SELECT sender_id, receiver_id FROM friend_requests WHERE id = $request_id";
        $result_get_request = $conn->query($sql_get_request);
        
        if ($result_get_request && $result_get_request->num_rows > 0) {
            $row = $result_get_request->fetch_assoc();
            $sender_id = $row['sender_id'];
            $receiver_id = $row['receiver_id'];

            // Insert friendships for both users
            $sql_insert_friendship1 = "INSERT INTO friends (user_id, friend_id) VALUES ($sender_id, $receiver_id)";
            $sql_insert_friendship2 = "INSERT INTO friends (user_id, friend_id) VALUES ($receiver_id, $sender_id)";

            $conn->query($sql_insert_friendship1);
            $conn->query($sql_insert_friendship2);
        }
    }

    // Redirect to avoid resubmission on refresh
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Get logged-in user details
$user = getUserDetails($conn, $user_id);

?>
<!--
 _._     _,-'""`-._
(,-.`._,'(       |\`-/|
    `-.-' \ )-`( , o o)
          `-    \`_`"'- miau
-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexuinChat</title>
    <link rel="icon" href="animated_favicon.gif" type="image/gif" >
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #333; /* Dark background color */
            color: #fff; /* Light text color */
            padding-top: 50px;
        }
        .card {
            margin-bottom: 20px;
            background-color: #444; /* Darker card background color */
            color: #fff; /* Light card text color */
        }
        .card-header {
            background-color: #555; /* Darker card header background color */
        }
        .list-group-item {
            background-color: #555; /* Darker list item background color */
            color: #fff; /* Light list item text color */
        }
        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }


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
</head>
<body>


<div class="container">
<h2 style="display: flex; align-items: center;">
    Welcome, <?php echo $user['username']; ?>
    <?php if ($user['username'] === 'kirinaru'): ?>
        <i class="fas fa-crown" style="margin-left: 10px; color: gold;"></i>
    <?php endif; ?>
    <?php if ($user['profile_pic']): ?>
        <img src="<?php echo $user['profile_pic']; ?>" class="profile-pic" style="width: 50px; height: 50px; border-radius: 50%; margin-left: 10px;" alt="Profile Picture"
        <?php
        $userData = getUserData($conn, $_SESSION['user_id']);

            // Render friends list with profile pictures
            

                $cover_image = isset($userData['cover_image']) ? $userData['cover_image'] : 'path_to_default_image.jpg';


                echo ' onclick="openMiniProfile(\'' . $userData['id'] . '\', 
                                                     \'' . $userData['username'] . '\', 
                                                     \'' . $userData['banner_url'] . '\', 
                                                     \'' . $userData['aboutme_text'] . '\', 
                                                     \'' . $userData['profile_pic'] . '\',
                                                     \'' . $cover_image . '\',
                                                     \'' . $userData['created_at'] . '\',
                                                     \'' . $userData['isStaff'] . '\',
                                                     \'' . $userData['isBetaTester'] . '\')">
                        ' . $userData[''] . '';
            
        ?>
        </h2>
    <?php endif; ?>

  
    <!-- Send Friend Request Form -->
    <div class="card">
        <div class="card-header">
            <h4>Send Friend Request</h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="input-group mb-3">
                    <input type="text" name="receiver_username" class="form-control" placeholder="Enter friend's username">
                    <button type="submit" name="send_request" class="btn btn-primary">Send Request</button>
                </div>
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Pending Requests Section -->
    <div class="card">
        <div class="card-header">
            <h4>Pending Requests</h4>
        </div>
        <div class="card-body">
            <ul class="list-group">
                <?php
                $pending_requests = getPendingRequests($conn, $user_id);
                foreach ($pending_requests as $request) {
                    echo '<li class="list-group-item d-flex justify-content-between align-items-center">
                            <img src="' . $request['profile_pic'] . '" class="profile-pic">
                            ' . $request['username'] . '
                            <form method="POST">
                                <input type="hidden" name="request_id" value="' . $request['id'] . '">
                                <button type="submit" name="respond_request" value="accepted" class="btn btn-success btn-sm"><i class="fa-solid fa-check"></i> Accept</button>
                                <button type="submit" name="respond_request" value="rejected" class="btn btn-danger btn-sm"><i class="fa-solid fa-x"></i> Reject</button>
                            </form>
                          </li>';
                }
                ?>
            </ul>
        </div>
    </div>
<!-- Friends Section -->
<div class="card">
    <div class="card-header">
        <h4>Friends</h4>
    </div>
    <div class="card-body">
        <ul class="list-group">
            <?php
            $friends = getFriendsListWithPfp($conn, $user_id);

            // Render friends list with profile pictures
            foreach ($friends as $friend) {

                $cover_image = isset($friend['cover_image']) ? $friend['cover_image'] : 'path_to_default_image.jpg';


                echo '<li class="list-group-item d-flex align-items-center">
                        <img src="' . $friend['profile_pic'] . '" class="profile-pic" 
                             onclick="openMiniProfile(\'' . $friend['id'] . '\', 
                                                     \'' . $friend['username'] . '\', 
                                                     \'' . $friend['banner_url'] . '\', 
                                                     \'' . $friend['aboutme_text'] . '\', 
                                                     \'' . $friend['profile_pic'] . '\',
                                                     \'' . $cover_image . '\',
                                                     \'' . $friend['created_at'] . '\',
                                                     \'' . $friend['isStaff'] . '\',
                                                     \'' . $friend['isBetaTester'] . '\')">
                        ' . $friend['username'] . '
                        <button type="button" class="btn btn-primary btn-sm ml-auto" onclick="openChat(\'' .$friend['id']. '\',\'' . $friend['username']. '\')" ><i class="fas fa-comment"></i></button>
                      </li>';
            }
            ?>
        </ul>
    </div>
</div>

<script>
    function openChat(receiverId, receiverUsername) {
        console.log('Receiver ID:', receiverId);
        console.log('Receiver Username:', receiverUsername);

        // Check if receiverUsername is not empty
        if (receiverUsername.trim() !== '') {
            // Set the receiver's username into the hidden input field
            $('#receiverUsernameInput').val(receiverUsername);

            // Show the chat modal
            $('#chatModal').modal('show');
            // Update the modal title with the receiver's username
            $('#chatModal .modal-title').text('Chat with ' + receiverUsername);
            // Use AJAX to fetch chat history and display it in the modal body
        } else {
            console.log('ReceiverUsername is empty or invalid.');
        }
    }

    $(document).ready(function() {
        // Initialize modal shown event
        $('#chatModal').on('shown.bs.modal', function () {
            // Get the receiver's username from the input field and log it
            var modalReceiverUsername = $('#receiverUsernameInput').val();
            console.log('ReceiverUsername (Modal Shown):', modalReceiverUsername);
        });
    });
</script>




</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chat Modal -->
<div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content chat-modal">
            <div class="modal-header chat-modal-header">
                <h5 class="modal-title" id="chatModalLabel">Chat with <span id="receiverUsername"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="chatModalBody">
            <?php

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

// Database configuration
$servername = "localhost";
$username = "nope";
$password = "no-password-for-u"; // Default password for XAMPP MySQL is empty
$database = "nexuinco_chatapp";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



// Function to fetch chat messages
function fetchChatMessages($conn, $senderId, $receiverId) {
    $sql = "SELECT * FROM messages WHERE (sender_id = '$senderId' AND receiver_id = '$receiverId') OR (sender_id = '$receiverId' AND receiver_id = '$senderId') ORDER BY timestamp";
    $result = $conn->query($sql);
    $messages = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
    }

    return $messages;
}

// Define a function to fetch the user's profile picture URL from the database
function fetchUserProfilePicture($conn, $userId) {
    // Example SQL query to fetch the profile picture URL based on user ID
    $sql = "SELECT profile_picture FROM users WHERE id = '$userId'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $userData = $result->fetch_assoc();
        return $userData['profile_picture']; // Return the profile picture URL
    } else {
        // Return a default profile picture URL or handle error as needed
        return 'path_to_default_profile_picture.jpg';
    }
}

// Retrieve sender's user ID and username from session
$senderId = $_SESSION['user_id'];

// Retrieve sender's username and profile picture from the database
$sqlSenderInfo = "SELECT username, profile_picture FROM users WHERE id = '$senderId'";
$resultSenderInfo = $conn->query($sqlSenderInfo);

if ($resultSenderInfo && $resultSenderInfo->num_rows > 0) {
    $senderData = $resultSenderInfo->fetch_assoc();
    $senderName = $senderData['username'];
    $senderProfilePic = $senderData['profile_picture'];
} else {
    $senderName = 'Unknown User';
    $senderProfilePic = 'default_profile_picture.jpg'; // Default profile picture path
}

// Proceed with retrieving receiver's information and handling new message
$receiverUsername = $_POST['receiverUsername'] ?? '';

if (!empty($receiverUsername) && isset($_POST['sendMessage'])) {
    // Get the new message content and sanitize
    $message = $conn->real_escape_string($_POST['chatMessage']);

    // Retrieve receiver's user ID and profile picture from the database
    $sqlReceiverInfo = "SELECT id, profile_picture FROM users WHERE username = '$receiverUsername'";
    $resultReceiverInfo = $conn->query($sqlReceiverInfo);

    if ($resultReceiverInfo && $resultReceiverInfo->num_rows > 0) {
        $receiverData = $resultReceiverInfo->fetch_assoc();
        $receiverId = $receiverData['id'];
        $receiverProfilePic = $receiverData['profile_picture'];

        // Insert the new message into the database
        $sqlInsertMessage = "INSERT INTO chat_messages (sender_id, receiver_id, message) 
                             VALUES ('$senderId', '$receiverId', '$message')";
        $resultInsert = $conn->query($sqlInsertMessage);

        if ($resultInsert) {
            // Fetch all messages between sender and receiver
            $sqlFetchMessages = "SELECT * FROM chat_messages 
                                 WHERE (sender_id = '$senderId' AND receiver_id = '$receiverId') 
                                    OR (sender_id = '$receiverId' AND receiver_id = '$senderId')
                                 ORDER BY timestamp ASC";
            $resultFetchMessages = $conn->query($sqlFetchMessages);

            if ($resultFetchMessages && $resultFetchMessages->num_rows > 0) {
                while ($row = $resultFetchMessages->fetch_assoc()) {
                    $messageContent = htmlspecialchars($row['message']);
                    $timestamp = date('Y-m-d H:i', strtotime($row['timestamp'])); // Format timestamp with date

                    $messageDateTime = new DateTime($row['timestamp']);
                    $currentDateTime = new DateTime();
                    $diff = $messageDateTime->diff($currentDateTime);

                    if ($diff->days > 0) {
                    $timestamp = date('Y-m-d H:i', strtotime($row['timestamp'])); // Format timestamp with date if message was sent on a different day
                    } else {
                    $timestamp = 'Today at ' . date('H:i', strtotime($row['timestamp'])); // Display "Today at" and time if message was sent today
                    }           


                    $isSender = ($row['sender_id'] == $senderId);

                    $profilePic = $isSender ? $senderProfilePic : $receiverProfilePic;
                    $displayName = $isSender ? $senderName : $receiverUsername;
                    $alignClass = $isSender ? 'text-end' : 'text-start'; // Alignment class for message container

                    echo '<div class="chat-message mb-3">';
                    echo '<div class="d-flex align-items-center justify-content-' . ($isSender ? 'end' : 'start') . '">'; // Align container to the end (right) or start (left)
                    echo '<img src="' . $profilePic . '" alt="Profile Picture" class="rounded-circle me-2" style="width: 40px; height: 40px;">';
                    echo '<div class="message-content ' . $alignClass . '">';
                    echo '<div>';
                    echo '<strong>' . $displayName . '</strong> <span class="badge bg-secondary">' . $timestamp . '</span>';
                    echo '</div>';
                    echo '<p class="mb-0" style="text-align: left;">' . $messageContent . '</p>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo "No messages found.";
            }
        } else {
            echo "Error sending message.";
        }
    } else {
        echo "User with username '$receiverUsername' not found.";
    }
}
?>

<script>
    // AJAX request to fetch chat messages
$.ajax({
    type: 'POST',
    url: 'chat.php',
    data: { receiverUsername: receiverUsername },
    success: function(response) {
        // Update modal body with chat messages
        $('#chatModalBody').html(response); // This line updates the chat messages
    },
    error: function(xhr, status, error) {
        console.error('AJAX Error:', error);
    }
});

</script>

            </div>
            <div class="modal-footer chat-modal-footer">
                <!-- Chat input form -->
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="d-flex align-items-center">
                    <!-- Hidden field to store the receiver's username -->
                    <input type="hidden" name="receiverUsername" id="receiverUsernameInput" value="">
                    <!-- Textarea for message -->
                    <textarea class="form-control flex-grow-1 me-2" name="chatMessage" placeholder="Type your message..." required></textarea>
                    <!-- Send button with Font Awesome icon -->
                    <button type="submit" name="sendMessage" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>





<style>
/* Custom CSS for the chat modal */
.chat-modal {
    background-color: #333; /* Dark background color */
    color: #fff; /* Light text color */
}

.chat-modal-header {
    background-color: #555; /* Darker header background color */
    border-bottom: none; /* Remove border bottom */
}

.chat-modal-body {
    height: 300px;
    overflow-y: auto;
}

.chat-modal-footer {
    background-color: #555; /* Darker footer background color */
    border-top: none; /* Remove border top */
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-modal-footer textarea {
    background-color: #444; /* Darker textarea background color */
    color: #fff; /* Light textarea text color */
    border: none; /* Remove textarea border */
    resize: none; /* Disable textarea resizing */
    width: 400px; /* Adjust the width of the textarea */
}

.chat-modal-footer button {
    background-color: #007bff; /* Primary button color */
    border: none; /* Remove button border */
    color: #fff; /* Button text color */
}

.chat-modal-footer button:hover {
    background-color: #0056b3; /* Darker button color on hover */
}

/* Media query for smaller viewport (e.g., mobile devices) */
@media (max-width: 768px) {
    .chat-modal-footer textarea {
        width: 300px; /* Set width to 200px on smaller screens */
    }
}
</style>

</body>
</html>

<?php

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

// Database configuration
$servername = "localhost";
$username = "nope";
$password = "no-password-for-u"; // Default password for XAMPP MySQL is empty
$database = "nexuinco_chatapp";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process sending message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sendMessage'])) {
    $senderId = $_SESSION['user_id'];
    $receiverUsername = $_POST['receiverUsername'];
    $message = $conn->real_escape_string($_POST['chatMessage']);

    // Get receiver ID based on username
    $sqlReceiverId = "SELECT id FROM users WHERE username = '$receiverUsername'";
    $resultReceiverId = $conn->query($sqlReceiverId);

    if ($resultReceiverId && $resultReceiverId->num_rows > 0) {
        $row = $resultReceiverId->fetch_assoc();
        $receiverId = $row['id'];

        // Insert message into messages table
        $sql = "INSERT INTO messages (sender_id, receiver_id, message, timestamp) VALUES ('$senderId', '$receiverId', '$message', NOW())";

        if ($conn->query($sql) === TRUE) {
            echo "Message sent successfully.";
        } else {
            echo "Failed to send message: " . $conn->error;
        }
    } else {
        echo "User with username '$receiverUsername' not found.";
    }
}

$conn->close(); // Close database connection
?>




<?php

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database configuration
$servername = "localhost";
$username = "nope";
$password = "no-password-for-u"; // Default password for XAMPP MySQL is empty
$database = "nexuinco_chatapp";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process changing username
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_username'])) {
    $new_username = $_POST['new_username'];
    $user_id = $_SESSION['user_id'];

    // Update username in the database
    $sql_update_username = "UPDATE users SET username = '$new_username' WHERE id = $user_id";
    if ($conn->query($sql_update_username) === TRUE) {
        // Update session with new username
        $_SESSION['username'] = $new_username;
        $username_change_message = [
            'type' => 'success',
            'text' => 'Username changed successfully.'
        ];
    } else {
        $username_change_message = [
            'type' => 'danger',
            'text' => 'Failed to update username.'
        ];
    }
}

// Process changing profile picture URL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_profile_picture'])) {
    $new_profile_picture_url = $_POST['new_profile_picture_url'];
    $user_id = $_SESSION['user_id'];

    // Update profile picture URL in the database
    $sql_update_profile_picture = "UPDATE users SET profile_pic = '$new_profile_picture_url', profile_picture = '$new_profile_picture_url' WHERE id = $user_id";
    if ($conn->query($sql_update_profile_picture) === TRUE) {
        $profile_picture_message = [
            'type' => 'success',
            'text' => 'Profile picture URL updated successfully.'
        ];
    } else {
        $profile_picture_message = [
            'type' => 'danger',
            'text' => 'Failed to update profile picture URL: ' . $conn->error
        ];
    }
}

// Process changing cover image URL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_cover_image'])) {
    $new_cover_image_url = $_POST['new_cover_image_url'];
    $user_id = $_SESSION['user_id'];

    // Update cover image URL in the database
    $sql_update_cover_image = "UPDATE users SET cover_image = '$new_cover_image_url' WHERE id = $user_id";
    if ($conn->query($sql_update_cover_image) === TRUE) {
        $cover_image_message = [
            'type' => 'success',
            'text' => 'Cover image updated successfully.'
        ];
    } else {
        $cover_image_message = [
            'type' => 'danger',
            'text' => 'Failed to update cover image URL: ' . $conn->error
        ];
    }
}


// Close connection
$conn->close();
?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Dark overlay container */
        .overlay-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0s linear 0.3s;
        }

        /* Show overlay when active */
        .overlay-container.active {
            opacity: 1;
            visibility: visible;
            transition-delay: 0s;
        }

        /* Settings container */
        .settings-container {
            background-color: #333;
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            width: 300px;
        }

        /* Gear icon style */
        .gear-icon {
            color: #fff;
            font-size: 3rem;
            transition: transform 0.3s ease-in-out;
        }

        /* Rotate gear icon on hover */
        .gear-icon:hover {
            transform: rotate(360deg);
        }

        /* Alert message style */
        .alert {
            margin-top: 15px;
            padding: 10px;
            border-radius: 5px;
        }
    </style>


    <!-- Settings Overlay (initially hidden) -->
<div class="overlay-container" id="settingsOverlay">
    <div class="settings-container dark-mode">
        <i class="gear-icon fas fa-times" onclick="toggleSettings()"></i>
        <h3>Settings</h3>
        
        <!-- Icon menu for settings sections -->
        <div class="menu-section" onclick="toggleMenuSection('generalSettings')">
            <i class="menu-icon fas fa-cog"></i>
            <span>General Settings</span>
        </div>
        <div class="menu-section" onclick="toggleMenuSection('profileSettings')">
            <i class="menu-icon fas fa-user"></i>
            <span>Profile Settings</span>
        </div>
        <div class="menu-section" onclick="toggleMenuSection('qrCodeSettings')">
            <i class="menu-icon fas fa-qrcode"></i>
            <span>QR Code Login</span>
        </div>
        <div class="menu-section" onclick="toggleMenuSection('MusicSettings')">
            <i class="menu-icon fa-solid fa-music"></i>
            <span>Music Settings</span>
        </div>
        
        <!-- General Settings Form -->
        <form method="POST" class="settings-form" id="generalSettings">
            <h4>General Settings</h4>
            <label for="new_username" class="form-label">New Username</label>
            <input type="text" class="form-control" id="new_username" name="new_username" required>
            <button type="submit" class="btn btn-primary" name="change_username">Change Username</button>
            <!-- PHP logic for displaying messages here -->
        </form>

        <!-- Profile Settings Form -->
        <div class="settings-form" id="profileSettings">
            <h4>Profile Settings</h4>
            <!-- Change Profile Picture Form -->
            <form method="POST" id="changeProfilePictureForm">
                <label for="new_profile_picture_url" class="form-label">New Profile Picture URL</label>
                <input type="text" class="form-control" id="new_profile_picture_url" name="new_profile_picture_url" required>
                <button type="submit" class="btn btn-primary" name="change_profile_picture">Change Profile Picture</button>
                <!-- PHP logic for displaying profile picture change messages here -->
            </form>

            <!-- Change Cover Image Form -->
            <form method="POST" id="changeCoverImageForm">
                <label for="new_cover_image_url" class="form-label">New Cover Image URL</label>
                <input type="text" class="form-control" id="new_cover_image_url" name="new_cover_image_url" required>
                <button type="submit" class="btn btn-primary" name="change_cover_image">Change Cover Image</button>
                <!-- PHP logic for displaying cover image change messages here -->
            </form>
        </div>

        <!-- QR Code Login Settings Form -->
        <form method="POST" class="settings-form" id="qrCodeSettings">
            <h4>QR Code Login</h4>
            <p>Log in using QR Code (beta)</p>
            <button type="button" class="btn btn-primary" onclick="openCamera()">Open Camera</button>
            <center>
            <div id="video-container">
                <video id="video" playsinline autoplay></video>
                <div id="guidelines"></div>
            </div>
            </center>
        </form>
         <!-- QR Code Login Settings Form -->
        <form method="POST" class="settings-form" id="MusicSettings">
            <h4>Music Settings</h4>
        </form>
        <center>
            <button onclick="showmp()">Enable Music Player</button>
            </center>

        <!-- Logout Button -->
        <button class="btn btn-danger" onclick="logout()">Logout</button>
    </div>
</div>

<script>
    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'index.php?logout';
        }
    }
</script>


    <style>
        /* Styling for Settings Overlay */

.settings-container {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: white;
    padding: 20px;
    width: 300px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.settings-container.dark-mode {
    background-color: #36393f; /* Discord's dark mode background color */
    color: white;
}

.settings-container h3 {
    font-size: 20px;
    margin-bottom: 20px;
}

.menu-section {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    cursor: pointer;
}

.menu-icon {
    font-size: 24px;
    margin-right: 10px;
}

.settings-form {
    display: none; /* Hide all forms initially */
}

.settings-form.active {
    display: block; /* Show active form */
}

    </style>
    <script>

        function logoutUser(){
            
        }
        // Function to toggle the display of settings overlay
function toggleSettings() {
    var overlay = document.getElementById('settingsOverlay');
    overlay.style.display = overlay.style.display === 'block' ? 'none' : 'block';
}

// Function to toggle active menu section
function toggleMenuSection(sectionId) {
    var sections = document.querySelectorAll('.settings-form');
    sections.forEach(function(section) {
        section.classList.remove('active');
    });
    var activeSection = document.getElementById(sectionId);
    activeSection.classList.add('active');
}

// Function to open camera for QR Code scanning (placeholder)
function openCamera() {
    // Add your QR code scanning logic here
    console.log('Opening camera for QR code scanning...');
}

    </script>
    <style>
        #video-container {
            position: relative;
            width: 80%;
            max-width: 400px;
            overflow: hidden;
            border: 2px solid #333;
        }
        #video {
            width: 100%;
            height: auto;
            transform: scaleX(-1); /* Flip video horizontally for better mirror effect */
        }
        #guidelines {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            height: 80%;
            box-sizing: border-box;
        }
        #guidelines::before, #guidelines::after {
            content: '';
            position: absolute;
            width: 50%;
            height: 50%;
            border: 2px solid #fff;
        }
        #guidelines::before {
            top: 0;
            left: 0;
            border-right: none;
            border-bottom: none;
        }
        #guidelines::after {
            bottom: 0;
            right: 0;
            border-top: none;
            border-left: none;
        }
    </style>

    <!-- Settings Button (to toggle settings overlay) -->
    <center>
    <button type="button" class="btn btn-secondary" onclick="toggleSettings()">
        <i class="gear-icon fas fa-cog"></i>
    </button>
    </center>

    <script>
        // Function to toggle settings overlay visibility
        function toggleSettings() {
            const overlay = document.getElementById('settingsOverlay');
            overlay.classList.toggle('active');
        }
    </script>

</body>

</html>


<script>
    function openMiniProfile(userId, username, bannerUrl, aboutMe,avatar,cover_image, registration_date, isStaff,isBetaTester) {
    // Set the user's information into the mini profile modal
    $('#miniProfileModal .profile-id').text(userId);
    $('#miniProfileModal .profile-username').text(username);
    $('#miniProfileModal .profile-banner').css('background-image', `url('${bannerUrl}')`);
    $('#miniProfileModal .profile-avatar').attr('src', avatar);
    $('#miniProfileModal .profile-about').text(aboutMe);
    $('#miniProfileModal .modal-cover-image').attr('src',cover_image);
    $('#miniProfileModal .registration_date').text(registration_date);
    $('#miniProfileModal .profile_isStaff').text(isStaff);
    $('#miniProfileModal .profile_isBetaTester').text(isBetaTester);

    // Show the mini profile modal
    $('#miniProfileModal').modal('show');
}

</script>

<style>

.modal-body{
    padding: 0;
}
/* Dark Mode Mini Profile Styling */
.mini-profile {
    background-color: #333; /* Dark background color */
    color: #fff; /* Light text color */
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    align-items: flex-start; /* Align items to the left */
    padding: 20px;
    width: 400px;
}

.profile-banner {
    width: 100%;
    height: 120px; /* Adjust banner height as needed */
    background-color: #555; /* Darker banner background color */
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 20px;
    background-size: cover;
}

.profile-banner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;

}

.modal-content {
    background-color: #333; /* Dark modal background color */
    color: #fff; /* Light modal text color */
}

.profile-pic-mini {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-right: 20px; /* Margin to separate avatar from username */
}

.profile-info {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    width: 100%; /* Ensure the info section spans full width */
}

.username {
    font-size: 1.5rem;
    font-weight: bold;
    text-align: left;
    margin-bottom: 10px;
}

.about-me {
    font-size: 0.9rem;
    line-height: 1.4;
    text-align: left !important;
    padding: 0 20px; /* Add padding to the about me text for better readability */
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .profile-pic-mini {
        width: 60px;
        height: 60px;
    }
    
    .username {
        font-size: 1.3rem;
    }
    
    .about-me {
        font-size: 0.8rem;
    }

    iframe{
        max-width: 425px;
    }
}

</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Show the modal using Bootstrap's modal API
        var modalElement = document.getElementById('miniProfileModal');
        var modal = new bootstrap.Modal(modalElement);

        // Add a listener for when the modal is fully shown
        modalElement.addEventListener('shown.bs.modal', function () {
            // Call a function to add the crown icon based on username
            addCrownIcon();
            addStaffIcon();
            addBetaIcon();
        });
    });

    // Function to add crown icon based on username
    function addCrownIcon() {
        // Retrieve the profile username element
        var profileUsername = document.getElementById('profileUsername');
        
        // Retrieve the username text
        var username = profileUsername.textContent.trim();
        

        // Check if the username matches 'kirinaru' to add crown icon
        if (username === 'kirinaru') {
            // Create crown icon element
            var crownIcon = document.createElement('i');
            crownIcon.className = 'fas fa-crown crown-icon';
            crownIcon.style.color = 'gold';
            crownIcon.style = "color: gold; margin-left: 5px; Background-color: #242323; border: 3px solid #242323; border-radius: 5px; padding: 0;padding-right: 1px;";
            
            // Set tooltip attributes
            crownIcon.setAttribute('data-bs-toggle', 'tooltip');
            crownIcon.setAttribute('data-bs-placement', 'top');
            crownIcon.setAttribute('title', 'Owner');
            
            // Append crown icon to profile username
            profileUsername.appendChild(crownIcon);

            // Initialize tooltip
            var tooltip = new bootstrap.Tooltip(crownIcon, {
                container: 'body', // Specify the tooltip container
                delay: { show: 100, hide: 200 }, // Optional: Set tooltip show/hide delay
            });
        }
    }

    function addStaffIcon() {
        // Retrieve the profile isStaff element
        var isStaff = document.getElementById('profile_isStaff');
        
        // Retrieve the isStaff text
        var text = isStaff.innerText;
        // Check if the username matches 'kirinaru' to add crown icon
        if ( text === '1') {
            // Create crown icon element
            var staffIcon = document.createElement('i');
            staffIcon.className = 'fa-regular fa-address-card';
            staffIcon.style.color = 'Emerald Green';
            staffIcon.style = "color: #5FFB17; margin-left: 5px; Background-color: #242323; border: 3px solid #242323; border-radius: 5px; padding: 0;padding-right: 1px;";
            
            // Set tooltip attributes
            staffIcon.setAttribute('data-bs-toggle', 'tooltip');
            staffIcon.setAttribute('data-bs-placement', 'top');
            staffIcon.setAttribute('title', 'Staff');
            
            // Append crown icon to profile username
            profileUsername.appendChild(staffIcon);

            // Initialize tooltip
            var tooltip = new bootstrap.Tooltip(staffIcon, {
                container: 'body', // Specify the tooltip container
                delay: { show: 100, hide: 200 }, // Optional: Set tooltip show/hide delay
            });
        }
    }

    function addBetaIcon() {
        // Retrieve the profile isStaff element
        var isBetaTester = document.getElementById('profile_isBetaTester');
        
        // Retrieve the isStaff text
        var text = isBetaTester.innerText;
        // Check if the username matches 'kirinaru' to add crown icon
        if ( text === '1') {
            // Create crown icon element
            var BetaIcon = document.createElement('i');
            BetaIcon.className = 'fa-solid fa-flask';
            BetaIcon.style.color = 'Emerald Green';
            BetaIcon.style = "color: #B041FF; margin-left: 5px; Background-color: #242323; border: 3px solid #242323; border-radius: 5px; padding: 0;padding-right: 1px;";
            
            // Set tooltip attributes
            BetaIcon.setAttribute('data-bs-toggle', 'tooltip');
            BetaIcon.setAttribute('data-bs-placement', 'top');
            BetaIcon.setAttribute('title', 'Beta Tester');
            
            // Append crown icon to profile username
            profileUsername.appendChild(BetaIcon);

            // Initialize tooltip
            var tooltip = new bootstrap.Tooltip(BetaIcon, {
                container: 'body', // Specify the tooltip container
                delay: { show: 100, hide: 200 }, // Optional: Set tooltip show/hide delay
            });
        }
    }
</script>


<!-- Mini Profile Modal -->
<div class="modal fade" id="miniProfileModal" tabindex="-1" aria-labelledby="miniProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content">
            <!-- Covering Image Container -->
            <div class="modal-cover-container">
                <img src="intro2.png" class="modal-cover-image">
            </div>
            
            <!-- Mini Profile Content -->
            <div class="modal-body">
                <div class="mini-profile">
                    <div class="profile-banner">
                        <img src="path_to_banner_image.jpg" alt="">
                    </div>
                    <div class="profile-info">
                        <p class="profile-id" id="profile-id" style="display: none;"></p>
                        <p class="profile_isStaff" id="profile_isStaff" style="display: none;"></p>
                        <p class="profile_isBetaTester" id="profile_isBetaTester" style="display: none;"></p>
                        <img src="path_to_avatar_image.jpg" class="img-fluid rounded-circle profile-avatar profile-pic-mini" alt="User Avatar">
                        <div class="profile-username" id="profileUsername">Username</div>
                    </div>
                    <strong>ABOUT ME</strong>
                    <p class="profile-about">About me text goes here...</p>
                    <br>
                    <strong>NEXUINCHAT MEMBER SINCE</strong>
                    <p id="registration_date" class="registration_date">1 Jan 1970</p>
                    <br>
                    <strong>NOTE</strong>
                    <textarea placeholder="Note goes here"></textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* CSS for Modal Cover Image Container */
    .modal-cover-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 999; /* Ensure image is above other content */
        display: none; /* Initially hide the cover container */
    }
    
    /* CSS for Modal Cover Image */
    .modal-cover-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    /* Additional styling for mini-profile content */
    .mini-profile {
        padding: 20px;
        /* Add any other necessary styles for mini-profile */
    }
    
    .profile-banner img {
        width: 100%;
        height: auto;
    }
    
    .profile-info {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .profile-info img {
        width: 80px;
        height: 80px;
        margin-right: 15px;
        border-radius: 50%;
    }
    
    .profile-username {
        font-weight: bold;
        font-size: 18px;
        /* Add any other necessary styles for username */
    }
    
    .profile-about {
        font-size: 16px;
        /* Add any other necessary styles for profile about */
    }
    
    textarea {
        width: 100%;
        height: 100px;
        resize: none;
        /* Add any other necessary styles for textarea */
    }
</style>

<!-- JavaScript for Animation -->
<script>
    $(document).ready(function() {
        $('#miniProfileModal').on('shown.bs.modal', function () {
            // Show and animate the cover image
            $('.modal-cover-container').fadeIn(0).css('opacity', 1).hide().fadeIn(1000);

            // Fade out the cover image after 3 seconds
            setTimeout(function() {
                $('.modal-cover-container').fadeOut(1000);
            }, 2000);
        });

        // Reset cover image animation on modal hide
        $('#miniProfileModal').on('hidden.bs.modal', function () {
            $('.modal-cover-container').stop(true, true).hide(); // Stop any ongoing animation and hide the cover image
        });
    });
</script>



<style>
    textarea {
  resize: none;
  border: 1px solid #232323;
  background-color: #333;
  color: white;
  border-radius: 15px;
  width: 350px;
  
}

#exampleModalCenter button{
    background-color: transparent;
    border: none;
}

</style>
<!--
<button onclick="vibrate()">Vibrate!</button>
-->
<script>
    const vibrate = () => {
        window.navigator.vibrate([250])
    }
</script>

 
<button type="button" style="display: none;" class="btn btn-primary" id="myInput" data-toggle="modal" data-target="#exampleModalCenter">
  Launch demo modal
</button>

<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">News</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closeModalBtn">
          <span aria-hidden="true"><i class="fa-solid fa-x" style="color: white;"></i></span>
        </button>
      </div>
      <div class="modal-body">
        <center>
       <iframe width="460" height="315" src="https://www.youtube-nocookie.com/embed/VW4EgkHmevs?si=XuwUXW0MOZI6FsaK" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
       </center>
        <h3>18.4.2024</h3>
        <ul>
          <li>Made Settings look better</li>
          <li>Added Logout button to settings</li>
          <li>Sadly the QR-Code login doesnt work because the websites needs an ssl certificat, I will have to migrate the whole site on my other webspace, which will take some time. Stay tight!</li>
        </ul>
        <hr>
        <center>
        <p> Thank you for using NexuinChat! </p>
        </center>
      </div>
      <div class="modal-footer">
      <button type="button" class="btn btn-secondary" id="closeModal" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
    // Add event listener to the button with ID "myInput"
    document.getElementById('myInput').addEventListener('click', function() {
        // Use Bootstrap's modal function to show the modal with ID "exampleModalCenter"
        $('#exampleModalCenter').modal('show');
    });

    // Function to close the modal
    document.getElementById('closeModal').addEventListener('click', function() {
        $('#exampleModalCenter').modal('hide');
    });

    // Alternative: Close modal using close button in modal header
    document.getElementById('closeModalBtn').addEventListener('click', function() {
        $('#exampleModalCenter').modal('hide');
    });


function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function postAttentionMessage() {
    console.log('%cAttention!', 'font-size: 30px; color: red; font-weight: bold;');
    console.log('%cAttackers could get hold of your account if you mess with the site code. If you do not know what you are doing, close this window. If you do know what you are doing, work for us! at http://nexuin.com/jobs', 'font-size: 20px; color: blue;');
}

// Call the function immediately to log the messages once
postAttentionMessage();

// Set interval to call the function every 10 seconds
setInterval(postAttentionMessage, 10000); // 10000 milliseconds = 10 seconds

document.addEventListener('DOMContentLoaded', function() {
        // Show the modal with ID 'exampleModalCenter'
        var myModal = new bootstrap.Modal(document.getElementById('exampleModalCenter'));
        myModal.show();
    });
</script>



<footer>
<p>Made with <i class="heart-icon fas fa-heart"></i> by Kirinaru</p>
</footer>

<style>
        footer {
            background-color: #333;
            padding: 20px;
            text-align: center;
            color: #fff; /* Text color */
        }
        .heart-icon {
            font-size: 24px;
            color: #ff4d4d; /* Heart color */
            transition: transform 0.3s ease-in-out;
        }
        .heart-icon:hover {
            transform: scale(1.2);
        }
    </style>




<script>
        

        function openCamera(){
// Check if the browser supports getUserMedia
if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Your browser does not support the camera API.');
        } else {
            const video = document.getElementById('video');
            const guidelines = document.getElementById('guidelines');

           

            // Request camera access
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } }) // Use rear camera if available
                .then((stream) => {
                    video.srcObject = stream;
                    video.onloadedmetadata = () => {
                        video.play();

                        // Draw guidelines for QR code placement
                        function drawGuidelines() {
                            const videoRect = video.getBoundingClientRect();
                            const guidelinesSize = Math.min(videoRect.width, videoRect.height) * 0.8;
                            guidelines.style.width = guidelinesSize + 'px';
                            guidelines.style.height = guidelinesSize + 'px';
                        }

                        drawGuidelines(); // Initial draw

                        // Redraw guidelines on window resize
                        window.addEventListener('resize', drawGuidelines);

                        // Load the jsQR library (efficient QR code scanning)
                        const script = document.createElement('script');
                        script.src = 'https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.js';
                        document.head.appendChild(script);

                        // Handle QR code scanning
                        script.onload = () => {
                            const canvas = document.createElement('canvas');
                            const context = canvas.getContext('2d');

                            // Continuously scan for QR codes
                            function scanQRCode() {
                                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                                    const videoWidth = video.videoWidth;
                                    const videoHeight = video.videoHeight;
                                    canvas.width = videoWidth;
                                    canvas.height = videoHeight;
                                    context.drawImage(video, 0, 0, videoWidth, videoHeight);

                                    const imageData = context.getImageData(0, 0, videoWidth, videoHeight);
                                    const code = jsQR(imageData.data, imageData.width, imageData.height, {
                                        inversionAttempts: 'dontInvert',
                                    });

                                    if (code) {
                                        // Save QR code data in a cookie
                                        document.cookie = 'scannedQR=' + encodeURIComponent(code.data);

                                        // Reload the page to trigger PHP processing
                                        location.reload();

                                        // Stop scanning after detecting a QR code
                                        stream.getTracks().forEach(track => track.stop());
                                    }
                                }

                                requestAnimationFrame(scanQRCode);
                            }

                            // Start scanning for QR codes
                            scanQRCode();
                        };
                    };
                })
                .catch((error) => {
                    console.error('Error accessing camera:', error);
                });
        }
        }
    </script>

    <?php
    // Check if the 'scannedQR' cookie is set
    if (isset($_COOKIE['scannedQR']) && !empty($_COOKIE['scannedQR']) &&  is_string($_COOKIE['scannedQR']) &&  strlen($_COOKIE['scannedQR']) > 1) {
        $scannedQR = $_COOKIE['scannedQR'];
        
        // Output the scanned QR code using PHP
        echo '<p>Scanned QR code: <strong>' . htmlspecialchars($scannedQR) . '</strong></p>';

        // Create connection
        $conn = new mysqli($servername, $username, $password, $database);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Insert the scanned QR code into the database
        $sql = "INSERT INTO sessions (session, user_id, timestamp) VALUES ('$scannedQR', '$user_id', now())";
        if ($conn->query($sql) === TRUE) {
            echo("Session saved in the database.");
        }
        $_COOKIE['scannedQR'] = 0;

        // Function to delete expired sessions (older than 5 minutes)
        function deleteExpiredSessions($conn) {
            // Calculate the timestamp 5 minutes ago
            $fiveMinutesAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));

            // SQL query to delete rows older than 5 minutes
            $sql = "DELETE FROM sessions WHERE timestamp < '$fiveMinutesAgo'";

            // Execute the SQL query
            if ($conn->query($sql) === TRUE) {
            } else {
            }
        }

        // Call the function to delete expired sessions
        deleteExpiredSessions($conn);
    }
    ?>

</body>
</html>

<!--Music Player-->
  <div class="music_player" id="music_player" style="display: none;">
    <span data-toggle="tooltip" data-placement="top" title="Maximize" onclick="ToggleMinimize()" id="music_btn" style="color:white; display: none; cursor: pointer;"><center><img class="lp" id="lp" src="schallplatteweiss.png" height="30px" width="30px"></center></span>
    <div onclick="ToggleMinimize()"><span class="bi bi-caret-down-fill" style="color: white; cursor: pointer; width: 20px;height: 20px;background: black;float: right;" data-toggle="tooltip" data-placement="top" title="Minimize"></span></div>
    <div class="music_player_current" id="music_player_current">
      <div class="cover_currently" id="cover_currently" height="300px" width="300px"></div>
      <div class="title_currently" id="title_currently"><span class="mp"><strong>Wassereis</strong></span></div>
      <div class="artist_currently" id="artist_currently"><span class="mp">Rubi</span></div>
    </div>
    <br>
    <div id="mp_time" class="mp_time">0:0/0:0</div>
    <div class="ra_con" class="center">
      <input type="range" id="slider">
    </div>
    <div class="mp_button_wrapper" id="mp_button_wrapper">
      <div class="mp_button kill" id="kill" data-toggle="tooltip" data-placement="top" title="Exit" onclick="minimizepause()"></div>
      <div class="mp_button backward" id="backward" data-toggle="tooltip" data-placement="top" title="Last Song" onclick="beginn()"></div>
      <div class="mp_button play" id="play" onclick="play()" data-toggle="tooltip" data-placement="top" title="Play/Pause"></div>
      <div class="mp_button forward" id="forward" data-toggle="tooltip" data-placement="top" title="Next Song"></div>
      <div class="mp_button repeat" id="repeat" onclick="toggleAPlay()" data-toggle="tooltip" data-placement="top" title="Loop" style="-webkit-backdrop-filter: saturate(200%) blur(0.5rem);backdrop-filter: saturate(200%) blur(0.3rem);box-shadow: 0 0 .7rem #000 ; transition: 0.7s;"></div>
    </div>
    <div class="mp_bottom_text">Powered by <strong style="cursor: pointer;" onclick="https://m.kirinaru.xyz">TuneMe</strong></div>
  </div>
  <audio crossOrigin = "anonymous" loop preload="metadata" class="player" id="audio" src="wassereis.mp3"></audio>
  
<script>

function minimizepause(){
  ToggleMinimize();
  play()
}

function showmp(){
  document.getElementById("music_player").style = "";
}

function beginn(){
  audio.duration = 0;
}

$(document).ready(function(){
  $('#liveToast').toast('show');
});

$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));}

window.addEventListener("load", (event) => {
        document.getElementById("site_loader").style = "opacity: 0;";
        sleep(2000)
        document.getElementById("site_loader").style = "opacity: 0; display: none;"
        console.log("page is fully loaded");
    });

//music
    var toggle;
    toggle = 0;
    var song_title
    var song_id;
    audio = document.getElementById("audio");
    slider = document.getElementById("slider")
    artist_np = document.getElementById("artist_currently")
    cover_np = document.getElementById("cover_currently")
    audio.volume = 0.3;
    slider.value = 0;
    automatic_play = true;
    isolated = false;
    once = 1;

    function play(){
      if (toggle === 1) {
            audio.pause();
            toggle = 0;
            document.getElementById("play").style = "background-image: url('play(neu).svg');"
            document.getElementById("lp").style = "animation: rotate 1s linear infinite;"
            document.getElementById("cover_currently").style.animation = "";
        }
        else{
                audio.play();
                toggle = 1;
                document.getElementById("play").style = "background-image: url('pause.svg');"
                document.getElementById("cover_currently").style.animation = "rotate 3s linear infinite;"
                document.getElementById("lp").style = "";
            }
    }
  

   function getDuration(media){
        
            alert(media.duration)
            document.getElementById("bufferduration").innerHTML = media.duration;
    
    }
repeat = document.getElementById("repeat")
tap = 1;
    function toggleAPlay(){
      if(tap === 0){
        audio.loop = true;
        repeat.style = "-webkit-backdrop-filter: saturate(200%) blur(0.5rem);backdrop-filter: saturate(200%) blur(0.3rem);box-shadow: 0 0 .7rem #000 ; transition: 0.7s;"
        tap = 1
      }
      else{
        audio.loop = false;
        repeat.style = "";
        tap = 0;
      }
    }

  function UpdatePosition(){
        if(toggle === 1){
            audio.duration
            myduri = audio.duration
            slider.value = 100*audio.currentTime/myduri

            c_minutes = Math.floor(audio.currentTime/60)
            a_minutes = Math.floor(myduri/60)
            c_seconds = Math.floor(audio.currentTime) % 60
            a_seconds = Math.floor(myduri) % 60

            mp_time = document.getElementById("mp_time");
            
            mp_time.innerHTML = c_minutes + ":" + c_seconds + "/" + a_minutes + ":" + a_seconds;
        }
      }
      togglem = 0;
      function ToggleMinimize(){
      if(togglem ===0){
          document.getElementById("mp_button_wrapper").style = "display: none;"
          document.getElementById("music_btn").style = "color:white; height: 70px; width: 70px;";
          document.getElementById("music_player").style = "height: 50px; width: 50px; border-radius: 50%; transition: 0.7s;"
          document.getElementById("music_player_current").style = "display: none;"
          document.getElementById("slider").style = "display: none;"

          togglem = 1
        
      }else{
          document.getElementById("music_btn").style = "display: none;"
          document.getElementById("mp_button_wrapper").style = ""
          document.getElementById("music_player").style = "transition: 0.7s;"
          document.getElementById("music_player_current").style = ""
          document.getElementById("slider").style = ""
          togglem = 0
      }
    }
    
audio.onloadedmetadata = function() {
    console.log(audio.duration)
};



const interval1 = setInterval(() => UpdatePosition(), 1000);
</script>

</body>
<style>

.cover_currently{
  background-image: url(cover.jpg);
  height: 50px;
  width: 50px;
  background-size: cover;
  border-radius: 50%;
  transition: background-size 0.3s ease; /* Add smooth transition on background size */
}

.music_player_current{
  
  display: grid;
  grid-template-columns: 50px 10px 300px;
  grid-template-rows: 20px;
}

.music_player{
  width: 400px;
  height: 200px;
  background: none;
  -webkit-backdrop-filter: saturate(130%) blur(0.5rem);
  backdrop-filter: saturate(160%) blur(0.3rem);
  box-shadow: 0 0 .7rem #0004;
  padding: 10px;
  position:fixed;
  right: 20px;
  bottom: 20px;
  border-radius: 18px;
  color: white;
  z-index: 10;
  transition: background-color 5s ease; /* Smooth transition effect */
}

@keyframes rotate{
    from{transform: rotate(0deg);}
    to{transform: rotate(360deg);}
}

.title_currently{
  grid-row: 1;
  grid-column: 3;
}

.artist_currently{
  grid-row: 2;
  grid-column: 3;
}
span .mp{
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  min-width: 0;
  max-width: 100%;
   width:100%;
  display:block;
}

.mp_button{
  height: 40px;
  width: 40px;
  background-position: center;
  background-size: contain;
  background-repeat: no-repeat;
  border-radius: 50%;
  transition: 0.7s;
  cursor: pointer;
}

.repeat{
  
}

.mp_button:hover{
  -webkit-backdrop-filter: saturate(200%) blur(0.5rem);
  backdrop-filter: saturate(200%) blur(0.3rem);
  box-shadow: 0 0 .7rem #151515;
  transition: 0.7s;
}

.play{
  background-image: url(play\(neu\).svg);
}

.forward{
  background-image: url(forward.svg);
}

.backward{
  background-image: url(backward.svg);
}

.kill{
  background-image: url(kill.svg);

}

.repeat{
  background-image: url(repeat.svg);
}

.mp_button_wrapper{
  display: flex;
position: fixed;
justify-content: space-around;
align-items: center;
width: 380px;
margin-top: 10px;
}

.music_player input{
  width: 370px;
  -webkit-appearance: none;
  appearance: none;
  background: transparent;
  cursor: pointer;
  outline: none;
}

/*thumb styling for chrome */
#slider::-webkit-slider-thumb {
	-webkit-appearance: none;
	width: 10px;
	height: 10px;
	background-color: white;
	border-radius: 50%;
	cursor: pointer;
	outline: none;
	box-shadow: 0 0 0 0 rgba(98,0,238,.1);
	transition: .3s ease-in-out;
}

/* thumb styling for firefox */
#slider::-moz-range-thumb{
  -webkit-appearance: none;
	width: 10px;
	height: 10px;
	background: white;
	border-radius: 50%;
	cursor: pointer;
	outline: none;
	box-shadow: 0 0 0 0 rgba(98,0,238,.1);
	transition: .3s ease-in-out;
  border: none;
}

.mp_bottom_text{
  float: right;
  margin-top: 60px;
  font-size: smaller;
}

.down{
  position: absolute;
  right: -350px;
  font-size:x-large;
}

.mp_time{
  position: absolute;
  left: 330px;
  top: 60px;
}



.cover_currently {
    position: relative;
    height: 50px; /* Set the height of your circular element */
    width: 50px; /* Set the width of your circular element */
    border-radius: 50%; /* Create a circular shape */
    overflow: hidden; /* Hide overflowing content */
    background-image: url(cover.jpg);
    background-size: cover;
    transition: background-size 0.3s ease; /* Add smooth transition on background size */
}

.cover_currently::before {
    content: ''; /* Create a pseudo-element to overlay the background */
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: inherit;
    background-size: 100%; /* Initially cover the pseudo-element */
    transition: transform 0.3s ease; /* Add smooth transition on transform */
    transform: scale(1); /* Set initial scale to 1 (normal size) */
}

.cover_currently:hover::before {
    transform: scale(1.2); /* Scale up the pseudo-element (background image) on hover */
}

</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>
<script src="https://cdnjs.cloudflare.com/ajax/libs/color-thief/2.3.0/color-thief.umd.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
      const musicPlayer = document.getElementById('music_player');
      const colorThief = new ColorThief();
      let currentIndex = 0;
      let vibrantColors = [];
  
      // Function to update background color with transition
      function updateBackgroundColor() {
          const coverImage = document.getElementById('cover_currently');
  
          // Check if the cover image has a valid background image source
          const backgroundImage = coverImage.style.backgroundImage;
          if (!backgroundImage || backgroundImage === 'none') {
              return; // No valid background image
          }
  
          // Extract the URL from the background image CSS property
          const imageUrl = backgroundImage.replace(/url\(['"]?([^'"]*)['"]?\)/, '$1');
  
          // Create a new image element to load the cover image
          const img = new Image();
          img.crossOrigin = 'Anonymous';
          img.onload = function() {
              // Extract vibrant colors from the loaded cover image
              vibrantColors = colorThief.getPalette(img, 5);
              console.log('Vibrant Colors:', vibrantColors);
  
              // Apply the next color from the palette
              applyNextColor();
          };
          img.src = imageUrl;
      }
  
      // Function to apply the next color from the vibrant colors palette
      function applyNextColor() {
          if (vibrantColors.length === 0) {
              return; // No vibrant colors available
          }
  
          const color = vibrantColors[currentIndex % vibrantColors.length];
          if (!color) {
              return; // Invalid color
          }
  
          musicPlayer.style.backgroundColor = `rgb(${color[0]}, ${color[1]}, ${color[2]})`;
          musicPlayer.style.transition = `3s`;
          currentIndex++;
      }
  
      // Observe changes to the cover image background
      const coverImage = document.getElementById('cover_currently');
      const observer = new MutationObserver(() => {
          updateBackgroundColor();
      });
  
      observer.observe(coverImage, { attributes: true, attributeFilter: ['style'] });
  
      // Call updateBackgroundColor initially and every 3 seconds
      updateBackgroundColor();
      setInterval(updateBackgroundColor, 3000); // Change color every 3 seconds (3000 milliseconds)
  });
  </script>
 
 <script>
  document.addEventListener("DOMContentLoaded", function() {
      const audioFiles = [
          {
              title: "Wassereis",
              artist: "Rubi",
              file: "wassereis.mp3",
              cover: "cover.jpg"
          },
          {
              title: "Anxiety",
              artist: "Julien Bam",
              file: "anxiety.mp3",
              cover: "cover2.jpg"
          },
          {
              title: "Lebenslang",
              artist: "Tream",
              file: "lebenslang.mp3",
              cover: "cover3.jpg"
          },
          {
              title: "Floschnpfand",
              artist: "TURBOBIER",
              file: "Floschnpfand.mp3",
              cover: "cover4.jpg"
          },
          {
              title: "fix net normal",
              artist: "AUT of ORDA",
              file: "AUT of ORDA - fix net normal.mp3",
              cover: "cover5.jpg"
          }
      ];
  
      const audio = document.getElementById('audio');
      const coverImage = document.getElementById('cover_currently');
      const titleElement = document.getElementById('title_currently');
      const artistElement = document.getElementById('artist_currently');
      const playButton = document.getElementById('play');
      const backwardButton = document.getElementById('backward');
      const forwardButton = document.getElementById('forward');
      const slider = document.getElementById('slider');
      const mpTime = document.getElementById('mp_time');
  
      let currentSongIndex = 0;
      let isPlaying = false;
  
      function loadSong(index) {
          const song = audioFiles[index];
          audio.src = song.file;
          titleElement.innerHTML = "<strong>"+song.title+"</strong>";
          artistElement.innerText = song.artist;
          coverImage.style.backgroundImage = `url(${song.cover})`;
      }
  
      function playSong() {
          audio.play();
          playButton.style.backgroundImage = "url('pause.svg')";
          coverImage.style.animation = "rotate 3s linear infinite";
          document.getElementById("lp").style.animation = "rotate 1s linear infinite";
          isPlaying = true;
      }
  
      function pauseSong() {
          audio.pause();
          playButton.style.backgroundImage = "url('play(neu).svg')";
          coverImage.style.animation = "none";
          document.getElementById("lp").style.animation = "";
          isPlaying = false;
      }
  
      function togglePlayPause() {
          if (isPlaying) {
              pauseSong();
          } else {
              playSong();
          }
      }
  
      function nextSong() {
          currentSongIndex = (currentSongIndex + 1) % audioFiles.length;
          loadSong(currentSongIndex);
          playSong(); // Automatically start playing the next song
      }
  
      function previousSong() {
          currentSongIndex = (currentSongIndex - 1 + audioFiles.length) % audioFiles.length;
          loadSong(currentSongIndex);
          playSong(); // Automatically start playing the previous song
      }
  
      audio.addEventListener('ended', () => {
          nextSong();
      });
  
      playButton.addEventListener('click', togglePlayPause);
      forwardButton.addEventListener('click', nextSong);
      backwardButton.addEventListener('click', previousSong);
  
      audio.addEventListener('timeupdate', () => {
          const currentTime = audio.currentTime;
          const duration = audio.duration;
          const progressPercentage = (currentTime / duration) * 100;
          slider.value = progressPercentage;
          mpTime.textContent = `${formatTime(currentTime)}/${formatTime(duration)}`;
      });
  
      slider.addEventListener('input', () => {
          const seekTime = (slider.value / 100) * audio.duration;
          audio.currentTime = seekTime;
      });
  
      // Initial song load
      loadSong(currentSongIndex);
  
      function formatTime(seconds) {
          const minutes = Math.floor(seconds / 60);
          const remainderSeconds = Math.floor(seconds % 60);
          return `${minutes}:${remainderSeconds < 10 ? '0' : ''}${remainderSeconds}`;
      }
  });
  </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>