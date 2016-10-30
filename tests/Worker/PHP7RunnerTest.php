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
namespace Teknoo\Tests\East\CodeRunnerBundle\Worker;

use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Teknoo\East\CodeRunnerBundle\Entity\Task\Task;
use Teknoo\East\CodeRunnerBundle\Task\PHPCode;
use Teknoo\East\CodeRunnerBundle\Worker\Interfaces\ComposerConfiguratorInterface;
use Teknoo\East\CodeRunnerBundle\Worker\Interfaces\PHPCommanderInterface;
use Teknoo\East\CodeRunnerBundle\Worker\Interfaces\RunnerInterface;
use Teknoo\East\CodeRunnerBundle\Worker\PHP7Runner;

/**
 * @covers \Teknoo\East\CodeRunnerBundle\Worker\PHP7Runner
 */
class PHP7RunnerTest extends AbstractRunnerTest
{
    /**
     * @var ProducerInterface
     */
    private $statusProducer;

    /**
     * @var ProducerInterface
     */
    private $resultProducer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ComposerConfiguratorInterface
     */
    private $composerConfigurator;

    /**
     * @var PHPCommanderInterface
     */
    private $phpCommander;

    /**
     * @return ProducerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getStatusProducerMock(): ProducerInterface
    {
        if (!$this->statusProducer instanceof \PHPUnit_Framework_MockObject_MockObject) {
            $this->statusProducer = $this->createMock(ProducerInterface::class);
        }

        return $this->statusProducer;
    }

    /**
     * @return ProducerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getResultProducerMock(): ProducerInterface
    {
        if (!$this->resultProducer instanceof \PHPUnit_Framework_MockObject_MockObject) {
            $this->resultProducer = $this->createMock(ProducerInterface::class);
        }

        return $this->resultProducer;
    }

    /**
     * @return LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getLoggerMock(): LoggerInterface
    {
        if (!$this->logger instanceof \PHPUnit_Framework_MockObject_MockObject) {
            $this->logger = $this->createMock(LoggerInterface::class);
        }

        return $this->logger;
    }

    /**
     * @return ComposerConfiguratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getComposerConfiguratorMock(): ComposerConfiguratorInterface
    {
        if (!$this->composerConfigurator instanceof \PHPUnit_Framework_MockObject_MockObject) {
            $this->composerConfigurator = $this->createMock(ComposerConfiguratorInterface::class);
        }

        return $this->composerConfigurator;
    }

    /**
     * @return PHPCommanderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getPhpCommanderMock(): PHPCommanderInterface
    {

        if (!$this->phpCommander instanceof \PHPUnit_Framework_MockObject_MockObject) {
            $this->phpCommander = $this->createMock(PHPCommanderInterface::class);
        }

        return $this->phpCommander;
    }

    /**
     * @return PHP7Runner|RunnerInterface
     */
    public function builderRunner(): RunnerInterface
    {
        return new PHP7Runner(
            $this->getStatusProducerMock(),
            $this->getResultProducerMock(),
            $this->getLoggerMock(),
            '7.0',
            $this->getComposerConfiguratorMock(),
            $this->getPhpCommanderMock()
        );
    }

    public function testComposerIsReady()
    {
        $this->getPhpCommanderMock()
            ->expects(self::once())
            ->method('execute');

        $this->getStatusProducerMock()
            ->expects(self::once())
            ->method('publish');

        parent::testComposerIsReady();
    }

    public function testCodeExecuted()
    {
        $this->getResultProducerMock()
            ->expects(self::once())
            ->method('publish');

        $this->getStatusProducerMock()
            ->expects(self::once())
            ->method('publish');

        $this->getPhpCommanderMock()
            ->expects(self::once())
            ->method('reset')
            ->willReturnSelf();

        $this->getComposerConfiguratorMock()
            ->expects(self::once())
            ->method('reset')
            ->willReturnSelf();

        parent::testCodeExecuted();
    }

    public function testErrorInCode()
    {
        $this->getResultProducerMock()
            ->expects(self::once())
            ->method('publish');

        $this->getStatusProducerMock()
            ->expects(self::once())
            ->method('publish');

        $this->getPhpCommanderMock()
            ->expects(self::once())
            ->method('reset')
            ->willReturnSelf();

        $this->getComposerConfiguratorMock()
            ->expects(self::once())
            ->method('reset')
            ->willReturnSelf();

        parent::testErrorInCode();
    }

    /**
     * @expectedException \Throwable
     */
    public function testExecuteBadMessage()
    {
        $this->builderRunner()->execute(new \stdClass());
    }

    public function testExecute()
    {
        $message = new AMQPMessage();
        $code = new PHPCode('echo "Hello World";', []);
        $message->body = json_encode((new Task())->setCode($code));

        $this->getStatusProducerMock()
            ->expects(self::once())
            ->method('publish');

        $runner = $this->builderRunner();
        $this->getComposerConfiguratorMock()
            ->expects(self::once())
            ->method('configure')
            ->with($code, $runner)
            ->willReturnSelf();

        $this->getResultProducerMock()
            ->expects(self::never())
            ->method('publish');

        self::assertTrue(
            $runner->execute($message)
        );
    }

    public function testExecuteError()
    {
        $message = new AMQPMessage();
        $code = new PHPCode('echo "Hello World";', []);
        $message->body = json_encode((new Task())->setCode($code));

        $this->getStatusProducerMock()
            ->expects(self::exactly(2))
            ->method('publish');

        $this->getResultProducerMock()
            ->expects(self::once())
            ->method('publish');

        $runner = $this->builderRunner();
        $this->getComposerConfiguratorMock()
            ->expects(self::once())
            ->method('configure')
            ->with($code, $runner)
            ->willThrowException(new \Exception());

        $this->getLoggerMock()
            ->expects(self::once())
            ->method('critical');

        self::assertFalse(
            $runner->execute($message)
        );
    }
}