<?php
session_start();
require_once '../facebook-php-sdk-v4-5.0.0/src/Facebook/autoload.php';
$fb = new Facebook\Facebook([
  'app_id' => '',	
  'app_secret' => '',
  'default_graph_version' => 'v2.5',
]);
$helper = $fb->getRedirectLoginHelper();
//TODO ADD PERMISSIONS
$permissions = []; // optional
//$permissions = []; // optional
$loginUrl = $helper->getLoginUrl('', $permissions);

echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
?>