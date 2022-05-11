<?php declare(strict_types=1);

spl_autoload_register(function($className) {
  if (strpos($className, 'Forge') === 0) {
    $dir = __DIR__;
    $sep = DIRECTORY_SEPARATOR;
    $class = str_replace('\\', $sep, $className);
    $file = "$dir$sep$class.php";
    include $file;
  }
});
