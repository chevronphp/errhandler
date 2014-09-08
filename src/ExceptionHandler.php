<?php

namespace Chevron\ErrHandler;

use \Psr\Log;
/**
 * a simple uncaught exception catcher inspired by corpus/autoloader
 *
 * @package Chevron\Exception
 */
class ExceptionHandler implements Log\LoggerAwareInterface {

	use Log\LoggerAwareTrait;

	/**
	 * the current environment
	 */
	protected $env;

	/**
	 * a dev environment
	 */
	const ENV_DEV   = 1;

	/**
	 * a staging environment
	 */
	const ENV_STAGE = 2;

	/**
	 * a production environment
	 */
	const ENV_PROD  = 3;

	/**
	 * error levels as strings
	 */
	protected $e_consts = [
		E_ERROR             => "E_ERROR",
		E_WARNING           => "E_WARNING",
		E_PARSE             => "E_PARSE",
		E_NOTICE            => "E_NOTICE",
		E_CORE_ERROR        => "E_CORE_ERROR",
		E_CORE_WARNING      => "E_CORE_WARNING",
		E_COMPILE_ERROR     => "E_COMPILE_ERROR",
		E_COMPILE_WARNING   => "E_COMPILE_WARNING",
		E_USER_ERROR        => "E_USER_ERROR",
		E_USER_WARNING      => "E_USER_WARNING",
		E_USER_NOTICE       => "E_USER_NOTICE",
		E_STRICT            => "E_STRICT",
		E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR",
		E_DEPRECATED        => "E_DEPRECATED",
		E_USER_DEPRECATED   => "E_USER_DEPRECATED",
		E_ALL               => "E_ALL",
	];

	/**
	 * @param int $env The current environment
	 * @param LoggerInterface $logger An optional logger
	 */
	function __construct($env, Log\LoggerInterface $logger = null){
		$this->env    = (int)$env;
		$this->logger = $logger;
	}

	/**
	 * @param Exception $e The thrown/uncaught exception
	 */
	function __invoke(){
		list($e) = func_get_args();

		if(!($e InstanceOf \Exception)){
			// if we caught something that wasn't an exception, the world is ending.
			$output = $this->nuclearOption();
			echo $this->is_cli() ? strip_tags($output) : $output;
			exit(1);
		}

		$info = [
			"file"     => $this->hideFile($e->getFile()),
			"line"     => $e->getLine(),
			"type"     => $this->getClass($e),
			"code"     => $e->getCode(),
			"severity" => $this->getSeverity($e),
			"message"  => $e->getMessage(),
			"class"    => get_class($e),
			"path"     => $e->getFile(),
		];

		$this->logException($info);

		unset($info["path"], $info["class"]); // hide some info after logging

		if($this->is_cli()){
			echo $this->toCli($info);
		}else{
			echo $this->toHtml($info);
		}

		exit($e->getCode());

	}

	/**
	 *
	 */
	function eol($multiplier = 1, $br = false){
		$eol = PHP_EOL . ($br ? "<br/>" : "");
		return str_repeat($eol, $multiplier);
	}

	/**
	 *
	 */
	function is_cli(){
		return substr(strtolower(php_sapi_name()), 0, 3) == "cli";
	}

	/**
	 *
	 */
	function getClass(\Exception $e){
		$type = get_class($e);
		if($this->env != static::ENV_DEV){
			$type = trim(substr($type, strrpos($type, "\\")), "\\");
		}
		return $type;
	}

	/**
	 *
	 */
	function getSeverity(\Exception $e){
		// use the error code unless it has a severity
		$severity = $e->getCode();
		if($e InstanceOf \ErrorException){
			$severity = $e->getSeverity();
		}

		// change the severity int for a descriptive string
		if(isset($this->e_consts[$severity])){
			$severity = $this->e_consts[$severity];
		}

		return $severity;
	}

	/**
	 *
	 */
	function logException(array $context = []){
		if($this->logger){
			$this->logger->error($context["class"], $context);
		}
	}

	/**
	 *
	 */
	function hideFile($file){
		// cleaup if we're not in DEV, strip the path off the file for security
		if($this->env != static::ENV_DEV){
			$file = pathinfo($file, PATHINFO_BASENAME);
		}
		return $file;
	}

	/**
	 *
	 */
	function toHtml($info){
		$output = "";
		$output .= "<div id=\"chevron\" class=\"exceptionhandler\">";
		$output .= "<p class=\"location\">{$info["file"]}:{$info["line"]}</p>";
		$output .= "<hr />";
		$output .= "<p class=\"type\">";
			$output .= "(Type) <strong>{$info["type"]}</strong> -- ";
			$output .= "(Code) <strong>{$info["code"]}</strong> -- ";
			$output .= "(Severity) <strong>{$info["severity"]}</strong>";
		$output .= "</p>";
		$output .= "<p class=\"message\">{$info["message"]}</p>";
		$output .= "</div>";
		return $output;
	}

	/**
	 *
	 */
	function toCli($info){
		$output = $this->eol(2);
		$output .= "{$info["file"]}:{$info["line"]}" . $this->eol();
		$output .= str_repeat("=", 54) . $this->eol();
		$output .= "(Type) {$info["type"]} ** (Code) {$info["code"]} ** (Severity) {$info["severity"]}" . $this->eol(2);
		$output .= "{$info["message"]}" . $this->eol();
		$output .= $this->eol(2);
		return $output;
	}

	/**
	 *
	 */
	function nuclearOption(){
		return "<h1>Please do not use the ExceptionHandler for non-exceptions.</h1>" . $this->eol(3);
	}

}