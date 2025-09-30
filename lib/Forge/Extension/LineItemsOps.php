<?php declare(strict_types=1);

namespace Forge\Extension;

use Forge\Base\ArrayOps;
use Forge\Base\Either;
use Forge\Stripe\LineItems;
use Exception;


final class LineItemsOps {

  final static function fromOrder($order): Either {
    $eCurrency     = Either::nullable($order['currency'], 'Currency.empty');
    $eItems        = Either::nullable($order['items'], 'Items.empty');

    $eProducts = $eItems->flatMap(fn($xs) =>
      ArrayOps::traverse($xs, fn($item) =>
        ProductOps::fromItem($item)
      )
    );

    return $eCurrency->flatMap(fn($currency) =>
      $eProducts->map(fn($products) =>
        LineItems::of($currency, $products)
      )
    )->leftMap(fn($e) => new Exception($e));
  }

}
