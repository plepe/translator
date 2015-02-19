<?php include "conf.php"; /* load a local configuration */ ?>
<?php include "modulekit/loader.php"; /* loads all php-includes */ ?>
<?php
session_start();
call_hooks("init"); /* Initializes all modules, also lang module */
Header("Content-Type: text/html; charset=UTF-8");

if(!oauth_check_auth()) {
  exit;
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
