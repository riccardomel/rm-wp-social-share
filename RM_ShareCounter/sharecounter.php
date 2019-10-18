<?php
/*
Plugin Name: Riccardo Mel - Share Counter
Plugin URI: https://riccardomel.com
Description: Riccardo Mel - Share Counter
Version: 1.0
Author: Riccardo Mel
Author URI: https://riccardomel.com
License: GPLv2
*/

//Future enhancement
//google Events works for every button, now works only with print
//Print js code embed in the plugin (now it's on theme)
//Icon Bundle Selections


//Start Plugin
//Pinterest counter
function PINTEREST_get_share_count( $url ) {
    $check_url = 'http://api.pinterest.com/v1/urls/count.json?callback=pin&url=' . urlencode( $url );
    $response = wp_remote_retrieve_body( wp_remote_get( $check_url ) );
     
    $response = str_replace( 'pin({', '{', $response);
    $response = str_replace( '})', '}', $response);
    $encoded_response = json_decode( $response, true );
     
    $share_count = intval( $encoded_response['count'] ); 
    return $share_count;
}

//Facebook counter
function curl_get_shares( $url ){
    $optionsPlugin =  get_option( 'share_counter' );
	$access_token = ''.$optionsPlugin['facebook_token'].'';
	$api_url = 'https://graph.facebook.com/v3.0/?id=' . urlencode( $url ) . '&fields=engagement&access_token=' . $access_token;
	$fb_connect = curl_init(); // initializing
	curl_setopt( $fb_connect, CURLOPT_URL, $api_url );
	curl_setopt( $fb_connect, CURLOPT_RETURNTRANSFER, 1 ); // return the result, do not print
	curl_setopt( $fb_connect, CURLOPT_TIMEOUT, 20 );
	$json_return = curl_exec( $fb_connect ); // connect and get json data
	curl_close( $fb_connect ); // close connection
	$body = json_decode( $json_return );
	return intval( $body->engagement->share_count );
}

//return current FB post_shares_count
function rm_get_post_shares() {
    $count = get_post_meta( get_the_ID(), 'post_shares_count', true );
    if($count == ""){ $count = 0; }
    return "$count";
}

//update FB post_shares_count
function rm_set_post_shares() {
    $key = 'post_shares_count';
    $post_id = get_the_ID();
    $count = (int) curl_get_shares( get_the_permalink() );
    update_post_meta( $post_id, $key, $count );
}

//Add to Table Admin FB post_shares_count
function rm_posts_column_shares( $columns ) {
    $columns['post_shares'] = 'Shares';
    return $columns;
}

function rm_posts_custom_column_shares( $column ) {
    if ( $column === 'post_shares') {
        echo rm_get_post_shares();
    }
}

//Check latest Fetch
function rm_get_post_fetchdate(){
    $last_scrape = get_post_meta( get_the_ID(), 'post_share_last_scrape', true );
    $key = 'post_share_last_scrape';
    $post_id = get_the_ID();

    if($last_scrape == "" || $last_scrape == "null"){ 
        update_post_meta( $post_id, $key, date('Y-m-d') );
    }else{
        update_post_meta( $post_id, $key, date('Y-m-d') );
        return "$last_scrape";
    }
}

add_filter( 'manage_posts_columns', 'rm_posts_column_shares' );
add_action( 'manage_posts_custom_column', 'rm_posts_custom_column_shares' );

/*
USAGE:
<?php
//Place it in your theme:
<?php if(shortcode_exists( 'share-counter' )) { echo do_shortcode("[share-counter]"); } ?>
*/

add_shortcode ( 'share-counter', 'riccardomel_share_counter' );
//shortcode function
function riccardomel_share_counter( $atts, $content ) {
    extract( shortcode_atts( array (
        'method' => ''
    ), $atts ) );
    
    global $post; 
    //is Single
    if(is_single()):
    $optionsPlugin =  get_option( 'share_counter' );
    ?>

        <?php 
        if($optionsPlugin['trigger_ajax'] == "No"):
            $last_scrape = rm_get_post_fetchdate();
            //print_r("DEBUG: ".$last_scrape);
            if($optionsPlugin['facebook_scrapetime'] == ""):
                $scrapeTime = 86400; // Default
            else:
                $scrapeTime = $optionsPlugin['facebook_scrapetime'];
            endif;
            if((time()-($scrapeTime)) < strtotime($last_scrape)){
                //echo "Ultimo scrape nelle ultime 24h";
                //Do Nothing
            }else{
               //echo "Ultimo scrape NON nelle ultime 24h - fai scrape";
               //Run Scrape
               rm_set_post_shares();
            }
        endif;
        ?>

	    <div class="social-single-padder">
			<div class="shareBox">
			    <ul>
                    <?php if($optionsPlugin['trigger_share_print'] == "Yes"): ?>
                        <?php if($optionsPlugin['trigger_google_events'] == "Yes"): ?>
                            <li class="share_btn print" onclick="window.RM_PrintPage.print();  gtag('event', 'RM_Print');">
                        <?php else: ?>
                            <li class="share_btn print" onclick="window.RM_PrintPage.print();">
                        <?php endif; ?>
                            <i class="fa fa-print" aria-hidden="true"></i><span class="mobileshare">Stampa</span>
                        </li>
                    <?php endif; ?>

                    <?php if($optionsPlugin['trigger_share_fb'] == "Yes"): ?>
                        <a rel="nofollow" target="_blank" href="https://www.facebook.com/dialog/share?app_id=<?php echo get_option('facebook_appid'); ?>&display=popup&href=<?php echo get_the_permalink() ?>">
                            <li class="share_btn facebook" id="facebookShare" >
                                <i class="fab fa-facebook-f"></i>
                                <span class="mobileshare">facebook</span>
                                <?php if($optionsPlugin['trigger_share_fb_counter'] == "Yes"): ?>
                                    <div class="results_share"> 
                                        <?php 
                                        if($optionsPlugin['trigger_ajax'] == "No"):
                                        echo rm_get_post_shares(); 
                                        endif;
                                        ?> 
                                    </div>
                                <?php endif; ?>
                            </li>
                        </a>
                    <?php endif; ?>

                    <?php if($optionsPlugin['trigger_share_pinterest'] == "Yes"): ?>
                        <a rel="nofollow" href="https://www.pinterest.com/pin/create/button/?url=<?php echo get_the_permalink() ?>&description=<?php echo get_the_title() ?>&media=<?php	echo the_post_thumbnail_url('full-thumbnail'); ?>" target="_blank" >
                            <li class="share_btn pinterest"  id="pinterestShare">
                                <i class="fab fa-pinterest" aria-hidden="true"></i>
                                <span class="mobileshare">Pinterest</span>
                            </li>
                        </a>
                    <?php endif; ?>

                    <?php if($optionsPlugin['trigger_share_wh'] == "Yes"): ?>
                        <a rel="nofollow" target="_blank" href="whatsapp://send?text=<?php echo get_the_permalink() ?>" >
                            <li class="share_btn whatsapp" id="whatsappShare" >
                                <i class="fab fa-whatsapp" aria-hidden="true"></i>
                                <span class="mobileshare">Whatsapp</span>
                            </li>
                        </a>
                    <?php endif; ?>

                    <?php if($optionsPlugin['trigger_share_tw'] == "Yes"): ?>
                        <a  rel="nofollow" target="_blank" href="https://twitter.com/intent/tweet?text=<?php echo get_the_title() ?>&url=<?php echo get_the_permalink() ?>" >
                            <li class="share_btn twitter" id="twitterShare" >
                                <i class="fab fa-twitter" aria-hidden="true"></i>
                                <span class="mobileshare">Twitter</span>
                            </li>
                        </a>
                    <?php endif; ?>

                    <?php if($optionsPlugin['trigger_share_linkedin'] == "Yes"): ?>
                        <a  rel="nofollow" target="_blank" href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo get_the_permalink() ?>" >
                            <li class="share_btn linkedin" id="linkedinShare" >
                                <i class="fab fa-linkedin" aria-hidden="true"></i>
                                <span class="mobileshare">Linkedin</span>
                            </li>
                        </a>
                    <?php endif; ?>
                    
					<?php if(shortcode_exists( 'favorite_button' ) && $optionsPlugin['trigger_share_favorities'] == "Yes") { echo do_shortcode("[favorite_button]"); } ?>	
                
                </ul>
			</div>
		</div><!--social-single-padder -->

<?php
    endif;//is Single
}


// Register stylesheet and javascript with hook 'wp_enqueue_scripts', which can be used for front end CSS and JavaScript
add_action( 'wp_enqueue_scripts', 'riccardomel_share_counter_scripts' );
//function that enqueue script only if shortcode is used
function riccardomel_share_counter_scripts() {
    global $post;

    //is Single
    if(is_single()):

        //Style
        wp_register_style( 'riccardomel_share_counter_style', plugins_url( 'style.css', __FILE__ ) );
        wp_enqueue_style( 'riccardomel_share_counter_style' );

        //Load JS only for Ajax
        $optionsPlugin =  get_option( 'share_counter' );
        if($optionsPlugin['trigger_ajax'] == "Yes"):
            wp_register_script( 'riccardomel_share_counter_script', plugins_url( 'sharecounter.js', __FILE__ ) );
            wp_enqueue_script( 'riccardomel_share_counter_script' );
        endif;
       
    endif;//is Single

}//riccardomel_share_counter_scripts


//Options Page
class ShareCounterSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'RM Share Counter', 
            'manage_options', 
            'my-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'share_counter' );
        ?>
        <div class="wrap">
            <h1>Share Counter by Riccardo Mel</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'share_counter_group' );
                do_settings_sections( 'my-setting-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'share_counter_group', // Option group
            'share_counter', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Share Counter settings page', // Title
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );  


        add_settings_field(
            'trigger_ajax', // ID
            'Enable Ajax?', // Title 
            array( $this, 'trigger_ajax_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'trigger_google_events', // ID
            'Enable Google Events?', // Title 
            array( $this, 'trigger_google_events_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'facebook_token', // ID
            'Facebook: APP|SECRET', // Title 
            array( $this, 'facebook_token_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );     


        add_settings_field(
            'facebook_scrapetime', // ID
            'Facebook Scrape Time', // Title 
            array( $this, 'facebook_scrapetime_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'trigger_share_fb', // ID
            'Enable Facebook?', // Title 
            array( $this, 'trigger_share_fb_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'trigger_share_fb_counter', // ID
            'Enable Facebook counter?', // Title 
            array( $this, 'trigger_share_fb_counter_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'trigger_share_tw', // ID
            'Enable Twitter?', // Title 
            array( $this, 'trigger_share_tw_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'trigger_share_wh', // ID
            'Enable Whatsapp?', // Title 
            array( $this, 'trigger_share_wh_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'trigger_share_pinterest', // ID
            'Enable Pinterest?', // Title 
            array( $this, 'trigger_share_pinterest_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'trigger_share_linkedin', // ID
            'Enable Linkedin?', // Title 
            array( $this, 'trigger_share_linkedin_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'trigger_share_print', // ID
            'Enable Print?', // Title 
            array( $this, 'trigger_share_print_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'trigger_share_favorities', // ID
            'Enable Favorities integration?', // Title 
            array( $this, 'trigger_share_favorities_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );   
    
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['trigger_share_fb'] ) )
            $new_input['trigger_share_fb'] =  $input['trigger_share_fb'];

        if( isset( $input['trigger_share_fb_counter'] ) )
            $new_input['trigger_share_fb_counter'] =  $input['trigger_share_fb_counter'];

        if( isset( $input['facebook_token'] ) )
            $new_input['facebook_token'] = sanitize_text_field( $input['facebook_token'] );

        if( isset( $input['trigger_share_tw'] ) )
            $new_input['trigger_share_tw'] =  $input['trigger_share_tw'];

        if( isset( $input['trigger_share_wh'] ) )
            $new_input['trigger_share_wh'] =  $input['trigger_share_wh'];

        if( isset( $input['trigger_share_pinterest'] ) )
            $new_input['trigger_share_pinterest'] =  $input['trigger_share_pinterest'];

        if( isset( $input['trigger_share_linkedin'] ) )
            $new_input['trigger_share_linkedin'] =  $input['trigger_share_linkedin'];

        if( isset( $input['trigger_share_print'] ) )
            $new_input['trigger_share_print'] =  $input['trigger_share_print'];

        if( isset( $input['trigger_share_favorities'] ) )
            $new_input['trigger_share_favorities'] =  $input['trigger_share_favorities'];

        if( isset( $input['trigger_ajax'] ) )
            $new_input['trigger_ajax'] =  $input['trigger_ajax'];

        if( isset( $input['trigger_google_events'] ) )
            $new_input['trigger_google_events'] =  $input['trigger_google_events'];

        if( isset( $input['facebook_scrapetime'] ) )
            $new_input['facebook_scrapetime'] =  $input['facebook_scrapetime'];

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print '<strong>NOTE:</strong> Facebook Scrape Time - default: 86400 (24h: 60x60x24) 
        <br /> <strong>NOTE2:</strong> If you enable Ajax create this page: /rm-shareupdater/ in your theme and assign the custom page template called "RM_ShareUpdater" present inside the plugin folder 
        <br /> <strong>NOTE3:</strong> All icons are from font-awesome. If you need another set please change the i tag inside the plugin.
        <br /> <strong>NOTE4:</strong> All Google Events required gtag of Google Anlytics. If use GTM please set to No.
        <br /> Edit your share trigger options below: ';
    }

    /** 
     * Get the settings option array and print one of its values
     */


    public function trigger_ajax_callback()
    {
        printf(
            '<select  id="trigger_ajax" name="share_counter[trigger_ajax]">
                <option>%s</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>',
            isset( $this->options['trigger_ajax'] ) ? esc_attr( $this->options['trigger_ajax']) : ''
        );

    }//trigger_ajax_callback


    public function trigger_google_events_callback()
    {
        printf(
            '<select  id="trigger_google_events" name="share_counter[trigger_google_events]">
                <option>%s</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>',
            isset( $this->options['trigger_google_events'] ) ? esc_attr( $this->options['trigger_google_events']) : ''
        );

    }//trigger_google_events_callback


    public function trigger_share_fb_callback()
    {
        printf(
            '<select  id="trigger_share_fb" name="share_counter[trigger_share_fb]">
                <option>%s</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>',
            isset( $this->options['trigger_share_fb'] ) ? esc_attr( $this->options['trigger_share_fb']) : ''
        );

    }//trigger_share_fb_callback
    public function trigger_share_fb_counter_callback()
    {
        printf(
            '<select  id="trigger_share_fb_counter" name="share_counter[trigger_share_fb_counter]">
                <option>%s</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>',
            isset( $this->options['trigger_share_fb_counter'] ) ? esc_attr( $this->options['trigger_share_fb_counter']) : ''
        );

    }//trigger_share_fb_counter_callback

    public function facebook_token_callback()
    {
        printf(
            '<input type="text" id="facebook_token" name="share_counter[facebook_token]" value="%s" />',
            isset( $this->options['facebook_token'] ) ? esc_attr( $this->options['facebook_token']) : ''
        );

    }//facebook_token_callback


    public function facebook_scrapetime_callback()
    {
        printf(
            '<input type="text" id="facebook_scrapetime" name="share_counter[facebook_scrapetime]" value="%s" />',
            isset( $this->options['facebook_scrapetime'] ) ? esc_attr( $this->options['facebook_scrapetime']) : ''
        );

    }//facebook_scrapetime_callback

    public function trigger_share_tw_callback()
    {
        printf(
            '<select  id="trigger_share_tw" name="share_counter[trigger_share_tw]">
                <option>%s</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>',
            isset( $this->options['trigger_share_tw'] ) ? esc_attr( $this->options['trigger_share_tw']) : ''
        );


    }//trigger_share_tw_callback

    public function trigger_share_wh_callback()
    {
        printf(
            '<select  id="trigger_share_wh" name="share_counter[trigger_share_wh]">
                <option>%s</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>',
            isset( $this->options['trigger_share_wh'] ) ? esc_attr( $this->options['trigger_share_wh']) : ''
        );


    }//trigger_share_wh_callback

    public function trigger_share_pinterest_callback()
    {
        printf(
            '<select  id="trigger_share_pinterest" name="share_counter[trigger_share_pinterest]">
                <option>%s</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>',
            isset( $this->options['trigger_share_pinterest'] ) ? esc_attr( $this->options['trigger_share_pinterest']) : ''
        );


    }//trigger_share_pinterest_callback

    public function trigger_share_linkedin_callback()
    {
        printf(
            '<select  id="trigger_share_linkedin" name="share_counter[trigger_share_linkedin]">
                <option>%s</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>',
            isset( $this->options['trigger_share_linkedin'] ) ? esc_attr( $this->options['trigger_share_linkedin']) : ''
        );


    }//trigger_share_linkedin_callback

    public function trigger_share_print_callback()
    {
        printf(
            '<select  id="trigger_share_print" name="share_counter[trigger_share_print]">
                <option>%s</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>',
            isset( $this->options['trigger_share_print'] ) ? esc_attr( $this->options['trigger_share_print']) : ''
        );


    }//trigger_share_print_callback

    public function trigger_share_favorities_callback()
    {
        printf(
            '<select  id="trigger_share_favorities" name="share_counter[trigger_share_favorities]">
                <option>%s</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>',
            isset( $this->options['trigger_share_favorities'] ) ? esc_attr( $this->options['trigger_share_favorities']) : ''
        );


    }//trigger_share_favorities_callback


}

if( is_admin() )
    $my_settings_page = new ShareCounterSettingsPage();
    