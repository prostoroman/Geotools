<?php

/**
 * This file is part of the Geotools library.
 *
 * (c) Antoine Corcy <contact@sbin.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Geotools\Tests\CLI\Convert;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Geotools\Tests\TestCase;
use Geotools\CLI\Convert\DMS;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class DMSTest extends TestCase
{
    protected $application;
    protected $command;
    protected $commandTester;

    protected function setUp()
    {
        $this->application = new Application();
        $this->application->add(new DMS());

        $this->command = $this->application->find('convert:dms');

        $this->commandTester = new CommandTester($this->command);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not enough arguments.
     */
    public function testExecuteWithoutArguments()
    {
        $this->commandTester->execute(array(
            'command' => $this->command->getName(),
        ));
    }

    /**
     * @expectedException Geotools\Exception\InvalidArgumentException
     * @expectedExceptionMessage It should be a valid and acceptable ways to write geographic coordinates !
     */
    public function testExecuteInvalidArguments()
    {
        $this->commandTester->execute(array(
            'command'    => $this->command->getName(),
            'coordinate' => 'foo, bar',
            '--format'   => ' ',
        ));
    }

    public function testExecuteWithoutFormatOption()
    {
        $this->commandTester->execute(array(
            'command'    => $this->command->getName(),
            'coordinate' => '48.8234055, 2.3072664',
        ));

        $this->assertTrue(is_string($this->commandTester->getDisplay()));
        $this->assertRegExp('/48°49′24″N, 2°18′26″E/', $this->commandTester->getDisplay());
    }

    public function testExecuteWithFormatOption()
    {
        $this->commandTester->execute(array(
            'command'    => $this->command->getName(),
            'coordinate' => '40° 26.7717, -79° 56.93172',
            '--format'   => '%P%D:%M:%S, %p%d:%m:%s',
        ));

        $this->assertTrue(is_string($this->commandTester->getDisplay()));
        $this->assertRegExp('/40:26:46, -79:56:56/', $this->commandTester->getDisplay());
    }

    public function testExecuteWithEmptyFormatOption()
    {
        $this->commandTester->execute(array(
            'command'    => $this->command->getName(),
            'coordinate' => '40° 26.7717, -79° 56.93172',
            '--format'   => ' ',
        ));

        $this->assertTrue(is_string($this->commandTester->getDisplay()));
        $this->assertEmpty(trim($this->commandTester->getDisplay()));
    }

    /**
     * @expectedException Geotools\Exception\InvalidArgumentException
     * @expectedExceptionMessage Please provide an ellipsoid name !
     */
    public function testExecuteWithEmptyEllipsoidOption()
    {
        $this->commandTester->execute(array(
            'command'     => $this->command->getName(),
            'coordinate'  => '40° 26.7717, -79° 56.93172',
            '--ellipsoid' => ' ',
        ));
    }

    /**
     * @expectedException Geotools\Exception\InvalidArgumentException
     * @expectedExceptionMessage foo ellipsoid does not exist in selected reference ellipsoids !
     */
    public function testExecuteWithoutAvailableEllipsoidOption()
    {
        $this->commandTester->execute(array(
            'command'     => $this->command->getName(),
            'coordinate'  => '40° 26.7717, -79° 56.93172',
            '--ellipsoid' => 'foo',
        ));
    }

    public function testExecuteWithEllipsoidOption()
    {
        $this->commandTester->execute(array(
            'command'     => $this->command->getName(),
            'coordinate'  => '40° 26.7717, -79° 56.93172',
            '--format'    => '%P%D:%M:%S, %p%d:%m:%s',
            '--ellipsoid' => 'BESSEL_1841',
        ));

        $this->assertTrue(is_string($this->commandTester->getDisplay()));
        $this->assertRegExp('/40:26:46, -79:56:56/', $this->commandTester->getDisplay());
    }
}
