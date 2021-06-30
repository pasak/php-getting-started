<?php
$access_token = str_replace(' ','+',$_REQUEST['Token']);

// echo '$access_token ', $access_token;

$arrContextOptions=array(
      "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );
// Get POST body content
$content = file_get_contents('php://input');
// Parse JSON
$events = json_decode($content, true);
// Validate parsed JSON data
if (!is_null($events['events'])) {
	// Loop through each event
	foreach ($events['events'] as $event) {
      if ($event['source']['type'] == 'user') $ParentID = '' ;
      else $ParentID = ($event['source']['type'] == 'group') ? $event['source']['groupId'] : $event['source']['roomId'] ;

      $url = 'https://serviceq.online/rider/' . $_REQUEST['Group'] . '-reply.php?Token=' . $access_token .
             '&SourceType=' . $event['source']['type'] . '&ParentID=' . $ParentID .
             '&UserID='. $event['source']['userId'] . '&ReplyToken=' . $event['replyToken'] ;

      $url .= '&MessageID=' . $event['message']['id'] . '&MessageType=' . $event['message']['type'] .
  	          '&EventType=' . $event['type'] ;

      switch ($event['message']['type'] ) {
          case 'location':
            $url .= '&Title=' . urlencode($event['message']['title']) .
                    '&Location=' . urlencode($event['message']['address']) .
                    '&Latitude=' . $event['message']['latitude'] .
                    '&Longitude=' . $event['message']['longitude'] ;
            break;
          default:
            $url .= '&Text=' . urlencode($event['message']['text']);
            break;
      }

        $json = file_get_contents($url, false, stream_context_create($arrContextOptions));

        $messages = json_decode($json,true);

        // $messages = array(['type' => 'text','text' => $url]);

  			// Get replyToken
  			$replyToken = $event['replyToken'];
        // Make a POST Request to Messaging API to reply to sender
  			$url = 'https://api.line.me/v2/bot/message/reply';

  			$data = [
  				'replyToken' => $replyToken,
  				'messages' => $messages,
  			];
  			$post = json_encode($data);
  			$headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token);
  			$ch = curl_init($url);
  			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  			$result = curl_exec($ch);
  			curl_close($ch);
  			echo $result . "\r\n";
		  // } // if (in_array
	} // foreach
} // if (!is_null

echo "OK";

/*
require('../vendor/autoload.php');

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

// Register view rendering
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

// Our web handlers

$app->get('/', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('index.twig');
});

$app->run();
*/
?>
