<?php
include 'db.php'; // Include database connection

$result = $conn->query("SELECT * FROM podcasts");
if ($result === false) {
    die("Error fetching data: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>View Podcasts</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
       
        .swiper-slide {
            display: flex;
            justify-content: center;
            align-items: center;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .podcast_card {
            display: flex;
            flex-direction: column;
            width: 100%;
        
        }
        .podcast_card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        .podcast_audio {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }
        .podcast_audio audio {
            display: none;
        }
        .podcast_audio .play-button, .podcast_audio .download-button {
            cursor: pointer;
            margin-right: 10px;
            font-size: 24px;
            color: #555;
        }
        .podcast_info {
            margin-top: 10px;
        }
       
    </style>
</head>
<body>
    <div class="swiper-container">
        <div class="swiper mySwiper">
            <div class="podcast_title">
               <h2>Telehaven - Audio Health Podcast</h2>
            </div>
            <div class="swiper-wrapper">
                <?php while($row = $result->fetch_assoc()) { ?>
                <div class="swiper-slide podcast_slide">
                    <div class="podcast_card">
                        <img src="<?php echo htmlspecialchars($row['image'] ?? 'default.jpg'); ?>" alt="">
                        <div class="podcast_info">
                            <p><?php echo htmlspecialchars($row['title'] ?? 'No Title'); ?></p>
                            <p>Topic: <?php echo htmlspecialchars($row['topic'] ?? 'No Topic'); ?></p>
                            <p>Host: <?php echo htmlspecialchars($row['host'] ?? 'No Host'); ?></p>
                        </div>
                        <div class="podcast_audio">
                            <audio id="audio-<?php echo htmlspecialchars($row['id']); ?>" src="<?php echo htmlspecialchars($row['audio']); ?>"></audio>
                            <span class="play-button" id="play-<?php echo htmlspecialchars($row['id']); ?>"><i class="fas fa-play"></i></span>
                            <span class="pause-button" id="pause-<?php echo htmlspecialchars($row['id']); ?>" style="display: none;"><i class="fas fa-pause"></i></span>
                            <a class="download-button" id="download-<?php echo htmlspecialchars($row['id']); ?>" href="<?php echo htmlspecialchars($row['audio']); ?>" download><i class="fas fa-download"></i></a>
                            <span class="time" id="timestamp-<?php echo htmlspecialchars($row['id']); ?>">0:00</span>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 1,
            spaceBetween: 10,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                640: {
                    slidesPerView: 1,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 2,
                    spaceBetween: 40,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 50,
                },
            },
        });

        function initializeAudioPlayer(count) {
            const audio = document.getElementById('audio-' + count);
            const playBtn = document.getElementById('play-' + count);
            const pauseBtn = document.getElementById('pause-' + count);
            const timestamp = document.getElementById('timestamp-' + count);

            playBtn.addEventListener('click', () => {
                audio.play();
                playBtn.style.display = 'none';
                pauseBtn.style.display = 'inline-block';
            });

            pauseBtn.addEventListener('click', () => {
                audio.pause();
                pauseBtn.style.display = 'none';
                playBtn.style.display = 'inline-block';
            });

            audio.addEventListener('timeupdate', () => {
                // Update the timestamp during normal playback
                const minutes = Math.floor(audio.currentTime / 60);
                const seconds = Math.floor(audio.currentTime - minutes * 60);
                const formattedTime = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                timestamp.textContent = formattedTime;
            });
        }

        // Initialize audio players
        const numPlayers = <?php echo $result->num_rows; ?>;
        for (let i = 1; i <= numPlayers; i++) {
            initializeAudioPlayer(i);
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>



