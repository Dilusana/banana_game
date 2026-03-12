<?php 
    session_start(); 
    ob_start(); 

    $valid_username = "student"; 
    $valid_password = "password123"; 
    $error_msg = "";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
        $username = $_POST['username']; 
        $password = $_POST['password']; 

        if ($username === $valid_username && $password === $valid_password) { 
            $_SESSION['username'] = $username; 
            header("Location: dashboard.php"); 
            exit(); 
        } else { 
            $error_msg = "Invalid credentials. Please try again."; 
        } 
    } 
    ob_end_flush(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Banana Game - Login</title>
    <style>
    /* 1. Base Reset */
    * {
        box-sizing: border-box;
    }

    body, html {
        margin: 0;
        padding: 0;
        height: 100%;
        width: 100%;
        overflow: hidden;
        font-family: 'Segoe UI', sans-serif;
    }

    /* 2. Responsive Video Background */
    #video-bg {
        position: fixed;
        top: 50%;
        left: 50%;
        min-width: 100%;
        min-height: 100%;
        width: auto;
        height: auto;
        z-index: -1;
        transform: translate(-50%, -50%); /* Keeps video centered */
        object-fit: cover; /* Important: prevents stretching */
    }

    /* 3. Dark Overlay */
    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;

        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px; /* Prevents box from touching screen edges on mobile */
    }

    /* 4. The Login Box (Responsive) */
    .login-container {
        padding: 2rem;
        border-radius: 20px;

        
        /* Flexibility settings */
        width: 100%;
        max-width: 400px; /* Maximum size on desktop */
        margin: auto;
        text-align: center;
    }

    /* 5. Inputs & Buttons */
    .input-group { margin-bottom: 1.5rem; text-align: left; }
    
    label { 
        display: block; 
        font-weight: bold; 
        margin-bottom: 0.5rem; 
        color: #ffffff; 
        
    }
    
    input {
        width: 100%;
        padding: 12px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 16px; /* Prevents iOS auto-zoom on focus */
    }

    button {
        background-color: #f1c40f;
        color: #000000;
        border: none;
        padding: 14px;
        font-weight: bold;
        border-radius: 8px;
        cursor: pointer;
        width: 100%;
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }

    /* 6. Mobile Specific Adjustments */
    @media (max-width: 480px) {
        .login-container {
            padding: 1.5rem;
        }

    }
    </style>
</head>
<body>

    <video autoplay muted loop id="video-bg">
        <source src="assests/loginbackground.mp4" type="video/mp4">
        Your browser does not support HTML5 video.
    </video>

    <div class="overlay">
        <div class="login-container">

            
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

                <button type="submit">Play Now!</button>

                <div class="crete account">
                    <label>Don't have a account click </label>
                </div>

                 <button type="Crete">Create Account</button>

                 <div class="crete account">
                    <label>to create one.</label>
                </div>
            </form>
        </div>
    </div>

</body>
</html>