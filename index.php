<?php
require 'vendor/autoload.php';
$Parsedown = new Parsedown();
error_reporting(E_ALL|E_STRICT);

# load contents of config.ini
config(parse_ini_file(__DIR__.'/config.ini'));
$dbPath = config('dbpath');
$current = config('current');
$forthcoming = config('forthcoming');

# read studia data as json from file
$db = json_decode(file_get_contents($dbPath), true) or die('cannot find json source');

map(['/', '/index','/index.php'], function ($db, $current, $forthcoming) {

  print phtml('home');
});

map(['/all', '/all-issues', '/issues'], function($db, $current, $forthcoming){

  foreach ($db['issue'] as $id => $issue) {
    $issues[$id] = $issue['info'];
  }

  print phtml('all-issues', ['issues' => $issues]);
});

map(['/issues/{id}'], function ($params, $db, $current, $forthcoming, $Parsedown) {
  $issue = $db['issue'][$params['id']];
  // $latest = $db['issue'][$current];
  $journal = $db['journal'];
  $contents = $issue['contents'];

  function showCoordinators($issue){
    $html = '';
    for ($i=0; $i<count($issue['issueCoordinator']); $i++) {
      if ($i>0){$html .= ' & ';};
      $html .= $issue['issueCoordinator'][$i];
    }
    return $html;
  }

  $toc = phtml('toc', ['contents' => $issue['contents'], 'Parsedown' => $Parsedown], false);
  $order = phtml('order', ['order' => $issue['order']], false);
  print phtml('issue', ['issue' => $issue['info'], 'journal' => $journal, 'order' => $order, 'toc' => $toc], 'layout');
});

map('GET', '/editorial-board', function($db, $current, $forthcoming){
  print phtml('editorial-board');
});

map('GET', '/call-for-papers', function($db, $current, $forthcoming){
  print phtml('placeholder', ['content' => 'call-for-papers']);
});

map('GET', '/buy-subscribe', function($db, $current, $forthcoming){
  print phtml('placeholder', ['content' => 'buy-subscribe']);
});

map('GET', '/how-to-sumit-articles', function($db, $current, $forthcoming){
  print phtml('placeholder', ['content' => 'how-to-sumit-articles']);
});

map('GET', '/contact', function($db, $current, $forthcoming){
  print phtml('placeholder', ['content' => 'contact']);
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
  echo 'eroare 404: resursa nu a fost gasita!';
});


# pass along our data store
dispatch($db, $current, $forthcoming, $Parsedown);
