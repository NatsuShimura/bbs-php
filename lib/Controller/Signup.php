<?php

namespace Bbs\Controller;

// Controller：親クラス継承
class Signup extends \Bbs\Controller {

  public function run() {
    //isLoggedInメソッドの返り値がtrueであればサイトトップへ遷移
    if ($this->isLoggedIn()) {
      header('Location: ' . SITE_URL);
      exit();
    }
    // POSTメソッドがリクエストされていればpostProcessメソッド実行
    //$_SERVERメソッド: 送信されてきたものを検知している
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      //var_dump($_POST);
      //exit;
      $this->postProcess();
    }
  }
  protected function postProcess() {
    try {
      $this->validate();
      //Excetionクラスを拡張
      //エラーが出たら上からcatchされてしまう
    } catch (\Bbs\Exception\InvalidEmail $e) {
        $this->setErrors('email', $e->getMessage());
    } catch (\Bbs\Exception\InvalidName $e) {
        $this->setErrors('username', $e->getMessage());
    } catch (\Bbs\Exception\InvalidPassword $e) {
        $this->setErrors('password', $e->getMessage());
    }
    $this->setValues('email', $_POST['email']);
    $this->setValues('username', $_POST['username']);
    //hasError：エラーが引数のフィールドで起きていないかチェック
    if ($this->hasError()) {
      return;
    } else {
      try {
        //重要: Model中のUserクラスがインスタンス化されている
        //DBのやり取りが発生
        $userModel = new \Bbs\Model\User();
        $user = $userModel->create([
          //連想配列でModelにデータを渡している
          //View側のname属性と紐付いている
          'email' => $_POST['email'],
          'username' => $_POST['username'],
          'password' => $_POST['password']
        ]);
      }
      catch (\Bbs\Exception\DuplicateEmail $e) {
        $this->setErrors('email', $e->getMessage());
        return;
      }
    }

    //ログイン処理
    $userModel = new \Bbs\Model\User();
      $user = $userModel->login([
        //signup.phpに紐付いている
        'email' => $_POST['email'],
        'password' => $_POST['password']
      ]);
      //session_regenerate_id:セッションハイジャック対策
      session_regenerate_id(true);
      //変数の値をセッションに保存する
      $_SESSION['me'] = $user;
      header('Location: '. SITE_URL . '/thread_all.php');
      exit();
    }

    //todo：ユーザー登録後にログイン処理を行う

  // バリデーションメソッド
  private function validate() {
    // トークンが空またはPOST送信とセッションに格納された値が異なるとエラー
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
      echo "不正なトークンです!";
      exit();
    }
    if (!isset($_POST['email']) || !isset($_POST['username']) || !isset($_POST['password'])) {
      echo "不正なフォームから登録されています!";
      exit();
    }
    //バリデーションメッセージ
    if (!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)) {
      throw new \Bbs\Exception\InvalidEmail("メールアドレスが不正です!");
    }
    if ($_POST['username'] === '') {
      throw new \Bbs\Exception\InvalidName("ユーザー名が入力されていません!");
    }
    if (!preg_match('/\A[a-zA-Z0-9]+\z/', $_POST['password'])) {
      throw new \Bbs\Exception\InvalidPassword("パスワードが不正です!");
    }
  }
}