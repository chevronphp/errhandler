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
	 * @param Exception $e The thrown/uncaught exception
	 */
	function __invoke(){
		list($e) = func_get_args();

		if(!($e InstanceOf \Exception)){
			// if we caught something that wasn't an exception, the world is ending.
			echo "Something VERY wrong is happening." . $this->$eol(3);
			exit(1);
		}

		$type = get_class($e);
		if($this->env >= static::ENV_STAGE){
			$type = trim(substr($type, strrpos($type, "\\")), "\\");
		}

		// use the error code unless it has a severity
		$severity = $e->getCode();
		if($e InstanceOf \ErrorException){
			$severity = $e->getSeverity();
		}

		// change the severity int for a descriptive string
		if(isset($this->e_consts[$severity])){
			$severity = $this->e_consts[$severity];
		}

		if($this->logger){
			$this->logger->error($type, [
				"message"  => $e->getMessage(),
				"severity" => $severity,
				"file"     => $e->getFile(),
				"line"     => $e->getLine(),
				"code"     => $e->getCode(),
			]);
		}

		// cleaup if we're not in DEV, strip the path off the file for security
		$file = $e->getFile();
		if($this->env != static::ENV_DEV){
			$file    = pathinfo($file, PATHINFO_BASENAME);
		}

		$line    = $e->getLine();
		$message = $e->getMessage();
		$code    = $e->getCode();

		$info = [
			"file"     => $file,
			"line"     => $line,
			"type"     => $type,
			"code"     => $code,
			"severity" => $severity,
			"message"  => $message,
		];

		if($this->is_cli()){
			echo $this->outCli($info);
		}else{
			echo $this->outHtml($info);
		}

		exit($e->getCode());

	}

	/**
	 *
	 */
	function outHtml($info){
		$output = "";
		$output .= "<div class=\"exception\">";
		$output .= "<p class=\"location\">{$info["file"]}:{$info["line"]}</p>";
		$output .= "<hr />";
		$output .= "<p class=\"type\">";
			$output .= "(Type) <strong>{$info["type"]}</strong> -- ";
			$output .= "(Code) <strong>{$info["code"]}</strong> -- ";
			$output .= "(Severity) <strong>{$info["severity"]}</strong>";
			// $output .= "Type: <strong>{$info["type"]}</strong> -- ";
			// $output .= "Code: <strong>{$info["code"]}</strong> -- ";
			// $output .= "Severity: <strong>{$info["severity"]}</strong>";
		$output .= "</p>";
		$output .= "<p class=\"message\">{$info["message"]}</p>";
		$output .= "</div>";
		return $output;
	}

	/**
	 *
	 */
	function outCli($info){
		$output = $this->eol(2);
		$output .= "{$info["file"]}:{$info["line"]}" . $this->eol();
		$output .= str_repeat("=", 54) . $this->eol();
		$output .= "{$info["type"]} -- Code: {$info["code"]} -- Severity: {$info["severity"]}" . $this->eol(2);
		$output .= "{$info["message"]}" . $this->eol();
		$output .= $this->eol(2);
		return $output;
	}

}