<?php include "conf.php"; /* load a local configuration */ ?>
<?php include "modulekit/loader.php"; /* loads all php-includes */ ?>
<?php
call_hooks("init"); /* Initializes all modules, also lang module */

$data = json_decode(file_get_contents("data/openstreetmap-tag-translations/tags/de.json", "r"), true);
$template_str = array(
  'message'     => array(
    'name'        => "Singular",
    'type'        => 'text',
  ),
  '!=1'         => array(
    'name'        => "Plural",
    'type'        => 'text',
  ),
  'gender'      => array(
    'name'        => "Gender",
    'type'        => 'select',
    'values'      => array("male", "female", "neuter"),
  ),
);

$form_def = array();
foreach($data as $k => $v) {
  $form_def[$k] = array(
    'type'      => 'form',
    'def'       => $template_str,
    'name'      => $k,
    'desc'      => isset($v['description']) ? $v['description'] : null,
  );

  if(gettype($v) == "string") {
    $data[$k] = array("message"=>$v);
  }
}

$form = new form('lang', $form_def);
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
print $form->show();
?>
</body>
