<?php declare(strict_types=1);

namespace Forge\Base;


final class Product2 {
  private $_1;
  private $_2;

  final function _1() {
    return $this->_1;
  }

  final function _2() {
    return $this->_2;
  }

  private function __construct($_1, $_2) {
    $this->_1 = $_1;
    $this->_2 = $_2;
  }

  final static function of($_1, $_2): Product2 {
    return new Product2($_1, $_2);
  }
}
