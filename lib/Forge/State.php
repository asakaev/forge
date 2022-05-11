<?php declare(strict_types=1);

namespace Forge;

use Exception;
use Forge\Base\Either;
use Forge\Stripe\Metadata;


final class State {

  private string $merchantId;
  private string $orderId;

  final function merchantId(): string {
    return $this->merchantId;
  }

  final function orderId(): string {
    return $this->orderId;
  }

  final function toMetadata(): Metadata {
    return Metadata::apply(
      array(
        'merchant_id' => $this->merchantId,
        'order_id'    => $this->orderId
      )
    );
  }

  final static function fromMetadata(Metadata $metadata): Either {
    $value       = $metadata->value();
    $eMerchantId = Either::nullable($value['merchant_id'], 'Metadata.empty');
    $eOrderId    = Either::nullable($value['order_id'], 'OrderId.empty');

    return $eMerchantId->flatMap(fn($mid) =>
      $eOrderId->map(fn($oid) =>
        State::apply($mid, $oid)
      )
    )->leftMap(fn($e) => new Exception($e));
  }

  final static function fromJson(array $json): Either {
    $eMerchantId = Either::nullable(
      $json['data']['object']['metadata']['merchant_id'],
      'Metadata.empty'
    );

    $eOrderId = Either::nullable(
      $json['data']['object']['metadata']['order_id'],
      'OrderId.empty'
    );

    return $eMerchantId->flatMap(fn($mid) =>
      $eOrderId->map(fn($oid) =>
        State::apply($mid, $oid)
      )
    )->leftMap(fn($e) => new Exception($e));
  }

  function __construct(string $merchantId, string $orderId) {
    $this->merchantId = $merchantId;
    $this->orderId = $orderId;
  }

  final static function apply(string $merchantId, string $orderId): State {
    return new State($merchantId, $orderId);
  }

}
