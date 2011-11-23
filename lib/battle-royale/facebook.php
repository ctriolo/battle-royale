<?php
/**
 * facebook.php
 *
 * create functions around the things we need from facebook
 */

// INCLUDES
require_once dirname(__FILE__).'/constants.php';
require_once dirname(__FILE__).'/../facebook/src/facebook.php';

// FUNCTIONS
function facebook_get_user() {
  // Create our Application instance (replace this with your appId and secret).
  $facebook = new Facebook(array(
    'appId'  => FACEBOOK_APP_ID,
    'secret' => FACEBOOK_APP_SECRET,
  ));

  $user = $facebook->getUser();
  if ($user) {
    try {
      // Proceed knowing you have a logged in user who's authenticated.
      $user = $facebook->api('/me');
    } catch (FacebookApiException $e) {
      error_log($e);
      $user = null;
    }
  }
  
  return $user;
}

function facebook_get_log_in_url() {
  // Create our Application instance (replace this with your appId and secret).
  $facebook = new Facebook(array(
    'appId'  => FACEBOOK_APP_ID,
    'secret' => FACEBOOK_APP_SECRET,
  ));

  return $facebook->getLoginUrl(array(
    'scope' => 'user_education_history',
    'display' => 'page'
  ));
}

function facebook_get_log_out_url() {
  // Create our Application instance (replace this with your appId and secret).
  $facebook = new Facebook(array(
    'appId'  => FACEBOOK_APP_ID,
    'secret' => FACEBOOK_APP_SECRET,
  ));

  return $logoutUrl = $facebook->getLogoutUrl();
}

function facebook_get_friends($num) {
  $facebook = new Facebook(array(
    'appId'  => FACEBOOK_APP_ID,
    'secret' => FACEBOOK_APP_SECRET,
  ));

  $user = facebook_get_user();
  $friends_short = $facebook->api('/me/friends');
  $friends_short = $friends_short['data'];
  $friends_long = array();
  for ($i = 0; $i < $num; $i++) {
    $friend_short = $friends_short[$i];
    $friends_long[] = $facebook->api('/'.$friend_short['id']);
  }
  return $friends_long;
}