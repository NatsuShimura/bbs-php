<?php
//JSで送信されたデータをモデルクラスに渡してDBにアクセスする、テーブルデータを書き換える

require_once(__DIR__ .'/../config/config.php');


//bbs.jsから渡ってきたデータが$_POSTに格納されている
//条件：bbs.jsからPOST送信されてきたらtrue
$threadApp = new \Bbs\Model\Thread();
if($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    //ModelのThreadクラスのchangeFavoriteメソッドを呼び出す
    $res = $threadApp->changeFavorite([
      'thread_id' => $_POST['thread_id'],
      'user_id' => $_POST['user_id']
    ]);
    //json：Ajaxと仲いい
    header('Content-Type: application/json');
    echo json_encode($res);
  } catch (Exception $e) {
    header($_SERVER['SERVER_PROTOCOL']. '500 Internal Server Error', true, 500);
    echo $e->getMessage();
  }
}