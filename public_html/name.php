<?php
  class Name {
    private $lat;
    private $lng;

    public function createName($lat, $lng){
      $this->lat = $lat;
      $this->lng = $lng;
      return $this->create();
    }

    public function create(){
      $url = "http://placeinfo.olp.yahooapis.jp/V1/get?lat=".$this->lat."&lon=".$this->lng."&appid=dj0zaiZpPU5hZ0NVTWEzMW9nZSZzPWNvbnN1bWVyc2VjcmV0Jng9MmU-&output=json";
      $json = file_get_contents($url);
      $data = json_decode($json, true);
      $data_a = array();
      for ($i = 0; $i < count($data['ResultSet']['Result']); $i++) {
        $data_a[$i] = $data['ResultSet']['Result'][$i]['Name'];
      }
      return $data_a;
    }
  }
