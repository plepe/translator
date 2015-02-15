<?php
class osm_tags extends default_file {
  function form_load(&$form_def, &$data, &$template) {
    global $template_str;

    $last_base = null;
    $i = 0;
    while($i < sizeof($form_def)) {
      $keys = array_keys($form_def);
      $k = $keys[$i];

      if(strpos($k, "=")) {
        $base = substr($k, 0, strpos($k, "="));
        $value = substr($k, strpos($k, "=") + 1);
      }
      else
        $base = $k;

      if(($last_base !== null) && ($last_base != $base)) {
        $form_def =
          array_slice($form_def, 0, $i, true) +
          array("NEW:$last_base"=>array(
            'type'=>"form",
            'name'=>"new values for {$last_base}",
            'def'=>$this->form_string($last_base . "=NEW", "new_value"),
            'count'=>array("default"=>0, 'order'=>false, 'button:add_element'=>"Add new value"))) +
          array_slice($form_def, $i, sizeof($form_def) - $i, true);

        $i++;
      }

      $last_base = $base;
      $i++;
    }

    $form_def['NEW_KEY'] = array(
      'type'        => 'form',
      'count'       => array('default'=>0, 'order'=>false, 'button:add_element'=>"Add new key"),
      'name'        => "New key(s)",
      'def'         => $this->form_string("tag:NEW", "new_key"),
    );
  }

  function form_save(&$form_def, &$data, &$template) {
    $i = 0;
    while($i < sizeof($data)) {
      $keys = array_keys($data);
      $k = $keys[$i];
      $v = $data[$k];

      if(substr($k, 0, 4) == "NEW:") {
        if(!sizeof($v)) {
          unset($data[$k]);
          $i--;
        }
        else {
          $new_v = array();
          foreach($v as $v1) {
            $new_value = substr($k, 4) . '=' . $v1['value'];
            unset($v1['value']);
            $new_v[$new_value] = $v1;
          }

          $data = array_slice($data, 0, $i, true) +
            $new_v +
            array_slice($data, $i + 1, sizeof($data) - $i, true);
          $i += sizeof($v) - 1;
        }
      }
      elseif($k == "NEW_KEY") {
        foreach($data[$k] as $k1) {
          $new_key = 'tag:' . $k1['key'];
          $values = $k1['values'];
          unset($k1['key']);
          unset($k1['values']);
          $data[$new_key] = $k1;

          foreach($values as $v1) {
            $new_value = $new_key . '=' . $v1['value'];
            unset($v1['value']);
            $data[$new_value] = $v1;
          }
        }

        unset($data[$k]);
      }

      $i++;
    }
  }

  function form_string($k, $new="") {
    $ret = array();

    if($new == "new_value") {
      $ret = array(
        'value'         =>array(
          'type'        => 'text',
          'name'        => "Value",
          'req'         => true,
          'desc'        => "E.g. 'bank' for 'amenity=bank'"
        ),
      );
    }
    elseif($new == "new_key") {
      $ret = array(
        'key'         => array(
          'type'        => 'text',
          'name'        => "Key",
          'req'         => true,
          'desc'        => "E.g. 'amenity'"
        ),
        'values'      => array(
          'type'          => 'form',
          'count'       => array('default'=>0, 'order'=>false, 'button:add_element'=>"Add new value"),
          'name'        => "New values(s)",
          'def'         => $this->form_string($k . "=NEW", "new_value"),
        ),
      );
    }

    $ret = array_merge($ret, array(
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
    ));

    return $ret;
  }
}
