<?php declare(strict_types=1);

namespace Forge\Stripe;

use Forge\Base\Either;
use Forge\Base\Left;
use Forge\Base\Product2;
use Forge\Util\FormData;
use Forge\Util\Json;
use Exception;


/**
 * https://stripe.com/docs/api/checkout/sessions/create
 */
final class CreateSession {

  private Config $conf;

  static string $url = 'https://api.stripe.com/v1/checkout/sessions';
  static array $headers = array('Content-Type: application/x-www-form-urlencoded');

  final static function request($url, $headers, $data, $secret): Either {
    return Either::attempt(function() use ($url, $headers, $data, $secret) {
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
      curl_setopt($curl, CURLOPT_USERPWD, "$secret:");
      $body = curl_exec($curl);
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      curl_close($curl);
      return Product2::apply($httpCode, $body);
    });
  }

  final static function failure($json): Either {
    return Either::nullable($json['error']['message'], new Exception('ErrorMessage.empty'))
      ->map(fn($e) => new Exception($e));
  }

  final static function success($json): Either {
    return Either::nullable($json['url'], new Exception('Url.empty'));
  }

  final static function decode(Product2 $tuple): Either {
    return $tuple->_1() === 200 ?
      Json::parse($tuple->_2())
        ->flatMap(fn($json) => self::success($json)) :
      Json::parse($tuple->_2())
        ->flatMap(fn($json) => self::failure($json))
        ->fold(
          fn($e) => Left::apply($e),
          fn($a) => Left::apply($a)
        );
  }

  final static function toFormData(
    string    $successUrl,
    string    $cancelUrl,
    LineItems $lineItems,
    Metadata  $metadata
  ): FormData {
      $a = LineItems::toFormData($lineItems);
      $b = Metadata::toFormData($metadata);

      $c = FormData::apply(
        array(
          'success_url' => $successUrl,
          'cancel_url' => $cancelUrl,
          'mode' => 'payment'
        )
      );

    return array_reduce(
      array($a, $b, $c),
      fn(FormData $acc, FormData $x) => $acc->combine($x),
      FormData::empty()
    );
  }

  final function apply(LineItems $lineItems, Metadata $metadata): Either {
    return self::request(
      self::$url,
      self::$headers,
      self::toFormData(
        $this->conf->successUrl(),
        $this->conf->cancelUrl(),
        $lineItems,
        $metadata
      )->encode(),
      $this->conf->secret()
    )->flatMap(fn($x) => self::decode($x));
  }

  function __construct($conf) {
    $this->conf = $conf;
  }

  final static function make(Config $conf): CreateSession {
    return new CreateSession($conf);
  }

}
