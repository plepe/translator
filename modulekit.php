<?php
$id = "modulekit-translator";

$depend = array("hooks", "form", "lang", "json_readable_encode");

$include = array(
  'php' => array(
    "inc/oauth.php",
    "inc/knatsort.php",
    "file_types/default_file.php",
    "file_types/osm_tags.php",
    "file_types/json_files.php",
  ),
  'css' => array(
    "style.css",
  ),
);
