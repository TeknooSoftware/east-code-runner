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
namespace Teknoo\East\CodeRunnerBundle\Worker\PHP7Runner;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Teknoo\East\CodeRunnerBundle\Entity\Task\Task;
use Teknoo\East\CodeRunnerBundle\Task\Interfaces\CodeInterface;
use Teknoo\East\CodeRunnerBundle\Task\Interfaces\ResultInterface;
use Teknoo\East\CodeRunnerBundle\Task\Status;
use Teknoo\East\CodeRunnerBundle\Task\TextResult;
use Teknoo\East\CodeRunnerBundle\Worker\PHP7Runner\Interfaces\ComposerConfiguratorInterface;
use Teknoo\East\CodeRunnerBundle\Worker\PHP7Runner\Interfaces\PHPCommanderInterface;
use Teknoo\East\CodeRunnerBundle\Worker\PHP7Runner\Interfaces\PHPCommandInterface;
use Teknoo\East\CodeRunnerBundle\Worker\PHP7Runner\Interfaces\RunnerInterface;

class PHP7Runner implements ConsumerInterface, RunnerInterface
{
    /**
     * @var Producer
     */
    private $statusProducer;

    /**
     * @var Producer
     */
    private $resultProducer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $version;

    /**
     * @var ComposerConfiguratorInterface
     */
    private $composerConfigurator;

    /**
     * @var PHPCommanderInterface
     */
    private $phpCommander;

    /**
     * PHP7Runner constructor.
     * @param Producer $statusProducer
     * @param Producer $resultProducer
     * @param LoggerInterface $logger
     * @param string $version
     * @param ComposerConfiguratorInterface $composerConfigurator
     * @param PHPCommanderInterface $phpCommander
     */
    public function __construct(
        Producer $statusProducer,
        Producer $resultProducer,
        LoggerInterface $logger,
        string $version,
        ComposerConfiguratorInterface $composerConfigurator,
        PHPCommanderInterface $phpCommander
    ) {
        $this->statusProducer = $statusProducer;
        $this->resultProducer = $resultProducer;
        $this->logger = $logger;
        $this->version = $version;
        $this->composerConfigurator = $composerConfigurator;
        $this->phpCommander = $phpCommander;
    }


    /**
     * @param AMQPMessage $message
     * @return Task
     */
    private function extractTask(AMQPMessage $message): Task
    {
        return Task::jsonDeserialize(\json_decode($message->body, true));
    }

    /**
     * @param AMQPMessage $msg
     * @return bool
     */
    public function execute(AMQPMessage $msg)
    {
        try {
            $this->statusProducer->publish(json_encode(new Status('Prepare')));

            $task = $this->extractTask($msg);

            $this->composerConfigurator->configure($task->getCode(), $this);

        } catch (\Throwable $e) {
            $error = $e->getMessage().PHP_EOL;
            $error .= $e->getFile().':'.$e->getLine().PHP_EOL;
            $error .= $e->getTraceAsString();

            $this->logger->critical($e);

            $result = new TextResult(
                '',
                $error,
                $this->version,
                \memory_get_usage(true),
                0
            );

            $this->resultProducer->publish(json_encode($result));
            $this->statusProducer->publish(json_encode(new Status('Failure')));
        }

        return true;
    }

    /**
     * @param CodeInterface $code
     * @return RunnerInterface
     */
    public function composerIsReady(CodeInterface $code): RunnerInterface
    {
        $this->statusProducer->publish(json_encode(new Status('Executing')));
        $this->phpCommander->execute($code, $this);

        return $this;
    }

    /**
     *
     */
    private function reset()
    {
        $this->composerConfigurator->reset();
        $this->phpCommander->reset();
    }

    /**
     * @param CodeInterface $code
     * @param ResultInterface $result
     * @return RunnerInterface
     */
    public function codeExecuted(CodeInterface $code, ResultInterface $result): RunnerInterface
    {
        $this->resultProducer->publish(json_encode($result));
        $this->statusProducer->publish(json_encode(new Status('Finished')));

        $this->reset();

        return $this;
    }

    /**
     * @param CodeInterface $code
     * @param ResultInterface $result
     * @return RunnerInterface
     */
    public function errorInCode(CodeInterface $code, ResultInterface $result): RunnerInterface
    {
        $this->resultProducer->publish(json_encode($result));
        $this->statusProducer->publish(json_encode(new Status('Failure')));

        $this->reset();

        return $this;
    }
}