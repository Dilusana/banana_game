   <?php 
    session_start(); 
 
    // Destroy session 
    session_unset(); 
    session_destroy(); 
 
    // Clear cookie 
    setcookie('user_info', '', time() - 3600, '/'); 
 
    // Redirect to login page 
    header("Location: index.php"); 
    exit(); 
    ?> 