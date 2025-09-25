<?php declare(strict_types=1);

namespace Forge;

use Forge\Base\ArrayOps;
use Forge\Base\Either;
use Forge\Base\Product2;
use Forge\Stripe\Config;
use Forge\Stripe\CreateSession;
use Forge\Stripe\LineItems;
use Forge\Stripe\Product;


final class ProcessOrder {

  private Config $conf;

  final function apply(LineItems $li, State $state, Tax $tax, Shipping $shipping): Either {
    $groupedProducts = self::groupProducts($li->products());
    $s = Product::apply('', "Shipping ({$shipping->carrier()})", $shipping->priceCents(), 1, '');
    $v = Product::apply('', 'VAT', $tax->value(), 1, '');
    $products = array_merge($groupedProducts, [$s, $v]);

    return CreateSession::make($this->conf)->apply(
      $li->withProducts($products),
      $state->toMetadata()
    );
  }

  /**
   * @param array<Product> $xs
   */
  final static function partition(array $xs): Product2 {
    $reducer = function(Product2 $acc, Product $p): Product2 {
      /** @var array<Product> $xs */
      $xs = $acc->_1();

      /** @var array<int, array<Product>> $m */
      $m = $acc->_2();

      return $p->parentId() === ''
        ? Product2::apply(ArrayOps::append($xs, $p), $m)
        : Product2::apply(
          $xs,
          ArrayOps::updated(
            $m,
            $p->parentId(),
            ArrayOps::append(ArrayOps::getOrElse($m, $p->parentId(), []), $p)
          )
        );
    };

    return array_reduce($xs, $reducer, Product2::apply([], []));
  }

  /**
   * @param Product2 $tuple
   * @return array<Product>
   */
  final static function group(Product2 $tuple): array {
    $names = fn(array $xs): string =>
      ArrayOps::mkString(array_map(fn(Product $p) => $p->name(), $xs), ', ');

    $price = function(array $xs): int {
      $reducer = fn(int $acc, Product $p): int =>
        $acc + $p->amountCents();
      return array_reduce($xs, $reducer, 0);
    };

    /** @var array<Product> $xs */
    $xs = $tuple->_1();

    /** @var array<int, array<Product>> $m */
    $m = $tuple->_2();

    $reducer = function(array $acc, Product $p) use ($m, $names, $price): array {
      /** @var array<Product> $ys */
      $ys = ArrayOps::getOrElse($m, $p->id(), []);

      $name = count($ys) > 0
        ? "{$p->name()} [{$names($ys)}]"
        : $p->name();

      $amountCents = $p->amountCents() + $price($ys);

      return ArrayOps::append($acc, $p->withName($name)->withAmountCents($amountCents));
    };

    return array_reduce($xs, $reducer, []);
  }

  /**
   * @param array<Product> $products
   * @return array<Product>
   */
  final static function groupProducts(array $products): array {
    return self::group(self::partition($products));
  }

  function __construct(Config $conf) {
    $this->conf = $conf;
  }

  final static function make(Config $conf): ProcessOrder {
    return new ProcessOrder($conf);
  }

}
