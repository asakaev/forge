<?php declare(strict_types=1);

namespace Forge;

use Forge\Base\ArrayOps;
use Forge\Base\Either;
use Forge\Base\Product2;
use Forge\Stripe\Config;
use Forge\Stripe\CreateSession;
use Forge\Stripe\LineItems;
use Forge\Stripe\ListTaxRates;
use Forge\Stripe\Product;
use Forge\Stripe\TaxRate;
use Forge\Stripe\TaxRates;


final class ProcessOrder {

  private Config $conf;

  final function apply(LineItems $li, State $state): Either {
    return ListTaxRates::make($this->conf->secret())->apply()
      ->flatMap(fn(array $json) => TaxRate::fromJson($json))
      ->map(fn(array $xs) => TaxRates::apply(TaxRate::collectActive($xs)))
      ->flatMap(fn(TaxRates $taxRates) =>
        CreateSession::make($this->conf)->apply(
          $li->withProducts(self::groupProducts($li->products())),
          $taxRates,
          $state->toMetadata()
        )
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
