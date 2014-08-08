<?php

require "vendor/autoload.php";

use \Chevron\ErrHandler\ErrorHandler;
use \Chevron\ErrHandler\ExceptionHandler;

set_error_handler(new ErrorHandler);
set_exception_handler(new ExceptionHandler(ExceptionHandler::ENV_PROD));

trigger_error("This should throw an exception.");
// throw new \Exception("This should throw an exception.");