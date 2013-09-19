<?php
  require_once 'geo.php';
  require_once 'name.php';
  require_once 'select.php';
  require_once 'profiler.php';
  require_once '../config/config.php';

  # 必要なプロパティが送られているか確認
  function isPropertySatisfied($property) {
    return (
      isset($property['message']) &&
      isset($property['lat']) &&
      isset($property['lng']) &&
      isset($property['date']));
  }

  # 時刻を送信された形式からDBに保存する形式に変換
  function convertDatetimeStr($date_str) {
    try {
      $date_str = substr($date_str, 0, strlen($date_str) - 4);
      $date = new DateTime($date_str);
      $result = $date->format('Y-m-d H:i:s');
    } catch(Exception $e) {
      throw new Exception("Invalid date or time format.");
    }
    return $result;
  }

  # _POSTから連想配列を生成
  function parseProperty($source) {
    $lat = (double)$source['lat'];
    $lng = (double)$source['lng'];

    # 文字数の確認
    $message_max_length = 99;
    if(mb_strlen($source['message'], "UTF-8") > $message_max_length) {
      throw new Exception('Message is too long.');
    }

    # 緯度経度が定義の範囲内にあるか
    if($lat < -90 || 90 < $lat || $lng < -180 || 180 < $lng) {
      throw new Exception("Invalid latitude or longitude ($lat, $lng).");
    }

    return array(
      'message' => $source['message'],
      'lat' => $lat,
      'lng' => $lng,
      'date' => convertDatetimeStr($source['date']));
  }

  # DBに保存
  function save($name, $message, $date, $location) {
    global $db_host, $db_user, $db_pass, $db_dbname;
    $pdo = new PDO("mysql:dbname=$db_dbname;host=$db_host", $db_user, $db_pass);
    $query = $pdo->prepare("INSERT INTO posts_no (name, message, date, city) VALUES (:name, :message, :date, :city)");
    $query->bindParam(":name", $name, PDO::PARAM_STR);
    $query->bindParam(":message", $message, PDO::PARAM_STR);
    $query->bindParam(":date", $date, PDO::PARAM_STR);
    $query->bindParam(":city", $location, PDO::PARAM_STR);
    $query->execute();

    # Error check for database
    $pdo_info = $pdo->errorInfo();
    if($pdo_info[0] != "00000") {
      throw new Exception("Can not access database.({$pdo_info[2]})");
    }

    # Error check for query
    $query_info = $query->errorInfo();
    if($query_info[0] != "00000") {
      throw new Exception("Can not save.({$query_info[2]})");
    }
  }

  # one, anotherの一方がNULLだった場合、もう片方と同じ値を代入する
  function complement(&$one, &$another) {
    if($one === NULL && $another === NULL) {
      return false;
    } else if($one === NULL) {
      $one = $another;
    } else if($another === NULL) {
      $another = $one;
    }
    return true;
  }

  # 近隣ランドマークを取得
  function getLandmarksAround($lat, $lng) {
    $name_generator = new Name();
    $result = $name_generator->createName($lat, $lng);
    if($result === NULL) {
      throw new Exception("Error occured while getting the landmarks.");
    }
    return $result;
  }

  # 位置名称を取得
  function getLocationName($lat, $lng) {
    $geo_api = new Town();
    return $geo_api->latlng($lat, $lng);
  }

  # 名前を生成
  function generateName($landmarks, $location) {
    $name_generator = new Select();
    $landmark = $name_generator->selectname($landmarks);
    # 片方が正常に取得できている場合補完する。
    if(!complement($landmark, $location)) {
      throw new Exception("Can't generate your name.");
    }
    return $name_generator->bondname($landmark);
  }

  # Main
  if(isPropertySatisfied($_POST)) {
    # Post
    try {
      $profiler = new Profiler();
      $profiler->start('post');
      $property = parseProperty($_POST);
      $profiler->lap("parse property");
      $landmarks = getLandmarksAround($property['lat'], $property['lng']);
      $profiler->lap("get landmark");
      $location = getLocationName($property['lat'], $property['lng']);
      $profiler->lap("get locaiton name");
      $name = generateName($landmarks, $location);
      $profiler->lap("generate name");
      save($name, $property['message'], $property['date'], $location);
      $profiler->lap("save message");
      echo 'Success.';
      error_log('Post: ('.$property['lat'].', '.$property['lat'].') '.$location.' '.$name.' '.$property['message']);
      error_log($profiler->getFormattedResult());
    } catch(Exception $e) {
      exit("Post error: ({$e->getMessage()}).");
    }
  } else {
    echo('Lack of properties.');
  }
