<?php

/**
 * East CodeRunnerBundle.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/coderunner Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
namespace Teknoo\East\CodeRunnerBundle\Runner\ClassicPHP7Runner\States;

use Teknoo\East\CodeRunnerBundle\Manager\Interfaces\RunnerManagerInterface;
use Teknoo\East\CodeRunnerBundle\Runner\Interfaces\RunnerInterface;
use Teknoo\East\CodeRunnerBundle\Runner\ClassicPHP7Runner\ClassicPHP7Runner;
use Teknoo\East\CodeRunnerBundle\Task\Interfaces\TaskInterface;
use Teknoo\East\CodeRunnerBundle\Task\Status;
use Teknoo\East\CodeRunnerBundle\Task\TextResult;
use Teknoo\States\State\AbstractState;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;

/**
 * State Busy
 * @mixin ClassicPHP7Runner
 */
class Busy implements StateInterface
{
    use StateTrait;

    private function doReset()
    {
        /**
         * {@inheritdoc}
         */
        return function(): RunnerInterface {
            $this->currentTask = null;
            $this->currentResult = null;
            $this->currentManager = null;

            $this->updateStates();

            return $this;
        };
    }

    private function run()
    {
        return function () {
            $this->currentManager->pushStatus($this, new Status('Executing'));
            $timeBefore = \microtime(true) * 1000;

            $output = '';
            $this->currentManager->pushStatus($this, new Status('Executed'));
            $error = '';
            try {
                $output = eval($this->currentTask->getCode()->getCode());
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }

            $timeAfter = \microtime(true) * 1000;

            $time = $timeAfter - $timeBefore;

            $this->currentResult = new TextResult($output, $error, $this->getVersion(), \memory_get_usage(true), $time);

            $this->currentManager->pushStatus($this, new Status('Executed'));
            $this->currentManager->pushResult($this, $this->currentResult);
        };
    }
}