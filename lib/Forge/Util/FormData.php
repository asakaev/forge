<?php declare(strict_types=1);

namespace Forge\Util;

use Forge\Base\ArrayOps;
use Forge\Base\Product2;


final class FormData {

  private array $value;

  final function value(): array {
    return $this->value;
  }

  final function combine(FormData $that): FormData {
    return FormData::of(array_merge($this->value, $that->value()));
  }

  final function encode(): string {
    $xs = ArrayOps::map($this->value, fn(Product2 $tuple) => "{$tuple->_1()}={$tuple->_2()}");
    return ArrayOps::mkString($xs, '&');
  }

  final static function empty(): FormData {
    return new FormData([]);
  }

  private function __construct(array $value) {
    $this->value = $value;
  }

  final static function of(array $value): FormData {
    return new FormData($value);
  }

}
