<?php
class default_file {
  function form_load(&$form_def, &$data, &$template) {
  }

  function form_save(&$form_def, &$data, &$template) {
  }

  function form_string($k) {
    return array(
      'message'     => array(
        'name'        => "Message",
        'type'        => 'text',
      ),
    );
  }

  function form_template($k) {
    return array(
      'description'     => array(
        'name'        => "Description",
        'type'        => 'textarea',
      ),
    );
  }
}
