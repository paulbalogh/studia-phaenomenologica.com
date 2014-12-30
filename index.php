<?php
require __DIR__.'/assets/js/dispatch.php';
require __DIR__.'/assets/js/parsedown.php';

error_reporting(E_ALL|E_STRICT);

map('GET', '/', function ($db) {
  $latest = $db['issue'][14];
  $journal = $db['journal'];
  $contents = $latest['contents'];

  function showCoordinators($issue){
    $html = '';
    for ($i=0; $i<count($issue['issueCoordinator']); $i++) {
      if ($i>0){$html .= ' & ';};
      $html .= $issue['issueCoordinator'][$i];
    }
    return $html;
  }

  function showContents($contents){
    $toc = '';
    if (count($contents) == 0) {} else {
      $sections = '';
      foreach ($contents as $section) {
        if(isset($section['articles'])){
          $articles = '';
          foreach ($section['articles'] as $article) {
            if(isset($article['abstract'])){
              $article['hashID'] = hash('md5', $article['title']); //generate hash ID based on title
              $Parsedown = new Parsedown();
              $article['abstract'] = $Parsedown->text($article['abstract']);
            }
            $articles .= phtml('toc-article', ['article' => $article]);
          }
        $sections .= phtml('toc-section', ['sectionTitle' => $section['sectionTitle'], 'articles' => $articles]);
        }

        if(isset($section['entry'])){
          $entries = '';
          foreach ($section['entry'] as $entry) {
            $Parsedown = new Parsedown();
            $entry = $Parsedown->text($entry);
            $entries .= phtml('toc-entry', ['entry' => $entry]);
          }
        $sections .= phtml('toc-section',['sectionTitle' => $section['sectionTitle'], 'entries' => $entries]);
        }
        
      }
    $toc = phtml('toc', ['sections' => $sections, 'entries' => $entries]);
    }
  return $toc;
  }
  $toc = showContents($contents);
  $order = phtml('order', ['order' => $latest['order']]);
  print phtml('index', ['issue' => $latest, 'journal' => $journal, 'order' => $order, 'toc' => $toc]);
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


# load contents of config.ini
config(parse_ini_file(__DIR__.'/config.ini'));
$dbPath = config('dbpath');

# prep the db
// !file_exists($db = __DIR__.'/posts.txt') && touch($db);
// !file_exists($db = __DIR__.'/assets/js/studia.json') && touch($db);
$db = json_decode(file_get_contents($dbPath), true);


# pass along our data store
dispatch($db);
