<?php
namespace DiscussionBot;

class TerminalLogger extends Logger
{
  public function log($obj)
  {
    if (is_string($obj)) {
      echo $obj . PHP_EOL;
    } 
    else {
      echo var_export($obj, true) . PHP_EOL;
    }
  }
}


class JSONTerminalLogger extends Logger
{
  public function log($obj)
  {
    if (is_string($obj)) {
      echo json_encode(new class($obj)
      {
        public function __construct($obj)
        {
          $this->notice = $obj;
        }
      }) . PHP_EOL;
    } 
    elseif (is_object($obj)) {
      unset($obj->headers);
      unset($obj->rawdata);
      echo json_encode($obj) . PHP_EOL;
    } else {
      echo json_encode($obj) . PHP_EOL;
    }
  }
}