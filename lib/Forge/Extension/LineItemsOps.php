<?php declare(strict_types=1);

namespace Forge\Extension;

use Forge\Base\ArrayOps;
use Forge\Base\Either;
use Forge\Money;
use Forge\Stripe\LineItems;
use Exception;


final class LineItemsOps {

  final static function fromOrder($order): Either {
    $eCurrency     = Either::nullable($order['currency'], 'Currency.empty');
    $eShipping     = Either::nullable($order['shipping'], 'Shipping.empty');
    $eShippingName = Either::nullable($order['shipping_name'], 'ShippingName.empty');
    $eItems        = Either::nullable($order['items'], 'Items.empty');

    $eProducts = $eItems->flatMap(fn($xs) =>
      ArrayOps::traverse($xs, fn($item) =>
        ProductOps::fromItem($item)
      )
    );

    return $eCurrency->flatMap(fn($currency) =>
      $eShipping->flatMap(fn($shipping) =>
        $eShippingName->flatMap(fn($shippingName) =>
          $eProducts->map(fn($products) =>
            LineItems::apply($currency, Money::toCents($shipping), $shippingName, $products)
          )
        )
      )
    )->leftMap(fn($e) => new Exception($e));
  }

}
