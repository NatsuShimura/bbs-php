<?php


namespace Bbs\Controller;

class UserUpdate extends \Bbs\Controller {

  public function run()
   {
    //showUser：ユーザー情報を表示
    //条件：POST送信されてきた時のみ以下の処理を実行する
    $this->showUser();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      //var_dump($_FILES['image']);
      //exit;

      //常に実行されている
      //【重要】updateUserの処理
      $this->updateUser();
    }
  }

  //ログインしているユーザー情報を表示
  protected function showUser() {

    //ModelのUserクラスをインスタンス化している
    $user = new \Bbs\Model\User();
    //ユーザーの情報をidを渡している
    //Userクラスのfindメソッドで実行している
    $userData = $user->find($_SESSION['me']->id);
    //setValuesで紐づけるセットをしている
    //$userdataのusername,email,imageを使うよ
    $this->setValues('username', $userData->username);
    $this->setValues('email', $userData->email);
    $this->setValues('image', $userData->image);
  }

  //mypage.phpから渡ってきたデータを$_POSTに格納
  protected function updateUser() {

    //echo '<pre>';
    //var_dump($_POST);
    //echo '</pre>';
    //exit;

    try {
      $this->validate();
    } catch (\Bbs\Exception\InvalidEmail $e) {
      $this->setErrors('email', $e->getMessage());
    } catch (\Bbs\Exception\InvalidName $e) {
      $this->setErrors('username', $e->getMessage());
    }
    $this->setValues('username', $_POST['username']);
    $this->setValues('email', $_POST['email']);
    if ($this->hasError()) {
      return;

    } else {
      //$_FILESメソッド：アップロードした画像データを取得
      //$user_imgには新しい画像データが入っている
      $user_img = $_FILES['image'];
      //$old_imgには古い画像データが入っている
      $old_img = $_POST['old_image'];

      // 【課題】条件：古い画像が存在しなかったら
      if($old_img == '') {
        $old_img = NULL;
      }


      //$extにユーザーがアップロードした画像をユニーク(重複しない)ものに変えている
      //strrposでドットより前の文字列を取得して切り出している
      $ext = substr($user_img['name'], strrpos($user_img['name'], '.') + 1);
      $user_img['name'] = uniqid("img_") .'.'. $ext;

      try {
        //ModelのUserクラスのデータを$userModelに格納
        $userModel = new \Bbs\Model\User();

        //$user_imgが0より大きいとき
        //画像がアップロードされたら
        if($user_img['size'] > 0) {
          //unlink：指定した古い画像のデータを削除
          unlink('./gazou/'.$old_img);
          //指定したディレクトリにファイルを保存するメソッド
          move_uploaded_file($user_img['tmp_name'],'./gazou/'.$user_img['name']);
          $userModel->update([
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'userimg' => $user_img['name']
          ]);
          //セッション変数にユーザー画像名を登録
          $_SESSION['me']->image = $user_img['name'];

          //画像がアップロードされなかったら
        } else {
          $userModel->update([
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'userimg' => $old_img
          ]);
          $_SESSION['me']->image = $old_img;
        }
      }
      catch (\Bbs\Exception\DuplicateEmail $e) {
        $this->setErrors('email', $e->getMessage());
        return;
      }
    }
    $_SESSION['me']->username = $_POST['username'];
    header('Location: '. SITE_URL . '/mypage.php');
    exit();
  }

  private function validate() {
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
      echo "不正なトークンです!";
      exit();
    }
    if (!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)) {
      throw new \Bbs\Exception\InvalidEmail("メールアドレスが不正です!");
    }
    if ($_POST['username'] === '') {
      throw new \Bbs\Exception\InvalidName("ユーザー名が入力されていません!");
    }
  }
}
