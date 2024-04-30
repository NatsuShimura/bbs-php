<?php
//例外発生時にエラーメッセージを記載する

namespace Bbs\Exception;

class Deleteuser extends \Exception {
  protected $message = '既に退会済みのユーザーです！';
}