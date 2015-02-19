<?php
function oauth_check_auth() {
  global $oauth_config;
  global $db;

  if(!isset($_SESSION['state'])) {
    if(isset($_COOKIE['translator_oauth'])) {
      $cookie = $_COOKIE['translator_oauth'];

      $res = $db->query("select * from oauth_data where cookie=" . $db->quote($cookie));

      $elem = $res->fetch();

      $_SESSION['state'] = 2;
      $_SESSION['secret'] = $elem['secret'];
      $_SESSION['token'] = $elem['token'];

      $res->closeCursor();
    }
  }

  // from: http://php.net/manual/en/oauth.examples.fireeagle.php
  // In state=1 the next request should include an oauth_token.
  // If it doesn't go back to 0
  if(!isset($_GET['oauth_token']) && $_SESSION['state']==1)
    $_SESSION['state'] = 0;

  try {
    $oauth = new OAuth($oauth_config['conskey'],$oauth_config['conssec'],OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
    $oauth->enableDebug();
    if(!isset($_GET['oauth_token']) && !$_SESSION['state']) {
      $request_token_info = $oauth->getRequestToken($oauth_config['req_url']);
      $_SESSION['secret'] = $request_token_info['oauth_token_secret'];
      $_SESSION['state'] = 1;
      header('Location: '.$oauth_config['authurl'].'?oauth_token='.$request_token_info['oauth_token']);
      exit;
    } else if($_SESSION['state']==1) {
      $oauth->setToken($_GET['oauth_token'],$_SESSION['secret']);
      $access_token_info = $oauth->getAccessToken($oauth_config['acc_url']);
      $_SESSION['state'] = 2;
      $_SESSION['token'] = $access_token_info['oauth_token'];
      $_SESSION['secret'] = $access_token_info['oauth_token_secret'];

      $cookie = md5(uniqid());
      setcookie("translator_oauth", $cookie, 2147483647);

      $db->query("insert into oauth_data values (". $db->quote($cookie) .", ". $db->quote($_SESSION['secret']) .", ". $db->quote($_SESSION['token']) . ")");
    } 
    $oauth->setToken($_SESSION['token'],$_SESSION['secret']);

    $oauth->fetch($oauth_config['api_url']);
    $_SESSION['userdata'] = new DOMDocument();
    $_SESSION['userdata']->loadXML($oauth->getLastResponse());
    $_SESSION['username'] = $_SESSION['userdata']->getElementsByTagName("user")->item(0)->getAttribute("display_name");

    $db->query("update oauth_data set username=". $db->quote($_SESSION['username']) .", timestamp=datetime('now') where cookie=". $db->quote($_COOKIE['translator_oauth']));

  } catch(OAuthException $E) {
    print_r($E);
    return false;
  }

  return true;
}
