<?php
//退会処理を実行
//UserDeleteコントローラーを呼び出している
require_once(__DIR__ .'/header.php');
$app = new Bbs\Controller\UserDelete();
$app->run();
