<?php declare(strict_types=1);

namespace Forge;

use Exception;
use Forge\Base\Either;


final class Shipping {
  private int $priceCents;
  private string $carrier;

  final function priceCents(): int {
    return $this->priceCents;
  }

  final function carrier(): string {
    return $this->carrier;
  }

  final static function fromOrder($order): Either {
    $eShipping     = Either::nullable($order['shipping'], 'Shipping.empty');
    $eShippingName = Either::nullable($order['shipping_name'], 'ShippingName.empty');

    return $eShipping->flatMap(fn($shipping) =>
      $eShippingName->map(fn($shippingName) =>
        Shipping::of(Money::toCents($shipping), $shippingName)
      )
    )->leftMap(fn($e) => new Exception($e));
  }

  private function __construct(int $priceCents, string $carrier) {
    $this->priceCents = $priceCents;
    $this->carrier = $carrier;
  }

  final static function of(int $priceCents, string $carrier): Shipping {
    return new Shipping($priceCents, $carrier);
  }
}
