<?php declare(strict_types=1);

namespace Forge;

use Forge\Base\Either;
use Forge\Stripe\SessionCompleted;
use waPayment;


final class ProcessEvent {

  private SaveTransaction $saveTransaction;
  private ExecAppCallback $execAppCallback;

  final function apply(SessionCompleted $sc): Either {
    return self::transaction($sc)
      ->flatMap(fn(Transaction $t) =>
        $this->saveTransaction->apply($t->encode())
      )
      ->flatMap(fn($t) =>
        $this->execAppCallback->apply($t)
      );
  }

  final static function transaction(SessionCompleted $sc): Either {
    return State::fromMetadata($sc->metadata())
      ->map(fn(State $s) =>
        Transaction::of(
          $s->orderId(),
          $sc->id(),
          $sc->amountTotal(),
          $sc->currency(),
          waPayment::OPERATION_AUTH_CAPTURE,
          waPayment::STATE_CAPTURED,
          $sc->created()
        )
    );
  }

  private function __construct(SaveTransaction $saveTransaction, ExecAppCallback $execAppCallback) {
    $this->saveTransaction = $saveTransaction;
    $this->execAppCallback = $execAppCallback;
  }

  final static function make(
    SaveTransaction $saveTransaction,
    ExecAppCallback $execAppCallback
  ): ProcessEvent {
    return new ProcessEvent($saveTransaction, $execAppCallback);
  }

}
