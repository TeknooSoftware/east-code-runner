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

namespace Teknoo\East\CodeRunner\Entity\Task\States;

use Teknoo\East\CodeRunner\Entity\Task\Task;
use Teknoo\East\CodeRunner\Task\Interfaces\ResultInterface;
use Teknoo\East\CodeRunner\Task\Interfaces\StatusInterface;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;

/**
 * State Registered.
 * State enable only when the task has not been executed but is registered into task manager
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @mixin Task
 *
 * @property StatusInterface $statusInstance
 * @property ResultInterface $resultInstance
 */
class Registered implements StateInterface
{
    use StateTrait;

    private function doRegisterStatus()
    {
        /**
         * To be able to change the status of the task
         * @param StatusInterface $status
         * @return Task
         */
        return function (StatusInterface $status): Task {
            $this->statusInstance = $status;

            return $this;
        };
    }

    private function doRegisterResult()
    {
        /**
         * To be able to register the result from the runner for this task
         * @param ResultInterface $result
         * @return Task
         */
        return function (ResultInterface $result): Task {
            $this->resultInstance = $result;

            $this->updateStates();

            return $this;
        };
    }
}
