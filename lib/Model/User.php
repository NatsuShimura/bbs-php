<?php

namespace Bbs\Model;

class User extends \Bbs\Model {
  //createメソッド: ユーザーを新規登録する
  //Signup.phpと紐付いている
  public function create($values) {

    //prepareメソッドはユーザーからの入力をSQLに含めることができる（つまり変数を埋め込みできる)
    //ユーザー入力した内容がすぐ反映することにより、SQLインジェクションを受けてしまうため対策する
    //対策： prepareを使用し、すぐ反映せずワンクッション置いている
    //prepare:プリペアードステートメント
    //$values:　ユーザーの入力データが格納される
    $stmt = $this->db->prepare(
      "INSERT INTO users (username,email,password,created,modified)
      VALUES (:username,:email,:password,now(),now())");
    $res = $stmt->execute([
      ':username' => $values['username'],
      ':email' => $values['email'],
      // パスワードのハッシュ化(DBへ格納される場合に暗号化)
      ':password' => password_hash($values['password'],PASSWORD_DEFAULT)
    ]);
    // メールアドレスがユニーク(重複しない)でなければfalseを返す
    if ($res === false) {
      //throw:  ある条件と一致する場合に例外を発生させ、エラーメッセージを返す
      throw new \Bbs\Exception\DuplicateEmail();
    }
  }

  //ログインメソッド

  public function login($values) {
    //var_dump($values);
    //exit();

    //:ユーザーが入力したemailが$stmtに格納されている
    $stmt = $this->db->prepare(
      "SELECT * FROM users WHERE email = :email;"
    );
    $stmt->execute([
      //$values['email']はemailが格納されている
      //SQL文の「WHERE email = :email」と紐付いている
      ':email' => $values['email']
    ]);

    $stmt->setFetchMode(\PDO::FETCH_CLASS, 'stdClass');
    //SQL文の結果が格納されている、もしくは格納いない
    $user = $stmt->fetch();
    //　empty： ユーザーが空かどうか判定する(空であればtrue)
    if (empty($user)) {
      throw new \Bbs\Exception\UnmatchEmailOrPassword();
    }
    //条件: Passwordが合っているか合っていないか、!password_verifyでPasswordを検知している
    //条件: 第一引数、第二引数が合っていないか(!がついているから否定形)
    if (!password_verify($values['password'], $user->password)) {
      throw new \Bbs\Exception\UnmatchEmailOrPassword();
    }
    //【課題】退会ユーザーでログインしようとしたとき(例外処理)
    //$(ダラー)userにはユーザーのログインが入っている
    if ($user->delflag == 1) {
      throw new \Bbs\Exception\DeleteUser();
    }
    //$userをloginメソッドの外に出すことで再利用できるようにしている
    return $user;
  }

 //find,updateメソッドの追加

  //ユーザーがログインしている情報usersテーブルから取得する
  //引数$idにControllerのuserUpdateクラスから渡ってきたログインユーザーのid($_SESSION)が格納されている
  //$_SESSION['me']：ユーザーがログインしている情報が格納されている
  //処理順：SQL文→vindValueで紐づける→executeで実行→実行結果をreturnで返す
  public function find($id) {
    $stmt = $this->db->prepare
    ("SELECT * FROM users WHERE id = :id;");
    $stmt->bindValue('id',$id);
    $stmt->execute();
    $stmt->setFetchMode(\PDO::FETCH_CLASS, 'stdClass');
    $user = $stmt->fetch();
    //取得したものを返している
    return $user;
  }

  //ControllerのUserUpdateクラスのUserUpdateメソッドから渡ってきた
  //
  public function update($values) {

    //var_dump($values);
    //exit;

    //usersテーブル内容を更新する
    //usersテーブルのid列とログインユーザーのid($_SESSION)が等しい
    $stmt = $this->db->prepare
    ("UPDATE users SET username = :username,email = :email, image = :image, modified = now() where id = :id");
    //execute内でbindで実行前にワンクッション置いている
    $res =  $stmt->execute([
      ':username' => $values['username'],
      ':email' => $values['email'],
      'image' => $values['userimg'],
      ':id' => $_SESSION['me']->id,
    ]);

    if ($res === false) {
      throw new \Bbs\Exception\DuplicateEmail();
    }
  }

  //ユーザー退会
  public function delete() {
    $stmt = $this->db->prepare(
      //usersテーブルのdelflagの内容を1に変更している
      //WHEREの条件は、usersテーブルのid列とログインユーザーのidと等しい
      "UPDATE users SET delflag = :delflag,modified = now() where id = :id"
    );
    //【重要】delflagをUPDATE文で1(退会済)に変更している
    //論理削除している
    $stmt->execute([
      ':delflag' => 1,
      ':id' => $_SESSION['me']->id,
    ]);
  }
}

