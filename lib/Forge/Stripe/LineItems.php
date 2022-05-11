<?php declare(strict_types=1);

namespace Forge\Stripe;

use Forge\Base\ArrayOps;
use Forge\Base\Product2;
use Forge\Util\FormData;


final class LineItems {

  private string $currency;
  private int $shipping;
  private string $shippingName;
  private array $products;

  final function currency(): string {
    return $this->currency;
  }

  final function shipping(): int {
    return $this->shipping;
  }

  final function shippingName(): string {
    return $this->shippingName;
  }

  /**
   * @return array<Product>
   */
  final function products(): array {
    return $this->products;
  }

  final function withProducts(array $products): LineItems {
    return LineItems::apply(
      $this->currency,
      $this->shipping,
      $this->shippingName,
      $products
    );
  }

  final static function toFormData(LineItems $lineItems, TaxRates $taxRates): FormData {
    $reducer = fn(string $currency, TaxRates $taxRates) =>
    function (FormData $acc, Product2 $tuple) use ($currency, $taxRates): FormData {
      $i = $tuple->_1(); /** @var int $i */
      $p = $tuple->_2(); /** @var Product $p */

      $data1 = FormData::apply(
        array(
          "line_items[$i][price_data][currency]" => $currency,
          "line_items[$i][price_data][product_data][name]" => $p->name(),
          "line_items[$i][price_data][unit_amount]" => $p->amountCents(),
          "line_items[$i][quantity]" => $p->quantity()
        )
      );

      $data2 = TaxRates::toFormData($taxRates, $i);
      return $acc->combine($data1)->combine($data2);
    };

    $data1 = FormData::apply(
      array(
        'shipping_options[0][shipping_rate_data][display_name]' => $lineItems->shippingName(),
        'shipping_options[0][shipping_rate_data][type]' => 'fixed_amount',
        'shipping_options[0][shipping_rate_data][fixed_amount][amount]' => $lineItems->shipping(),
        "shipping_options[0][shipping_rate_data][fixed_amount][currency]" => $lineItems->currency()
      )
    );

    $data2 = ArrayOps::fold(
      $lineItems->products(),
      FormData::empty(),
      $reducer(strtolower($lineItems->currency()), $taxRates)
    );

    return $data1->combine($data2);
  }

  final function __construct(
    string $currency,
    int $shipping,
    string $shippingName,
    array $products
  ) {
    $this->currency = $currency;
    $this->shipping = $shipping;
    $this->shippingName = $shippingName;
    $this->products = $products;
  }

  final static function apply(
    string $currency,
    int $shipping,
    string $shippingName,
    array $products
  ): LineItems {
    return new LineItems($currency, $shipping, $shippingName, $products);
  }
}
