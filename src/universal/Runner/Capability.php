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

namespace Teknoo\East\CodeRunner\Runner;

use Teknoo\East\CodeRunner\Runner\Interfaces\CapabilityInterface;
use Teknoo\Immutable\ImmutableTrait;

/**
 * Class Capability.
 * Default implementation of CapabilityInterface, value object to represent capabilities of a runner, needed by
 * tasks to be executed.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Capability implements CapabilityInterface
{
    use ImmutableTrait;

    /**
     * @var string
     */
    private $type;

    /**
     * @var mixed
     */
    private $value;

    /**
     * Capability constructor.
     *
     * @param string $type
     * @param mixed  $value
     */
    public function __construct(string $type, $value)
    {
        $this->type = $type;
        $this->value = $value;

        $this->uniqueConstructorCheck();
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public static function jsonDeserialize(array $values): CapabilityInterface
    {
        if (!isset($values['class']) || static::class != $values['class']) {
            throw new \InvalidArgumentException('class is not matching with the serialized values');
        }

        return new static($values['type'], $values['value']);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'class' => static::class,
            'type' => $this->getType(),
            'value' => $this->getValue(),
        ];
    }
}
