<?php

session_start();

$conn = new mysqli("localhost","root","","banana_game",3307);

$username = $_SESSION['username'];
$score = $_POST['score'];

$stmt = $conn->prepare("UPDATE leaderboard SET bananas=? WHERE username=?");
$stmt->bind_param("is",$score,$username);
$stmt->execute();

$stmt->close();
$conn->close();

?>