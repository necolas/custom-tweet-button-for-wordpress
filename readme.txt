== Verified working with Wordpress 3.0.1

== Requirements

* Bit.ly account and API key
* Wordpress 3.0.1+

For more info: http://nicolasgallagher.com/custom-tweet-button-for-wordpress

== Installation ==

Step 1: Download the Custom Tweet Button for WordPress files from Github.

Step 2: Login to your bit.ly account and get your API key from the "settings" page. Replace the bit.ly username and API key placeholders in the tweet_button function with your own.

Step 3: Include the custom-tweet-button.php file in your theme's functions.php file.

Step 4: Add the custom Tweet Button CSS to your theme's style.css file and the include the tweet.png image in your theme's image folder. Customise the CSS as you desire and make sure the image is correctly referenced.

Step 5: Call the function tweet_button in your template files (e.g. single.php) at the position(s) in the HTML you'd like the Tweet Button to appear:

<?php
if (function_exists('tweet_button'))
   tweet_button(get_permalink());
?>

== Changelog ==

= 0.1 =
* First version