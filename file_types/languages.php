<?php
class languages extends default_file {
  function form_load(&$form_def, &$data, &$template) {
    $form_def['NEW_KEY'] = array(
      'type'        => 'form',
      'count'       => array(
        'default'=>0,
        'order'=>false,
        'button:add_element'=>"Add language",
        'hide_label'  => true,
      ),
      'name'        => "Add language",
      'def'         => array(
        'type'        => 'text',
      ),
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

    $data = knatsort($data);
  }

  function update_template(&$template_data, $new_keys) {
    $template_data = array_merge($template_data, array_combine($new_keys, array_fill(0, sizeof($new_keys), null)));
    $template_data = knatsort($template_data);
  }
}
