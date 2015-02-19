<?php include "conf.php"; /* load a local configuration */ ?>
<?php include "modulekit/loader.php"; /* loads all php-includes */ ?>
<?php
session_start();

if(!isset($_SESSION['username'])) {
  Header("Location: .");
}
if(!isset($_REQUEST['app']) || (!isset($_REQUEST['lang']))) {
  Header("Location: .");
}

$error = array();
call_hooks("init"); /* Initializes all modules, also lang module */

if(!array_key_exists($_REQUEST['app'], $translation_apps)) {
  print "Invalid App!";
  exit;
}

$app = $translation_apps[$_REQUEST['app']];
$lang = $_REQUEST['lang'];

if(!preg_match("/^[a-z_\-A-Z]*$/", $lang)) {
  print "Invalid language code!";
  exit;
}

$template_file = "{$app['path']}/template.json";
$file = "{$app['path']}/{$lang}.json";

$file_type = new $app['type']($lang);

$template_data = json_decode(file_get_contents($template_file), true);
if(!$template_data) {
  print "Could not read template file.";
  $template_data = array();
}

$lang_config = null;
if(array_key_exists('languages', $translation_apps)) {
  $tmp_file = "{$translation_apps['languages']['path']}/{$lang}.json";
  $tmp = json_decode(file_get_contents($tmp_file), true);
  if(array_key_exists('lang:config', $tmp))
    $lang_config = $tmp['lang:config'];
}
if(!$lang_config) {
  $tmp_file = modulekit_file("modulekit-lang", "lang/lang_{$lang}.json");
  $tmp = json_decode(file_get_contents($tmp_file), true);
  if(array_key_exists('lang:config', $tmp))
    $lang_config = $tmp['lang:config'];
}
if((!$lang_config) && ($_REQUEST['app'] != "languages")) {
  $error[] = "Please first <a href='translate.php?lang={$lang}&app=languages'>configure this language</a>!";
}
if(!$lang_config)
  $lang_config = array();

$data = json_decode(file_get_contents($file), true);

$form_string_fun = "form_string";
if($_REQUEST['lang'] == "template")
  $form_string_fun = "form_template";

$form_def = array();
foreach($template_data as $k => $v) {
  $form_def[$k] = array(
    'type'      => 'form',
    'def'       => call_user_func(array($file_type, $form_string_fun), $k, $v),
    'name'      => $k,
    'desc'      => isset($v['description']) ? $v['description'] : null,
  );

  if(array_key_exists($k, $data) && (gettype($data[$k]) == "string")) {
    $data[$k] = array("message"=>$data[$k]);
  }

  // make sure that data values which are not being edited don't get lost
  if(array_key_exists($k, $data) && is_array($data[$k])) {
    $no_edit_data = array_diff_key($data[$k], $form_def[$k]['def']);

    if(sizeof($no_edit_data)) {
      $form_def[$k]['def']['_other'] = array(
        'type' => 'hidden',
        'name' => '_other',
      );
      $data[$k]['_other'] = $no_edit_data;
    }
  }
}

foreach($data as $k => $v) if(!array_key_exists($k, $template_data)) {
  $form_def[$k] = array(
    'type'      => 'form',
    'def'       => call_user_func(array($file_type, $form_string_fun), $k, $v),
    'name'      => $k,
    'desc'      => (isset($v['description']) ? $v['description'] : "") . " This message does not exist in the template file.",
  );

  if(gettype($v) == "string") {
    $data[$k] = array("message"=>$v);
  }
}

$file_type->form_load($form_def, $data, $template_data);

$form = new form('data', $form_def);
if($form->is_complete()) {
  $old_data = $data;
  $data = $form->save_data();

  // copy non-edited-data back
  foreach($data as $k=>$v) {
    if(is_array($v) && array_key_exists('_other', $v)) {
      $data[$k] = array_merge($data[$k], $data[$k]['_other']);
      unset($data[$k]['_other']);
    }
  }

  $file_type->form_save($form_def, $data, $template_data);

  // remove null values
  foreach($data as $k=>$v) {
    foreach($v as $k1=>$v1) {
      if($v1 === null)
        unset($data[$k][$k1]);
    }

    if((sizeof($data[$k]) == 1) && (isset($data[$k]['message'])))
      $data[$k] = $data[$k]['message'];
    elseif(sizeof($data[$k]) == 0)
      $data[$k] = null;
  }

  file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

  $new_keys = array_diff(array_keys($data), array_keys($old_data));
  $file_type->update_template($template_data, $new_keys);
  file_put_contents($template_file, json_encode($template_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

  chdir($app['path']);
  system("git add \"{$lang}.json\"");
  system("git add \"template.json\"");
  system("git -c user.name='OSM Translator' -c user.email='translator@openstreetbrowser.org' commit -m 'Update translation ({$lang})' --author='{$_SESSION['username']} <{$_SESSION['username']}@openstreetmap.org>'");
}

if($form->is_empty())
  $form->set_data($data);

Header("Content-Type: text/html; charset=UTF-8");
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
if(sizeof($error)) {
  foreach($error as $e) {
    print "<li>{$e}";
  }
}
else {
  ?>
  <form method='post' enctype='multipart/form-data'>
  <?php
  print $form->show();
  ?>
  <input type='submit' value='Save'/>
  </form>
  <?php
}
?>
</body>
