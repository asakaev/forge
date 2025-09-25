<?php declare(strict_types=1);

namespace Forge;

use Exception;
use Forge\Base\Either;


final class Tax {
  private int $value;

  final function value(): int {
    return $this->value;
  }

  final static function fromOrder($order): Either {
    return Either::nullable($order['tax'], new Exception('Tax.empty'))->map(fn($tax) =>
      Tax::apply(Money::toCents($tax))
    );
  }

  private function __construct(int $value) {
    $this->value = $value;
  }

  final static function apply(int $value): Tax {
    return new Tax($value);
  }
}
