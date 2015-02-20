<?php
class default_file {
  function form_load(&$form_def, &$data, &$template) {
  }

  function form_save(&$form_def, &$data, &$template) {
  }

  function string_type($k, $template_data) {
    if(!is_array($template_data))
      return 'default';
    if(!array_key_exists('type', $template_data))
      return 'default';

    return $template_data['type'];
  }

  function form_string($k, $template_data=null) {
    global $lang_config;

    $type = $this->string_type($k, $template_data);

    switch($type) {
      case "object":
        $ret = array(
          'message'     => array(
            'name'        => "Text",
            'type'        => 'text',
          ),
        );

        if(array_key_exists('has_plural', $lang_config) && $lang_config['has_plural']) {
          $ret['message']['name'] = "Singular";
          $ret = array_merge($ret, array(
            '!=1'         => array(
              'name'        => "Plural",
              'type'        => 'text',
            ),
          ));
        }

        if(array_key_exists('has_gender', $lang_config) && $lang_config['has_gender']) {

          $ret = array_merge($ret, array(
            'gender'      => array(
              'name'        => "Gender",
              'type'        => 'select',
              'values'      => $lang_config['gender_list'],
            ),
          ));
        }

        return $ret;
      case "lang_config":
        return array(
          'has_plural'  => array(
            'name'        => "Has plural",
            'type'        => "boolean",
            'desc'        => "Check, if the current language has a different form for objects in plural than in singular",
          ),
          'has_gender'      => array(
            'name'        => "Has gender",
            'type'        => "boolean",
            'desc'        => "Check, if the current language has a grammatical gender (e.g. different articles whether a word is male or female)",
          ),
          'gender_list' => array(
            'name'        => "List of genders",
            'type'        => "checkbox",
            'desc'        => "Check all genders which the current language distinguishes",
            'values'      => array("male", "female", "neuter"),
            'show_depend' => array("check", "has_gender", array("is", true)),
          ),
        );
      case "default":
      default:
        return array(
          'message'     => array(
            'name'        => "Text",
            'type'        => 'text',
          ),
        );
    }
  }

  function form_template($k, $template_data=null) {
    return array(
      'description'     => array(
        'name'        => "Description",
        'type'        => 'textarea',
      ),
      'type'            => array(
        'name'            => "Type",
        'type'            => 'select',
        'values'          => array(null=>"Default", "object"=>"Object", "lang_config"=>"Language Config"),
      ),
    );
  }

  function update_template(&$template_data, $new_keys) {
  }
}
