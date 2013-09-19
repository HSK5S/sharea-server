<?php

  require_once('geo.php');
  require_once('name.php');
  require_once('select.php');
  require_once('profiler.php');
  require_once('../config/config.php');

  $lat = $_GET['lat'];//35.6641222;
  $lng = $_GET['lng'];//139.729426;
  $id = $_GET['id'];
  $debug = $_GET['debug'];
  $town = new Town();
  $c_name = new Name();

  if($_SERVER['SERVER_ADDR'] === '210.149.64.198') {
 		$fac_select = new Select();
	}
	else{
		$fac_select = "roppongi";
	}

  $profiler = new Profiler();
  $profiler->start('post');


  $place = $town->latlng($lat,$lng);

  //データベース接続用変数とクライアント側からの入力に対するエラー処理
  if(!isset($lat) || !isset($lng) || !isset($id)){
    echo "Does not have a value of the lat or leg or id.";
    exit();
  }
  if(!isset($place)){
      $facility_all = $c_name->createName($lat,$lng);
      $place = $fac_select->selectname($facility_all);
    if(!isset($place)){
      echo "Place names are not getting.";
      exit();
    }
  }
  if(!isset($db_dbname) || !isset($db_host) || !isset($db_user) || !isset($db_pass)){
    echo "There is no DB data set.";
    exit();
  }

  //データベースからの呼び出しと格納
  $msg_info = array();
  $msg_info_array = array();

  $profiler->lap("in city");    
  try{
    $pdo = new PDO("mysql:dbname=$db_dbname; host=$db_host", $db_user, $db_pass);
  }
  catch(PDOException $Exception){
  	echo "Can not connect DB";
  	exit();
  }
  $profiler->lap("acsess db");
  
  if(!$st = $pdo->query("SELECT * FROM posts_no WHERE city='$place' AND num>$id ORDER BY num desc limit 50")){
		echo "SQL Syntax error";
		exit();
	}
  $profiler->lap("SQL load");
	
  while($row = $st->fetch()){
    $id = htmlspecialchars($row['num']);
    $name = htmlspecialchars($row['name']);
    $message = htmlspecialchars($row['message']);
    $date = htmlspecialchars($row['date']);

    $msg_info = array("id"=>$id,"name"=>$name,"message"=>$message,"date"=>$date);
    array_push($msg_info_array, $msg_info);
  }
  $profiler->lap("in array");
  error_log('read');
  error_log($profiler->getFormattedResult());

  //デバック出力用
  if(isset($debug) && $debug){
    $result = array('area'=>$place, 'post'=>$msg_info_array );
    printf("<h1>現在の場所は%sです。</h1><br><br><hr>",$result['area']);
    foreach ($result['post'] as $fruit) {
        printf("<h4>%s</h4><br>%s<br><h6>%s</h6>",$fruit['name'],$fruit['message'],$fruit['date']);
        echo "<hr>";
        $i++;
    }
  }
  else{
    //返り値
    $msg_info_sort = array_reverse($msg_info_array);
    $result = array('area'=>$place, 'post'=>$msg_info_sort );
    echo json_encode($result);
  }