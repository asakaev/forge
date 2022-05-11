<?php declare(strict_types=1);

namespace Forge\Stripe;

use Forge\Util\FormData;


final class TaxRates {

  /**
   * @var array<string>
   */
  private array $value;

  /**
   * @return array<string>
   */
  final function value(): array {
    return $this->value;
  }

  static function toFormData(TaxRates $taxRates, int $itemIndex): FormData {
    return array_reduce($taxRates->value(), fn(FormData $acc, string $txr) =>
    $acc->combine(
      FormData::apply(array("line_items[$itemIndex][dynamic_tax_rates][]" => $txr))
    ),
      FormData::empty());
  }

  function __construct($value) {
    $this->value = $value;
  }

  /**
   * @param array<string> $value
   */
  final static function apply(array $value): TaxRates {
    return new TaxRates($value);
  }

}
