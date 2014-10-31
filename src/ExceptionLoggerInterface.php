<?php

namespace Chevron\ErrHandler;
use Psr\Log;

interface ExceptionLoggerInterface extends Log\LoggerAwareInterface {

	function __invoke(\Exception $e, array $context = []);

	function logException(\Exception $e, array $context = []);

}