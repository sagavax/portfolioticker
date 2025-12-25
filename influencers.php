<?php
     include("includes/dbconnect.php");
     include("includes/functions.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="css/influencers.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" type="image/png" sizes="32x32" href="investment.png">
    <script src="js/clock.js?<?php echo time() ?>" defer></script>
    <title>Portfolio Ticker - Influencers</title>
</head>
<body>
    <header>
      <a href="."><img src="portfolio-ticker-logo.svg" alt="Portfolio Ticker"></a><input type="text" name="search_inflences"  id="search_influencers" autocomplete="off" placeholder="search influencers here"><button type="button" name="clear_search" title="clear search" class="button small_button clear_button tooltip>"><i class="fa fa-times"></i></button>
            <button type="button" class="button small_button" name="new_influencer" title="add new influencer"><i class="fa fa-plus"></i></button><div class="clockWrapper"><button type ="button" class="secondary" name="worldclock"  id="worldclock">World Clock</button><div id="clock">--:--:--</div></div>
    </header>
    
    <main>
        <div class="grid">
            <div class="card">
              <!--   <div class="card_header">
                    <button class="secondary_button" name="add_influencer"><i class="fa fa-plus"></i> Add influencer</button>
                    <button class="secondary_button" name="delete_influencer"><i class="fa fa-trash"></i> Remove influencer</button>
                    <button class="secondary_button" name="edit_influencer"><i class="fa fa-edit"></i>Edit influencer </button>
                </div>  -->
                   
                <div class="influencers">
                    <h3>Influencers:</h3>
                    <?php echo GetAllInfluencers(); ?>
                </div>
                <div class="list_of_videos">
                    <div class="loading" style="display: none;">Loading...</div>
                    <div class="video_list"></div>
                </div>
            </div>    
        </div>
    </main>    
    
</body>
</html>