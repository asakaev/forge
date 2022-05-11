<?php declare(strict_types=1);

require __DIR__ . '/autoload.php';

use Forge\Base\Either;
use Forge\Base\Product2;
use Forge\ExecAppCallback;
use Forge\Extension\LineItemsOps;
use Forge\ProcessEvent;
use Forge\ProcessOrder;
use Forge\SaveTransaction;
use Forge\State;
use Forge\Stripe\Config;
use Forge\Stripe\LineItems;
use Forge\Stripe\Props;
use Forge\Stripe\SessionCompleted;
use Forge\Util\Http;
use Forge\Util\Json;


/**
 * @property string|null $secret
 * @property string $merchant_id
 */
final class forgePayment extends waPayment {

  final function allowedCurrency(): array {
    return Props::currencies;
  }

  final function payment($payment_form_data, $order_data, $auto_submit = false): string {
    $eUrls = Either::attempt(fn() => $this->getAdapter())->map(fn(waAppPayment $a) =>
      Product2::apply(
        $a->getBackUrl(waAppPayment::URL_SUCCESS),
        $a->getBackUrl(waAppPayment::URL_FAIL)
      )
    );

    $eSecret = Either::nullable($this->secret, new Exception('Secret.empty'));

    $eConfig = $eUrls->flatMap(fn(Product2 $tuple) =>
      $eSecret->map(fn($secret) =>
        Config::apply($secret, $tuple->_1(), $tuple->_2())
      )
    );

    $eOrderId = Either::nullable($order_data['order_id'], new Exception('OrderId.empty'));

    return $eConfig->flatMap(fn(Config $conf) =>
      LineItemsOps::fromOrder($order_data)->flatMap(fn(LineItems $lineItems) =>
        $eOrderId->flatMap(fn($orderId) =>
          ProcessOrder::make($conf)->apply(
            $lineItems,
            State::apply($this->merchant_id, $orderId)
          )
        )
      )
    )
    ->leftMap(fn(Exception $e) => waLog::log($e->getMessage()))
    ->fold(
      fn() => 'Failure',
      function (string $stripeUrl) use ($auto_submit) {
        $view = wa()->getView();
        $view->assign('plugin', $this);
        $view->assign('form_url', $stripeUrl);
        $view->assign('auto_submit', $auto_submit);
        return $view->fetch($this->path . '/templates/payment.html');
      }
    );
  }

  final function callbackInit($request): forgePayment {
    return Http::body()
      ->flatMap(fn($s) => Json::parse($s))
      ->flatMap(fn($json) =>
        State::fromJson($json)->map(fn(State $s) => $s->merchantId())
      )
      ->leftMap(fn(Exception $e) => waLog::log($e->getMessage()))
      ->fold(
        fn($e) => $this,
        function($mid) use ($request) {
          $this->merchant_id = $mid;
          return parent::callbackInit($request);
        }
      );
  }

  final function callbackHandler($request): array {
    $processEvent = ProcessEvent::make(
      SaveTransaction::fromFunction(fn($t) =>
        Either::attempt(fn() => $this->saveTransaction($t))
      ),
      ExecAppCallback::fromFunction(fn($t) =>
        Either::attempt(fn() => $this->execAppCallback(waPayment::CALLBACK_PAYMENT, $t))
      )
    );

    // TODO: check signature

    return Http::body()
      ->flatMap(fn($s) => Json::parse($s))
      ->flatMap(fn($json) => SessionCompleted::fromJson($json))
      ->flatMap(fn($sc) => $processEvent->apply($sc))
      ->leftMap(fn(Exception $e) => waLog::log($e->getMessage()))
      ->fold(
        fn($e) => array('status' => 500),
        fn()   => array('status' => 200)
    );
  }

}
