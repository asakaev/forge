<?php declare(strict_types=1);

namespace Forge\Base;

use Closure;


final class ArrayOps {

  final static function updated(array $m, $k, $v): array {
    $m1 = $m;
    $m1[$k] = $v;
    return $m1;
  }

  final static function append(array $xs, $v): array {
    $ys = $xs;
    $ys[] = $v;
    return $ys;
  }

  final static function getOrElse(array $m, $k, $ifEmpty) {
    return array_key_exists($k, $m) && isset($m[$k])
      ? $m[$k]
      : $ifEmpty;
  }

  final static function map(array $xs, Closure $f): array {
    $ys = [];
    foreach ($xs as $k => $v) {
      $ys[] = $f(Product2::apply($k, $v));
    }
    return $ys;
  }

  final static function fold(array $xs, $zero, Closure $f) {
    $acc = $zero;
    foreach ($xs as $k => $v) {
      $acc = $f($acc, Product2::apply($k, $v));
    }
    return $acc;
  }

  final static function traverse(array $xs, Closure $f): Either {
    $reducer = fn(Either $acc, $x) =>
      $acc->fold(
        fn($e)  => Left::apply($e),
        fn($ys) => $f($x)->map(fn($a) => ArrayOps::append($ys, $a))
      );
    return array_reduce($xs, $reducer, Right::apply(array()));
  }

  /**
   * @param array<string> $xs
   * @param string $sep
   * @return string
   */
  final static function mkString(array $xs, string $sep): string {
    $concat = function(array $xs, string $sep): string {
      $s = $xs[0];
      $i = 1;
      while ($i < count($xs)) {
        $s = "$s$sep$xs[$i]";
        $i = $i + 1;
      }
      return $s;
    };

    return count($xs) === 0
      ? ''
      : $concat($xs, $sep);
  }

}
