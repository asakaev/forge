<?php declare(strict_types=1);

namespace Forge\Stripe;

use Forge\Base\Either;
use Forge\Util\Json;
use Exception;


final class ListTaxRates {

  private string $secret;

  final static function request(string $secret): Either {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/tax_rates');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_USERPWD, "$secret:");

    $reply = curl_exec($ch);
    $errorNumber = curl_errno($ch);
    curl_close($ch);

    return Either::cond(
      $errorNumber === 0,
      $reply,
      new Exception("CurlError: [$errorNumber]")
    );
  }

  final function apply(): Either {
    return self::request($this->secret)->flatMap(fn($data) =>
      Json::parse($data)
    );
  }

  function __construct($secret) {
    $this->secret = $secret;
  }

  final static function make(string $secret): ListTaxRates {
    return new ListTaxRates($secret);
  }

}
