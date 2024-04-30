<?php

namespace Bbs\Controller;


//ViewからControllerに渡ったデータは$_POSTに格納
class Thread extends \Bbs\Controller {

  public function run() {

    //条件：thread_create.phpからthread_disp.phpからPOST送信されてきたらtrue
    if($_SERVER['REQUEST_METHOD'] === 'POST') {

      //var_dump($_POST['type']);
      //exit();
      //$_POST['name属性']　=== 'name属性の値（name="createthread"）'
      if ($_POST['type']  === 'createthread') {
        $this->createThread();
      } elseif($_POST['type']  === 'createcomment') {
        $this->createComment();
      }

      //thread_search.phpからGET送信かつ、「name属性type === searchthread」が送られてきたらtrue
      //厳密にはthread_search.phpでデータを渡していないが$_GETを使用するとURLのクエリパラメータのデータを好きに取得できる
      //isset関数でthread_create.phpのname属性のtypeを設定する
    } elseif($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['type']) === 'searchthread') {

      $threadData = $this->searchThread();
      return $threadData;
    }
  }


  private function createThread(){
    //バリデーション: エラーがなければスレッド一覧画面に遷移
    try {
      //private function validate()のthrowされたものをエラートラップでキャッチしている
      //validateメソッドでバリデーションチェック
      $this->validate();
      //空だったらエラー
    } catch (\Bbs\Exception\EmptyPost $e) {
        $this->setErrors('create_thread', $e->getMessage());
        //CherLength：文字制限
    } catch (\Bbs\Exception\CharLength $e) {
        $this->setErrors('create_thread', $e->getMessage());
    }
    //引数をsetでワンクッション置いてスレッド名とコメントを受け取る
    $this->setValues('thread_name', $_POST['thread_name']);
    $this->setValues('comment', $_POST['comment']);
    if ($this->hasError()){
      return;
    } else {

      //var_dump($_POST);
      //exit();

      //ユーザーの入力した内容、$_POSTに格納されている
      //Model中のThreadクラスと紐付いている
      $threadModel = new \Bbs\Model\Thread();

        //ControllerからModelにデータを渡している
        //連想配列で記述
        //ユーザーが入力した「thread_name」にtitle、「comment」にcomment
        //名前のところは変数であるため、任意名がつけられる！
        //$_POST['thread_name']と$_POST['comment']はユーザー入力した部分

        //var_dump($_SESSION['me']);
        //exit();
        $threadModel->createThread([
        'title' => $_POST['thread_name'],
        'comment' => $_POST['comment'],
        //「sessionのid」にuser_idと名前をつける(Login.phpのログインしているユーザー情報全部入っている)
        'user_id' => $_SESSION['me']->id
      ]);
      header('Location: '. SITE_URL . '/thread_all.php');
      exit();
    }
  }

  //コメント登録
  //Thread_disp.phpから渡ってきたコメントのデータを$_POSTに格納
  private function createComment() {
    try {
        $this->validate();
      } catch (\Bbs\Exception\EmptyPost $e) {
          $this->setErrors('content', $e->getMessage());
      } catch (\Bbs\Exception\CharLength $e) {
          $this->setErrors('content', $e->getMessage());
      }
      $this->setValues('content', $_POST['content']);
      if ($this->hasError()) {
        return;
      } else {
        //Modelをインスタンス化
          $threadModel = new \Bbs\Model\Thread();
          $threadModel->createComment([
            //引数を使ってデータをModelに渡している
            //理由：HTMLのタグは書けないから
            'thread_id' => $_POST['thread_id'],
            'user_id' => $_SESSION['me']->id,
            'content' => $_POST['content']
          ]);
      }
      //headerメソッド：ページ遷移する
      //コメントの書き込みをするとリロードしてブラウザに追加して反映される
      header('Location: '. SITE_URL . '/thread_disp.php?thread_id=' . $_POST['thread_id']);
      exit();
  }


//CSV
//thread_csv.phpから渡ってきたデータ$thread_idに格納
//データ：CSV出力したいスレッドid

  public function outputCsv($thread_id){
    try {
      $threadModel = new \Bbs\Model\Thread();
      $data = $threadModel->getCommentCsv($thread_id);
      $csv=array('num','username','content','date');
      $csv=mb_convert_encoding($csv,'SJIS-WIN','UTF-8');
      $date = date("YmdH:i:s");
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename='. $date .'_thread.csv');
      $stream = fopen('php://output', 'w');
      stream_filter_prepend($stream,'convert.iconv.utf-8/cp932');
      $i = 0;
      foreach ($data as $row) {
        if($i === 0) {
          fputcsv($stream , $csv);
        }
        fputcsv($stream , $row);
        $i++;
      }
    } catch(Exception $e) {
      echo $e->getMessage();
    }
  }

  //バリデーションの処理：条件式
  private function validate() {
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
      echo "不正なトークンです!";
      exit();
    }
    //$_POST['name属性'] === 'name属性の値(value)'
    if ($_POST['type'] === 'createthread') {
      if (!isset($_POST['thread_name']) || !isset($_POST['comment'])){
        echo '不正な投稿です';
        exit();
      }
      if ($_POST['thread_name'] === '' || $_POST['comment'] === ''){
        throw new \Bbs\Exception\EmptyPost("スレッド名または最初のコメントが入力されていません！");
      }
      //mb_strlen：文字数をカウントするメソッド
      if (mb_strlen($_POST['thread_name']) > 20) {
        throw new \Bbs\Exception\CharLength("スレッド名が長すぎます！");
      }
      if (mb_strlen($_POST['comment']) > 200) {
        throw new \Bbs\Exception\CharLength("コメントが長すぎます！");
      }
    }elseif($_POST['type'] === 'createcomment') {
      if (!isset($_POST['content'])){
        echo '不正な投稿です';
        exit();
      }
      if ($_POST['content'] === '') {
        throw new \Bbs\Exception\EmptyPost("コメントが入力されていません！");
      }
    }
  }

  //検索スレッドの処理
  public function searchThread(){
    try {
      $this->validateSearch();
    } catch (\Bbs\Exception\EmptyPost $e) {
      $this->setErrors('keyword', $e->getMessage());
    } catch (\Bbs\Exception\CharLength $e) {
      $this->setErrors('keyword', $e->getMessage());
    }

    //ユーザーが入力した検索キーワードを変数$keywordに格納
    $keyword = $_GET['keyword'];
    $this->setValues('keyword', $keyword);
    if ($this->hasError()) {
      return;
    } else {
      $threadModel = new \Bbs\Model\Thread();
       //ModelのThreadクラスのsearchThreadメソッドを実行
      //引数にユーザーが入力したキーワードを付与している
      //searchThreadの実行結果を$keywordに格納
      //returnは実行されたメソッド外部へデータを渡す
      //$theadDataで外部へ使用するためにreturnしている
      $threadData = $threadModel->searchThread($keyword);
      return $threadData;
    }
  }

  //validateSearchメソッドの追加
  //GET送信のバリデーション
  //条件：未入力だった場合、２０文字以上だった場合
  private function validateSearch() {
    if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['type'])) {
      //ユーザーが入力した検索ワードが空だったら
      if ($_GET['keyword'] === ''){
        throw new \Bbs\Exception\EmptyPost("検索キーワードが入力されていません！");
      }
      //mb_strlen関数：テキストの文字を計算している
      if (mb_strlen($_GET['keyword']) > 20) {
        throw new \Bbs\Exception\CharLength("キーワードが長すぎます！");
      }
    }
  }
}


