<?php
class osm_tags extends default_file {
  function __construct($lang) {
    $this->lang = $lang;
  }

  function form_load(&$form_def, &$data, &$template) {
    global $template_str;

    $form_string_fun = "form_string";
    if($this->lang == "template")
      $form_string_fun = "form_template";

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
        if((!array_key_exists($last_base, $template)) ||
           ($this->lang == "template") || // lang 'template' can always add values
           is_array($template[$last_base]) &&
           array_key_exists("translate_values", $template[$last_base]) &&
           $template[$last_base]["translate_values"]) {

          $el = array(
              'type'    => "form",
              'name'    => "new values for {$last_base}",
              'def'     => call_user_func(array($this, $form_string_fun), $last_base . "=NEW", null, "new_value"),
              'count'   => array(
                'default'       => 0,
                'order'         => false,
                'button:add_element' => "Add new value"
              )
            );

          // TODO: show "new values" only when "translate_values" is checked
          //if($this->lang == "template")
          //  $el['count']['show_depend'] = array("check", "translate_values", true);

          $form_def =
            array_slice($form_def, 0, $i, true) +
            array("NEW:$last_base" => $el) +
            array_slice($form_def, $i, sizeof($form_def) - $i, true);

          $i++;
        }
      }

      $last_base = $base;
      $i++;
    }

    $form_def['NEW_KEY'] = array(
      'type'        => 'form',
      'count'       => array('default'=>0, 'order'=>false, 'button:add_element'=>"Add new key"),
      'name'        => "New key(s)",
      'def'         => call_user_func(array($this, $form_string_fun), "tag:NEW", null, "new_key"),
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

  function form_string($k, $template_data, $new="") {
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

    $ret = array_merge($ret, parent::form_string($k, $template_data));

    return $ret;
  }

  function form_template($k, $template_data, $new="") {
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
      );
    }

    $ret = array_merge($ret, parent::form_template($k));

    if(strpos($k, "=") === false) {
      $ret['translate_values'] = array(
        'type'  => "boolean",
        'name'  => "Translate tag values",
        'default'       => false,
      );
    }

    if($new == "new_key") {
      $ret = array_merge($ret, array(
        'values'      => array(
          'type'          => 'form',
          'count'       => array('default'=>0, 'order'=>false, 'button:add_element'=>"Add new value"),
          'name'        => "New values(s)",
          'def'         => $this->form_template($k . "=NEW", "new_value"),
        ),
      ));
    }

    return $ret;
  }

  function update_template(&$template_data, $new_keys) {
    $template_data = array_merge($template_data, array_combine($new_keys, array_fill(0, sizeof($new_keys), null)));
    $template_data = knatsort($template_data);
  }
}
