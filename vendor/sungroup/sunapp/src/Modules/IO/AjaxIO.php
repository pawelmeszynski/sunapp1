<?php

namespace SunApp\Modules\IO;

use Composer\IO\ConsoleIO as BaseConsoleIO;
use SunApp\Modules\Traits\IO;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AjaxIO extends BaseConsoleIO
{
    use IO;

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
    private $verbosity;

    private $progress = 1;

    private $info_section;
    private $console;
    private $data = [];

    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        $this->helperSet = new HelperSet(array());
        $this->verbosity = 32;
        $this->verbosityMap = array(
            self::QUIET => OutputInterface::VERBOSITY_QUIET,
            self::NORMAL => OutputInterface::VERBOSITY_NORMAL,
            self::VERBOSE => OutputInterface::VERBOSITY_VERBOSE,
            self::VERY_VERBOSE => OutputInterface::VERBOSITY_VERY_VERBOSE,
            self::DEBUG => OutputInterface::VERBOSITY_DEBUG,
        );
    }

    public function getOutput()
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isDecorated()
    {
        return $this->formatter->isDecorated();
    }

    /**
     * {@inheritDoc}
     */
    public function isVerbose()
    {
        return $this->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }

    /**
     * {@inheritDoc}
     */
    public function isVeryVerbose()
    {
        return $this->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE;
    }

    /**
     * {@inheritDoc}
     */
    public function isDebug()
    {
        return $this->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG;
    }

    public function writeInfo($messages, $newline = true, $verbosity = self::NORMAL)
    {
        $sfVerbosity = $this->verbosityMap[$verbosity];
        if ($sfVerbosity > $this->getVerbosity()) {
            return;
        }

        $this->data['log'] = $messages;
        $this->sendData();
    }

    /**
     * @param array|string $messages
     * @param bool         $newline
     * @param bool         $stderr
     * @param int          $verbosity
     */
    private function doWrite($messages, $newline, $stderr, $verbosity)
    {

        $sfVerbosity = $this->verbosityMap[$verbosity];
        if ($sfVerbosity > $this->verbosity) {
            return;
        }
        // hack to keep our usage BC with symfony<2.8 versions
        // this removes the quiet output but there is no way around it
        // see https://github.com/composer/composer/pull/4913
        if (OutputInterface::VERBOSITY_QUIET === 0) {
            $sfVerbosity = OutputInterface::OUTPUT_NORMAL;
        }

        $this->data['message'] = $messages;
        $this->sendData();
        $this->lastMessage = implode($newline ? "\n" : '', (array) $messages);
    }

    public function upLine()
    {
        //$this->output->write("\033[1A",false);
    }

    public function title($message)
    {
        $this->data['title'] = __($message);
        $this->sendData();
    }

    public function createProgressBar($max = 0)
    {
        $progress = new AjaxProgressBar($this, $this->progress, $max);
        $progress->setMessage('');
        $this->progress++;
        return $progress;
    }

    public function sendData()
    {
        echo 'data: ' . json_encode($this->data) . "\n\n";
        if (isset($this->data['log'])) {
            unset($this->data['log']);
        }
        ob_flush();
        flush();
    }

    public function setProgressData($progress, $data)
    {
        $this->data['progress'][$progress] = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function setVerbosity($level)
    {
        $this->verbosity = (int) $level;
    }

    /**
     * {@inheritdoc}
     */
    public function getVerbosity()
    {
        return $this->verbosity;
    }
}
