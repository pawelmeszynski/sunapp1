<?php

namespace SunApp\Modules\IO;

use Composer\IO\ConsoleIO;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class FileIO extends ConsoleIO
{
    public const VERBOSITY_QUIET = 16;
    public const VERBOSITY_NORMAL = 32;
    public const VERBOSITY_VERBOSE = 64;
    public const VERBOSITY_VERY_VERBOSE = 128;
    public const VERBOSITY_DEBUG = 256;

    public const OUTPUT_NORMAL = 1;
    public const OUTPUT_RAW = 2;
    public const OUTPUT_PLAIN = 4;


    /** @var InputInterface */
    protected $input;
    /** @var OutputInterface */
    protected $output;
    /** @var HelperSet */
    protected $helperSet;
    /** @var string */
    protected $lastMessage;
    /** @var string */
    protected $lastMessageErr;

    /** @var float */
    private $startTime;
    /** @var array<int, int> */
    private $verbosityMap;
    private $filepath;
    private $formatter;
    private $st;

    /**
     * @param string $input
     * @param int $verbosity
     * @param OutputFormatterInterface|null $formatter
     */
    public function __construct(
        $input = '',
        $verbosity = StreamOutput::VERBOSITY_NORMAL,
        OutputFormatterInterface $formatter = null
    ) {
        $filename = $input;
        $input = new StringInput($input);
        $input->setInteractive(false);
        $this->filepath = 'modules/' . $filename . '.log';
        if (!file_exists(storage_path('app/modules'))) {
            mkdir(storage_path('app/modules'));
        }
        if (!file_exists(storage_path('app/' . $this->filepath))) {
            touch(storage_path('app/' . $this->filepath));
        }
        $this->st = fopen(storage_path('app/' . $this->filepath), 'rw');
        $output = new StreamOutput($this->st, $verbosity, $formatter ? $formatter->isDecorated() : false, $formatter);

        $this->formatter = $formatter ?: new OutputFormatter();
        $this->formatter->setDecorated($formatter ? $formatter->isDecorated() : false);
        parent::__construct($input, $output, new HelperSet(array()));
    }

    /**
     * {@inheritDoc}
     */
    public function writeError($messages, $newline = true, $verbosity = self::NORMAL)
    {
        $this->doWrite($messages, $newline, true, $verbosity);
    }

    /**
     * @param array|string $messages
     * @param bool $newline
     * @param bool $stderr
     * @param int $verbosity
     */
    private function doWrite($messages, $newline, $stderr, $verbosity)
    {
        $sfVerbosity = $this->verbosityMap[$verbosity];
        if ($sfVerbosity > $this->output->getVerbosity()) {
            return;
        }

        // hack to keep our usage BC with symfony<2.8 versions
        // this removes the quiet output but there is no way around it
        // see https://github.com/composer/composer/pull/4913
        if (OutputInterface::VERBOSITY_QUIET === 0) {
            $sfVerbosity = OutputInterface::OUTPUT_NORMAL;
        }
        if (null !== $this->startTime) {
            $memoryUsage = memory_get_usage() / 1024 / 1024;
            $timeSpent = microtime(true) - $this->startTime;
            $messages = array_map(function ($message) use ($memoryUsage, $timeSpent) {
                return sprintf('[%.1fMiB/%.2fs] %s', $memoryUsage, $timeSpent, $message);
            }, (array)$messages);
        }
        if (true === $stderr && $this->output instanceof ConsoleOutputInterface) {
            $this->output->getErrorOutput()->write($messages, $newline, $sfVerbosity);
            $this->lastMessageErr = implode($newline ? "\n" : '', (array)$messages);

            return;
        }
        echo "test";
        $this->streamWrite($messages, $newline, $sfVerbosity);
        $this->lastMessage = implode($newline ? "\n" : '', (array)$messages);
    }


    /**
     * {@inheritdoc}
     */
    public function streamWrite($messages, $newline = false, $options = self::OUTPUT_NORMAL)
    {
        if (!is_iterable($messages)) {
            $messages = [$messages];
        }

        $types = self::OUTPUT_NORMAL | self::OUTPUT_RAW | self::OUTPUT_PLAIN;
        $type = $types & $options ?: self::OUTPUT_NORMAL;

        $verbosities = self::VERBOSITY_QUIET | self::VERBOSITY_NORMAL | self::VERBOSITY_VERBOSE
            | self::VERBOSITY_VERY_VERBOSE | self::VERBOSITY_DEBUG;
        $verbosity = $verbosities & $options ?: self::VERBOSITY_NORMAL;

        if ($verbosity > $this->output->getVerbosity()) {
            return;
        }

        foreach ($messages as $message) {
            switch ($type) {
                case OutputInterface::OUTPUT_NORMAL:
                    $message = $this->formatter->format($message);
                    break;
                case OutputInterface::OUTPUT_RAW:
                    break;
                case OutputInterface::OUTPUT_PLAIN:
                    $message = strip_tags($this->formatter->format($message));
                    break;
            }
            \Storage::append($this->filepath, $message);
        }
    }

    public function getOutput()
    {
        fseek($this->output->getStream(), 0);

        $output = stream_get_contents($this->output->getStream());
        //$output = \Storage::get($this->filepath);

        $output = preg_replace_callback("{(?<=^|\n|\x08)(.+?)(\x08+)}", function ($matches) {
            $pre = strip_tags($matches[1]);

            if (strlen($pre) === strlen($matches[2])) {
                return '';
            }

            // TODO reverse parse the string, skipping span tags and \033\[([0-9;]+)m(.*?)\033\[0m style blobs
            return rtrim($matches[1]) . "\n";
        }, $output);

        return $output;
    }
}
