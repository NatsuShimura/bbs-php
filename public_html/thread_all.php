<?php
//スレッド一覧

require_once(__DIR__ .'/header.php');
//アロー演算子の先はメソッド
//クラスを呼び出すときはバックスラッシュ
$threadMod = new Bbs\Model\Thread();
$threads = $threadMod->getThreadAll();

//echo '<pre>';
//var_dump($threads);
//echo '<pre>';
//exit();
?>
<h1 class="page__ttl">スレッド一覧</h1>

<!-- スレッド検索機能 -->
<!-- ControllerのThreadと紐付いている-->
<form action="thread_search.php" method="get" class="form-group form-search">
  <div class="form-group">
    <input type="text" name="keyword" placeholder="スレッド検索">
  </div>
  <div class="form-group">
    <input type="submit" value="検索" class="btn btn-primary">
    <input type="hidden" name="type" value="searchthread">
  </div>
</form>
<ul class="thread">
  <!-- $threadsテーブルの配列ループさせる $threadは任意名-->
  <?php foreach($threads as $thread): ?>
    <!--　カスタム属性：Threadのidには値が入る-->
    <li class="thread__item" data-threadid="<?= $thread->t_id; ?>">
      <div class="thread__head">
        <h2 class="thread__ttl">
          <!-- メソッド部分で取得データを表示するものを変えられる、title→idならidの列が取得される-->
          <?= h($thread->title); ?>
        </h2>
        <!-- active -->
        <!-- bbs.js26行目と紐付いている-->
        <div class="fav__btn<?php if(isset($thread->f_id)) { echo ' active';} ?>"><i class="fas fa-star"></i></div>
      </div>
      <ul class="thread__body">
        <?php
          //コメント取得
          $comments = $threadMod->getComment($thread->t_id);
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
      <div class="operation">
        <!-- ユーザー退会 -->
        <!-- a(アンカー)タグ：クリックするだけの場合に使用する-->
        <!-- クリックしたときにページ遷移する先を記述する -->
        <!-- SITE_URLでthread_disp.php-->
        <!-- ?thead_idに格納されたデータをthread_Disp.phpに渡す-->
        <a href="<?= SITE_URL; ?>/thread_disp.php?thread_id=<?= $thread->t_id; ?>">
        書き込み&すべて読む(<?= h($threadMod->getCommentCount($thread->t_id)); ?>)</a>
        <p class="thread__date">スレッド作成日時：<?= h($thread->created); ?></p>
      </div>
    </li>
  <?php endforeach; ?>
</ul><!-- thread -->
<?php
require_once(__DIR__ .'/footer.php');
?>