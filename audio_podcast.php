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

 

    <style>
      
        
        .podcast_card {
            display: flex;
            flex-direction: column;
            width: 100%;
            text-align: left;
        border: 2px solid #ddd;
        margin-top: 30px;
     
        }
        .podcast_card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            /* border-radius: 10px; */
            
        }
        .podcast_audio {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }
        .podcast_audio audio {
            display: none;
        }
        .podcast_audio .play-button, .podcast_audio .download-button {
            cursor: pointer;
            margin-right: 10px;
            color: #555;
        }

        .podcast_info{
            padding: 0 5%;
            margin-top: 20px;
        }

        .podcast_audio{
            padding: 0 5%;
            margin-bottom: 20px;
        }
        .podcast_slide{
            background-color: transparent;
        }

        .podcast_all{
            margin-top: 50px;
        }
       
    </style>
</head>
<body>
    <div class="podcast_all">
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
                         
                            <p>Topic: <?php echo htmlspecialchars($row['topic'] ?? 'No Topic'); ?></p>
                            <p>Host: <?php echo htmlspecialchars($row['host'] ?? 'No Host'); ?></p>
                        </div>
                        <div class="podcast_audio">
                            <audio id="audio-<?php echo htmlspecialchars($row['id']); ?>" src="<?php echo htmlspecialchars($row['audio']); ?>"></audio>
                            <span class="play-button" id="play-<?php echo htmlspecialchars($row['id']); ?>"><i class="fas fa-play"></i></span>
                            <span class="pause-button" id="pause-<?php echo htmlspecialchars($row['id']); ?>" style="display: none;"><i class="fas fa-pause"></i></span>
                            <span class="time" id="timestamp-<?php echo htmlspecialchars($row['id']); ?>">0:00</span>
                            <a class="download-button" id="download-<?php echo htmlspecialchars($row['id']); ?>" href="<?php echo htmlspecialchars($row['audio']); ?>" download><i class="fas fa-download"></i></a>
                          
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <!-- <div class="arrows"> -->
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    <!-- </div> -->
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



