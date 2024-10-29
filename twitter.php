<?php

/*
 Plugin Name: AJAXed Twitter for Wordpress
 Version: 0.5.1
 Plugin URI: http://derhofbauer.at/blog/ajaxed-twitter-plugin-for-wordpress
 Description: Displays your public Twitter messages for all to read using XMLHttpRequest and mootools. Based on <a href="http://rick.jinlabs.com/code/twitter/">Twitter for Wordpress 1.9.7</a> by <a href="http://rick.jinlabs.com">Ricardo Gonz&aacute;lez</a>.
 Author: Alexander Hofbauer
 Author URI: http://derhofbauer.at/blog
 */

/*  Copyright 2007 Ricardo González Castro (rick[in]jinlabs.com)
 *  Copyright 2010 Alexander Hofbauer

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


if (!defined('AJAXED_TWITTER_FRAMEWORK')) define('AJAXED_TWITTER_FRAMEWORK', 'jquery');

if (!class_exists("AJAXedTwitter")) { class AJAXedTwitter
{
	private static $options = false;
	public static $default_options = array(
		'id' => 'ajaxed-twitter-for-wordpress',
		'title' => 'Tweets',
		'username' => '',
		'num' => 5,
		'list' => true,
		'update' => true,
		'linked' => '#',
		'hyperlinks' => true,
		'twitter-users' => true,
		'encode-utf8' => false,
		'retries' => 2,
		'animate' => true,
		'cache-age' => 1800,
		'timeout' => 2
	);
	public static $option_fields = array(
		'title' => array('label' => 'Title', 'type' => 'text'),
		'username' => array('label' => 'Username', 'type' => 'text'),
		'num' => array('label' => 'Number of links', 'type' => 'text'),
		'list' => array('label' => 'Ouput as unordered list', 'type' => 'checkbox'),
		'update' => array('label' => 'Show timestamps', 'type' => 'checkbox'),
		'linked' => array('label' => 'Linked', 'type' => 'text'),
		'hyperlinks' => array('label' => 'Discover Hyperlinks', 'type' => 'checkbox'),
		'twitter-users' => array('label' => 'Discover @replies', 'type' => 'checkbox'),
		'encode-utf8' => array('label' => 'UTF8 Encode', 'type' => 'checkbox'),
		'retries' => array('label' => 'Retries', 'type' => 'text'),
		'animate' => array('label' => 'Animate', 'type' => 'checkbox'),
		'cache-age' => array('label' => 'Expiry of cached tweets (seconds)', 'type' => 'text')
		//'timeout' => array('label' => 'Timeout for RSS download', 'type' => 'text')
	);
	
	
	public static function init()
	{
		if (is_admin()) return;
		
		if (AJAXED_TWITTER_FRAMEWORK == 'mootools' || AJAXED_TWITTER_FRAMEWORK == 'both') {
			wp_enqueue_script('ajaxed-twitter-mootools', self::plugin_path().'/js/mootools.twitter.js');
		}

		if (AJAXED_TWITTER_FRAMEWORK == 'jquery' || AJAXED_TWITTER_FRAMEWORK == 'both') {
			wp_enqueue_script('jquery.twitter', self::plugin_path().'/js/jquery.twitter.js', array('jquery'));
		}
	}
	
	
	/**
	 * Get Twitter messages
	 * 
	 * If $options is passed empty, stored options are taken instead.
	 */
	public static function messages(array $options)
	{
		if (empty($options)) return;
		self::$options = array_merge(self::$default_options, $options);
		$options = &self::$options;
		
		$output = '';
		$messages = self::fetch_rss('http://twitter.com/statuses/user_timeline/'.$options['username'].'.rss');
		
		if ($options['list']) $output = '<ul class="twitter">';
		
		if ($options['username'] == '') {
			if ($options['list']) $output .= '<li>';
			$output .= '<span class="twitter-error">No Twitter user</span>';
			if ($options['list']) $output .= '</li>';
			
		} else {
			if (empty($messages->items)) {
				if ($options['list']) $output .= '<li>';
				$output .= '<span class="twitter-error">No public Twitter messages.</span>';
				if ($options['list']) $output .= '</li>';
				
			} else {
				foreach ($messages->items as $i => $message ) {
					$msg = ' '.substr(strstr($message['description'], ': '), 2, strlen($message['description'])).' ';
					if ($options['encode-utf8']) $msg = utf8_encode($msg);
					$link = $message['link'];
	
					if ($options['list']) {
						$last = ($i === $options['num']-1) ? ' last' : '';
						$first = ($i === 0) ? ' first' : '';
						$output .= '<li class="twitter-item'.$last.$first.'">';
					} else if ($options['num'] != 1) {
						$output .= '<p class="twitter-message">';
					}
	
					if ($options['hyperlinks']) $msg = self::hyperlink($msg);
					if ($options['twitter-users']) $msg = self::userlink($msg);
	
					if ($options['linked'] != '' || $options['linked'] != false) {
						if ($options['linked'] == 'all') {
							$msg = '<a href="'.$link.'" class="twitter-link">'.$msg.'</a>';  // Puts a link to the status of each tweet
						} else {
							$msg = $msg . '<a href="'.$link.'" class="twitter-link">'.$options['linked'].'</a>'; // Puts a link to the status of each tweet
								
						}
					}
	
					$output .= $msg;
						
					if ($options['update']) {
						$time = strtotime($message['pubdate']);
							
						if ((abs(time() - $time)) < 86400 ) {
							$h_time = sprintf(__('%s ago'), human_time_diff($time));
						} else {
							$h_time = date(__('Y/m/d'), $time);
						}
						
						$output .= sprintf(__('%s', $options['id']), ' <span class="twitter-timestamp"><abbr title="' . date(__('Y/m/d H:i:s'), $time) . '">' . $h_time . '</abbr></span>' );
					}
					
					if ($options['list']) {
						$output .= '</li>';
					} else if ($options['num'] != 1) {
						$output .= '</p>';
					}
					
					if ($i == $options['num']-1) break;
				}
			}
		}
		
		if ($options['list']) $output .= '</ul>';
		return $output;
	}
	
	
	/**
	 * Copy of fetch_rss() in WP's rss.php (version 2.9.1).
	 * 
	 * We want our personal cache settings without disturbing
	 * any other plugin or altering common cache settings.
	 */
	private static function fetch_rss($url)
	{
		if (!defined('MAGPIE_INPUT_ENCODING')) define('MAGPIE_INPUT_ENCODING', 'UTF-8');
		if (!defined('MAGPIE_OUTPUT_ENCODING')) define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
		if (!defined('MAGPIE_FETCH_TIME_OUT')) define('MAGPIE_FETCH_TIME_OUT', self::$options['timeout']);
		
		// don't know whether the next two are needed
		if (!defined('MAGPIE_CACHE_FRESH_ONLY')) define('MAGPIE_CACHE_FRESH_ONLY', 0);
		if (!defined('MAGPIE_USE_GZIP')) define('MAGPIE_USE_GZIP', true);
	
		include_once(ABSPATH.WPINC.'/rss.php');
		
		if (self::$options['cache-age'] === -1) {
			$resp = _fetch_remote_file($url);
			if (is_success($resp->status)) {
				return _response_to_rss($resp);
			} else {
				return false;
			}
		}
		
		$cache = new RSSCache(
			!defined('MAGPIE_CACHE_DIR') ? './cache' : MAGPIE_CACHE_DIR,
			self::$options['cache-age']
		);
		
		$request_headers = array(); // HTTP headers to send with fetch
		$rss 			 = 0;		// parsed RSS object
		$errormsg		 = 0;		// errors, if any
		$cache_status = (!$cache->ERROR) ? $cache->check_cache($url) : 0;
		
		if ($cache_status == 'HIT') {
			$rss = $cache->get($url);
			if (isset($rss) and $rss) {
				$rss->from_cache = 1;
				return $rss;
			}
		}
		
		if ($cache_status == 'STALE') {
			$rss = $cache->get($url);
			if (isset($rss->etag) and $rss->last_modified) {
				$request_headers['If-None-Match'] = $rss->etag;
				$request_headers['If-Last-Modified'] = $rss->last_modified;
			}
		}
		
		$resp = _fetch_remote_file($url, $request_headers);

		if (isset($resp) and $resp) {
			if ($resp->status == '304') {
				$cache->set($url, $rss);
				return $rss;
			
			} elseif (is_success($resp->status)) {
				$rss = _response_to_rss($resp);
				if ( $rss ) {
					$cache->set($url, $rss);
					return $rss;
				}
			
			} else {
				$errormsg = "Failed to fetch $url. ";
				if ($resp->error) {
					$http_error = substr($resp->error, 0, -2);
					$errormsg .= "(HTTP Error: $http_error)";
				} else {
					$errormsg .= "(HTTP Response: " . $resp->response_code .')';
				}
			}
		
		} else {
			$errormsg = "Unable to retrieve RSS file for unknown reasons.";
		}
		
		if ($rss) return $rss;
		return false;
	}
	
	
	private static function hyperlink($text)
	{
		// Props to Allen Shaw & webmancers.com
		// match protocol://address/path/file.extension?some=variable&another=asf%
		//$text = preg_replace("/\b([a-zA-Z]+:\/\/[a-z][a-z0-9\_\.\-]*[a-z]{2,6}[a-zA-Z0-9\/\*\-\?\&\%]*)\b/i","<a href=\"$1\" class=\"twitter-link\">$1</a>", $text);
		$text = preg_replace(
			'/\b([a-zA-Z]+:\/\/[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',
			'<a href="$1" class="twitter-link">$1</a>',
			$text
		);
		
		// match www.something.domain/path/file.extension?some=variable&another=asf%
		//$text = preg_replace("/\b(www\.[a-z][a-z0-9\_\.\-]*[a-z]{2,6}[a-zA-Z0-9\/\*\-\?\&\%]*)\b/i","<a href=\"http://$1\" class=\"twitter-link\">$1</a>", $text);
		$text = preg_replace(
			'/\b(?<!:\/\/)(www\.[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',
			'<a href="http://$1" class="twitter-link">$1</a>',
			$text
		);
		
		// match name@address
		$text = preg_replace(
			'/\b([a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]*\@[a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]{2,6})\b/i',
			'<a href="mailto://$1" class="twitter-link">$1</a>',
			$text
		);
		
		//mach #trendingtopics. Props to Michael Voigt
		$text = preg_replace(
			'/([\.|\,|\:|\¡|\¿|\>|\{|\(]?)#{1}(\w*)([\.|\,|\:|\!|\?|\>|\}|\)]?)\s/i',
			'$1<a href="http://twitter.com/#search?q=$2" class="twitter-link">#$2</a>$3 ',
			$text
		);
		
		return $text;
	}
	
	
	private static function userlink($text)
	{
		$text = preg_replace(
			'/([\.|\,|\:|\¡|\¿|\>|\{|\(]?)@{1}(\w*)([\.|\,|\:|\!|\?|\>|\}|\)]?)\s/i',
			'$1<a href="http://twitter.com/$2" class="twitter-user">@$2</a>$3 ',
			$text
		);
		
		return $text;
	}
	
	
	public static function plugin_path()
	{
		return '/'.path_join(PLUGINDIR, basename(dirname(__FILE__)));
	}
}}




if (!class_exists("AJAXedTwitterWidget")) { class AJAXedTwitterWidget extends WP_Widget
{
	public function __construct()
	{
		parent::__construct('AJAXedTwitterWidget', 'AJAXed Twitter', array(), array('height' => 500, 'width' => 450));
	}
	

	public function form($instance)
	{
		$options = array_merge(AJAXedTwitter::$default_options, $instance);
		
		echo '<table cellspacing="6">';
		foreach (AJAXedTwitter::$option_fields as $option => $field) {
			if ($field['type'] == 'checkbox') {
				if ($options[$option] === true) {
					$value = 'value="true" checked="checked"';
				} else {
					$value = 'value="false"';
				}
			} else {
				$value = 'value="'.$options[$option].'"';
			}
			
			echo '<tr><td><label for="'.$this->get_field_id($option).'">'.$field['label'].'</label></td>';
			echo '<td><input type="'.$field['type'].'" name="'.$this->get_field_name($option).'" id="'.$this->get_field_id($option).'" '.$value.' /></td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	

	public function update($new_instance, $old_instance)
	{
		foreach (AJAXedTwitter::$default_options as $option => $value) {
			if ($option == 'linked') {
				// linked is a hybrid and can be false or a string
				$new_instance['linked'] = ($new_instance['linked'] === '') ? false : $new_instance['linked'];
				continue;
			}
			
			if (AJAXedTwitter::$option_fields[$option]['type'] == 'checkbox') {
				$new_instance[$option] = (isset($new_instance[$option])) ? true : false;
			} else {
				$new_instance[$option] = $new_instance[$option];
			}
		}
		$new_instance['id'] = $this->id;
		return $new_instance;
	}
	
	
	public function widget($args, $instance)
	{
		$instance = array_merge(AJAXedTwitter::$default_options, $instance);
		
		echo $args['before_widget'];
		echo $args['before_title'].$instance['title'].$args['after_title'];
		echo $args['after_widget'];
		
		if ($instance['username'] == '') return;
		if (AJAXED_TWITTER_FRAMEWORK == 'mootools') {
			echo "<p>Please set AJAXED_TWITTER_FRAMEWORK in wp_config.php to 'both'<br />( define('AJAXED_TWITTER_FRAMEWORK', 'both'); )</p>";
			return;
		}
		
		
		$options = $instance;
		foreach (array('title', 'retries', 'animate') as $item) {
			unset($options[$item]);
		}
		$options = str_replace('\'', '\\\'', json_encode($options));
		
		echo '<script type="text/javascript">';
		echo 'jQuery("#'.$this->id.'")
			.twitter({
				url: "'.get_bloginfo('wpurl').AJAXedTwitter::plugin_path().'/request/tweets.php",
				retries: '.$instance['retries'].',
				animate: '.(($instance['animate']) ? 'true' : 'false').',
				options: \''.$options.'\'
			})
		;';
		echo '</script>';
	}
}}

if (class_exists("AJAXedTwitter")) {
	add_action('init', array('AJAXedTwitter', 'init')); // initialize plugin (enqueues scripts)
	add_action('widgets_init', create_function('', 'return register_widget("AJAXedTwitterWidget");'));
}
