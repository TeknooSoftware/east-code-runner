<?php

/**
 * East CodeRunner.
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

namespace Teknoo\Tests\East\CodeRunner\Runner;

use Teknoo\East\CodeRunner\Runner\Interfaces\CapabilityInterface;

/**
 * Base test for all capability class implementing CapabilityInterface.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
abstract class AbstractCapabilityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * To get an instance of the class to test.
     *
     * @return CapabilityInterface
     */
    abstract public function buildCapacity(): CapabilityInterface;

    public function testGetTypeReturn()
    {
        self::assertInternalType(
            'string',
            $this->buildCapacity()->getType()
        );
    }

    public function testGetValueReturnNotNull()
    {
        self::assertNotNull($this->buildCapacity()->getValue());
    }

    public function testGetValueReturnNull()
    {
        self::assertNull($this->buildCapacity()->getValue());
    }

    /**
     * @expectedException \Teknoo\Immutable\Exception\ImmutableException
     */
    public function testValueObjectBehaviorSetException()
    {
        $this->buildCapacity()->foo = 'bar';
    }

    /**
     * @expectedException \Teknoo\Immutable\Exception\ImmutableException
     */
    public function testValueObjectBehaviorUnsetException()
    {
        unset($this->buildCapacity()->foo);
    }

    /**
     * @expectedException \Teknoo\Immutable\Exception\ImmutableException
     */
    public function testValueObjectBehaviorConstructor()
    {
        $this->buildCapacity()->__construct();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testJsonDeserializeEmptyClass()
    {
        $capability = $this->buildCapacity();
        $className = get_class($capability);
        $className::jsonDeserialize([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testJsonDeserializeBadClass()
    {
        $capability = $this->buildCapacity();
        $className = get_class($capability);
        $className::jsonDeserialize(['class' => '\DateTime']);
    }

    public function testJsonEncodeDecode()
    {
        $capability = $this->buildCapacity();
        $className = get_class($capability);
        self::assertEquals(
            $capability,
            $className::jsonDeserialize(json_decode(json_encode($capability), true))
        );
    }
}
