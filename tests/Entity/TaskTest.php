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
namespace Teknoo\Tests\East\CodeRunnerBundle\Entity;

use Teknoo\East\CodeRunnerBundle\Entity\Task\Task;
use Teknoo\East\CodeRunnerBundle\Manager\Interfaces\TaskManagerInterface;
use Teknoo\East\CodeRunnerBundle\Task\Interfaces\TaskInterface;
use Teknoo\East\CodeRunnerBundle\Task\PHPCode;
use Teknoo\East\CodeRunnerBundle\Task\Status;
use Teknoo\East\CodeRunnerBundle\Task\TextResult;
use Teknoo\Tests\East\CodeRunnerBundle\Entity\Traits\PopulateEntityTrait;
use Teknoo\Tests\East\CodeRunnerBundle\Task\AbstractTaskTest;

/**
 * @covers \Teknoo\East\CodeRunnerBundle\Entity\Task\Task
 * @covers \Teknoo\East\CodeRunnerBundle\Entity\Task\States\Executed
 * @covers \Teknoo\East\CodeRunnerBundle\Entity\Task\States\Registered
 * @covers \Teknoo\East\CodeRunnerBundle\Entity\Task\States\Unregistered
 */
class TaskTest extends AbstractTaskTest
{
    use PopulateEntityTrait;

    /**
     * @return TaskInterface|Task
     */
    public function buildTask(): TaskInterface
    {
        return new Task();
    }

    /**
     * @return Task|TaskInterface
     */
    protected function buildEntity()
    {
        return $this->buildTask();
    }

    public function testGetId()
    {
        self::assertEquals(
            123,
            $this->generateEntityPopulated(['id'=>123])->getId()
        );
    }

    public function testGetCreatedAt()
    {
        $date = new \DateTime('2016-07-28');
        self::assertEquals(
            $date,
            $this->generateEntityPopulated(['createdAt'=>$date])->getCreatedAt()
        );
    }

    public function testSetCreatedAt()
    {
        $date = new \DateTime('2016-07-28');
        $entity = $this->buildTask();
        self::assertInstanceOf(
            Task::class,
            $entity->setCreatedAt($date)
        );

        self::assertEquals(
            $date,
            $entity->getCreatedAt()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetCreatedAtExceptionOnBadArgument()
    {
        $this->buildTask()->setCreatedAt(new \stdClass());
    }

    public function testGetUpdatedAt()
    {
        $date = new \DateTime('2016-07-28');
        self::assertEquals(
            $date,
            $this->generateEntityPopulated(['updatedAt'=>$date])->getUpdatedAt()
        );
    }

    public function testSetUpdatedAt()
    {
        $date = new \DateTime('2016-07-28');
        $entity = $this->buildTask();
        self::assertInstanceOf(
            Task::class,
            $entity->setUpdatedAt($date)
        );

        self::assertEquals(
            $date,
            $entity->getUpdatedAt()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetUpdatedAtExceptionOnBadArgument()
    {
        $this->buildTask()->setUpdatedAt(new \stdClass());
    }

    public function testGetDeletedAt()
    {
        $date = new \DateTime('2016-07-28');
        self::assertEquals(
            $date,
            $this->generateEntityPopulated(['deletedAt'=>$date])->getDeletedAt()
        );
    }

    public function testSetDeletedAt()
    {
        $date = new \DateTime('2016-07-28');
        $entity = $this->buildTask();
        self::assertInstanceOf(
            Task::class,
            $entity->setDeletedAt($date)
        );

        self::assertEquals(
            $date,
            $entity->getDeletedAt()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetDeletedAtExceptionOnBadArgument()
    {
        $this->buildTask()->setDeletedAt(new \stdClass());
    }

    public function testPostLoadJsonUpdateAlreadyDecoded()
    {
        $code = new PHPCode('<?php phpinfo();', []);
        self::assertEquals(
            $code,
            $this->generateEntityPopulated(['code' => $code])->postLoadJsonUpdate()->getCode()
        );
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testPostLoadJsonUpdateNoClass()
    {
        $this->generateEntityPopulated(['code' => json_decode(json_encode([]), true)])->postLoadJsonUpdate()->getCode();
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testPostLoadJsonUpdateClassDoesNotExist()
    {
        $this->generateEntityPopulated(['code' => json_decode(json_encode(['class' => 'fooBar']), true)])->postLoadJsonUpdate()->getCode();
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testPostLoadJsonUpdateNoCallable()
    {
        $this->generateEntityPopulated(['code' => json_decode(json_encode(['class' => '\DateTime']), true)])->postLoadJsonUpdate()->getCode();
    }

    public function testPostLoadJsonUpdateNonDecoded()
    {
        $code = new PHPCode('<?php phpinfo();', []);
        $status = new Status('Test');
        $result = new TextResult('foo', 'bar', '7.0', 12, 23);

        /**
         * @var Task $task
         */
        $task = $this->generateEntityPopulated([
            'code' => json_decode(json_encode($code), true),
            'status' => json_decode(json_encode($status), true),
            'result' => json_decode(json_encode($result), true)
        ])->postLoadJsonUpdate();

        self::assertEquals(
            $code,
            $task->getCode()
        );

        self::assertEquals(
            $status,
            $task->getStatus()
        );

        self::assertEquals(
            $result,
            $task->getResult()
        );
    }

    public function testJsonEncodeDecodeWithTaskFulled()
    {
        $task = $this->buildTask();
        $task->setCreatedAt(new \DateTime('2016-10-29', new \DateTimeZone('UTC')));
        $task->setDeletedAt(new \DateTime('2016-10-31', new \DateTimeZone('UTC')));
        $task->setUpdatedAt(new \DateTime('2016-11-01', new \DateTimeZone('UTC'))); //Halloween haha !
        $task->setCode(new PHPCode('', []));
        $task->registerUrl('http://foo.bar');
        $task->registerStatus(new Status(''));
        $task->registerResult(
            $this->createMock(TaskManagerInterface::class),
            new TextResult('', '', '', 0, 0)
        );


        $final = Task::jsonDeserialize(json_decode(json_encode($task), true));

        self::assertEquals($task->getCode(), $final->getCode());
        self::assertEquals($task->getCreatedAt(), $final->getCreatedAt());
        self::assertEquals($task->getDeletedAt(), $final->getDeletedAt());
        self::assertEquals($task->getUpdatedAt(), $final->getUpdatedAt());
        self::assertEquals($task->getStatus(), $final->getStatus());
        self::assertEquals($task->getResult(), $final->getResult());
        self::assertEquals($task->getUrl(), $final->getUrl());
    }
}