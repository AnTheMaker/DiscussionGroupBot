#!/usr/bin/env php
<?php
error_reporting(E_ALL);
require_once "src/BotAPI.php";
require_once "src/Loggers.php";

use DiscussionBot\BotAPI;
use DiscussionBot\TerminalLogger;
use DiscussionBot\JSONTerminalLogger as DefaultLogger;

$config = json_decode(file_get_contents("config.json"));


$terminal = new TerminalLogger();
$bot = new BotAPI($config->token);

$BotAPI_logger = new DefaultLogger();
$bot->setLogger($BotAPI_logger);

$terminal->log($bot->getMe());

$bot->sendMessage([
  "chat_id" => $config->root, 
  "text"    => "I am alive!"
]);

$terminal->log("Starting the main loop");
$running = true;
while($running){
  if($response = $bot->getUpdates()){
    if($response->ok && !empty($response->result) ){
      foreach($response->result as $update){
        //New botapi v5.5
        if( $update->message->is_automatic_forward ?? false ){
          $terminal->log("Deleting message on {$update->message->chat->id}");
          $bot->deleteMessage([
            "chat_id"    => $update->message->chat->id,
            "message_id" => $update->message->message_id,
          ]);
        } //End If for channel forward
        
        elseif(isset($update->message->text)){
          $text = strtolower($update->message->text);
          if (($update->message->chat->type === "private" && $text === '/start') || $text === '/start@discussiongroupbot') {
            $terminal->log("Sending the /start command to {$update->message->chat->id}");
            $bot->sendMessage([
              "chat_id"    => $update->message->chat->id,
              "text" => "<i>Hello, nice to meet you!</i>\n"
                      . "Make me admin in your group and I will automatically remove all forwarded posts from your linked channel so the group doesn't get filled with channel messages!\n\n"
                      . "<b>Bot dev:</b> @PartyGuy and @FabianPastor",
              "parse_mode" => "html",
              "reply_to_message_id" => $update->message->message_id,
            ]);
          }
        } //end if text
      } // end foreach
    } //end if ok! && result not empty
  } //end Get Updates
} //end While
