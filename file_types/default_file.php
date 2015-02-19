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
    $type = $this->string_type($k, $template_data);

    switch($type) {
      case "object":
        return array(
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
      case "lang_config":
        return array(
          'has_plural'  => array(
            'name'        => "Has plural",
            'type'        => "boolean",
            'desc'        => "Check, if the current language has a different form for objects in plural than in singular",
          ),
          'gender'      => array(
            'name'        => "Has gender",
            'type'        => "boolean",
            'desc'        => "Check, if the current language has a grammatical gender (e.g. different articles whether a word is male or female)",
          ),
          'gender_list' => array(
            'name'        => "List of genders",
            'type'        => "checkbox",
            'desc'        => "Check all genders which the current language distinguishes",
            'values'      => array("male", "female", "neuter"),
            'show_depend' => array("check", "gender", array("is", true)),
          ),
        );
      case "default":
      default:
        return array(
          'message'     => array(
            'name'        => "Message",
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
}
