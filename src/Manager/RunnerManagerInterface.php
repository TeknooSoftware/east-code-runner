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
namespace Teknoo\East\CodeRunnerBundle\Manager;

use Teknoo\East\CodeRunnerBundle\Runner\RunnerInterface;
use Teknoo\East\CodeRunnerBundle\Task\ResultInterface;
use Teknoo\East\CodeRunnerBundle\Task\TaskInterface;

/**
 * A runner manager is a service able to register all available runner for a platform and dispatch execution on
 * these runner according to theirs capabilities
 */
interface RunnerManagerInterface
{
    /**
     * To register a runner in the manager to be able to send it a task to execute
     * @param RunnerInterface $runner
     * @return RunnerManagerInterface
     */
    public function registerMe(RunnerInterface $runner): RunnerManagerInterface;

    /**
     * To forget a runner from this manager, all tasks in execution are lost
     *
     * @param RunnerInterface $runner
     * @return RunnerManagerInterface
     */
    public function forgetMe(RunnerInterface $runner): RunnerManagerInterface;

    /**
     * To retrieve a result from an execution, pushed by a runner
     *
     * @param RunnerInterface $runner
     * @param ResultInterface $result
     * @return RunnerManagerInterface
     */
    public function pushResult(RunnerInterface $runner, ResultInterface $result): RunnerManagerInterface;

    /**
     * To execute a Task, sent by a task manager on a dedicated runner
     *
     * @param TaskManagerInterface $taskManager
     * @param TaskInterface $task
     * @return RunnerManagerInterface
     */
    public function executeForMeThisTask(TaskManagerInterface $taskManager, TaskInterface $task): RunnerManagerInterface;
}