<?php
class default_file {
  function form_load(&$data) {
  }

  function form_save(&$data) {
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
