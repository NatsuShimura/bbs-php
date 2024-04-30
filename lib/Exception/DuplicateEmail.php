<?php

namespace Bbs\Exception;

//DuplicateEmailクラスにExceptionファイルを継承
class DuplicateEmail extends \Exception {
  //変数$messageにエラーメッセージを代入
  protected $message = '既にメールアドレスが登録済みです!';
}