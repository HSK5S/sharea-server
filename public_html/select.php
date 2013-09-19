<?php
  require_once('../config/config.php');

  class Select {
    private $letter = 10;
    private $datan;
    private $namel = array();
    private $count = 0;
    private $datab;

    public function selectname($datan) {
      $this->datan = $datan;
      return $this->judgment();
    }

    // 周辺施設の中から 1 つを選ぶ
    public function judgment() {
      for ($i = 0; $i < count($this->datan); $i++){
        $length = mb_strlen($this->datan[$i],'UTF-8');
        if($length <= $this->letter){
          $namel[$this->count] = $this->datan[$i];
          $this->count++;
        }
      }
      // $namel=NULLの場合、そのままNULLでreturn
      // それ以外のとき、$namelの配列の中から1つ選択
      $result = empty($namel);
      if ($result) {
        return $namel;
      }else {
        $nameone = $namel[rand(0, count($namel) - 1)];
        return $nameone;
      }
    }

    public function bondname($datab) {
      $this->datab = $datab;
      return $this->bond();
    }

    // [建物名]+の+[DBからランダムに選んだ名詞] を作成
    public function bond() {
      global $db_host, $db_user, $db_pass, $db_dbname;
      if(!$pdo = new PDO("mysql:dbname=$db_dbname;host=$db_host","$db_user","$db_pass")){
        throw new Exception("Cannot connect DB.");
      }else {
        // データベースのデータの個数を取り出す
        $que = $pdo->query("SELECT count(*) FROM nouns");
        $n = $que->fetch();
        $count = (int) $n[0];
        // 1~[データの個数分] からランダムに数字を1つ選ぶ
        $num = rand(1,$count);
        // ランダムで生成した数字と同じidのnounを取り出す。
        $quen = $pdo->query("SELECT noun FROM nouns WHERE id = $num");
        $m = $quen->fetch();
        $randname = ($this->datab)."の".$m[0];
        return $randname;
      }
    }
        public function namenull($datan) {
      $this->datan = $datan;
      return $this->selectnull();
    }

    // 現在地の表示の地名が NULL のときの周辺施設の中から 1 つを選ぶ
    // 例: 東京ディズニーランド にいるとき
    public function selectnull() {
      for ($i = 0; $i < count($this->datan); $i++){
        $length = mb_strlen($this->datan[$i],'UTF-8');
        if($length <= $this->letter){
          $namel[$this->count] = $this->datan[$i];
          $this->count++;
        }
      }
      // $namel=NULLの場合、そのままNULLでreturn
      // それ以外のとき、$namelの配列の中から1番目を選択
      $result = empty($namel);
      if ($result) {
        return $namel;
      }else {
        $nameone = $namel[0];
        return $nameone;
      }
    }
  }