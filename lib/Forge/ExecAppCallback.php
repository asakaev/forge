<?php declare(strict_types=1);

namespace Forge;

use Closure;
use Forge\Base\Either;


abstract class ExecAppCallback {
  abstract function apply(array $transaction): Either;

  final static function fromFunction(Closure $f): ExecAppCallback {
    return new ExecAppCallback_($f);
  }
}

final class ExecAppCallback_ extends ExecAppCallback {
  private Closure $f;

  function __construct(Closure $f) {
    $this->f = $f;
  }

  final function apply(array $transaction): Either {
    $f = $this->f;
    return $f($transaction);
  }
}
