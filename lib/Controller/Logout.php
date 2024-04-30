<?php

namespace Bbs\Controller;

class Logout extends \Bbs\Controller {
  public function run() {
    //条件: header.phpからPOST送信されていればtrue
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      //条件1️⃣: name属性tokenが送信されなかったらtrue
      //条件2️⃣: SESSIONと比較して正しくなければtrue
      if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
        echo "不正なトークンです!";
        exit();
      }
      //セッション変数に格納されている値を空にする
      $_SESSION = [];
      //クッキーの名前がセットされていたら空にする
      //session_name(): session['me']のこと
      if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 86400, '/');
      }
      // セッションの破棄
      session_destroy();
    }
    // トップページへリダイレクト
    header('Location: ' . SITE_URL);
  }
}