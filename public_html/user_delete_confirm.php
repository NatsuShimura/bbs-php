<?php
//ユーザー情報を表示させて、ユーザーに最終確認を促している
require_once(__DIR__.'/header.php');
$app = new Bbs\controller\UserDelete();
$app->run();
?>
<h1 class="page__ttl">ユーザー退会</h1>
<p class="user-disp">以下のユーザーを退会します。実行する場合は「退会」を押してください。</p>
<div class="container">
  <div class="form-group">
    <!-- 三項演算子 条件：emailがあったらtrue-->
    <!-- true：emailを表示-->
    <!-- false：表示なし-->
    <!--【重要】 $this->setValues('email', $userData->email);と紐付いている-->
    <p>メールアドレス：<?= isset($app->getValues()->email) ? h($app->getValues()->email):''; ?></p>
  </div>
  <div class="form-group">
    <p>ユーザー名：<?= isset($app->getValues()->username) ? h($app->getValues()->username):''; ?></p>
  </div>
  <form class="user-delete user-confirm" action="user_delete_done.php" method="post">
    <!--history.back：前のページへ戻る-->
    <!-- 入力したブラウザの履歴を基に前の画面に戻る-->
    <a class="btn btn-primary" href="javascript:history.back();">まだしません。</a>
    <input type="submit" class="btn btn-primary" value="退会">
    <input type="hidden" name="token" value="<?= h($_SESSION['token']); ?>">
    <input type="hidden" name="type" value="delete">
  </form>
</div><!--container -->
<?php
require_once(__DIR__ .'/footer.php');
?>
