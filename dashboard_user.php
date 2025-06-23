<?php
session_start();
require 'config.php';
require 'includes/functions.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['voice_log'])) {
    $log = trim($_POST['voice_log']);
    if ($log !== "") {
        $encrypted = encryptData($log);
        $stmt = $conn->prepare("INSERT INTO navigation_logs (admin_id, log_data) VALUES (?, ?)");
        $stmt->bind_param("i", $userId, $encrypted);
        $stmt->execute();
        $message = "📝 Log saved successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Eye Assist - Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary: #007BFF;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
            --gray: #6c757d;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #e0f7fa, #e8f5e9);
        }

        .container {
            max-width: 720px;
            margin: auto;
            padding: 1rem;
        }

        .card {
            background: #fff;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        h2 {
            text-align: center;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        label {
            font-weight: 500;
            display: block;
            margin-bottom: 0.5rem;
            color: var(--gray);
        }

        textarea,
        input[type="text"] {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            color: green;
            text-align: center;
            margin-bottom: 1rem;
        }

        .map,
        #camera {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        #map {
            height: 300px;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .actions a {
            flex: 1;
            text-align: center;
            text-decoration: none;
            padding: 0.75rem;
            border-radius: 8px;
            background: var(--light);
            color: var(--primary);
            font-weight: bold;
            border: 1px solid var(--primary);
        }

        .actions a:hover {
            background: var(--primary);
            color: white;
        }

        .camera-container {
            text-align: center;
        }

        @media (max-width: 480px) {
            .actions {
                flex-direction: column;
                gap: 0.5rem;
            }

            button {
                font-size: 0.95rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <h2>👋 Welcome, <?= htmlspecialchars($username) ?></h2>
            <?php if (!empty($message)): ?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>📍 Live Location</h3>
            <div id="map" class="map"></div>
        </div>

        <div class="card">
            <h3>📝 Voice Logs</h3>
            <form method="post">
                <label>Record a feedback or instruction:</label>
                <textarea name="voice_log" rows="3" placeholder="e.g., Obstacle ahead, turn left..."></textarea>
                <button type="submit">Save Log</button>
            </form>
        </div>

        <div class="card">
            <h3>🗣️ Speak Instruction</h3>
            <form onsubmit="speakText(event)">
                <label>What should Eye Assist say?</label>
                <input type="text" id="speechText" placeholder="Type instruction..." required>
                <button type="submit">🔊 Speak Now</button>
            </form>
        </div>

        <div class="card">
            <h3>🎤 Voice Command</h3>
            <button onclick="startVoiceControl()">🎙 Start Listening</button>
            <p id="recognized" style="margin-top: 1rem; color: #007BFF;"></p>
        </div>

        <div class="card camera-container">
            <h3>📷 Camera Preview</h3>
            <video id="camera" autoplay playsinline></video>
        </div>

        <div class="actions">
            <a href="logs.php">📚 View Logs</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>

    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyADXhFsWeOPpaaRlRYWieL7z7w4W4jL9ZM&callback=initMap" async defer></script>

    <script>
        let map, marker;

        function initMap() {
            const defaultLoc = {
                lat: 14.5995,
                lng: 120.9842
            };
            map = new google.maps.Map(document.getElementById("map"), {
                center: defaultLoc,
                zoom: 16
            });

            marker = new google.maps.Marker({
                position: defaultLoc,
                map: map,
                title: "Your Location"
            });

            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(pos => {
                    const coords = {
                        lat: pos.coords.latitude,
                        lng: pos.coords.longitude
                    };
                    marker.setPosition(coords);
                    map.setCenter(coords);
                    speak(`📍 New location: Latitude ${coords.lat.toFixed(4)}, Longitude ${coords.lng.toFixed(4)}`);
                }, err => {
                    speak("⚠️ Location error: " + err.message);
                });
            } else {
                speak("⚠️ Geolocation not supported.");
            }
        }

        function speak(text) {
            const utter = new SpeechSynthesisUtterance(text);
            utter.lang = 'en-US';
            window.speechSynthesis.speak(utter);
        }

        function speakText(event) {
            event.preventDefault();
            const text = document.getElementById("speechText").value;
            if (text.trim() !== "") speak(text);
        }

        function startVoiceControl() {
            if (!('webkitSpeechRecognition' in window)) {
                alert("Speech recognition not supported in this browser.");
                return;
            }

            const recognition = new webkitSpeechRecognition();
            recognition.lang = 'en-US';
            recognition.continuous = false;
            recognition.interimResults = false;

            recognition.onresult = function(event) {
                const command = event.results[0][0].transcript.toLowerCase();
                document.getElementById("recognized").textContent = "You said: " + command;
                speak("You said " + command);

                if (command.includes("stop") || command.includes("exit")) {
                    speak("Stopping voice assistant.");
                } else if (command.includes("log")) {
                    speak("Opening logs.");
                    window.location.href = 'logs.php';
                }
            };

            recognition.onerror = function(event) {
                speak("❌ Voice error: " + event.error);
            };

            recognition.start();
        }

        // Start Camera Stream
        const cam = document.getElementById("camera");
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({
                    video: true
                })
                .then(stream => cam.srcObject = stream)
                .catch(() => speak("⚠️ Camera access denied."));
        } else {
            speak("⚠️ Camera not supported.");
        }
    </script>
</body>

</html>