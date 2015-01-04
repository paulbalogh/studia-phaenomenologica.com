<?php
require 'vendor/autoload.php';
$Parsedown = new Parsedown();
error_reporting(E_ALL|E_STRICT);
// $myData = new pData();

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

map(['/issues/{slug}'], function ($params, $db, $current, $forthcoming, $Parsedown) {
  // get all slugs
  foreach ($db['issue'] as $id => $issue) {
    $slugs[$id] = $issue['info']['slug'];
  }
  // check if present
  if(in_array($params['slug'], $slugs)){

    $id = array_search($params['slug'], $slugs);
    $issue = $db['issue'][$id];
    $journal = $db['journal'];

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
  } 
  else 
  {
    error(404, 'not a know slug');
  }
});

map('GET', '/editorial-board', function($db, $current, $forthcoming){
  print phtml('editorial-board');
});

map('GET', '/call-for-papers', function($db, $current, $forthcoming){
  print phtml('call-for-papers');
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

map(404, function ($code, $info) {
  $error =  'Eroare 404: '.(is_string($info) ? $info : 'resursa nu a fost gasita');
  print phtml('404', ['error' => $error]);
});


# pass along our data store
dispatch($db, $current, $forthcoming, $Parsedown);
