#!/usr/bin/php
<?php
$config = json_decode('{
  "telegram":{
    "root": 123456789,
    "token":"YOUR API TOKEN HERE"
  }
}');
// IMPORTANT: This is a beta version of the bot using long-polling instead of webhooks


class TelegramBotAPIWrapper{
  const API_BASE = 'https://api.telegram.org/bot';
  public $token;

  public function __construct($token){
    $this->token = $token; 
  }

  public function __call($name, $args){
     return $this->sendRequest($name, $args[0]);
  }

  public function sendRequest($method, $params = []){
    
    if(empty($params)){
      $opts = [
        "http" => [
            "method"  => "GET",
        ]
      ];
    }else{
      
      $opts = [
        "http" => [
          "method"  => "POST",
          "header"  => "Content-Type: application/json\r\n",
          "content" => json_encode($params),
          "timeout" => $params["timeout"] ?? 1,
        ]
      ];
    }
    
    $context = stream_context_create($opts);
    $content = file_get_contents(SELF::API_BASE.$this->token."/$method",false, $context);
    return json_decode($content);
  }
}
  
$bot = new TelegramBotAPIWrapper($config->telegram->token);
$offset = 0;
$bot->sendMessage([
  "chat_id" => $config->telegram->root,
  "text" => "I am alive!",
]);

$running = true;
while($running){
  echo "Getting updates...";
  $updates = $bot->getUpdates(["offset" => $offset, "timeout" => 60]);
  
  if(empty($updates->result)){
     echo " Received 0 or malformed updates. Lets continue\n";
     continue;
   }
  echo " Received ".count($updates->result)." Updates.\n";
  echo "Parsing them...\n";
  if($updates->ok === true){
    foreach($updates->result as $update){
      $offset = $update->update_id+1;
      if(
        isset($update->message->from->id) && 
        $update->message->from->id == 777000
      ){
        if($bot->deleteMessage([
          "chat_id" => $update->message->chat->id,
          "message_id" =>  $update->message->message_id,
        ])){
          echo "  Deleted message.\n";
          
        }else{
          echo "  Some error happened.\n";
        }
      }
      elseif(isset($update->message->text)){
        $msg = strtolower($update->message->text);
        if($msg == '/start' || $msg == '/start@discussiongroupbot'){
          $txt = '_Hello, nice to meet you!_'."\n"
          .'Make me admin in your group and I will automatically remove all forwarded posts from your linked channel so the group doesn\'t get filled with channel messages!'."\n"
          .'Bot made by @PartyGuy and @FabianPastor';
          
          $bot->sendMessage([
            "chat_id" => $update->message->chat->id,
            "text" => $txt,
            "parse_mode" => "markdown",
            "reply_to_message_id" => $update->message->message_id,
          ]);
          echo "  Sent Start message.\n";
        }
      }
    }
  }
}
