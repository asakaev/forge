<?php declare(strict_types=1);

namespace Forge\Util;

use Forge\Base\Either;
use Exception;


final class Http {

  final static function body(): Either {
    $data = @file_get_contents('php://input');
    return Either::cond(
      $data !== null,
      $data,
      new Exception("HttpBodyFailure")
    );
  }

}
