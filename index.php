<?php
require 'vendor/autoload.php';
use Peekmo\JsonPath\JsonStore; // ask for JsonStore($json) object

error_reporting(E_ALL|E_STRICT);

# load contents of config.ini
config(parse_ini_file(__DIR__.'/config.ini'));
$dbPath = config('dbpath');
$current = config('current');
$forthcoming = config('forthcoming');

# read studia data as json from file
$db = json_decode(file_get_contents($dbPath), true) or die('cannot find json source');

map('GET', '/', function ($db, $current, $forthcoming) {
  $latest = $db['issue'][$current];
  $journal = $db['journal'];
  $contents = $latest['contents'];
  $Parsedown = new Parsedown();

  function showCoordinators($issue){
    $html = '';
    for ($i=0; $i<count($issue['issueCoordinator']); $i++) {
      if ($i>0){$html .= ' & ';};
      $html .= $issue['issueCoordinator'][$i];
    }
    return $html;
  }

  $toc = phtml('toc', ['contents' => $latest['contents'], 'Parsedown' => $Parsedown], false);
  $order = phtml('order', ['order' => $latest['order']], false);
  print phtml('home', ['issue' => $latest, 'journal' => $journal, 'order' => $order, 'toc' => $toc], 'layout');
});

// # show a post
// map('GET', '/posts/{id}', function ($args, $db) {
//   foreach (file($db) as $post) {
//     $post = unserialize($post);
//     if ($post['id'] != $args['id']) {
//       continue;
//     }
//     print phtml('post', ['post' => $post]);
//   }
// });

// # new post form
// map('GET', '/submit', function () {
//   print phtml('submit', blanks('title', 'body'));
// });

// # create a new post
// map('POST', '/create', function ($db) {

//   $post = $_POST['post'];
//   $post['id'] = time();

//   file_put_contents($db, serialize($post)."\n", FILE_APPEND);

//   return redirect('/');
// });

map(404, function ($code) {
  echo 'eroare 404: resursa nu a fost gasită!';
});


# pass along our data store
dispatch($db, $current, $forthcoming);
