<?php

/**
 * Geek Namespace
 *
 * Reserved for Geek command line tools
 *
 * @package       Nerd
 * @subpackage    Geek
 */
namespace Geek\Design\Interactive;

/**
 * Abstract class for Geek tasks
 *
 * @package	      Nerd
 * @subpackage    Geek
 */
abstract class Task {

	/**
	 * A list of questions to ask in your task
	 *
	 * @var    array
	 */
	public static $questions = [];

	/**
	 * A list of answers to questions
	 *
	 * @var    array
	 */
	public static $answers = [];

	/**
	 * Which question are we on?
	 *
	 * @var    integer
	 */
	public static $questioned = 0;

	/**
	 * How many questions answered?
	 *
	 * @var    integer
	 */
	public static $answered   = 0;

	/**
	 * Holds an instance of Geek for this task
	 *
	 * @var    Geek\Geek
	 */
	public static $geek;

	/**
	 * Welcome message
	 *
	 * @var    string
	 */
	public static $welcome = 'Type "quit" to end this task';

	/**
	 * This type of task is interactive
	 *
	 * @return    boolean
	 */
	final public static function interactive()
	{
		return true;
	}

	/**
	 * Run the interactive task "program"
	 *
	 * @param     Geek\Geek     Instance of Geek
	 * @return    void
	 */
	final public static function run(\Geek\Geek $geek)
	{
		static::$geek = $geek;

		$goodbye = array(
			'Good day and happy hacking!',
			'Look us up sometime, drinks on us.',
			'Hats off to you.',
			'Good work was done we hope.',
		);

		$message = $goodbye[\array_rand($goodbye)];

		static::$geek->write();
		static::$geek->write('---------------------------------------------------------------------------------');
		static::$geek->write('WELCOME TO ION v.'.\Nerd\Nerd::VERSION_SIMPLE);
		static::$geek->write('---------------------------------------------------------------------------------');
		static::$geek->write(static::$welcome);
		static::$geek->write();

		static::setup();
		static::loop();

		static::$geek->write();
		static::$geek->write('---------------------------------------------------------------------------------');
		static::$geek->write($message.' Love, the Nerd team');
		static::$geek->write('---------------------------------------------------------------------------------');
		static::$geek->write();
	}

	/**
	 * This method is run before the loop is executed, you
	 * can use it for things like preloading files.
	 *
	 * @return    void
	 */
	public static function setup()
	{
		// Reserved
	}

	/**
	 * Executes the program in question.
	 *
	 * @return    void
	 */
	final private static function loop()
	{
		while(true)
		{
			if(static::$questioned === static::$answered)
			{
				static::question();
			}

			$input = static::$geek->input();

			if(!empty($input))
			{
				if($input === 'quit')
				{
					break;
				}
				
				// Post answer
				static::answer($input);
			}

			\usleep(500); // Don't overtax, every half second intervals are fine I think.
		}
	}

	/**
	 * Output the question you wish to ask
	 *
	 * @return    void
	 */
	final public static function question()
	{
		static::$geek->write(static::$questions[static::$questioned]);
		static::$geek->write_nobreak('> ');
		static::$questioned++;
	}

	/**
	 * Accept the answer and alternatively process the input
	 * from the user if all questions have been answered
	 *
	 * @return    void
	 */
	final public static function answer($input)
	{
		static::$answers[static::$questioned] = $input;
		static::$answered++;
		
		if(static::$answered === (\count(static::$questions)))
		{
			static::process();
		}
	}

	/**
	 * Reset the task to start from the beginning
	 *
	 * @var    void
	 */
	final public static function reset()
	{
		static::$answers    = [];
		static::$questioned = 0;
		static::$answered   = 0;
	}

	/**
	 * Process input from the user (required)
	 *
	 * @return    void
	 */
	public static function process() {}

	/**
	 * Help for this task (required)
	 *
	 * @return    string
	 */
	public static function help() {}
}

/* End of file task.php */