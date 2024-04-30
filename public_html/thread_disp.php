<?php
//スレッド詳細表示

require_once(__DIR__ .'/header.php');

$threadCon = new Bbs\Controller\Thread();
$threadCon->run();
//thread_all.phpから渡ってきたクエリパラメーターを取得するために$_GETを使用
$thread_id = $_GET['thread_id'];

//var_dump($thread_id);
//exit();

//Model側のThreadをインスタンス化
$threadMod = new Bbs\Model\Thread();
//Model側のThreadクラスのgetThreadメソッド引数にクエリパラメータを指定
//threadDispにクエリパラメータに紐づくthreadテーブルのデータが１件入っている
$threadDisp = $threadMod->getThread($thread_id);

//echo '<pre>';
//var_dump($threadDisp);
//exit;
//echo '<pre>';

?>


<!-- スレッド詳細 -->
<h1 class="page__ttl">スレッド詳細</h1>
<div class="thread">
  <div class="thread__item">
    <div class="thread__head">
      <h2 class="thread__ttl">
        <?= h($threadDisp->title); ?>
      </h2>
      <!-- CSV -->
      <form id="csvoutput" method="post" action="thread_csv.php">
        <button class="btn btn-primary" onclick="document.getElementById('csvoutput').submit();">CSV出力</button>
        <!-- 自動で送信される -->
        <input type="hidden" name="thread_id" value="<?= h($thread_id); ?>">
        <input type="hidden" name="token" value="<?= h($_SESSION['token']); ?>">
        <input type="hidden" name="type" value="outputcsv">
      </form>
      <!-- end CSV -->
    </div>
    <ul class="thread__body">

    <?php
    //取得してきた全コメントをループさせて反映する
    //getCommentAll：全コメントの情報を出力する
      $comments = $threadMod->getCommentAll($threadDisp->id);
      foreach($comments as $comment):
    ?>

      <li class="comment__item">
        <div class="comment__item__head">
          <span class="comment__item__num"><?= h($comment->comment_num); ?></span>
          <span class="comment__item__name">名前：<?= h($comment->username); ?></span>
          <span class="comment__item__date">投稿日時：<?= h($comment->created); ?></span>
        </div>
        <p class="comment__item__content"><?= h($comment->content); ?></p>
    <?php endforeach; ?>

      </li>
    </ul>

    <!-- POST送信 -->
    <form action="" method="post" class="form-group">
      <div class="form-group">
        <label>コメント</label>
        <!-- name属性をControllerの条件式に渡している-->
        <textarea type="text" name="content" class="form-control">
          <?= isset($threadCon->getValues()->content) ? h($threadCon->getValues()->content) : ''; ?></textarea>
        <p class="err"><?= h($threadCon->getErrors('content')); ?></p>
      </div>
      <div class="form-group">
        <input type="submit" value="書き込み" class="btn btn-primary">
      </div>
      <input type="hidden" name="thread_id" value="<?= h($thread_id); ?>">

      <!-- 【重要】createcommentはControllerの条件式と紐付いている-->
      <input type="hidden" name="type" value="createcomment">
        <!-- セッションハイジャック対策を$_SESSION['token']（ログインしてるユーザー）でしている-->
      <input type="hidden" name="token" value="<?= h($_SESSION['token']); ?>">
    </form>
    <p class="comment-page thread__date">スレッド作成日時：<?= h($threadDisp->created); ?></p>
  </div>
</div><!-- thread -->
<?php require_once(__DIR__ .'/footer.php'); ?>