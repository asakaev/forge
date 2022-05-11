<?php declare(strict_types=1);

namespace Forge;


final class Money {

  static function toCents(float $d): int {
    return (int)($d * 100);
  }

  static function fromCents(int $i): float {
    return $i / 100.00;
  }

}
