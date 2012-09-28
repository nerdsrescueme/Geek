<?php

/**
 * Geek Namespace
 *
 * Reserved for Geek command line tools
 *
 * @package       Nerd
 * @subpackage    Geek
 */
namespace Geek\Design;

/**
 * Abstract class for Geek tasks
 *
 * @package	      Nerd
 * @subpackage    Geek
 */
abstract class Task {

	/**
	 * Holds an instance of ion
	 *
	 * @var    \Geek\Geek
	 */
	public $geek;

	/**
	 * Setup this task
	 *
	 * @param    Geek\Geek     Current running instance of Geek
	 * @return   void
	 */
	final public function __construct()
	{
		$this->geek = \Geek\Application::instance();
	}

	/**
	 * Default method to be run by Geek.
	 *
	 * @return    void
	 */
	abstract public function run();

	/**
	 * Help for this task, used by \Nerd\Tasks\Help
	 *
	 * @return    void     Nothing is returned
	 */
	abstract public function help();

	/**
	 * Magic Caller
	 *
	 * Pass all non-findable method calls through to the geek instance.
	 */
	public function __call($method, $params)
	{
		return call_user_func_array($this->geek->$method, $params);
	}
}