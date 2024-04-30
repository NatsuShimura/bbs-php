<?php


require_once(__DIR__ .'/../config/config.php');
//条件：Thread_disp.phpからname属性typeが送られてきたら

//var_dump($_POST);
//exit;

if(isset($_POST['type'])) {
  //'thread_id'は数字を代入できる
   //thread_disp.phpから送信された$thread_idを取得
  $thread_id=$_POST['thread_id'];
  $threadCon = new
  //ControllerのThreadクラスをインスタンス化
  Bbs\Controller\Thread();
  //ControllerのThreadクラスのoutputCsvメソッドを実行
  //引数$thread_idをControllerへ渡している
  $threadCon->outputCsv($thread_id);
  exit();
} else {
  header('Location: '. SITE_URL . '/thread_all.php');
  exit();
}
?>