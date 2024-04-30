<?php

namespace Bbs\Model;
//ModelのThread.phpを継承している
class Thread extends \Bbs\Model
{
  //ユーザー入力した内容を$valuesに格納
  public function createThread($values)
  {
    //var_dump($values);
    //exit();

    try {
      //トランザクション内にSQL文２つ処理をひとまとまりにして実行する必要がある
      $this->db->beginTransaction();
      $sql =
        "INSERT INTO threads (user_id,title,created,modified)
       VALUES (:user_id,:title,now(),now())";
      //prepareでSQL文を実行準備
      $stmt = $this->db->prepare($sql);
      //bindValue：$sqlのSQL文とユーザー入力したものを紐づけている
      $stmt->bindValue('user_id', $values['user_id']);
      $stmt->bindValue('title', $values['title']);
      //executeで実行
      $res = $stmt->execute();

      //commentsテーブルの処理
      //lastInsertIdは最後に登録したデータのIDを取得する
      //threadsテーブルのIDが対象
      $thread_id = $this->db->lastInsertId();
      $sql =
        "INSERT INTO comments (thread_id,comment_num,user_id,content,created,modified)
       VALUES (:thread_id,1,:user_id,:content,now(),now())";
      //bindValue：$sqlのSQL文とユーザー入力したものを紐づけている
      $stmt = $this->db->prepare($sql);
      $stmt->bindValue('thread_id', $thread_id);
      $stmt->bindValue('user_id', $values['user_id']);
      $stmt->bindValue('content', $values['comment']);
      //executeで実行
      $res = $stmt->execute();
      //commitであれば処理を完了させる
      $this->db->commit();
    } catch (\Exception $e) {
      echo $e->getMessage();
      //処理を中断してトランザクション処理をなかったことにされる
      $this->db->rollBack();
    }
  }


  // 全スレッド取得
  public function getThreadAll()
  {
    $user_id = $_SESSION['me']->id;
    //スレッド一覧画面で、ログイン中のユーザーがお気に入りしたスレッドかどうか判定する処理
    $stmt = $this->db->query
    ("SELECT t.id AS t_id,title,t.created,f.id AS f_id FROM threads AS t
    LEFT JOIN favorites AS f ON t.delflag = 0
    AND t.id = f.thread_id  AND f.user_id = $user_id
    ORDER BY t.id desc");
    return $stmt->fetchAll(\PDO::FETCH_OBJ);
  }


  // お気に入り中の全スレッド取得
  public function getThreadFavoriteAll()
  {
    //ログインしているユーザーidを$user_idに格納
    $user_id = $_SESSION['me']->id;
    $stmt = $this->db->query
    ("SELECT t.id AS t_id,title,t.created,f.id AS f_id FROM threads AS t
    INNER JOIN favorites AS f ON t.delflag = 0
     AND t.id = f.thread_id  AND f.user_id = $user_id
    ORDER BY t.id desc");
    return $stmt->fetchAll(\PDO::FETCH_OBJ);
  }


  // コメント取得
  //getComment:threadsのid列と紐付いてるコメントを複数件取得
  //$thread_idは、Viewのthreadsテーブルid列が引数に格納
  public function getComment($thread_id)
  {

    //「SELECT comment_num,username,content」はcommentsテーブル列
    //「comments.created」もcommentsテーブル列
    //「INNER JOIN users」は、commentsテーブルとusersテーブルを結合してる
    //「ON user_id = users.id WHERE thread_id =:thread_id」のON句は結合条件(条件:Commentsテーブルのuser_idとusersテーブルのidが等しい場合、結合する)

    //「WHERE thread_id =:thread_id AND comments.delflag = 0 」は、commentsテーブルthread_idとView側のthread_idが等しい かつ Commentsテーブルのdelflagが0のものを取得する
    //「ORDER BY comment_num ASC LIMIT 5」は、コメント数、名前、コメント、作成日を1番から昇順に5件取得している
    //「WHERE thread_id =:thread_id」は、commentsテーブルthread_idとView側のthread_idを紐づけている
    $stmt = $this->db->prepare(
      "SELECT comment_num,username,content,comments.created FROM comments
      INNER JOIN users ON user_id = users.id WHERE thread_id =:thread_id
      AND comments.delflag = 0
      ORDER BY comment_num
       ASC LIMIT 5;"
       );
    $stmt->execute([':thread_id' => $thread_id]);
    return $stmt->fetchAll(\PDO::FETCH_OBJ);
  }


  // コメント数取得
  //threadテーブルのthread_idが引数に格納
  public function getCommentCount($thread_id)
  {
    $stmt = $this->db->prepare
      //COUNT関数：コメント数をカウントしている
      //AS句でrecord_numと別名つけている
      //「thread_id = :thread_id」はViewと等しい
      //$thread_idに紐づくコメント数はrecord_numに入っている
      ("SELECT COUNT(comment_num) AS record_num FROM comments  WHERE thread_id = :thread_id AND delflag = 0;");
    //$thread_idとView側とbindValueによって紐づけている
    $stmt->bindValue('thread_id', $thread_id);
    $stmt->execute();
    //FETCH_ASSOC:取得結果を連想配列で返してくれる
    //comment_numを配列形式で単体をfetchで取得
    //SQLの実行結果を$resに格納
    $res =  $stmt->fetch(\PDO::FETCH_ASSOC);

    //var_dump($res);
    //exit;
    //returnで結果を返す
    return $res['record_num'];
  }

  // 渡ってきたクエリパラメータに紐づくスレッド1件取得(クエリパラメータに紐づくスレッド)
  //$thread_idの引数にはクエリパラメータ情報が格納されている
  public function getThread($thread_id)
  {

    //var_dump($thread_id);
    //exit;

    //threadsテーブルのid とクエリパラメータが等しい
    //SELECT文でとりあえずすべて取得しているが、delflagは取得しなくていい
    $stmt = $this->db->prepare(
      "SELECT * FROM threads WHERE id = :id AND delflag = 0;"
    );
    //SQLのidとthread_disp.phpのクエリパラメータと紐付いている
    //:idはSQL文の:idと紐付いている
    //$thread_id->;idの処理でも実行可能である
    $stmt->bindValue(":id", $thread_id);
    $stmt->execute();
    //fetchで１件だけ取得したものを返している
    //returnを使用することで処理を外部へ取り出している
    return $stmt->fetch(\PDO::FETCH_OBJ);
  }

  // 渡ってきたThreadテーブルのパラメータに紐づくコメント全件取得
  public function getCommentAll($thread_id)
  {

    //Commentテーブルとuserテーブルが結合(結合理由：ユーザー名を記載したいから)
    //Commentテーブル: comment_num,content,comments.created
    //userテーブル: username
    //結合条件：Commentテーブルのusers_idとuserテーブルのuser_idが等しい
    //commentsテーブルthread_idとクエリパラメータのthread_idを紐づけている
    $stmt = $this->db->prepare(
      "SELECT comment_num,username,content,comments.created FROM comments
    INNER JOIN users ON user_id = users.id
     WHERE thread_id =:thread_id AND comments.delflag = 0
     ORDER BY comment_num ASC;"
    );
    $stmt->execute([':thread_id' => $thread_id]);
    return $stmt->fetchAll(\PDO::FETCH_OBJ);
  }

  // コメント投稿

  //ControllerのThread.phpから渡ってきたデータは引数$valuesに格納されている
  public function createComment($values)
  {

    //var_dump($values);
    //exit;

    try {
      $this->db->beginTransaction();
      $lastNum = 0;
      //「 ORDER BY comment_num DESC LIMIT 1」はcomment_numが一番大きいものを１件取得する
      $sql =
        "SELECT comment_num FROM comments
    WHERE thread_id = :thread_id
    ORDER BY comment_num DESC LIMIT 1";
      $stmt = $this->db->prepare($sql);

      //Thread＿idが２番のもの(取得したスレッドの数字)だけを取得している
      $stmt->bindValue('thread_id', $values['thread_id']);
      $stmt->execute();
      //取得したデータ内容を$resに格納
      $res = $stmt->fetch(\PDO::FETCH_OBJ);
      //$lastNumには、数字が入っている
      $lastNum = $res->comment_num;
      //$lastNumをインクリメントしてプラスしていく
      //Comment_numが重複しないように＋１している！
      $lastNum++;

      $sql =
        //新規登録
      "INSERT INTO comments (thread_id,comment_num,user_id,content,created,modified)
      VALUES (:thread_id,:comment_num,:user_id,:content,now(),now())";
      //プリペアードステートメントでワンクッション置いている
      //「$values['thread_id']」は、クエリパラメータのThread_id
      //「$values['user_id']」は、$_SSESIONのこと、ユーザーのid
      //「$values['content']」は、ユーザーが入力したコメント
      //bindValueで$sqlをSQL文を紐づけている
      //【重要】「$lastNum」で最後に取得したコメントのcomment_numが入っている
      $stmt = $this->db->prepare($sql);
      $stmt->bindValue('thread_id', $values['thread_id']);
      $stmt->bindValue('comment_num', $lastNum);
      $stmt->bindValue('user_id', $values['user_id']);
      $stmt->bindValue('content', $values['content']);
      //処理を実行
      $stmt->execute();
      //処理の実行成功
      $this->db->commit();
    } catch (\Exception $e) {
      echo $e->getMessage();
      //処理をすべて中断
      $this->db->rollBack();
    }
  }


  //スレッドお気に入りの処理

  //ControllerのThread.phpから渡ってきたデータは$valuesに格納されている
  public function changeFavorite($values)
  {
    try {
      $this->db->beginTransaction();
      // レコード取得
      //ajax.phpから送信されたthread_id、user_idがfavoriteテーブルのthread_id、user_idと等しいかどうか
      $stmt = $this->db->prepare(
        "SELECT * FROM favorites
         WHERE thread_id = :thread_id AND user_id = :user_id"
      );
      //ajax.phpから渡ってきたデータとSQLを紐づけて実行
      $stmt->execute([
        ':thread_id' => $values['thread_id'],
        ':user_id' => $values['user_id']
      ]);
      $stmt->setFetchMode(\PDO::FETCH_CLASS, 'stdClass');
      //$recに取り出したデータが格納
      //データがあるかないか
      $rec = $stmt->fetch();

      //0＝お気に入りされていない状態
      $fav_flag = 0;
      //$recが空の場合、つまりお気に入りしていない状態
      if (empty($rec)) {
        $sql = "INSERT INTO favorites (thread_id,user_id,created) VALUES (:thread_id,:user_id,now())";
        $stmt = $this->db->prepare($sql);
        //ajax.phpから渡ってきたデータをSQLと紐づけている
        $stmt->execute([
          ':thread_id' => $values['thread_id'],
          ':user_id' => $values['user_id']
        ]);
        $fav_flag = 1;
      } else {
        $sql = "DELETE FROM favorites WHERE thread_id = :thread_id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute([
          ':thread_id' => $values['thread_id'],
          ':user_id' => $values['user_id']
        ]);
        $fav_flag = 0;
      }
      $this->db->commit();
      return $fav_flag;
    } catch (\Exception $e) {
      echo $e->getMessage();
      // エラーがあったら元に戻す
      $this->db->rollBack();
    }
  }

    // CSV出力

    //$thread_idに紐づくコメント情報を全件取得してCSVファイルのデータをつくる
    public function getCommentCsv($thread_id){
      $stmt = $this->db->prepare
      //comment,users,threadテーブルが紐付いている
      ("SELECT comment_num,username,content,comments.created
      FROM (threads INNER JOIN comments on threads.id = comments.thread_id)
      INNER JOIN  users ON comments.user_id = users.id WHERE threads.id =:thread_id
      AND comments.delflag = 0
      ORDER BY comment_num ASC;"
      );
      $stmt->execute([':thread_id' => $thread_id]);
      return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    //スレッド検索
    //ユーザーが入力した検索キーワードは、$keywordに格納されている
    public function searchThread($keyword)
    {
      //【重要】検索機能作成：LIKEと%を使用
      //SQL文：「WHERE title LIKE :title」でthreadsテーブルのtitle列からユーザーが入力した検索キーワードを検索している
      $stmt = $this->db->prepare(
      "SELECT * FROM threads WHERE title
      LIKE :title AND delflag = 0;"
      );
      //SQLとユーザーが入力したキーワードを紐づけて実行している
      //％：あいまい検索→ユーザー入力したキーワードの前後に別テキストが入っていてもいい
      $stmt->execute([
        ':title' => '%'.$keyword.'%'
      ]);
      return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

}

