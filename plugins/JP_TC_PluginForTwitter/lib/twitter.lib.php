<?php
/*
 * Copyright (c) <2008> Justin Poliey <jdp34@njit.edu>
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * Twitterlibphp is a PHP implementation of the Twitter API, allowing you
 * to take advantage of it from within your PHP applications.
 *
 * @author Justin Poliey <jdp34@njit.edu>
 * @package twitterlibphp
 */

/**
 * Twitter API abstract class
 * @package twitterlibphp
 */

/*
	Additional fix jparker(jparker2.0(at)gmail.com).
*/


abstract class TwitterBase {

	/**
	 * the last HTTP status code returned
	 * @access private
	 * @var integer
	 */
	private $http_status;

	/**
	 * the whole URL of the last API call
	 * @access private
	 * @var string
	 */
	private $last_api_call;

	/**
	 * the application calling the API
	 * @access private
	 * @var string
	 */
	private $application_source;

	/**
	 * Returns the 20 most recent statuses from non-protected users who have set a custom user icon.
	 * @param string $format Return format
	 * @return string
	 */
	function getPublicTimeline($format = 'xml') {
		return $this->apiCall('statuses/public_timeline', 'get', $format, array(), false);
	}

	/**
	 * Returns the 20 most recent statuses posted by the authenticating user and that user's friends.
	 * @param array $options Options to pass to the method
	 * @param string $format Return format
	 * @return string
	 */
	function getFriendsTimeline($options = array(), $format = 'json') {
		return $this->apiCall('statuses/friends_timeline', 'get', $format, $options);
	}

	/**
	 * Returns the 20 most recent statuses posted from the authenticating user.
	 * @param array $options Options to pass to the method
	 * @param string $format Return format
	 * @return string
	 */
	function getUserTimeline($options = array(), $format = 'json') {
		return $this->apiCall('statuses/user_timeline', 'get', $format, $options, true);
	}

	/**
	* Returns the 20 most recent mentions (status containing @username) for the authenticating user.
	* @param array $options Options to pass to the method
	* @param string $format Return format
	* @return string
	*/
	function getMentions($options = array(), $format = 'xml') {
		return $this->apiCall("statuses/mentions", 'get', $format, $options);
	}

	/**
	* Returns the 20 most recent @replies (status updates prefixed with @username) for the authenticating user.
	* @param array $options Options to pass to the method
	* @param string $format Return format
	* @return string
	* @deprecated
	*/
	function getReplies($options = array(), $format = 'json') {
		return $this->apiCall('statuses/replies', 'get', $format, $options);
	}

	/**
	 * Returns a single status, specified by the $id parameter.
	 * @param string|integer $id The numerical ID of the status to retrieve
	 * @param string $format Return format
	 * @return string
	 */
	function getStatus($id, $format = 'xml') {
		return $this->apiCall("statuses/show/{$id}", 'get', $format, array(), false);
	}

	/**
	 * Updates the authenticated user's status.
	 * @param string $status Text of the status, no URL encoding necessary
	 * @param string|integer $reply_to ID of the status to reply to. Optional
	 * @param string $format Return format
	 * @return string
	 */
	function updateStatus($status, $reply_to = null, $format = 'json') {
		$options = array('status' => $status);
//		$options['source'] = "TextcubeTweet";
		if ($reply_to) {
			$options['in_reply_to_status_id'] = $reply_to;
		}
		return $this->apiCall('statuses/update', 'post', $format, $options);
	}

	/**
	 * Destroys the status specified by the required ID parameter. The authenticating user must be the author of the specified status.
	 * @param integer|string $id ID of the status to destroy
	 * @param string $format Return format
	 * @return string
	 */
	function destroyStatus($id, $format = 'json') {
		return $this->apiCall("statuses/destroy/{$id}", 'post', $format, $options);
	}

	/**
	 * Returns the authenticating user's friends, each with current status inline.
	 * @param array $options Options to pass to the method
	 * @param string $format Return format
	 * @return string
	 */
	function getFriends($options = array(), $format = 'json') {
		return $this->apiCall('statuses/friends', 'get', $format, $options, true);
	}

	/**
	 * Returns the authenticating user's followers, each with current status inline.
	 * @param array $options Options to pass to the method
	 * @param string $format Return format
	 * @return string
	 */
	function getFollowers($options = array(), $format = 'json') {
		return $this->apiCall('statuses/followers', 'get', $format, $options);
	}

	/**
	 * Returns extended information of a given user.
	 * @param array $options Options to pass to the method
	 * @param string $format Return format
	 * @return string
	 */
	function showUser($options = array(), $format = 'xml') {
		if (!array_key_exists('id', $options) && !array_key_exists('user_id', $options) && !array_key_exists('screen_name', $options)) {
			$options['id'] = substr($this->credentials, 0, strpos($this->credentials, ':'));
		}
		return $this->apiCall('users/show', 'get', $format, $options, false);
	}

	function getSearch($options = array(), $format = 'json') {
		return $this->apiCall('', 'get', $format, $options, true, 'http://search.twitter.com/search.' . $format);
	}

	/**
	 * Returns a list of the 20 most recent direct messages sent to the authenticating user.
	 * @param array $options Options to pass to the method
	 * @param string $format Return format
	 * @return string
	 */
	function getMessages($options = array(), $format = 'json') {
		return $this->apiCall('direct_messages', 'get', $format, $options);
	}
	
	function getRecipientsList($options = array(), $format = 'json') {
		return $this->apiCall('direct_messages/recipients_list', 'get', $format, $options, true, '', false);
	}

	/**
	 * Returns a list of the 20 most recent direct messages sent by the authenticating user.
	 * @param array $options Options to pass to the method
	 * @param string $format Return format
	 * @return string
	 */
	function getSentMessages($options = array(), $format = 'json') {
		return $this->apiCall('direct_messages/sent', 'get', $format, $options);
	}

	/**
	 * Sends a new direct message to the specified user from the authenticating user.
	 * @param string $user The ID or screen name of a recipient
	 * @param string $text The message to send
	 * @param string $format Return format
	 * @return string
	 */
	function newMessage($user, $text, $format = 'json') {
		$options = array(
			'user' => $user,
			'text' => $text
		);
		return $this->apiCall('direct_messages/new', 'post', $format, $options);
	}

	/**
	 * Destroys the direct message specified in the required $id parameter.
	 * @param integer|string $id The ID of the direct message to destroy
	 * @param string $format Return format
	 * @return string
	 */
	function destroyMessage($id, $format = 'json') {
		return $this->apiCall("direct_messages/destroy/{$id}", 'post', $format, $options);
	}

	/**
	 * Befriends the user specified in the ID parameter as the authenticating user.
	 * @param array $options Options to pass to the method
	 * @param string $format Return format
	 * @return string
	 */
	function createFriendship($id, $options = array(), $format = 'json') {
		if (!array_key_exists('follow', $options)) {
			$options['follow'] = 'true';
		}
		return $this->apiCall("friendships/create/{$id}", 'post', $format, $options);
	}

	/**
	 * Discontinues friendship with the user specified in the ID parameter as the authenticating user.
	 * @param integer|string $id The ID or screen name of the user to unfriend
	 * @param string $format Return format
	 * @return string
	 */
	function destroyFriendship($id, $format = 'json') {
		$options = array('id' => $id);
		return $this->apiCall('friendships/destroy', 'post', $format, $options);
	}

	/**
	 * Tests if a friendship exists between two users.
	 * @param integer|string $user_a The ID or screen name of the first user
	 * @param integer|string $user_b The ID or screen name of the second user
	 * @param string $format Return format
	 * @return string
	 */
	function friendshipExists($user_a, $user_b, $format = 'json') {
		$options = array(
			'user_a' => $user_a,
			'user_b' => $user_b
		);
		return $this->apiCall('friendships/exists', 'get', $format, $options);
	}

	/**
	 * Returns an array of numeric IDs for every user the specified user is followed by.
	 * @param array $options Options to pass to the method
	 * @param string $format Return format
	 * @return string
	 */
	function getFriendIDs($options = array(), $format = 'json') {
		return $this->apiCall('friends/ids', 'get', $format, $options);
	}

	/**
	 * Returns an array of numeric IDs for every user the specified user is following.
	 * @param array $options Options to pass to the method
	 * @param string $format Return format
	 * @return string
	 */
	function getFollowerIDs($options = array(), $format = 'json') {
		return $this->apiCall('followers/ids', 'get', $format, $options);
	}

	/**
	 * Returns an HTTP 200 OK response code and a representation of the requesting user if authentication was successful; returns a 401 status code and an error message if not.
	 * @param string $format Return format
	 * @return string
	 */
	function verifyCredentials($format = 'json') {
		return $this->apiCall('account/verify_credentials', 'get', $format, array());
	}

  /**
	 * Returns the remaining number of API requests available to the requesting user before the API limit is reached for the current hour.
	 * @param boolean $authenticate Authenticate before calling method
   * @param string $format Return format
	 * @return string
	 */
	function rateLimitStatus($authenticate = false, $format = 'json') {
		return $this->apiCall('account/rate_limit_status', 'get', $format, array(), $authenticate);
	}

	/**
	 * Ends the session of the authenticating user, returning a null cookie.
	 * @param string $format Return format
	 * @return string
	 */
	function endSession($format = 'xml') {
		return $this->apiCall('account/end_session', 'post', $format, array());
	}

	/**
	 * Sets which device Twitter delivers updates to for the authenticating user.
	 * @param string $device The delivery device used. Must be sms, im, or none
	 * @return string
	 */
	function updateDeliveryDevice($device, $format = 'json') {
		$options = array('device' => $device);
		return $this->apiCall('account/update_delivery_device', 'post', $format, $options);
	}

	/**
	 * Sets one or more hex values that control the color scheme of the authenticating user's profile page on twitter.com.
	 * @param array $options Options to pass to the method
	 * @param string $format Return format
	 * @return string
	 */
	function updateProfileColors($options, $format = 'json') {
		return $this->apiCall('account/update_profile_colors', 'post', $format, $options);
	}

	/**
	 * Sets values that users are able to set under the "Account" tab of their settings page.
	 * @param array $options Options to pass to the method
	 * @param string $format Return format
	 * @return string
	 */
	function updateProfile($options, $format = 'json') {
		return $this->apiCall('account/update_profile', 'post', $format, array());
	}


	/**
	 * Returns the 20 most recent favorite statuses for the authenticating user or user specified by the ID parameter in the requested format.
	 * @param array $options Options to pass to the method
	 * @param string $format Return format
	 * @return string
	 */
	function getFavorites($options = array(), $format = 'json') {
		return $this->apiCall('favorites', 'get', $format, $options);
	}

	/**
	 * Favorites the status specified in the ID parameter as the authenticating user.
	 * @param integer|string $id The ID of the status to favorite
	 * @param string $format Return format
	 * @return string
	 */
	function createFavorite($id, $format = 'json') {
		return $this->apiCall("favorites/create/{$id}", 'post', $format, array());
	}

	/**
	 * Un-favorites the status specified in the ID parameter as the authenticating user.
	 * @param integer|string $id The ID of the status to un-favorite
	 * @param string $format Return format
	 * @return string
	 */
	function destroyFavorite($id, $format = 'json') {
		return $this->apiCall("favorites/destroy/{$id}", 'post', $format, array());
	}

	/**
	 * Enables notifications for updates from the specified user to the authenticating user.
	 * @param integer|string $id The ID or screen name of the user to follow
	 * @param string $format Return format
	 * @return string
	 */
	function follow($id, $format = 'json') {
		$options = array('id' => $id);
		return $this->apiCall('notifications/follow', 'post', $format, $options);
	}

	/**
	 * Disables notifications for updates from the specified user to the authenticating user.
	 * @param integer|string $id The ID or screen name of the user to leave
	 * @param string $format Return format
	 * @return string
	 */
	function leave($id, $format = 'json') {
		$options = array('id' => $id);
		return $this->apiCall('notifications/leave', 'post', $format, $options);
	}

	/**
	 * Blocks the user specified in the ID parameter as the authenticating user.
	 * @param integer|string $id The ID or screen name of the user to block
	 * @param string $format Return format
	 * @return string
	 */
	function createBlock($id, $format = 'json') {
		$options = array('id' => $id);
		return $this->apiCall('blocks/create', 'post', $format, $options);
	}

	/**
	 * Unblocks the user specified in the ID parameter as the authenticating user.
	 * @param integer|string $id The ID or screen name of the user to unblock
	 * @param string $format Return format
	 * @return string
	 */
	function destroyBlock($id, $format = 'json') {
		$options = array('id' => $id);
		return $this->apiCall('blocks/destroy', 'post', $format, $options);
	}

  /**
	 * Returns if the authenticating user is blocking a target user.
	 * @param array $options Options to pass to the method
	 * @param string $format Return format
	 * @return string
	 */
	function blockExists($options, $format = 'json') {
		return $this->apiCall('blocks/exists', 'get', $format, $options);
	}

  /**
	 * Returns an array of user objects that the authenticating user is blocking.
   * @param array $options Options to pass to the method
	 * @param string $format Return format
	 * @return string
	 */
	function getBlocking($options, $format = 'json') {
		return $this->apiCall('blocks/blocking', 'get', $format, $options);
	}

  /**
	 * Returns an array of numeric user ids the authenticating user is blocking.
   * @param array $options Options to pass to the method
	 * @param string $format Return format
	 * @return string
	 */
	function getBlockingIDs($format = 'json') {
		return $this->apiCall('blocks/blocking/ids', 'get', $format, array());
	}


	function savedSearches($format = 'json') {
		return $this->apiCall('saved_searches', 'get', $format, array(), true);
	}

	function createSavedSearches($options, $format = 'json') {
		return $this->apiCall('saved_searches/create', 'post', $format, $options, true);
	}

	function destroySavedSearches($id, $format = 'json') {
		return $this->apiCall("saved_searches/destroy/{$id}", 'post', $format, array(), true);
	}

	/**
	 * Returns the string "ok" in the requested format with a 200 OK HTTP status code.
	 * @param string $format Return format
	 * @return string
	 */
	function test($format = 'json') {
		return $this->apiCall('help/test', 'get', $format, array(), false);
	}

	/**
	 * Returns the last HTTP status code
	 * @return integer
	 */
	function lastStatusCode() {
		return $this->http_status;
	}

	/**
	 * Returns the URL of the last API call
	 * @return string
	 */
	function lastApiCall() {
		return $this->last_api_call;
	}

	function bitShortenURL($options = array(), $format = 'json') {
		if (!empty($options['longUrl'])) {
			$options['version']	= "2.0.1";
			$options['format']	= $format;
			$shortenURL = "http://api.bit.ly/shorten";
	        return $this->apiCall('', 'get', $format, $options, false, $shortenURL);
		}
	}
}

/**
 * Access to the Twitter API through HTTP auth
 * @package twitterlibphp
 */
class Twitter extends TwitterBase {

	/**
	 * the Twitter credentials in HTTP format, username:password
	 * @access private
	 * @var string
	 */
	var $credentials;

	/**
	 * Fills in the credentials {@link $credentials} and the application source {@link $application_source}.
	 * @param string $username Twitter username
	 * @param string $password Twitter password
	 * @param $source string Optional. Name of the application using the API
	 */
	function __construct($username, $password, $source = null) {
		$this->credentials = sprintf("%s:%s", $username, $password);
		$this->application_source = $source;
	}


	protected function curlCheck() {
	  if( !function_exists("curl_init") &&
		  !function_exists("curl_setopt") &&
		  !function_exists("curl_exec") &&
		  !function_exists("curl_close") ) return false;
	  else return true;
	}

    protected $options = array(
        'timeout' => 30,
        'userAgent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.9 Twitter/TextcubeTweet/1.0'
    );

	/**
	* Executes an API call
	* @param string $twitter_method The Twitter method to call
	* @param string $http_method The HTTP method to use
	* @param string $format Return format
	* @param array $options Options to pass to the Twitter method
	* @param boolean $require_credentials Whether or not credentials are required
	* @return string
	*/

	protected function apiCall($twitter_method, $http_method, $format, $options, $require_credentials = true, $uri = '', $jsonFlag = true) {

		if (function_exists('json_decode')) {
			if ($format == 'json' && $jsonFlag) {
				require_once ('JSON.php');	
				$json = new JSON();
			}
		}

		$api_url = sprintf('http://twitter.com/%s.%s', $twitter_method, $format);
		$api_url = empty($uri) ? $api_url : $uri;

		$queryString = "";
		if (count($options) > 0) {
			$queryString = http_build_query($options);
		}

		if (($http_method == 'get') && (count($options) > 0)) {
			$api_url .= '?' . $queryString;
		}
		
		$headers = array(
			'User-Agent: ' . $this->options['userAgent'],
			'X-Twitter-Client: TextcubeTweet',
			'X-Twitter-Client-Version: 1.8',
            'X-Twitter-Client-URL: http://create74.com/_textcube/twitter_textcube.xml',
			'Content-Type: application/x-www-form-urlencoded'
		); 

		require_once('libcurlemu/libcurlemu.inc.php');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api_url);
		if ($require_credentials) {
			curl_setopt($ch, CURLOPT_USERPWD, $this->credentials);
		}

		curl_setopt($ch, CURLOPT_USERAGENT, $this->options['userAgent']);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_NOBODY, false); 
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->options['timeout']);
		
		if ($http_method == 'post') {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
		}

		$twitter_data = trim(curl_exec($ch));
		$this->http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$this->last_api_call = $api_url;
		
		if ($format == 'xml') {
			$response_data = @simplexml_load_string($twitter_data);
		} else {
			if ($jsonFlag) {
				$response_data = function_exists('json_decode') ? json_decode($twitter_data) : $json->decode($twitter_data);
			} else {
				$response_data = $twitter_data;
			}
		}

		curl_close($ch);

        return $response_data;
    }
}

/**
 * TODO: Add TwitterOAuth class
 */
?>
