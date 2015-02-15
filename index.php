<?php include "conf.php"; /* load a local configuration */ ?>
<?php include "modulekit/loader.php"; /* loads all php-includes */ ?>
<?php
session_start();
call_hooks("init"); /* Initializes all modules, also lang module */
Header("Content-Type: text/html; charset=UTF-8");

$req_url = 'https://www.openstreetmap.org/oauth/request_token';
$authurl = 'https://www.openstreetmap.org/oauth/authorize';
$acc_url = 'https://www.openstreetmap.org/oauth/access_token';
$api_url = 'http://api.openstreetmap.org/api/0.6/user/details';

// from: http://php.net/manual/en/oauth.examples.fireeagle.php
// In state=1 the next request should include an oauth_token.
// If it doesn't go back to 0
if(!isset($_GET['oauth_token']) && $_SESSION['state']==1) $_SESSION['state'] = 0;
try {
  $oauth = new OAuth($oauth_conskey,$oauth_conssec,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
  $oauth->enableDebug();
  if(!isset($_GET['oauth_token']) && !$_SESSION['state']) {
    $request_token_info = $oauth->getRequestToken($req_url);
    $_SESSION['secret'] = $request_token_info['oauth_token_secret'];
    $_SESSION['state'] = 1;
    header('Location: '.$authurl.'?oauth_token='.$request_token_info['oauth_token']);
    exit;
  } else if($_SESSION['state']==1) {
    $oauth->setToken($_GET['oauth_token'],$_SESSION['secret']);
    $access_token_info = $oauth->getAccessToken($acc_url);
    $_SESSION['state'] = 2;
    $_SESSION['token'] = $access_token_info['oauth_token'];
    $_SESSION['secret'] = $access_token_info['oauth_token_secret'];
  } 
  $oauth->setToken($_SESSION['token'],$_SESSION['secret']);

  $oauth->fetch("$api_url");
  $_SESSION['userdata'] = new DOMDocument();
  $_SESSION['userdata']->loadXML($oauth->getLastResponse());
  $_SESSION['username'] = $_SESSION['userdata']->getElementsByTagName("user")->item(0)->getAttribute("display_name");

} catch(OAuthException $E) {
  print_r($E);
}

$languages = array();

foreach($translation_apps as $id=>$data) {
  $f = opendir($data['path']);
  while($r = readdir($f)) {
    // make sure, that the template file is added last
    if(preg_match("/^(.*)\.json$/", $r, $m) && ($m[1] != "template")) {
      $languages[$m[1]] = lang("lang:{$m[1]}");
    }
  }

  $languages['template'] = "Template";

  closedir($f);
}

$form_def = array(
  'lang' => array(
    'type' => 'select',
    'name' => 'Language',
    'values' => $languages,
    'req' => true,
  ),
  'app' => array(
    'type' => 'radio',
    'name' => 'Application',
    'values' => array_map(function($e) {
        return $e['name'];
      }, $translation_apps),
    'req' => true,
  ),
);

$form = new form(null, $form_def);

if($form->is_complete()) {
  $parts = array();
  foreach($form->get_data() as $k=>$v) {
    $parts[] = urlencode($k) ."=". urlencode($v);
  }

  $url = "translate.php?" . implode("&", $parts);
  Header("Location: {$url}");
}

?>
<html>
<head>
  <?php print modulekit_to_javascript(); /* pass modulekit configuration to JavaScript */ ?>
  <?php print modulekit_include_js(); /* prints all js-includes */ ?>
  <?php print modulekit_include_css(); /* prints all css-includes */ ?>
  <?php print print_add_html_headers(); /* prints additional html headers */ ?>
</head>
<body>

<?php
print "<p>Hi, User {$_SESSION['username']}!";

print "<p>What do you want to translate:\n";
?>
<form method='get'>
<?php
print $form->show();
?>
<input type='submit'>
</form>


</body>
