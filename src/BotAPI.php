<?php
namespace DiscussionBot;

class HTTPResponse{
  public $query;
  public $rawdata;
  public $data;
  public $headers;
  
}
class HTTP{
  const TIMEOUT = 60;
  
  public static function jsonPost($url, $post = []){
    $context = stream_context_create([
      "http" => [
        "method"        => "POST",
        "header"        => "Content-type: application/json",
        "content"       => json_encode($post),
        "timeout"       => HTTP::TIMEOUT,
        "ignore_errors" => true,
      ]
    ]);
    
    $response = new HTTPResponse;
    $response->query = $post;
    try{
      if($response->rawdata = @file_get_contents($url, false, $context)){
        
      }else{
        $response->rawdata = '{"error":"Unknown: BotAPI Could not be accessed."}';
      }
    }catch(\Throwable $e){
      $response->rawdata = '{"error":"'.$e->getMessage().'"}';
    }
    
    if($obj = json_decode($response->rawdata)){
      $response->data = $obj;
    }else{
      $response->data = $response->rawdata;
    }
    $response->headers = $http_response_header;
    return $response;
  }
}
abstract class Logger
{
  abstract public function log($obj);
}

class BotAPI{
  const ENDPOINT = "https://api.telegram.org";
  protected $token;
  protected $offset = 0;
  protected $logger;
  protected $allowed_updates;
  protected $my_id;
  
  
  public function __construct(string $token){
    $token_parts = explode(":",$token);
    if(count($token_parts) !== 2 || !is_numeric($token_parts[0])){
      throw new \Exception("Not Valid Token.");
    }
    
    $this->my_id = $token_parts[0];
    $this->token = $token;
  }
  
  public function __call($name, $arguments){
    if(strpos($name,"_") === 0) $name = substr($name,1);
    return $this->api_query($name, $arguments[0]??[]);
  }
  
  protected function api_query(string $method, array $arguments = []){
    //$method = strtolower($method);
    $arguments["method"] = $method;
    
    $result = HTTP::jsonPost(BotAPI::ENDPOINT . "/bot{$this->token}/", $arguments);
    
    if( !(  isset($result->data->result) && $result->data->result == []  ) ) {
      $this->log($result);
    }

    return $result->data;
  }
  
  public function getUpdates(array $arguments = []){
      $arguments["timeout"] = $arguments["timeout"] ?? HTTP::TIMEOUT;
      if (isset($this->allowed_updates)) {
        $arguments["allowed_updates"] = $this->allowed_updates;
      }
      $arguments["offset"] = $this->offset;

      $response = $this->api_query("getUpdates", $arguments);
      
      if($response->ok){
        if ($response->result !== []) {
          $this->offset = end($response->result)->update_id + 1;
          reset($response->result);
        }
      }
      
      return $response;
  }
  
  public function setLogger($logger){
    if(is_subclass_of($logger, "\DiscussionBot\Logger")){
      $this->logger = &$logger;
    }
    return $this;
  }
  
  protected function log($obj){
    if(isset($this->logger)){
      $this->logger->log($obj);
    }
  }
  
  public function setAllowedUpdates(array $updates){
    $this->allowed_updates = json_encode($updates);
    return $this;
  }
  public function unsetAllowedUpdates(){
    $this->allowed_updates = null;
    return $this;
  }
  public function setOffset(int $offset){
    $this->offset = $offset;
    return $this;
  }
  
  public function getMyID(){
    return $this->my_id;
  }
}