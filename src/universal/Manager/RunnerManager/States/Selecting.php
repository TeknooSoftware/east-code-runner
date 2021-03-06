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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/coderunner Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\CodeRunner\Manager\RunnerManager\States;

use Teknoo\East\CodeRunner\Manager\Interfaces\RunnerManagerInterface;
use Teknoo\East\CodeRunner\Manager\RunnerManager\RunnerManager;
use Teknoo\East\CodeRunner\Runner\Interfaces\RunnerInterface;
use Teknoo\East\CodeRunner\Task\Interfaces\TaskInterface;
use Teknoo\East\CodeRunner\Task\Status;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;

/**
 * Class Selecting.
 * RunnerManager's state to dispatch a task to a good runner able to execute the task.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @property RunnerInterface[] $runners
 * @property bool $taskAcceptedByARunner
 * @property RunnerInterface $runnerAccepted
 * @mixin RunnerManager
 */
class Selecting implements StateInterface
{
    use StateTrait;

    private function doTaskAccepted()
    {
        /*
         * {@inheritdoc}
         */
        return function (RunnerInterface $runner, TaskInterface $task): RunnerManagerInterface {
            $this->taskAcceptedByARunner = true;
            $this->runnerAccepted = $runner;

            return $this;
        };
    }

    private function doTaskRejected()
    {
        /*
         * {@inheritdoc}
         */
        return function (RunnerInterface $runner, TaskInterface $task): RunnerManagerInterface {
            $this->taskAcceptedByARunner = false;

            return $this;
        };
    }

    private function browseRunners()
    {
        /*
         * Method to browse all available runner, until any runner has accepted
         * @return \Generator
         */
        return function () {
            foreach ($this->runners as $runner) {
                yield $runner;

                if (true === $this->taskAcceptedByARunner) {
                    break;
                }
            }
        };
    }

    private function selectRunnerToExecuteTask()
    {
        /*
         * To find and select the runner able to execute a task. If no runner found, the method throws the exception
         * \DomainException
         * @param TaskInterface $task
         * @return RunnerInterface
         * @throws \DomainException
         */
        return function (TaskInterface $task): RunnerInterface {
            $this->taskAcceptedByARunner = false;

            foreach ($this->browseRunners() as $runner) {
                /*
                 * @var RunnerInterface $runner
                 */
                $runner->canYouExecute($this, $task);
            }

            if (false === $this->taskAcceptedByARunner) {
                throw new \DomainException('No runner available to execute the task');
            }

            $task->registerStatus(new Status(Status::STATUS_PLANNED));

            return $this->runnerAccepted;
        };
    }
}
