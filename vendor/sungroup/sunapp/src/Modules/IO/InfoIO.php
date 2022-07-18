<?php

namespace SunApp\Modules\IO;

use Composer\IO\ConsoleIO as BaseConsoleIO;
use Composer\Question\StrictConfirmationQuestion;
use SunApp\Modules\Traits\IO;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class InfoIO extends BaseConsoleIO
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

    private $progress;

    /**
     * Constructor.
     *
     * @param InputInterface  $input     The input instance
     * @param OutputInterface $output    The output instance
     * @param HelperSet       $helperSet The helperSet instance
     */
    public function __construct(InputInterface $input, $output, HelperSet $helperSet)
    {
        $this->input = $input;
        $this->output = $output;
        $this->helperSet = $helperSet;
        $this->verbosityMap = array(
            self::QUIET => OutputInterface::VERBOSITY_QUIET,
            self::NORMAL => OutputInterface::VERBOSITY_NORMAL,
            self::VERBOSE => OutputInterface::VERBOSITY_VERBOSE,
            self::VERY_VERBOSE => OutputInterface::VERBOSITY_VERY_VERBOSE,
            self::DEBUG => OutputInterface::VERBOSITY_DEBUG,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isDecorated()
    {
        return $this->output->isDecorated();
    }

    /**
     * {@inheritDoc}
     */
    public function isVerbose()
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }

    /**
     * {@inheritDoc}
     */
    public function isVeryVerbose()
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE;
    }

    /**
     * {@inheritDoc}
     */
    public function isDebug()
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG;
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
            }, (array) $messages);
        }

        if (true === $stderr && $this->output instanceof ConsoleOutputInterface) {
            $this->output->getErrorOutput()->write($messages, $newline, $sfVerbosity);
            $this->lastMessageErr = implode($newline ? "\n" : '', (array) $messages);

            return;
        }

        $this->io->writeInfo($messages, $newline, $verbosity);
        $this->lastMessage = implode($newline ? "\n" : '', (array) $messages);
    }

    public function setIO($io)
    {
        $this->io = $io;
    }

    /**
     * {@inheritDoc}
     */
    public function ask($question, $default = null)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->helperSet->get('question');
        $question = new Question($question, $default);
        return $helper->ask($this->input, $this->getErrorOutput(), $question);
    }

    /**
     * {@inheritDoc}
     */
    public function askConfirmation($question, $default = true)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->helperSet->get('question');
        $question = new StrictConfirmationQuestion($question, $default);

        return $helper->ask($this->input, $this->getErrorOutput(), $question);
    }

    /**
     * {@inheritDoc}
     */
    public function askAndValidate($question, $validator, $attempts = null, $default = null)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->helperSet->get('question');
        $question = new Question($question, $default);
        $question->setValidator($validator);
        $question->setMaxAttempts($attempts);

        return $helper->ask($this->input, $this->getErrorOutput(), $question);
    }

    /**
     * {@inheritDoc}
     */
    public function askAndHideAnswer($question)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->helperSet->get('question');
        $question = new Question($question);
        $question->setHidden(true);

        return $helper->ask($this->input, $this->getErrorOutput(), $question);
    }

    /**
     * {@inheritDoc}
     */
    public function select(
        $question,
        $choices,
        $default,
        $attempts = false,
        $errorMessage = 'Value "%s" is invalid',
        $multiselect = false
    ) {
        /** @var QuestionHelper $helper */
        $helper = $this->helperSet->get('question');
        $question = new ChoiceQuestion($question, $choices, $default);
        $question->setMaxAttempts($attempts ?: null); // IOInterface requires false, and Question requires null or int
        $question->setErrorMessage($errorMessage);
        $question->setMultiselect($multiselect);

        $result = $helper->ask($this->input, $this->getErrorOutput(), $question);

        if (!is_array($result)) {
            return (string) array_search($result, $choices, true);
        }

        $results = array();
        foreach ($choices as $index => $choice) {
            if (in_array($choice, $result, true)) {
                $results[] = (string) $index;
            }
        }

        return $results;
    }
}
