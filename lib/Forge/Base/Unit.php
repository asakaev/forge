<?php declare(strict_types=1);

namespace Forge\Base;


final class Unit {
  private static $instance;

  private function __construct() {}
  private function __clone() {}

  final static function apply(): Unit {
    if (self::$instance === null) {
      self::$instance = new Unit();
    }
    return self::$instance;
  }
}
