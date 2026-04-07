<?php
session_start();

header('Content-Type: text/plain');

if (!isset($_SESSION['username'])) {
    echo "error: not logged in";
    exit;
}

if (!isset($_POST['score'])) {
    echo "error: no score provided";
    exit;
}

$username = $_SESSION['username'];
$score = intval($_POST['score']);

$conn = new mysqli("localhost", "root", "", "banana_game");

if ($conn->connect_error) {
    echo "error: database connection failed";
    exit;
}

// Check if user exists in leaderboard
$stmt = $conn->prepare("SELECT bananas FROM leaderboard WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Update existing record
    $updateStmt = $conn->prepare("UPDATE leaderboard SET bananas = ? WHERE username = ?");
    $updateStmt->bind_param("is", $score, $username);
    if ($updateStmt->execute()) {
        echo "success: score updated to " . $score;
    } else {
        echo "error: update failed";
    }
    $updateStmt->close();
} else {
    // Insert new record
    $insertStmt = $conn->prepare("INSERT INTO leaderboard (username, bananas) VALUES (?, ?)");
    $insertStmt->bind_param("si", $username, $score);
    if ($insertStmt->execute()) {
        echo "success: new record inserted with score " . $score;
    } else {
        echo "error: insert failed";
    }
    $insertStmt->close();
}

$stmt->close();
$conn->close();
?>