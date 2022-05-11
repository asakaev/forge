<?php declare(strict_types=1);

namespace Forge\Util;

use Forge\Base\Either;
use Exception;


final class Json {

  final static function parse(string $s): Either {
    $json = json_decode($s, true);
    $error = json_last_error();

    return Either::cond(
      $error === JSON_ERROR_NONE,
      $json,
      new Exception("JsonFailure: [$error]")
    );
  }

}
