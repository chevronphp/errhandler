<?php

namespace Chevron\ErrHandler;
use Psr\Log;
/**
 * a simple error to exception convertor inspired by corpus/autoloader
 *
 * @package Chevron\Error
 */
class ExceptionLogger implements ExceptionLoggerInterface {

	use Log\LoggerAwareTrait;

	/**
	 * invokable proxy
	 * @param \Exception $e
	 * @param array $context
	 * @return
	 */
	function __invoke(\Exception $e, array $context = []){
		$this->logException($e, $context);
	}

	/**
	 * create a flat key => value array of an exception and it's getPrevious() values
	 * @param \Exception $e
	 * @param array $context
	 * @return
	 */
	function logException(\Exception $e, array $context = []){

		if($this->logger InstanceOf Log\LoggerInterface){

			$i = 1;

			while($e && $i){
				$context += [
					"e.[{$i}].type"    => get_class($e),
					"e.[{$i}].message" => $e->getMessage(),
					"e.[{$i}].code"    => $e->getCode(),
					"e.[{$i}].file"    => $e->getFile(),
					"e.[{$i}].line"    => $e->getLine(),
				];

				$e = $e->getPrevious();

				$i += 1;
			}

			$this->logger->error("Logging Exception", $context);
		}
	}
}

