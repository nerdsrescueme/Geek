<?php

/**
 * Geek Namespace
 *
 * Reserved for Geek command line tools
 *
 * @package       Nerd
 * @subpackage    Geek
 */
namespace Geek;

/**
 * Geek Command Line Tool
 *
 * Ease development with task based command line tools. Geek does not do
 * much other than run tasks. It is up to the actual tasks to do the
 * heavy lifting. The goal of this tool is to remain as simple as possible
 * and allow the team and developers to develop tasks around it.
 *
 * @package	      Nerd
 * @subpackage    Geek
 */
class Application implements \Nerd\Design\Initializable
{
	use \Nerd\Design\Creational\Singleton;

	public static function __initialize()
	{
		$app = static::instance();

		$args = $_SERVER['argv'] and array_shift($args); // Remove geek from the args

		for($i = 0; $i < \count($args); $i++)
		{
			$arg = \explode('=', $args[$i]);

			// Arguments
			if(\substr($arg[0], 0, 1) !== '-')
			{
				$app->args[$i] = $arg[0];
			}

			// Flags
			if(\count($arg) > 1 or (\substr($arg[0], 0, 1) === '-'))
			{
				$app->flags[\ltrim($arg[0], '-')] = isset($arg[1]) ? $arg[1] : true;
			}
		}
	}

	/**
	 * Available ANSII foreground color codes
	 *
	 * @var    array
	 */
	protected static $foreground_colors = array(
		'black'        => '0;30',
		'dark_gray'    => '1;30',
		'blue'         => '0;34',
		'dark_blue'    => '1;34',
		'light_blue'   => '1;34',
		'green'        => '0;32',
		'light_green'  => '1;32',
		'cyan'         => '0;36',
		'light_cyan'   => '1;36',
		'red'          => '0;31',
		'light_red'    => '1;31',
		'purple'       => '0;35',
		'light_purple' => '1;35',
		'light_yellow' => '0;33',
		'yellow'       => '1;33',
		'light_gray'   => '0;37',
		'white'        => '1;37',
	);

	/**
	 * Available ANSII background color codes
	 *
	 * @var    array
	 */
	protected static $background_colors = array(
		'black'      => '40',
		'red'        => '41',
		'green'      => '42',
		'yellow'     => '43',
		'blue'       => '44',
		'magenta'    => '45',
		'cyan'       => '46',
		'light_gray' => '47',
	);


	/**
	 * Change text to a color from the foreground and background arrays
	 *
	 * @param    string     Text
	 * @param    string     Foreground color
	 * @param    string     Background color
	 * @return   string     Formatted text
	 */
	public static function color($text, $foreground, $background = null)
	{
		if(static::is_windows())
		{
			return $text;
		}

		$string = '\033['.static::$foreground_colors[$foreground].'m';

		if($background !== null)
		{
			$string .= '\033['.static::$background_colors[$background].'m';
		}

		return $text.'\033[0m';
	}

	/**
	 * Determine whether the current operating system is Windows
	 *
	 * @return    boolean     Is the current OS Windows?
	 */
 	public static function is_windows()
 	{ 
 		return 'win' === \strtolower(\substr(\php_uname("s"), 0, 3));
 	}

	/**
	 * The cached arguments
	 *
	 * @var    array
	 */
	public $args;

	/**
	 * The cached flags
	 *
	 * @var    array
	 */
	public $flags;


	/**
	 * Geek Command Line Tool
	 *
	 * Find and run the task we wish to run.
	 *
	 * @return    void      No value is returned
	 */
	public function execute()
	{
		ob_start();

		$args = $this->args;

		// Parse out the task
		$data = explode('.', array_shift($args));

		if (count($data) === 1)
		{
			array_unshift($data, 'nerd');
		}

		// If no method, use default
		$data[2] = (empty($data[2])) ? 'run' : $data[2];

		list($package, $task, $method) = $data;

		// Create class
		$class = '\\'.ucfirst($package).'\\Tasks\\'.ucfirst($task);

		if(!class_exists($class) or !method_exists($class, $method))
		{
			$this->halt("Cannot find $method task in ".ucfirst($package).'\\'.ucfirst($task));
		}

		$class = new $class();
		$class->{$method}();

		ob_end_clean();
	}

	/**
	 * Read input from the command line
	 *
	 * @return    string     Input
	 */
	public function input()
	{
		return \fgets(STDIN);
	}

	/**
	 * Write text to the STDOUT constant, optionally changing it to
	 * a desired color before-hand
	 *
	 * @param    string     Text to output
	 * @param    string     Foreground color
	 * @param    string     Background color
	 * @return   void
	 */
	public function write($text = '', $foreground = null, $background = null)
	{
		$this->write_nobreak($text.PHP_EOL, $foreground, $background);
	}

	/**
	 * Same as above, except it does not automatically add a line break
	 *
	 * @see    Geek::write()
	 */
	public function write_nobreak($text = '', $foreground = null, $background = null)
	{
		if($foreground or $background)
		{
			$text = static::color($text, $foreground, $background);
		}

		\fwrite(STDOUT, $text);
	}

	/**
	 * Write text to the STDERR constant, optionally changing it to
	 * a desired color before-hand
	 *
	 * @param    string     Text to output
	 * @param    string     Foreground color
	 * @param    string     Background color
	 * @return   void
	 */
	public function error($text = '', $foreground = 'light_red', $background = null)
	{
		if($foreground OR $background)
		{
			$text = static::color($text, $foreground, $background);
		}

		\fwrite(STDERR, $text.PHP_EOL);
	}

	/**
	 * Same as Geek::error() except it halts execution.
	 *
	 * @see    Geek::error();
	 */
	public function halt($text = '', $foreground = 'light_red', $background = null)
	{
		$this->error($text, $foreground, $background);

		\ob_end_clean();
		exit(0);
	}
	
	/**
	 * Clears the screen of output
	 *
	 * @return    void     No value is returned
	 */
	public function clear()
	{
		static::is_windows() ? $this->write() : \fwrite(STDOUT, \chr(27)."[H".\chr(27)."[2J");
	}

	/**
	 * Get an argument by offset
	 *
	 * @param    integer     Arguments offset
	 * @param    mixed       Default return value
	 * @return   mixed       Value of argument
	 */
	public function arg($offset = 0, $default = false)
	{
		return isset($this->args[$offset]) ? $this->args[$offset] : $default;
	}

	/**
	 * Get a flag by name
	 *
	 * @param    string     Flag name
	 * @param    mixed      Default return value
	 * @return   mixed      Value of flag or boolean for existence
	 */
	public function flag($name, $default = false)
	{
		return isset($this->flags[$name]) ? $this->flags[$name] : $default;
	}
}