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
        'values'          => array(null=>"Default", "object"=>"Object"),
      ),
    );
  }
}
