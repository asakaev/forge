<?php declare(strict_types=1);

namespace Forge\Stripe;

use Forge\Base\ArrayOps;
use Forge\Base\Product2;
use Forge\Util\FormData;


final class LineItems {

  private string $currency;
  private array $products;

  final function currency(): string {
    return $this->currency;
  }

  /**
   * @return array<Product>
   */
  final function products(): array {
    return $this->products;
  }

  final function withProducts(array $products): LineItems {
    return LineItems::apply($this->currency, $products);
  }

  final static function toFormData(LineItems $lineItems): FormData {
    $reducer = fn(string $currency) =>
      function (FormData $acc, Product2 $tuple) use ($currency): FormData {
        $i = $tuple->_1(); /** @var int $i */
        $p = $tuple->_2(); /** @var Product $p */

        $data = FormData::apply(
          array(
            "line_items[$i][price_data][currency]" => $currency,
            "line_items[$i][price_data][product_data][name]" => $p->name(),
            "line_items[$i][price_data][unit_amount]" => $p->amountCents(),
            "line_items[$i][quantity]" => $p->quantity()
          )
        );

        return $acc->combine($data);
      };

    return ArrayOps::fold(
      $lineItems->products(),
      FormData::empty(),
      $reducer(strtolower($lineItems->currency()))
    );
  }

  private function __construct(string $currency, array $products) {
    $this->currency = $currency;
    $this->products = $products;
  }

  final static function apply(string $currency, array $products): LineItems {
    return new LineItems($currency, $products);
  }
}
