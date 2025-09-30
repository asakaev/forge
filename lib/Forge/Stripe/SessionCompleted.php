<?php declare(strict_types=1);

namespace Forge\Stripe;

use Forge\Base\Either;
use Forge\Base\Unit;
use Exception;


final class SessionCompleted {

  private string $id;
  private Metadata $metadata;
  private string $currency;
  private int $amountTotal;
  private int $created;

  final function id(): string {
    return $this->id;
  }

  final function metadata(): Metadata {
    return $this->metadata;
  }

  final function currency(): string {
    return $this->currency;
  }

  final function amountTotal(): int {
    return $this->amountTotal;
  }

  final function created(): int {
    return $this->created;
  }

  final static function fromJson($json): Either {
    $eTypeOk = Either::cond(
      $json['type'] === 'checkout.session.completed',
      Unit::of(),
      "Type.unknown: {$json['type']}"
    );

    $eId          = Either::nullable($json['data']['object']['id'], 'Id.empty');
    $eMetadata    = Metadata::fromJson($json);
    $eCurrency    = Either::nullable($json['data']['object']['currency'], 'Currency.empty');
    $eAmountTotal = Either::nullable($json['data']['object']['amount_total'], 'AmountTotal.empty');
    $eCreated     = Either::nullable($json['created'], 'Created.empty');

    return $eTypeOk->flatMap(fn() =>
      $eId->flatMap(fn($id) =>
        $eMetadata->flatMap(fn($metadata) =>
          $eCurrency->flatMap(fn($cur) =>
            $eAmountTotal->flatMap(fn($at) =>
              $eCreated->map(fn($created) =>
                SessionCompleted::of($id, $metadata, $cur, $at, $created)
              )
            )
          )
        )
      )
    )
    ->leftMap(fn($e) => new Exception($e));
  }

  private function __construct(
    string   $id,
    Metadata $metadata,
    string   $currency,
    int      $amountTotal,
    int      $created
  ) {
    $this->id = $id;
    $this->metadata = $metadata;
    $this->currency = $currency;
    $this->amountTotal = $amountTotal;
    $this->created = $created;
  }

  final static function of(
    string   $id,
    Metadata $metadata,
    string   $currency,
    int      $amountTotal,
    int      $created
  ): SessionCompleted {
    return new SessionCompleted($id, $metadata, $currency, $amountTotal, $created);
  }

}
