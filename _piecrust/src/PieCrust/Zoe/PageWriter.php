<?php
namespace PieCrust\Zoe;

require_once 'sfYaml/lib/sfYamlDumper.php';

class PageWriter {
  protected $piecrust;
  protected $data;
  public function __construct($piecrust,$data=null){
    $this->piecrust = $piecrust;
    $this->data = $data===null ? json_decode(file_get_contents('php://input'),true) : $data;
  }
  public function write(){
    $yaml = new \sfYamlDumper();
    $header = "---\n".$yaml->dump($this->data['page'], 3)."---\n";
    $body = '';
    foreach($this->data['segments'] as $name=>$segment){
      foreach($segment as $piece){
        $body .= "---$name";
        if(!empty($piece['format'])) $body .= ":$piece[format]";
        $body .= "---\n$piece[content]\n";
      }
    }
    die("$header$body");
  }
}