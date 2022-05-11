<?php declare(strict_types=1);

namespace Forge\Stripe;


final class Config {

  private string $secret;
  private string $successUrl;
  private string $cancelUrl;

  final function secret(): string {
    return $this->secret;
  }

  final function successUrl(): string {
    return $this->successUrl;
  }

  final function cancelUrl(): string {
    return $this->cancelUrl;
  }

  function __construct($secret, $successUrl, $failureUrl) {
    $this->secret = $secret;
    $this->successUrl = $successUrl;
    $this->cancelUrl = $failureUrl;
  }

  final static function apply($secret, $successUrl, $cancelUrl): Config {
    return new Config($secret, $successUrl, $cancelUrl);
  }
}
