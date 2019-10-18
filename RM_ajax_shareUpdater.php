<?php 

/**
* Template Name: RM_ShareUpdater
*
* @package WordPress
* @author Riccardo Mel
*/

//Developed by Riccardo Mel 
// info@riccardomel.com

 //Essential Wp Boostrap
 require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php'); 

 if(isset($_POST['postID'])):
 ?>

    <?php $query = new WP_Query( 'p='.$_POST['postID'].'' ); ?>
    <?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?>

        <?php 
        $optionsPlugin =  get_option( 'share_counter' );
        if($optionsPlugin['facebook_scrapetime'] == ""):
            $scrapeTime = 86400; // Default
        else:
            $scrapeTime = $optionsPlugin['facebook_scrapetime'];
        endif;
        $last_scrape = rm_get_post_fetchdate();
        //print_r("DEBUG: ".$last_scrape);
        if((time()-($scrapeTime)) < strtotime($last_scrape)){
            //echo "Ultimo scrape nelle ultime 24h";
            echo rm_get_post_shares();
        }else{
           //echo "Ultimo scrape NON nelle ultime 24h - fai scrape";
            rm_set_post_shares();
            echo rm_get_post_shares();
        }
        ?>


    <?php endwhile; 
    wp_reset_postdata();
    else : ?>
    <p><?php esc_html_e( '-' ); ?></p>
    <?php endif; ?>

<?php else: ?>
    <p><?php esc_html_e( '-' ); ?></p>
<?php endif; ?>