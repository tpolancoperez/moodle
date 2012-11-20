<?php

class block_itunesu_link extends block_base {
  public function init() {
    $this->title = get_string('itunesu_link', 'block_itunesu_link');
  }
  function get_content() {
    $id = optional_param('id', 0, PARAM_INT);
  
    if (!$id) {
      ## I think we are in the admin section, so no content
      $this->content->text = "";
      return $this->content;
    }
  
    ## We have to get the course ID first
    ## $id = required_param('id', PARAM_INT);   // If id is not present this will throw an error 
  
    ## This is the link, it has to be here
    $required_content = "<a href=\"http://mdltest2test.fuller.edu/blocks/itunesu_link/connect_to_itunesu.php?id=$id\" target=\"_blank\">Connect to iTunesU</a>";
  
    if ($this->content != required_content) {
      $this->content->text = $required_content;
      return $this->content;
    }
  
    $this->content = new stdClass;
    $this->content->text = $this->config->text;
    ## $this->content->footer = 'Footer here...';
  
    return $this->content;
  }
  
  function specialization() {
    if (!empty($this->config->title)) {
      $this->title = $this->config->title;
    } else {
      $this->config->title = 'iTunesU Content';
    }
  }
  
  function instance_allow_multiple() {
    return false;
  }
  
  function has_config() {
    return true;
  }
  
  function instance_config_save($data) {
    $data = stripslashes_recursive($data);
    $this->config = $data;
    return set_field('block_instance',
                     'configdata',
                     base64_encode(serialize($data)),
                     'id',
                     $this->instance->id);
  }
}
  
?>
