<?php
class Profiler {
  private $title;
  private $start_time = 0;
  private $result;

  private function now() {
    return microtime(true);
  }

  public function start($title) {
    $this->result = array();
    $this->title = $title;
    $this->start_time = $this->now();
    $this->lap_time = $this->start_time;
  }

  public function lap($quit_process_name) {
    array_push($this->result, array(
      'time' => $this->now() - $this->lap_time,
      'name' => $quit_process_name));
    $this->lap_time = microtime(true);
  }

  public function getFormattedResult() {
    $whole_time = $this->now() - $this->start_time;
    $result = '';
    $result .= 'Profile('.$this->title.') : '.$whole_time.'(sec)'.PHP_EOL;
    foreach($this->result as $item) {
      $result .= '    '.$item['name'].' : '.$item['time'].'(sec)'.PHP_EOL;
    }
    return $result;
  }
}