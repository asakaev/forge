<?php declare(strict_types=1);

namespace Forge\Extension;

use Forge\Base\ArrayOps;
use Forge\Base\Either;
use Forge\Money;
use Forge\Stripe\Product;
use Exception;


final class ProductOps {

  static function fromItem($item): Either {
    $eId       = Either::nullable($item['id'], 'ProductId.empty');
    $eName     = Either::nullable($item['name'], 'ProductName.empty');
    $ePrice    = Either::nullable($item['price'], 'ProductPrice.empty');
    $eQuantity = Either::nullable($item['quantity'], 'ProductQuantity.empty');

    // TODO: Use Option instead of '' to express empty value
    $parentId = ArrayOps::getOrElse($item, 'parent_id', '');

    return $eId->flatMap(fn($id) =>
      $eName->flatMap(fn($n) =>
        $ePrice->flatMap(fn($p) =>
          $eQuantity->map(fn($q) =>
            Product::apply($id, $n, Money::toCents($p), $q, $parentId)
          )
        )
      )
    )->leftMap(fn($e) => new Exception($e));
  }

}
