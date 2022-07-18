<?php

namespace SunApp\Modules\Traits;

use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait IO
{
    /**
     * @param float $startTime
     */
    public function enableDebugging($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * {@inheritDoc}
     */
    public function isInteractive()
    {
        return $this->input->isInteractive();
    }

    /**
     * {@inheritDoc}
     */
    public function write($messages, $newline = true, $verbosity = self::NORMAL)
    {
        $this->doWrite($messages, $newline, false, $verbosity);
    }

    /**
     * {@inheritDoc}
     */
    public function writeError($messages, $newline = true, $verbosity = self::NORMAL)
    {
        $this->doWrite($messages, $newline, true, $verbosity);
    }

    /**
     * {@inheritDoc}
     */
    public function overwrite($messages, $newline = true, $size = null, $verbosity = self::NORMAL)
    {
        $this->doOverwrite($messages, $newline, $size, false, $verbosity);
    }

    /**
     * {@inheritDoc}
     */
    public function overwriteError($messages, $newline = true, $size = null, $verbosity = self::NORMAL)
    {
        $this->doOverwrite($messages, $newline, $size, true, $verbosity);
    }

    /**
     * @param array|string $messages
     * @param bool         $newline
     * @param int|null     $size
     * @param bool         $stderr
     * @param int          $verbosity
     */
    private function doOverwrite($messages, $newline, $size, $stderr, $verbosity)
    {
        // messages can be an array, let's convert it to string anyway
        $messages = implode($newline ? "\n" : '', (array) $messages);

        // since overwrite is supposed to overwrite last message...
        if (!isset($size)) {
            // removing possible formatting of lastMessage with strip_tags
            $size = strlen(strip_tags($stderr ? $this->lastMessageErr : $this->lastMessage));
        }
        // ...let's fill its length with backspaces
        $this->doWrite(str_repeat("\x08", $size), false, $stderr, $verbosity);

        // write the new message
        $this->doWrite($messages, false, $stderr, $verbosity);

        // In cmd.exe on Win8.1 (possibly 10?), the line can not be cleared, so we need to
        // track the length of previous output and fill it with spaces to make sure the line is cleared.
        // See https://github.com/composer/composer/pull/5836 for more details
        $fill = $size - strlen(strip_tags($messages));
        if ($fill > 0) {
            // whitespace whatever has left
            $this->doWrite(str_repeat(' ', $fill), false, $stderr, $verbosity);
            // move the cursor back
            $this->doWrite(str_repeat("\x08", $fill), false, $stderr, $verbosity);
        }

        if ($newline) {
            $this->doWrite('', true, $stderr, $verbosity);
        }

        if ($stderr) {
            $this->lastMessageErr = $messages;
        } else {
            $this->lastMessage = $messages;
        }
    }

    /**
     * @return OutputInterface
     */
    private function getErrorOutput()
    {
        if ($this->output instanceof ConsoleOutputInterface) {
            return $this->output->getErrorOutput();
        }

        return $this->output;
    }
}
