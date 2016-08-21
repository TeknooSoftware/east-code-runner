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
namespace Teknoo\Tests\East\CodeRunnerBundle\Manager;

use Teknoo\East\CodeRunnerBundle\Manager\Interfaces\RunnerManagerInterface;
use Teknoo\East\CodeRunnerBundle\Manager\Interfaces\TaskManagerInterface;
use Teknoo\East\CodeRunnerBundle\Runner\Interfaces\RunnerInterface;
use Teknoo\East\CodeRunnerBundle\Task\Interfaces\ResultInterface;
use Teknoo\East\CodeRunnerBundle\Task\Interfaces\TaskInterface;

abstract class AbstractRunnerManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * To get an instance of the class to test
     * @return RunnerManagerInterface
     */
    abstract public function buildManager(): RunnerManagerInterface;

    /**
     * @exceptedException \Throwable
     */
    public function testRegisterMeBadTask()
    {
        $this->buildManager()->registerMe(new \stdClass());
    }

    public function testRegisterMeReturn()
    {
        self::assertInstanceOf(
            RunnerManagerInterface::class,
            $this->buildManager()->registerMe($this->createMock(RunnerInterface::class))
        );
    }
    
    /**
     * @exceptedException \Throwable
     */
    public function testForgetMeBadTask()
    {
        $this->buildManager()->forgetMe(new \stdClass());
    }

    public function testForgetMeReturn()
    {
        self::assertInstanceOf(
            RunnerManagerInterface::class,
            $this->buildManager()->forgetMe($this->createMock(RunnerInterface::class))
        );
    }

    /**
     * @exceptedException \Throwable
     */
    public function testPushResultBadRunner()
    {
        $this->buildManager()->pushResult(
            new \stdClass(),
            $this->createMock(ResultInterface::class)
        );
    }

    /**
     * @exceptedException \Throwable
     */
    public function testPushResultBadResult()
    {
        $this->buildManager()->pushResult(
            $this->createMock(RunnerInterface::class),
            new \stdClass()
        );
    }

    public function testPushResultReturn()
    {
        $manager= $this->buildManager();
        $runner = $this->createMock(RunnerInterface::class);
        $result = $this->createMock(ResultInterface::class);
        $task = $this->createMock(TaskInterface::class);

        $runner->expects(self::any())
            ->method('canYouExecute')
            ->willReturnCallback(function(RunnerManagerInterface $manager, TaskInterface $task) use ($runner) {
                $manager->taskAccepted($runner, $task);
            });

        self::assertInstanceOf(
            RunnerManagerInterface::class,
            $manager->registerMe(
                $runner
            )
        );

        self::assertInstanceOf(
            RunnerManagerInterface::class,
            $manager->executeForMeThisTask(
                $this->createMock(TaskManagerInterface::class),
                $task
            )
        );

        self::assertInstanceOf(
            RunnerManagerInterface::class,
            $manager->pushResult(
                $runner,
                $result
            )
        );
    }

    /**
     * @exceptedException \DomainException
     */
    public function testPushResultExceptionTaskUnknown()
    {
        $manager= $this->buildManager();
        $runner = $this->createMock(RunnerInterface::class);
        $result = $this->createMock(ResultInterface::class);

        self::assertInstanceOf(
            RunnerManagerInterface::class,
            $manager->pushResult(
                $runner,
                $result
            )
        );
    }

    /**
     * @exceptedException \Throwable
     */
    public function testTaskAcceptedBadRunner()
    {
        $this->buildManager()->taskAccepted(
            new \stdClass(),
            $this->createMock(TaskInterface::class)
        );
    }

    /**
     * @exceptedException \Throwable
     */
    public function testTaskAcceptedBadTask()
    {
        $this->buildManager()->taskAccepted(
            $this->createMock(RunnerInterface::class),
            new \stdClass()
        );
    }

    public function testTaskAcceptedReturn()
    {
        $manager= $this->buildManager();
        $task = $this->createMock(TaskInterface::class);

        $runner1 = $this->createMock(RunnerInterface::class);
        $runner1->expects(self::any())
            ->method('canYouExecute')
            ->willReturnCallback(function(RunnerManagerInterface $manager, TaskInterface $task) use ($runner1) {
                $manager->taskAccepted($runner1, $task);
            });

        self::assertInstanceOf(
            RunnerManagerInterface::class,
            $manager->registerMe(
                $runner1
            )
        );

        $runner2 = $this->createMock(RunnerInterface::class);
        $runner2->expects(self::never())
            ->method('canYouExecute');

        self::assertInstanceOf(
            RunnerManagerInterface::class,
            $manager->registerMe(
                $runner2
            )
        );

        self::assertInstanceOf(
            RunnerManagerInterface::class,
            $manager->executeForMeThisTask(
                $this->createMock(TaskManagerInterface::class),
                $task
            )
        );
    }

    /**
     * @exceptedException \DomainException
     */
    public function testTaskAcceptedReturnExceptionOnTaskUnknown()
    {
        self::assertInstanceOf(
            RunnerManagerInterface::class,
            $this->buildManager()->taskAccepted(
                $this->createMock(RunnerInterface::class),
                $this->createMock(TaskInterface::class)
            )
        );
    }
    
    /**
     * @exceptedException \Throwable
     */
    public function testTaskRejectedBadRunner()
    {
        $this->buildManager()->taskRejected(
            new \stdClass(),
            $this->createMock(TaskInterface::class)
        );
    }

    /**
     * @exceptedException \Throwable
     */
    public function testTaskRejectedBadTask()
    {
        $this->buildManager()->taskRejected(
            $this->createMock(RunnerInterface::class),
            new \stdClass()
        );
    }

    public function testTaskRejectedReturn()
    {
        $manager= $this->buildManager();
        $task = $this->createMock(TaskInterface::class);

        $runner1 = $this->createMock(RunnerInterface::class);
        $runner1->expects(self::once())
            ->method('canYouExecute')
            ->willReturnCallback(function(RunnerManagerInterface $manager, TaskInterface $task) use ($runner1) {
                $manager->taskRejected($runner1, $task);
            });

        self::assertInstanceOf(
            RunnerManagerInterface::class,
            $manager->registerMe(
                $runner1
            )
        );

        $runner2 = $this->createMock(RunnerInterface::class);
        $runner2->expects(self::once())
            ->method('canYouExecute')
            ->willReturnCallback(function(RunnerManagerInterface $manager, TaskInterface $task) use ($runner2) {
                $manager->taskAccepted($runner2, $task);
            });

        self::assertInstanceOf(
            RunnerManagerInterface::class,
            $manager->registerMe(
                $runner2
            )
        );

        self::assertInstanceOf(
            RunnerManagerInterface::class,
            $manager->executeForMeThisTask(
                $this->createMock(TaskManagerInterface::class),
                $task
            )
        );
    }

    /**
     * @exceptedException \DomainException
     */
    public function testTaskRejectedExceptionTaskUnknown()
    {
        self::assertInstanceOf(
            RunnerManagerInterface::class,
            $this->buildManager()->taskRejected(
                $this->createMock(RunnerInterface::class),
                $this->createMock(TaskInterface::class)
            )
        );
    }

    /**
     * @exceptedException \Throwable
     */
    public function testExecuteForMeThisTaskBadManager()
    {
        $this->buildManager()->executeForMeThisTask(
            new \stdClass(),
            $this->createMock(TaskInterface::class)
        );
    }

    /**
     * @exceptedException \Throwable
     */
    public function testExecuteForMeThisTaskBadTask()
    {
        $this->buildManager()->executeForMeThisTask(
            $this->createMock(TaskManagerInterface::class),
            new \stdClass()
        );
    }

    public function testExecuteForMeThisTaskReturn()
    {
        self::assertInstanceOf(
            RunnerManagerInterface::class,
            $this->buildManager()->executeForMeThisTask(
                $this->createMock(TaskManagerInterface::class),
                $this->createMock(TaskInterface::class)
            )
        );
    }

    /**
     * @exceptedException \DomainException
     */
    public function testExecuteForMeThisTaskExceptionWhenTaskNotExecutableByAnyRunners()
    {
        $manager= $this->buildManager();
        $runner = $this->createMock(RunnerInterface::class);
        $task = $this->createMock(TaskInterface::class);

        $runner->expects(self::any())
            ->method('canYouExecute')
            ->willReturnCallback(function(RunnerManagerInterface $manager, TaskInterface $task) use ($runner) {
                $manager->taskRejected($runner, $task);
            });

        self::assertInstanceOf(
            RunnerManagerInterface::class,
            $manager->registerMe(
                $runner
            )
        );

        self::assertInstanceOf(
            RunnerManagerInterface::class,
            $manager->executeForMeThisTask(
                $this->createMock(TaskManagerInterface::class),
                $task
            )
        );
    }
}