<div class="blog_all">
                <div class="swiper mySwiper">
                    <div class="blog_title">
                        <h2>Telehaven - Health Blog</h2>
                    </div>
                    <div class="swiper-wrapper">
                        <?php
                        include 'db.php';
                        $sql = "SELECT * FROM blogs ORDER BY created_at DESC";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $id = htmlspecialchars($row['id']);
                                $image = htmlspecialchars($row['image']);
                                $title = htmlspecialchars(substr($row['title'], 0, 60));
                                echo <<<HTML
                    <div class="swiper-slide blog_slide">
                        <a href="view_blog.php?id=$id">
                            <div class="blog_card">
                                <img src="blogs/$image" alt="">
                                <div class="blog_info">
                                    <p>$title...</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    HTML;
                            }
                        } else {
                            echo "<p>No Health Blog available</p>";
                        }
                        ?>
                    </div>
                    <div class="arrows">
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                    <br>
                    <div class="swiper-pagination"></div>
                </div>
            </div>