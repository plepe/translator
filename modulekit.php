<?php
$id = "modulekit-translator";

$depend = array("hooks", "form", "lang", "json_readable_encode", "modulekit-auth", "auth_form");

$include = array(
  'php' => array(
    "inc/knatsort.php",
    "file_types/default_file.php",
    "file_types/osm_tags.php",
    "file_types/json_files.php",
    "file_types/languages.php",
  ),
  'css' => array(
    "style.css",
  ),
);
