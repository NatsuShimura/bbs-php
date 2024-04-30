<?php
ini_set('display_errors',1);
define('DSN','mysql:host=localhost;charset=utf8;dbname=bbs');
define('DB_USERNAME','bbs_user');
//DBのパスワードをコピペして紐づける
define('DB_PASSWORD','is2(Q26VD5PHWbFi');
//SITE_URLが記載されている
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/bbs/public_html');
require_once(__DIR__ .'/../lib/Controller/functions.php');
require_once(__DIR__ . '/autoload.php');

session_start();
$current_uri =  $_SERVER["REQUEST_URI"];
  //basename関数：URLのファイル名を取得する
  $file_name = basename($current_uri);
  //strpos関数：文字列に該当する文字が含まれているか判定
  //画面にアクセスしようとしているのかどうかを判定している
  if(strpos($file_name,'login.php') !== false || strpos($file_name,'signup.php') !== false || strpos($file_name,'index.php') !== false || strpos($file_name,'public_html') !== false) {
    // URL内のファイル名がlogin.php、signup.php、index.php(public_html)のとき
  }
  else {
    // それ以外の時
    if(!isset($_SESSION['me'])){
      header('Location: ' . SITE_URL . '/login.php');
      exit();
    }
  }