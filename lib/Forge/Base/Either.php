<?php declare(strict_types=1);

namespace Forge\Base;

use Closure;
use Exception;


abstract class Either {
  abstract function map(Closure $f): Either;
  abstract function flatMap(Closure $f): Either;
  abstract function fold(Closure $fa, Closure $fb);
  abstract function leftMap(Closure $f): Either;

  final static function cond($test, $right, $left): Either {
    return $test ? Right::of($right) : Left::of($left);
  }

  final static function nullable($a, $ifEmpty): Either {
    return $a === null ? Left::of($ifEmpty) : Right::of($a);
  }

  final static function attempt(Closure $block): Either {
    try {
      return Right::of($block(null));
    } catch (Exception $e) {
      return Left::of($e);
    }
  }
}

final class Left extends Either {
  private $a;

  final function map(Closure $f): Left {
    return $this;
  }

  final function flatMap(Closure $f): Left {
    return $this;
  }

  final function fold(Closure $fa, Closure $fb) {
    return $fa($this->a);
  }

  final function leftMap(Closure $f): Left {
    return Left::of($f($this->a));
  }

  private function __construct($a) {
    $this->a = $a;
  }

  final static function of($a): Left {
    return new Left($a);
  }

}

final class Right extends Either {
  private $b;

  final function map(Closure $f): Right {
    return Right::of($f($this->b));
  }

  final function flatMap(Closure $f): Either {
    return $f($this->b);
  }

  final function fold(Closure $fa, Closure $fb) {
    return $fb($this->b);
  }

  final function leftMap(Closure $f): Right {
    return $this;
  }

  private function __construct($b) {
    $this->b = $b;
  }

  final static function of($b): Right {
    return new Right($b);
  }
}
