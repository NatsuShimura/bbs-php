<!-- thread_search.php -->

<?php
$threads != '' ? $con = count($threads) : $con = 0; ?>
<?php if (($threadcon->geterrors('keyword'))): ?>
<?php else : ?>
<div>キーワード：<?=$_GET['keyword']; ?> 該当件数:<?= $con; ?>件</div>
