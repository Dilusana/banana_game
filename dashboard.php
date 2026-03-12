<?php 
    session_start(); 
    if (!isset($_SESSION['username'])) { 
        header("Location: index.html"); 
        exit(); 
    } 
 
    // Retrieve the IP address and username from the cookie if it exists 
    if (isset($_COOKIE['user_info'])) { 
        $user_info = json_decode($_COOKIE['user_info'], true); 
 
        // Convert IPv6 loopback (::1) to IPv4 loopback (127.0.0.1) 
        if ($user_info['ip'] === '::1') { 
            $user_info['ip'] = '127.0.0.1'; 
        } 
    } else { 
        $user_info = null; 
    } 
    ?> 
    <!DOCTYPE html> 
    <html lang="en"> 
    <head> 
        <meta charset="UTF-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <title>Dashboard</title> 
    </head> 
    <body> 
        <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2> 
        <p>Your IP address: <?php echo $user_info ? $user_info['ip'] : 'N/A'; ?></p> 
        <p>Username from cookie: <?php echo $user_info ? $user_info['username'] : 'N/A'; 
?></p> 
        <p><a href="logout.php">Logout</a></p> 
    </body> 
    </html> 