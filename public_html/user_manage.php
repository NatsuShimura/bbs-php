<?php
require_once(__DIR__ . '/header.php');

?>


<div class="wrap">
  <h1 class="page__ttl">ユーザーテーブル管理画面</h1>
  <form action="" method="post">
    <div style="margin-bottom: 50px;">
      <input type="submit" name="create" value="新規登録画面へ" class="btn btn-primary">
      <p>更新または削除を行うユーザーを選択してください。</p>
      <table class="table">
        <tbody>
          <tr>
            <th></th>
            <th>id</th>
            <th>ユーザー名</th>
            <th>メールアドレス</th>
            <th>ユーザー画像</th>
            <th>権限</th>
            <th>削除フラグ</th>
          </tr>
          <?php foreach ($users as $user) : ?>
            <tr>
              <td>
                <input type="radio" name="id" value="<? h($user->id); ?>">
              </td>
              <td>
                <? h($user->id); ?>
              </td>
              <input type="text" name="username<?= $user->id; ?>" value="<?= h($user->username); ?>">
              </td>
              <td>
                <input type="text" name="email" <?= $user->id; ?> value="<? h($user->email); ?>">
              </td>
              <td>
                <input type="text" name="image<?= $user->id; ?>" value="<?= h($user->image); ?>">
              </td>
              <td>
                <input type="text" name="authority<?= $user->id; ?>" value="<?= h($user->authority); ?>">
              </td>
              <td>
                <input type="text" name="delflag<?= $user->id; ?>" value="<? h($user->delflag); ?>">
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="err"><?= h($adminCon->geterrors8('id')); ?></p>
      <input type="submit" name="update" value="更新" class="btn-primary">
      <input type="submit" name="token" value="<?= h($_SESSION['token']); ?>">
  </form>
</div>
<?php
require_once(__DIR__."/footer.php");
?>