<?php declare(strict_types=1);

namespace Forge\Stripe;

use Forge\Base\Either;
use Forge\Base\ArrayOps;
use Exception;


final class TaxRate {
  private string $id;
  private bool $active;

  final function id(): string {
    return $this->id;
  }

  final function active(): bool {
    return $this->active;
  }

  /**
   * @param array<TaxRate> $xs
   * @return array<string>
   */
  final static function collectActive(array $xs): array {
    $reducer = fn(array $acc, TaxRate $txr): array =>
      $txr->active()
        ? ArrayOps::append($acc, $txr->id())
        : $acc;

    return array_reduce($xs, $reducer, array());
  }

  final static function fromJson(array $json): Either {
    $xs = ArrayOps::getOrElse($json, 'data', array());

    $taxRate = function(array $json): Either {
      $eId     = Either::nullable($json['id'], 'TaxRateId.empty');
      $eActive = Either::nullable($json['active'], 'TaxRateActive.empty');

      return $eId->flatMap(fn($id) =>
        $eActive->map(fn($active) =>
          TaxRate::apply($id, $active)
        )
      );
    };

    return ArrayOps::traverse($xs, $taxRate)
      ->leftMap(fn($e) => new Exception($e));
  }

  final function __construct(string $id, bool $active) {
    $this->id = $id;
    $this->active = $active;
  }

  final static function apply(string $id, bool $active): TaxRate {
    return new TaxRate($id, $active);
  }
}
