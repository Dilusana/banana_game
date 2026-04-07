<?php
session_start(); // Add session start to get current user

// 1. Database Connection (using port 3307 as required)
$conn = new mysqli("localhost", "root", "", "banana_game");

// 2. Connection Check
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get current logged in user
$current_user = isset($_SESSION['username']) ? $_SESSION['username'] : null;

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

<audio id="bgMusic" loop autoplay>
    <source src="assests/game_music.mp3" type="audio/mpeg">
</audio>

<script>
    (function() {
        const audio = document.getElementById('bgMusic');
        
        // Set volume
        audio.volume = 0.3;
        
        // Try to play immediately
        const playAudio = () => {
            audio.play().then(() => {
                console.log('🎵 Music playing successfully!');
            }).catch(error => {
                console.log('Autoplay blocked:', error);
                // Create a big play button as last resort
                createPlayButton();
            });
        };
        
        // Create floating play button if autoplay fails
        function createPlayButton() {
            const btn = document.createElement('div');
            btn.innerHTML = '🎵 Click to Enable Music 🎵';
            btn.style.cssText = `
                position: fixed;
                bottom: 30px;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(0,0,0,0.9);
                color: #ffd700;
                padding: 12px 25px;
                border-radius: 50px;
                font-size: 16px;
                font-weight: bold;
                cursor: pointer;
                z-index: 10000;
                font-family: Arial;
                border: 2px solid #ffd700;
                box-shadow: 0 0 20px rgba(0,0,0,0.5);
                animation: pulse 1s infinite;
            `;
            
            // Add animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes pulse {
                    0% { transform: translateX(-50%) scale(1); }
                    50% { transform: translateX(-50%) scale(1.05); }
                    100% { transform: translateX(-50%) scale(1); }
                }
            `;
            document.head.appendChild(style);
            
            btn.onclick = () => {
                audio.play();
                btn.remove();
            };
            
            document.body.appendChild(btn);
            
            // Auto-hide after 10 seconds
            setTimeout(() => {
                if (btn.parentNode) btn.style.opacity = '0.5';
            }, 10000);
        }
        
        // Try to play when page loads
        window.addEventListener('load', playAudio);
        
        // Also try on any user interaction (for mobile)
        document.addEventListener('click', () => {
            if (audio.paused) {
                audio.play();
            }
        }, { once: true });
    })();
</script>
    <style>
        body { 
            font-family: Arial; 
            background-color: #222;
            background-image: url('assests/leaderboard.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: white; 
            text-align: center;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        table { 
            margin: auto; 
            border-collapse: collapse; 
            width: 500px; 
            background-color: rgba(241, 196, 15, 0.95);
            color: #222; 
            font-weight: bold;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        
        th, td { 
            padding: 12px 15px; 
            border: 1px solid #d4ac0d; 
        }
        
        th { 
            background: #04430e; 
            color: white; 
            font-size: 18px;
        }
        
        h1 { 
            color: #160111; 
            font-size: 48px;
            text-shadow: 2px 2px 4px rgba(186, 180, 167, 0.57);
            margin-bottom: 30px;
        }
        
        /* Special styling for top 3 rows */
        .rank-1 {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            font-size: 18px;
        }
        
        .rank-2 {
            background: linear-gradient(135deg, #c0c0c0, #e8e8e8);
            font-size: 17px;
        }
        
        .rank-3 {
            background: linear-gradient(135deg, #cd7f32, #e8a87b);
            font-size: 16px;
        }
        
        /* Current user row highlighting */
        .current-user {
            background: #277fae !important;
            color: white !important;
            border: 2px solid gold;
            box-shadow: 0 0 10px rgba(39, 174, 96, 0.5);
            font-weight: bold;
        }
        
        .current-user td {
            border-color: gold;
        }
        
        /* Badge styles */
        .badge {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            text-align: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 18px;
        }
        
        .badge-gold {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #8B6914;
            box-shadow: 0 0 5px gold;
        }
        
        .badge-silver {
            background: linear-gradient(135deg, #c0c0c0, #e8e8e8);
            color: #555;
            box-shadow: 0 0 5px silver;
        }
        
        .badge-bronze {
            background: linear-gradient(135deg, #cd7f32, #e8a87b);
            color: #5c3a1a;
            box-shadow: 0 0 5px #cd7f32;
        }
        
        /* Trophy emoji for rank 1 */
        .trophy {
            font-size: 20px;
        }
        
        /* Menu button container */
        .menu-container {
            margin-top: 30px;
            display: inline-block;
        }
        
        /* Responsive table */
        @media (max-width: 600px) {
            table {
                width: 95%;
                font-size: 14px;
            }
            
            th, td {
                padding: 8px 10px;
            }
            
            h1 {
                font-size: 36px;
            }
        }
        
        /* User info bar */
        .user-info {
            background: rgba(0,0,0,0.7);
            display: inline-block;
            padding: 10px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 16px;
            border: 1px solid #f1c40f;
        }
        
        .user-info span {
            color: #f1c40f;
            font-weight: bold;
        }
        
        /* No data message */
        .no-data {
            padding: 40px;
            font-size: 18px;
            background: rgba(0,0,0,0.7);
            border-radius: 10px;
            display: inline-block;
        }
    </style>
</head>
<body>

<h1>🏆 Banana Leaderboard 🏆</h1>

<?php if ($current_user): ?>
    <div class="user-info">
        Logged in as: <span><?php echo htmlspecialchars($current_user); ?></span>
    </div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Rank</th>
            <th>Player</th>
            <th>Bananas</th>
        </tr>
    </thead>
    <tbody>

    <?php
    // INITIALIZE RANK HERE
    $rank = 1; 
    $current_user_found = false;

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Determine row class for styling
            $row_class = '';
            $badge_html = '';
            
            // Add badge and special styling for top 3 ranks
            if ($rank == 1) {
                $row_class = 'rank-1';
                $badge_html = '<span class="badge badge-gold">👑</span>';
            } elseif ($rank == 2) {
                $row_class = 'rank-2';
                $badge_html = '<span class="badge badge-silver">🥈</span>';
            } elseif ($rank == 3) {
                $row_class = 'rank-3';
                $badge_html = '<span class="badge badge-bronze">🥉</span>';
            }
            
            // Check if this is the current logged in user
            $is_current_user = ($current_user && $row['username'] === $current_user);
            if ($is_current_user) {
                $row_class = 'current-user';
                $current_user_found = true;
            }
            
            // Display rank with trophy for #1
            $rank_display = $rank;
            if ($rank == 1) {
                $rank_display = '<span class="trophy">🏆</span> ' . $rank;
            }
            
            echo "<tr class='$row_class'>";
            echo "<td>" . $rank_display . " " . $badge_html . "</td>";
            echo "<td>" . htmlspecialchars($row['username']);
            if ($is_current_user) {
                echo " 👈 (You)";
            }
            echo "</td>";
            echo "<td>🍌 " . number_format($row['total_bananas']) . "</td>";
            echo "</tr>";
            
            $rank++;
        }
        
        // If current user is not in top 10, show their rank separately
        if ($current_user && !$current_user_found) {
            // Get current user's total bananas and rank
            $user_query = $conn->prepare("SELECT SUM(bananas) AS total_bananas 
                                          FROM leaderboard 
                                          WHERE username = ? 
                                          GROUP BY username");
            $user_query->bind_param("s", $current_user);
            $user_query->execute();
            $user_result = $user_query->get_result();
            
            if ($user_row = $user_result->fetch_assoc()) {
                $user_total = $user_row['total_bananas'];
                
                // Calculate user's rank
                $rank_query = $conn->query("SELECT COUNT(DISTINCT username) + 1 AS user_rank 
                                            FROM leaderboard 
                                            WHERE bananas > (SELECT SUM(bananas) FROM leaderboard WHERE username = '$current_user')");
                $rank_row = $rank_query->fetch_assoc();
                $user_rank = $rank_row['user_rank'] ?? ($rank + 1);
                
                echo "<tr style='background: #2c3e50; border-top: 2px solid gold;'>";
                echo "<td colspan='3' style='text-align: center; color: gold; font-size: 12px;'>--- Your Rank ---</td>";
                echo "</tr>";
                echo "<tr class='current-user'>";
                echo "<td>#" . $user_rank . "</td>";
                echo "<td>" . htmlspecialchars($current_user) . " 👈 (You)</td>";
                echo "<td>🍌 " . number_format($user_total) . "</td>";
                echo "</tr>";
            }
            $user_query->close();
        }
    } else {
        echo "<tr><td colspan='3'><div class='no-data'>🍌 No scores yet. Be the first! 🍌</div></td></tr>";
    }
    
    $conn->close();
    ?>
    </tbody>
</table>

<br>
<div class="menu-container">
    <a href="index.php" 
       style="
         position:relative;
         display:inline-block;
         width:160px;
         text-decoration: none;
       ">

        <img src="assests/button.png" alt="Menu"
             style="
               width:100%;
               height:auto;
               display:block;
             ">

        <span style="
            position:absolute;
            top:50%;
            left:50%;
            transform:translate(-50%, -50%);
            color:white;
            font-weight:bold;
            font-size:30px;
            pointer-events:none;
        ">
            Menu
        </span>
    </a>
</div>

</body>
</html>

//