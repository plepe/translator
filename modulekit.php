<?php
$id = "modulekit-translator";

$depend = array("hooks", "form", "lang");

$include = array(
  'php' => array(
    "inc/oauth.php",
    "inc/knatsort.php",
    "file_types/default_file.php",
    "file_types/osm_tags.php",
  ),
  'css' => array(
    "style.css",
  ),
);
