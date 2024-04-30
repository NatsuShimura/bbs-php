<?php

//var_dump('$_GET');
//exit;

require_once(__DIR__ .'/header.php');
$threadCon = new Bbs\Controller\Thread();
$threadMod = new Bbs\Model\Thread();
$threads =$threadCon->run();
?>
<h1 class="page__ttl">スレッド名検索</h1>
<form action="" method="get" class="form-group form-search">
  <div class="form-group">
    <input type="text" name="keyword" value="<?= isset($threadCon->getValues()->keyword) ? h($threadCon->getValues()->keyword): ''; ?>" placeholder="スレッド検索">
    <p class="err"><?= h($threadCon->getErrors('keyword')); ?></p>
  </div>
  <div class="form-group">
    <input type="submit" value="検索" class="btn btn-primary">
    <!--　Controllerのelseifの条件式-->
    <input type="hidden" name="type" value="searchthread">
  </div>
</form>

<!--　【重要】 三項演算子：if文を一行で記述している-->
<!-- 条件式　? 条件がtrueの場合の処理 ： 条件がfalseの場合の処理 ; -->
<!-- 条件：スレッドの検索結果がある場合はtrue-->
<!-- ・trueの場合：count関数を使用して$conに$threadsの数を格納-->
<!-- ・falseの場合：$conの中に0を格納-->
<?php $threads != '' ? $con = count($threads) : $con = 0; ?>
<?php if (($threadCon->getErrors('keyword'))): ?>
<?php else : ?>
  <!-- Thread.phpで入力されたキーワード、$_GETに格納されている-->
  <!-- 変数$conを使用 -->
<div>キーワード：<?= $_GET['keyword']; ?>　　該当件数：<?= $con; ?>件</div>
<?php endif; ?>
<ul class="thread">
<?php if ($con > 0): ?>
  <?php foreach($threads as $thread): ?>
    <li class="thread__item">
      <div class="thread__head">
        <h2 class="thread__ttl">
          <?= h($thread->title); ?>
        </h2>
      </div>
      <ul class="thread__body">
        <?php
          $comments = $threadMod->getComment($thread->id);
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
        <a href="<?= SITE_URL; ?>/thread_disp.php?thread_id=<?= $thread->id; ?>">書き込み&すべて読む(<?= h($threadMod->getCommentCount($thread->id)); ?>)</a>
        <p class="thread__date">スレッド作成日時：<?= h($thread->created); ?>
        </p>
      </div>
    </li>
  <?php endforeach?>
<?php else: ?>
  <p>キーワードに該当するスレッドが見つかりませんでした。</p>
<?php endif;?>
</ul><!-- thread -->
<?php
require_once(__DIR__ .'/footer.php');
?>