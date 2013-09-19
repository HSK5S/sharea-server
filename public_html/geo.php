<?php
  class Town {
    private $lat;
    private $lng;
    private $area;

    public function latlng($lat, $lng){
      $this->lat = $lat;
      $this->lng = $lng;
      return $this->city();
    }

    public function city(){
      $url = "http://reverse.search.olp.yahooapis.jp/OpenLocalPlatform/V1/reverseGeoCoder?lat=".$this->lat."&lon=".$this->lng."&appid=dj0zaiZpPU5hZ0NVTWEzMW9nZSZzPWNvbnN1bWVyc2VjcmV0Jng9MmU-&output=json";
      $json = file_get_contents($url);
      $data = json_decode($json, true);
      $addressElements = $data['Feature'][0]['Property']['AddressElement'];
      if($addressElements == NULL) {
        # On the sea
        return '海';
      }
      $area = $addressElements[2]['Name'];
      if($area == NULL) {
        # Over sea
        return '海';
      } else if(mb_strlen($area, 'UTF-8') == 0) {
        # Local area
        return $addressElements[1]['Name'];
      }
      # Urban area
      return $area;
    }
  }
