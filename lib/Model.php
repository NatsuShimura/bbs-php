<?php
//継承されている

namespace Bbs;

class Model {
  protected $db;
  public function __construct(){
    // Modelクラス及び子クラスのインスタンスを生成した際には、必ずPDOクラスのインスタンスを生成する
    //DB接続をしている
    try {
      $this->db = new \PDO(DSN, DB_USERNAME, DB_PASSWORD);
    } catch (\PDOException $e) {
      echo $e->getMessage();
      exit;
    }
  }
}
