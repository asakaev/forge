<?php declare(strict_types=1);

namespace Forge;

use Closure;
use Forge\Base\Either;


abstract class SaveTransaction {
  abstract function apply(array $transaction): Either;

  final static function fromFunction(Closure $f): SaveTransaction {
    return new SaveTransaction_($f);
  }
}

final class SaveTransaction_ extends SaveTransaction {
  private Closure $f;

  function __construct(Closure $f) {
    $this->f = $f;
  }

  final function apply(array $transaction): Either {
    $f = $this->f;
    return $f($transaction);
  }
}
