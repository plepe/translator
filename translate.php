<?php include "conf.php"; /* load a local configuration */ ?>
<?php include "modulekit/loader.php"; /* loads all php-includes */ ?>
<?php include "types/osm_tags.php"; /* loads all php-includes */ ?>
<?php
call_hooks("init"); /* Initializes all modules, also lang module */

$file = "data/openstreetmap-tag-translations/tags/de.json";

$data = json_decode(file_get_contents($file), true);
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

form_load($data, $form_def);

$form = new form('lang', $form_def);
if($form->is_complete()) {
  $data = $form->save_data();

  form_save($data, $form_def);

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
<form method='post' enctype='multipart/form-data'>
<?php
print $form->show();
?>
<input type='submit' value='Save'/>
</form>
</body>
