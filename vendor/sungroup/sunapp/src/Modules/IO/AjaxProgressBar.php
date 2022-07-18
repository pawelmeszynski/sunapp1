<?php

namespace SunApp\Modules\IO;

use Symfony\Component\Console\Helper\Helper;

final class AjaxProgressBar
{
    private $lp = 0;
    private $step = 0;
    private $max;
    private $format;
    private $stepWidth;
    private $startTime;
    private $percent = 0.0;
    private $output;
    private $finish = false;
    private $view = false;

    public function __construct($output, int $lp = 0, int $max = 0)
    {
        $this->lp = $lp;
        $this->output = $output;
    }

    public function setMaxSteps(int $max)
    {
        $this->format = null;
        $this->max = max(0, $max);
        $this->stepWidth = $this->max ? Helper::strlen((string) $this->max) : 4;
    }

    public function start(int $max = null)
    {
        $this->finish = false;
        $this->view = true;
        $this->startTime = time();
        $this->step = 0;
        $this->percent = 0.0;

        if (null !== $max) {
            $this->setMaxSteps($max);
        }

        $this->display();
    }

    public function setProgress(int $step)
    {
        if ($this->max && $step > $this->max) {
            $this->max = $step;
        } elseif ($step < 0) {
            $step = 0;
        }

        $this->step = $step;
        $this->percent = $this->max ? (float) $this->step / $this->max : 0;
        $this->display();
    }

    public function finish(): void
    {
        if (!$this->max) {
            $this->max = $this->step;
        }

        $this->setProgress($this->max);
        $this->finish = true;
    }

    public function clear(): void
    {
        $this->view = false;
        $this->display();
    }

    public function display(): void
    {
        /*echo 'data: ' . json_encode(['progress'.$this->lp=>[
                'message' => $this->messages['message'],
                'step' => $this->step,
                'percent' => $this->percent,
                'max' => $this->max,
                'stepWidth' => $this->stepWidth,

            ]]) . "\n\n";
        ob_flush();
        flush();*/
        $this->output->setProgressData(
            'progress' . $this->lp,
            [
                'message' => $this->messages['message'],
                'step' => $this->step,
                'percent' => $this->percent,
                'max' => $this->max,
                'stepWidth' => $this->stepWidth,
                'finish' => $this->finish,
                'view' => $this->view
            ]
        );
        $this->output->sendData();
    }

    public function setMessage(string $message, string $name = 'message')
    {
        $this->messages[$name] = __($message);
        $this->display();
    }
}
