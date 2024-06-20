<div class="swiper mySwiper2 ">
    <div class="video_title">
        <h2>Telehaven - Video Health Talk</h2>
    </div>
    <div class="swiper-wrapper">

        <?php
        // Include database connection
        require_once 'db.php';

        // Fetch videos from the database
        $query = "SELECT * FROM videos ORDER BY id DESC";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $video_url = htmlspecialchars($row['video_url']);
                $video_title = htmlspecialchars($row['video_title']);
        ?>
                <div class="swiper-slide video_slide">
             
                    <div class="video_card">
                        <video controls>
                            <source src="<?php echo $video_url; ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <div class="video_info">
                        <p><?php echo $video_title; ?></p>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
            echo "<p>No videos found.</p>";
        }

        // Close database connection
        $conn->close();
        ?>
    </div>
    <div class="arrows">
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
    <div class="swiper-pagination"></div>
</div>

<style>
   
</style>