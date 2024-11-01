<?php

namespace Tussendoor\Billink\Helpers;

use WC_Logger_Interface;
use Tussendoor\Billink\App;

class Log
{
    public static $levels = [
        'emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'
    ];

    /**
     * Holds the WooCommerce logger instance.
     * @var \WC_Logger_Interface
     */
    protected $logger;
    /**
     * Holds the source string which separates logs from eachother.
     * @var string
     */
    protected $source;
    /**
     * Holds an instance of this class.
     * @var \Tussendoor\Billink\Helpers\Log
     */
    protected static $instance;

    /**
     * Pass any static calls to the underlying WC_Logger_Interface implementation. Inject the
     * given data into the context, which will be processed independently by a filter.
     * @param  string $name
     * @param  array  $arguments
     * @return bool
     */
    public static function __callStatic($name, $arguments)
    {
        if (!in_array($name, static::$levels)) {
            return false;
        }

        $settings = ['source' => static::instance()->getSource()];

        // If only a single argument was given, we only need to log a simple message.
        if (count($arguments) == 1) {
            return static::instance()->log()->$name(reset($arguments), $settings);
        }

        // If more than one argument was found, it's because data was appended to the message.
        // We'll convert the data to json and inject it into the settings array.
        list($message, $data) = $arguments;
        $settings['billinkData'] = $data;

        return static::instance()->log()->$name($message, $settings);
    }

    /**
     * Boot up the logger by setting the logger implementation and the source string.
     * @param \WC_Logger_Interface|null $logger
     * @param string|null               $source
     */
    public function boot(WC_Logger_Interface $logger = null, $source = null, $minLevel = 'notice')
    {
        if (!is_null(static::$instance)) {
            return true;
        }

        $this->logger = $logger ? $logger : wc_get_logger();
        $this->source = $source ? $source : 'billink';

        add_filter('woocommerce_format_log_entry', [$this, 'formatLogMessage'], 10, 2);

        // Slice the log levels from $this->levels that come after the given $minLevel.
        static::$levels = array_slice(static::$levels, 0, (array_search($minLevel, static::$levels) + 1));

        static::$instance = $this;

        return true;
    }

    /**
     * Return an instance of this class.
     * @return \Tussendoor\Billink\Helpers\Log
     */
    public static function instance()
    {
        if (empty(static::$instance)) {
            $minLogLevel = apply_filters('billink_min_loglevel', App::get('debug') ? 'debug' : 'notice');

            $inst = new static();
            $inst->boot(null, 'billink', $minLogLevel);
        }

        return static::$instance;
    }

    /**
     * Return the path to the current log file.
     * @return string
     */
    public static function path()
    {
        return wc_get_log_file_path(static::instance()->getSource());
    }

    /**
     * Return an instance of the WC_Logger_Interface implementation.
     * @return \WC_Logger_Interface
     */
    public function log()
    {
        return $this->logger;
    }

    /**
     * Return the source string. Used by WooCommerce to separate logs.
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Format the entry string of a logmessage. Only does something if billinkData is set
     * in the given $context variable. Appends the given data as a json string.
     * @param  string $entry
     * @param  array  $context
     * @return string
     */
    public function formatLogMessage($entry, $context)
    {
        if (
            empty($context['context']) || 
            empty($context['context']['source']) || 
            (
                !empty($context['context']['source']) &&
                $context['context']['source'] !== $this->source
            )
        ) {
            return $entry;
        }

        list($timestamp, $severity) = explode(' ', $entry);

        $entry = str_replace(
            [$timestamp, $severity],
            ['[' . $timestamp . ']', '[' . strtolower($severity) . ']'],
            $entry
        );

        if (!empty($context['context']['billinkData'])) {
            $entry .= "\n" . $this->indent($this->dataToString($context['context']['billinkData']));
        }

        return $entry;
    }

    /**
     * Turn an array of data into a flat string. Uses var_export() for a visual representation.
     * @param  array  $data
     * @return string
     */
    protected function dataToString($data)
    {
        $export = '';
        foreach ($data as $key => $value) {
            $export .= "{$key}: ";
            $export .= preg_replace([
                '/=>\s+([a-zA-Z])/im',
                '/array\(\s+\)/im',
                '/^  |\G  /m'
            ], [
                '=> $1',
                'array()',
                '    '
            ], str_replace('array (', 'array(', var_export($value, true)));
            $export .= PHP_EOL;
        }

        return str_replace(['\\\\', '\\\''], ['\\', '\''], rtrim($export));
    }

    /**
     * Add indentation to the given string.
     * @param  string $string
     * @param  string $indent
     * @return string
     */
    protected function indent($string, $indent = '    ')
    {
        return $indent . str_replace("\n", "\n" . $indent, $string);
    }
}
