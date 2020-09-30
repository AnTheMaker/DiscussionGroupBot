<?php

$bottoken = 'your_token_goes_here'; // Put your Telegram Bot token here

$content = file_get_contents("php://input");
$update = json_decode($content, true);

$type = $update['message']['chat']['type'];
$chatID = $update['message']['chat']['id'];
$msg = strtolower($update['message']['text']);
$msg_id = $update['message']['message_id'];
$msg_from_id = $update['message']['from']['id'];


if($msg == '/start' || $msg == '/start@discussiongroupbot'){
  $txt = '_Hello, nice to meet you!_
Make me admin in your group and I will automatically remove all forwarded posts from your linked channel so the group doesn\'t get filled with channel messages!

*Bot dev:* @PartyGuy';

  file_get_contents('https://api.telegram.org/bot'.$bottoken.'/sendMessage?chat_id='.urlencode($chatID).'&text='.urlencode($txt).'&parse_mode=Markdown&reply_to_message_id='.$msg_id);

}else{
  if($msg_from_id == 777000){
    file_get_contents('https://api.telegram.org/bot'.$bottoken.'/deleteMessage?chat_id='.urlencode($chatID).'&message_id='.urlencode($msg_id));
  }
}


die('ok.');
?>
