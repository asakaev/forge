<?php declare(strict_types=1);

namespace Forge;


final class Transaction {

  private string $orderId;
  private string $nativeId;
  private int $amount;
  private string $currencyId;
  private string $type;
  private string $state;
  private int $dateTime;

  final function encode(): array {
    return array(
      'order_id'        => $this->orderId,
      'native_id'       => $this->nativeId,
      'amount'          => Money::fromCents($this->amount),
      'currency_id'     => strtoupper($this->currencyId),
      'type'            => $this->type,
      'state'           => $this->state,
      'date_time'       => date('Y-m-d H:i:s', $this->dateTime),
      'update_datetime' => date('Y-m-d H:i:s', $this->dateTime),
      'view_data'       => '',
      'result'          => true,
      'part_number'     => 0
    );
  }

  private function __construct(
    string $orderId,
    string $nativeId,
    int $amount,
    string $currencyId,
    string $type,
    string $state,
    int $dateTime
  ) {
    $this->orderId = $orderId;
    $this->nativeId = $nativeId;
    $this->amount = $amount;
    $this->currencyId = $currencyId;
    $this->type = $type;
    $this->state = $state;
    $this->dateTime = $dateTime;
  }

  final static function of(
    string $orderId,
    string $nativeId,
    int $amount,
    string $currencyId,
    string $type,
    string $state,
    int $dateTime
  ): Transaction {
    return new Transaction(
      $orderId, $nativeId, $amount, $currencyId, $type, $state, $dateTime
    );
  }

}
