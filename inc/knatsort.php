<?php
function knatsort($arr) {
  $keys = array_keys($arr);
  natcasesort($keys);

  $ret = array();
  foreach($keys as $k) {
    $ret[$k] = $arr[$k];
  }

  return $ret;
}
