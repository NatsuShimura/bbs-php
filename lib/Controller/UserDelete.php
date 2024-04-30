<?php

namespace Bbs\Controller;

class UserDelete extends \Bbs\Controller {
  public function run() {
    //Model側のUserクラスをインスタンス化
    $user = new \Bbs\Model\User();


    //findメソッド：ログインしているユーザー情報をusersテーブルから取得
    //引数にはログインしているユーザーidを定義
    $userData = $user->find($_SESSION['me']->id);
    //ゲッターで値をセット、表示
    //【重要】第一引数：値の名前部分、第二引数：セット値
    $this->setValues('username', $userData->username);
    $this->setValues('email', $userData->email);

    //条件：POST送信かつ「name="type" value="delete"」が送信されてきたら
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type']) == 'delete') {
      // バリデーション
      //【重要】クロスサイトリクエストフォージェリ(XSRF)対策
      //XSRF：今回の掲示板アプリ以外のフォームから意図的にデータの送信を送って攻撃すること
      if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
        echo "不正なトークンです!";
        exit;
      }

    //ユーザーモデルのdeleteメソッドを呼び出す
    $userModel = new \Bbs\Model\User();
    $userModel->delete();

    //ログインする際にセットしたセッションの情報を空にする
    $_SESSION = [];

    // クッキーにセッションで使用されているクッキーの名前がセットされていたら空にする
    if (isset($_COOKIE[session_name()])) {
      setcookie(session_name(), '', time() - 86400, '/');
    }

    // セッションの破棄
    // セッションハイジャック対策
    session_destroy();

    header('Location: ' . SITE_URL . '/index.php');
    exit();
    }
  }
}