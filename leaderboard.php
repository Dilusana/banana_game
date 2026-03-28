<?php
// 1. Database Connection (using port 3307 as required)
$conn = new mysqli("localhost", "root", "", "banana_game");

// 2. Connection Check
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. Optimized Query: Sums up bananas per user so they only appear once
$result = $conn->query("SELECT username, SUM(bananas) AS total_bananas 
                        FROM leaderboard 
                        GROUP BY username 
                        ORDER BY total_bananas DESC 
                        LIMIT 10");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Leaderboard</title>
    <style>
        body { font-family: Arial; background: #222; color: white; text-align: center; }
        table { margin: auto; border-collapse: collapse; width: 400px; }
        th, td { padding: 10px; border: 1px solid white; }
        th { background: #f1c40f; color: black; }
        h1 { color: #f1c40f; }
    </style>
</head>
<body>

<h1>🏆 Banana Leaderboard</h1>

<table>
    <tr>
        <th>Rank</th>
        <th>Player</th>
        <th>Bananas</th>
    </tr>

    <?php
    // INITIALIZE RANK HERE
    $rank = 1; 

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . $rank . "</td>
                    <td>" . htmlspecialchars($row['username']) . "</td>
                    <td>" . $row['total_bananas'] . "</td>
                  </tr>";
            $rank++;
        }
    } else {
        echo "<tr><td colspan='3'>No scores yet. Be the first!</td></tr>";
    }
    
    $conn->close();
    ?>
</table>

<br>
<a href="gamemap.php" style="color: #f1c40f; text-decoration: none;">⬅ Back to Game</a>

</body>
</html>
