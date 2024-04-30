<?php
//config.php: DB接続が記述されている
require_once(__DIR__ .'/../config/config.php');

try {
  //config.phpのDB情報を元にDB接続をPDOオブジェクトで行う
  $dbh = new PDO(DSN, DB_USERNAME, DB_PASSWORD);
  //SQL文をexecuteで実行
  $stmt = $dbh->query('SELECT * FROM test');
  $stmt->execute();
  //DB接続を切断
  $dbh = null;
  //$stmt->execute();で実行した結果を1件取得している
  $rec = $stmt->fetch(PDO::FETCH_ASSOC);
  //fetchで取得したものを$recに格納しnameカラムをechoで出力している
  echo $rec["name"];
} catch (\PDOException $e) {
  echo $e->getMessage();
  exit;
}


