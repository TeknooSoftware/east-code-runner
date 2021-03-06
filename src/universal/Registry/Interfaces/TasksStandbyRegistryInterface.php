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

namespace Teknoo\East\CodeRunner\Registry\Interfaces;

use Teknoo\East\CodeRunner\Runner\Interfaces\RunnerInterface;
use Teknoo\East\CodeRunner\Task\Interfaces\TaskInterface;
use Teknoo\East\Foundation\Promise\PromiseInterface;

/**
 * Interface TasksStandbyRegistryInterface.
 * Interface to define a registry able to manage the stand by queue of a runner, to return the next task to execute.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface TasksStandbyRegistryInterface
{
    /**
     * To add a task in standby list of a runner.
     *
     * @param RunnerInterface $runner
     * @param TaskInterface   $task
     *
     * @return TasksStandbyRegistryInterface|self
     */
    public function enqueue(RunnerInterface $runner, TaskInterface $task): TasksStandbyRegistryInterface;

    /**
     * Dequeues a standby task for a runner. If there are no standby queue, the method must return null.
     *
     * @param RunnerInterface  $runner
     * @param PromiseInterface $promise
     *
     * @return TasksStandbyRegistryInterface
     */
    public function dequeue(RunnerInterface $runner, PromiseInterface $promise): TasksStandbyRegistryInterface;

    /**
     * To clear all standby tasks in the persistent dbms.
     *
     * @return TasksStandbyRegistryInterface|self
     */
    public function clearAll(): TasksStandbyRegistryInterface;
}
