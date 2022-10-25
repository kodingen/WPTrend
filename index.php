<?php
/*
Plugin Name: WP Twitter Trends
Plugin URI: https://karson.com.tr
Description: Twitter Trend Topics Extension
Version: 0.1
Author: Karson IT LTD
Author URI: https://www.karson.com.tr
License: GNU
*/

if (!defined('ABSPATH')) exit;
add_action('admin_menu', 'WpTrend');

function WpTrend()
{

    add_menu_page('WP Twitter Trend', 'WP Twitter Trend', 'manage_options', 'WpTrend', 'ttgeneralfunctions');
}

function ttgeneralfunctions()
{
    if ($_POST["action"] == "save") {
        if (!isset($_POST["csrf"]) || !wp_verify_nonce($_POST["csrf"], 'csrf')) {
            echo "CSRF TOKEN ERROR!";
        } else {
            $key = sanitize_text_field($_POST["key"]);
            $secret = sanitize_text_field($_POST["secret"]);
            $woeid = sanitize_text_field($_POST["woeid"]);
            $count = sanitize_text_field($_POST["count"]);

            update_option('key', $key);
            update_option('secret', $secret);
            update_option('woeid', $woeid);
            update_option('count', $count);

            echo '<div class="updated">Successfull: WpTrend Setting Updated! :)</div>';
        }

    }
    echo "
<div class='wrap'>
<h1>WpTrends Settings</h1>
<div class='card'>
<form method='post'>
" . wp_nonce_field('csrf', 'csrf') . "

<div>
<h4 class='title'>Api Key</h4>
<input type='text' style='width:100%; border-radius: 10px;' name='key' value='" . get_option('key') . "'></div>
<div>
<h4 class='title'>Api Secret</h4>
<input type='text' style='width:100%; border-radius: 10px;' name='secret' value='" . get_option('secret') . "'></div>
<div>
<h4 class='title'>Woeid (Location ID from Yahoo)</h4>
<input type='number' style='width:100%; border-radius: 10px; ' name='woeid' value='" . get_option('woeid') . "'></div>

<div>
<h4 class='title'>Show Item Count</h4>
<input type='number' style='width:100%; border-radius: 10px; ' name='count' value='" . get_option('count') . "'></div>
<div><input type='hidden' name='action'  value='save'></div>

<p class='submit'>
		<input type='submit' name='submit' id='submit' class='button button-primary' value='Save Settings'>		
		<span class='spinner'></span>
	</p>
</form>
		</div>
</div>";

}

function list_trends()
{
    include_once('twitterRequest.php');
    $settings = array(
        'oauth_access_token' => "",
        'oauth_access_token_secret' => "",
        'consumer_key' => get_option('key'),
        'consumer_secret' => get_option('secret')
    );
    $url = 'https://api.twitter.com/1.1/trends/place.json';
    $getfield = '?id='.get_option('woeid');
    $requestMethod = 'GET';
    $twitter = new TwitterAPIExchange($settings);
    $trends = $twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest();
    $trend_decode=json_decode($trends,true)[0]["trends"];
    $list="";
    foreach ($trend_decode as $key=>$val ){
        if ($key+1 <= get_option('count')){
        if ($val["tweet_volume"] != "") { $volume ="(".$val["tweet_volume"].")"; } else { $volume ="";}
        $list .= "<li class='WpTrendList' style='list-style: none'><a class='WpTrendLink' target='_blank' href='$val[url]'>$val[name] $volume</a></li>";
    }
    }

    return $list;

}


function wptrend_register_widget()
{
    register_widget('wptrend_widget');
}
add_action('widgets_init', 'wptrend_register_widget');
class wptrend_widget extends WP_Widget
{
    function __construct()
    {
        parent::__construct(
            'wptrend_widget',
            __('WpTrend', ' wptrend_widget_desc'),

            array('description' => __('WpTrend', 'wptrend_widget_desc'),)
        );
    }

    public function widget($args, $instance)
    {
        $title = apply_filters('widget_title', $instance['title']);
        echo $args['before_widget'];

        if (!empty($title))
            echo $args['before_title'] . $title . $args['after_title'];

        echo __(list_trends(), 'wptrend_widget_desc');
        echo $args['after_widget'];
    }

    public function form($instance)
    {
        if (isset($instance['title']))
            $title = $instance['title'];
        else
            $title = __('Twitter Trends', 'wptrend_widget_desc');
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>"/>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }

}