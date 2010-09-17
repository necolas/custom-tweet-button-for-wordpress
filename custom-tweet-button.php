<?php
/*
Project name: Custom Tweet Button for Wordpress
Project URI: http://nicolasgallagher.com/custom-tweet-button-for-wordpress/
Description: A fully customisable HTML and CSS Tweet Button for Wordpress build using PHP and the bit.ly and Twitter APIs.
Version: 0.1
Author: Nicolas Gallagher
Author URI: http://nicolasgallagher.com

Copyright 2010 Nicolas Gallagher

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function tweet_button($url) {

    // Your bit.ly API credentials
    $bitly_login = "";
    $bitly_key = "";
    
    // Your twitter account names
    $twitter_via = "";
    // optional: add a related account
    // $twitter_related = "";
    
    global $post;
    $cache_interval = 60;
    $retweet_count = null;
    $count = 0;
    
    if (get_post_status($post->ID) == 'publish') {
        $title = $post->post_title;
        
        if ((function_exists('curl_init') || function_exists('file_get_contents')) && function_exists('json_decode')) {
            // shorten url
            if (get_post_meta($post->ID, 'bitly_short_url', true) == '') {
                $short_url = null;
                $short_url = shorten_bitly($url, $bitly_key, $bitly_login);
                if ($short_url) {
                    add_post_meta($post->ID, 'bitly_short_url', $short_url);
                }
            }
            else {
                $short_url = get_post_meta($post->ID, 'bitly_short_url', true);
            }

            // retweet data (twitter API)
            $retweet_meta = get_post_meta($post->ID, 'retweet_cache', true);
            if ($retweet_meta != '') {
                $retweet_pieces = explode(':', $retweet_meta);
                $retweet_timestamp = (int)$retweet_pieces[0];
                $retweet_count = (int)$retweet_pieces[1];
            }
            // expire retweet cache
            if ($retweet_count === null || time() > $retweet_timestamp + $cache_interval) {
                $retweet_response = urlopen('http://urls.api.twitter.com/1/urls/count.json?url=' . urlencode($url));
                if ($retweet_response) {
                    $retweet_data = json_decode($retweet_response, true);
                    if (isset($retweet_data['count']) && (int)$retweet_data['count'] >= $retweet_count) {
                        $retweet_count = $retweet_data['count'];
                        if ($retweet_meta == '') {
                            add_post_meta($post->ID, 'retweet_cache', time() . ':' . $retweet_count);
                        } else {
                            update_post_meta($post->ID, 'retweet_cache', time() . ':' . $retweet_count);
                        }
                    }
                }
            }
            
            // optional: 
            // manually set the starting number of retweets for a post that existed before the Tweet Button was created
            // the number can be roughly calculated by subtracting the twitter API's retweet count
            // from the estimated number of retweets according to the topsy, backtype, or tweetmeme services
            
            // this will check for the value of a Wordpress custom field called "retweet_count_start"
            $retweet_count_start = get_post_meta($post->ID, 'retweet_count_start', true);
            
            // calculate the total count to display
            $count = $retweet_count + (int)$retweet_count_start;
        }
        
        // construct the tweet button query string
        $twitter_params = 
        '?text=' . urlencode($title) . '+-' .
        '&amp;url=' . urlencode($short_url) . 
        '&amp;counturl=' .urlencode($url). 
        '&amp;via=' . $twitter_via . 
        //'&amp;related=' . $twitter_related .
        ''
        ;

        // HTML for the tweet button
        $twitter_share = '
        <div class="twitter-share">
            <a class="twitter-button" 
               rel="external nofollow" 
               title="Share this article on Twitter" 
               href="http://twitter.com/share' . $twitter_params . '" 
               target="_blank">Tweet this article</a>
            <span class="twitter-count">' . $count . '</span>
        </div>
        ';

        echo $twitter_share;
    }
}

// convert file contents into string
function urlopen($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    } else {
        return file_get_contents($url);
    }
}

// bit.ly url shortening
function shorten_bitly($url, $bitly_key, $bitly_login) {
    if ($bitly_key && $bitly_login && function_exists('json_decode')) {
        $bitly_params = '?login=' . $bitly_login . '&apiKey=' .$bitly_key . '&longUrl=' . urlencode($url);
        $bitly_response = urlopen('http://api.j.mp/v3/shorten' . $bitly_params);
        if ($bitly_response) {
            $bitly_data = json_decode($response, true);
            if (isset($bitly_data['data']['url'])) {
                $bitly_url = $bitly_data['data']['url'];
            }
        }
    }
    return $bitly_url;
}
?>