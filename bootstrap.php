<?php

namespace Geek;

// Aliasing rules
use Nerd\Config
  , Nerd\Environment;

define('APPLICATION_NS', 'geek');

error_reporting(Config::get('error.reporting'));
ini_set('display_errors', (Config::get('error.display', true) ? 'On' : 'Off'));
date_default_timezone_set(Config::get('application.timezone', 'UTC'));
\Nerd\Str::$mbString and mb_internal_encoding(Config::get('application.encoding', 'UTF-8'));

Application::instance()->execute();
