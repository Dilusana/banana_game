<?php 
    session_start(); 
    ob_start(); 

    $error_msg = "";

    // 1. Login Logic
    if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
        $username = isset($_POST['username']) ? $_POST['username'] : ""; 
        $password = isset($_POST['password']) ? $_POST['password'] : ""; 

        // 2. Database Connection (Using Port 3307)
        $conn = new mysqli("localhost", "root", "", "banana_game");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // 3. Query the 'users' table for the matching username and password
        // Use prepared statements to prevent SQL Injection
        $stmt = $conn->prepare("SELECT username FROM users WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) { 
            // Successful Login
            $_SESSION['username'] = $username; 
            
            header("Location: gamemap.php"); 
            exit(); 
        } else { 
            // Failed Login
            $error_msg = "Invalid username or password. Please try again."; 
        }

        $stmt->close();
        $conn->close();
    } 
    ob_end_flush(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Banana Game - Login</title>
    <style>
        /* Base Reset */
        * { box-sizing: border-box; }
        body, html { margin: 0; padding: 0; height: 100%; width: 100%; overflow: hidden; font-family: 'Segoe UI', sans-serif; }

        /* Video Background */
        #video-bg {
            position: fixed; top: 50%; left: 50%; min-width: 100%; min-height: 100%;
            z-index: -1; transform: translate(-50%, -50%); object-fit: cover;
        }

        /* Overlay & Box */
        .overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            display: flex; justify-content: center; align-items: center; padding: 20px;
        }

        .login-container {
            padding: 2rem; border-radius: 20px; width: 100%; max-width: 400px;
            text-align: center; background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.2);
        }

        .input-group { margin-bottom: 1.5rem; text-align: left; }
        label { display: block; font-weight: bold; margin-bottom: 0.5rem; color: #ffffff; }
        input { width: 100%; padding: 12px; border-radius: 8px; border: none; font-size: 16px; }

        .btn-play {
            background-color: #f1c40f; color: #000; border: none; padding: 14px;
            font-weight: bold; border-radius: 8px; cursor: pointer; width: 100%;
            font-size: 1.1rem; transition: 0.3s;
        }

        .btn-create {
            background-color: transparent; color: #f1c40f; border: 1px solid #f1c40f;
            padding: 10px; border-radius: 8px; cursor: pointer; width: 100%; margin-top: 10px;
        }

        .error { color: #ff4d4d; font-weight: bold; margin-bottom: 1rem; }
        .help-text { color: #ccc; font-size: 0.9rem; margin-top: 15px; display: block; }
    </style>
</head>
<body>

    <video autoplay muted loop id="video-bg">
        <source src="assests/loginbackground.mp4" type="video/mp4">
    </video>

    <div class="overlay">
        <div class="login-container">
            <h2 style="color: white; margin-bottom: 20px;">🍌 Jungle Login</h2>

            <?php if($error_msg): ?>
                <p class="error"><?php echo $error_msg; ?></p>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="input-group">
                    <label>Username</label>
                    <input type="text" name="username" required placeholder="Enter username">
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Enter password">
                </div>

                <button type="submit" class="btn-play">Play Now!</button>
                
                <span class="help-text">Don't have an account?</span>
                <button type="button" class="btn-create" onclick="window.location.href='register.php'">Create Account</button>
            </form>
        </div>
    </div>

</body>
</html>