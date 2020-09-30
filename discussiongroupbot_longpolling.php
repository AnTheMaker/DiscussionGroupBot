<?php

// IMPORTANT: This is a beta version of the bot using long-polling instead of webhooks

$bottoken = 'your_token_goes_here'; // Put your Telegram Bot token here

$api_base = 'https://api.telegram.org/bot'.$bottoken.'/';
$content = file_get_contents($api_base.'getUpdates');
$updates = json_decode($content, true);

if($updates['result']){
  foreach($updates['result'] as $key=>$update){
  
    $type = $update['message']['chat']['type'];
    $chatID = $update['message']['chat']['id'];
    $msg = strtolower($update['message']['text']);
    $msg_id = $update['message']['message_id'];
    $msg_from_id = $update['message']['from']['id'];
    $update_id = $update['update_id'];
    
    if($msg == '/start' || $msg == '/start@discussiongroupbot'){
      $txt = '_Hello, nice to meet you!_
    Make me admin in your group and I will automatically remove all forwarded posts from your linked channel so the group doesn\'t get filled with channel messages!

    *Bot dev:* @PartyGuy';
      file_get_contents($api_base.'sendMessage?chat_id='.urlencode($chatID).'&text='.urlencode($txt).'&parse_mode=Markdown&reply_to_message_id='.urlencode($msg_id));
    }else{
      if($msg_from_id == 777000){
        file_get_contents($api_base.'deleteMessage?chat_id='.urlencode($chatID).'&message_id='.urlencode($msg_id));
      }
    }

    file_get_contents($api_base.'getUpdates?offset='.($update_id+1));
  }
}
die('ok');
?>
