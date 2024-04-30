<?php

namespace Bbs\Controller;

//Controller.phpを継承してきている
class Login extends \Bbs\Controller {
  public function run() {

    // ログインしていればトップページへ移動(header関数)
    if ($this->isLoggedIn()) {
      header('Location: ' . SITE_URL);
      exit();
    }
    //送信されてきているか検知している
    //条件: Viewのlogin.phpからPOST送信されてきたら
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->postProcess();
    }
  }

  //POST送信されたものを格納
  protected function postProcess() {
    //var_dump($_POST);
    //exit();
    try {
      $this->validate();
    } catch (\Bbs\Exception\EmptyPost $e) {
        $this->setErrors('login', $e->getMessage());
    }
    //Controller.phpのsetValuesで渡ってきたものをセット
    $this->setValues('email', $_POST['email']);
    if ($this->hasError()) {
      return;
    } else {
      try {
        //User.phpのuserModelをインスタンス化
        $userModel = new \Bbs\Model\User();
        //ユーザーの入力情報を連想配列で受け渡している
        $user = $userModel->login([
          'email' => $_POST['email'],
          'password' => $_POST['password']
        ]);
      }
      catch (\Bbs\Exception\UnmatchEmailOrPassword $e) {
        $this->setErrors('login', $e->getMessage());
        return;
      }

      //【課題】例外をキャッチして実行する
      //例外処理：ログインを中止、エラーメッセージを表示する
      catch (\Bbs\Exception\DeleteUser $e) {
        $this->setErrors('login', $e->getMessage());
        return;
      }
      // ログイン処理
      //session: ブラウザにユーザーデータを保存(Cookie)
      //session_regenerate_id関数･･･現在のセッションIDを新しいものと置き換える。セッションハイジャック対策
      session_regenerate_id(true);
      // ユーザー情報をセッションに格納
      //$userにはDBのアカウントユーザー情報が格納されている
      $_SESSION['me'] = $user;
      // スレッド一覧ページ(thread_all.php)へリダイレクト(遷移)
      header('Location: '. SITE_URL . '/thread_all.php');
      exit();
    }
  }
  private function validate() {
    // トークンが空またはPOST送信とセッションに格納された値が異なるとエラー
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
      echo "トークンが不正です!";
      exit();
    }
    // emailとpasswordのキーがなかった場合、強制終了
    if (!isset($_POST['email']) || !isset($_POST['password'])) {
      echo "不正なフォームから登録されています!";
      exit();
    }
    if ($_POST['email'] === '' || $_POST['password'] === '') {
      throw new \Bbs\Exception\EmptyPost("メールアドレスとパスワードを入力してください!");
    }
  }
}