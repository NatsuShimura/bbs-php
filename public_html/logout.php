<?php
//コントローラーを呼び出すためだけ

require_once(__DIR__ .'/header.php');
//インスタンス化してrunメソッドを実行する
$app = new Bbs\Controller\Logout();
$app->run();