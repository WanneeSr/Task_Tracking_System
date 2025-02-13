<?php
session_start(); // Start the session

// If the user is already logged in, redirect them to index.php
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    require 'db.php'; // Connect to the database

    // Check if the email exists in the database
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Successful login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: index.php'); // Redirect to index.php after login
        exit();
    } else {
        $error = "Invalid credentials"; // Display error if invalid login
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <link rel="stylesheet" href="style.css">

</head>

<body>
    <div class="container">
        <div class="form_login">
            <form method="POST">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Login</button>
        
    </div>



    <?php if (isset($error)) { echo "<p>$error</p>"; } ?>
    </form>


    <script src="bootstrap\js\bootstrap.bundle.js"></script>
</body>

</html>
<!-- Login form -->