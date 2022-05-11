<?php declare(strict_types=1);

namespace Forge\Stripe;

use Exception;
use Forge\Base\ArrayOps;
use Forge\Base\Either;
use Forge\Base\Product2;
use Forge\Util\FormData;


/**
 * https://stripe.com/docs/api/metadata
 */
final class Metadata {
  private array $value;

  final function value(): array {
    return $this->value;
  }

  final static function toFormData(Metadata $metadata): FormData {
    return ArrayOps::fold($metadata->value(), FormData::empty(), function (FormData $acc, Product2 $tuple) {
      $data1 = FormData::apply(array("metadata[{$tuple->_1()}]" => $tuple->_2()));
      return $acc->combine($data1);
    });
  }
  
  final static function fromJson(array $json): Either {
    return Either::nullable($json['data']['object']['metadata'], new Exception('Metadata.empty'))
      ->map(fn($metadata) => Metadata::apply($metadata));
  }

  function __construct(array $value) {
    $this->value = $value;
  }

  final static function apply(array $value): Metadata {
    return new Metadata($value);
  }
}
