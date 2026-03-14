<?php
session_start();
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password']; // Note: use password_hash() for real security

    $conn = new mysqli("localhost", "root", "", "banana_game", 3307);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if username exists in the users table
    $checkUser = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $checkUser->bind_param("s", $user);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows > 0) {
        $message = "<p style='color:red;'>Username already taken!</p>";
    } else {
        // Start a transaction to ensure both tables are updated together
        $conn->begin_transaction();

        try {
            // 1. Insert into users table
            $stmt1 = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt1->bind_param("ss", $user, $pass);
            $stmt1->execute();

            // 2. Insert into leaderboard table to initialize score
            $stmt2 = $conn->prepare("INSERT INTO leaderboard (username, bananas) VALUES (?, 0)");
            $stmt2->bind_param("s", $user);
            $stmt2->execute();

            $conn->commit();
            $message = "<p style='color:green;'>Account created!</p>";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "<p style='color:red;'>Error creating account: " . $e->getMessage() . "</p>";
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Account - Banana Game</title>
    <style>
        body { background: #222; color: white; font-family: Arial; text-align: center; padding-top: 50px; }
        .reg-box { background: #333; padding: 30px; display: inline-block; border-radius: 15px; border: 2px solid #f1c40f; }
        input { display: block; margin: 10px auto; padding: 12px; width: 250px; border-radius: 5px; border: none; }
        button { background: #f1c40f; border: none; padding: 10px 20px; cursor: pointer; font-weight: bold; width: 100%; border-radius: 5px; color: black; }
        .link { margin-top: 15px; display: block; color: #ccc; text-decoration: none; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="reg-box">
        <h2>🍌 Join the Jungle</h2>
        <?php echo $message; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Choose Username" required>
            <input type="password" name="password" placeholder="Choose Password" required>
            <button type="submit">Create Account</button>
        </form>
        <style>
.login-btn{
    display:inline-block;
    margin-top:15px;
    padding:10px 18px;
    border-radius:8px;
    border:2px solid #f1c40f;
    color:#f1c40f;
    text-decoration:none;
    font-weight:bold;
    font-family:Segoe UI, sans-serif;
    transition:0.3s;
}

.login-btn:hover{
    background:#f1c40f;
    color:black;
    transform:scale(1.05);
}
</style>
<a href="login.php" class="login-btn">Login</a>

    </div>
</body>
</html>