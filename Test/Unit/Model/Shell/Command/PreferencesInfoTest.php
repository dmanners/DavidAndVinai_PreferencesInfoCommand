<?php

namespace DavidAndVinai\PreferencesInfoCommand\Test\Unit\Model\Shell\Command;

use DavidAndVinai\PreferencesInfoCommand\Model\Shell\Command\PreferencesInfo;
use Magento\Framework\ObjectManager\ConfigInterface as ObjectManagerConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers \DavidAndVinai\PreferencesInfoCommand\Model\Shell\Command\PreferencesInfo
 */
class PreferencesInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PreferencesInfo
     */
    private $preferencesInfoCommand;

    /**
     * @var ObjectManagerConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockObjectManagerConfig;

    /**
     * @var InputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockInput;

    /**
     * @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockOutput;

    /**
     * @param string[] $requestedInterfaces
     */
    private function setRequestedInterfaces(array $requestedInterfaces)
    {
        $this->mockInput->method('getArgument')->willReturnMap([
            ['interface', $requestedInterfaces]
        ]);
    }

    /**
     * @param string[] $preferencesFixture
     * @return \PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    private function setConfiguredPreferencesFixture(array $preferencesFixture)
    {
        $this->mockObjectManagerConfig->method('getPreferences')->willReturn($preferencesFixture);
    }

    /**
     * @param string[] $matchingPreferences
     */
    private function setExpectedMatches(array $matchingPreferences)
    {
        $expectedOutputLines = array_map(function($type) use ($matchingPreferences) {
            return sprintf('<info>%s => %s</info>', $type, $matchingPreferences[$type]);
        }, array_keys($matchingPreferences));
        $this->setExpectedOutputLines($expectedOutputLines);
    }

    /**
     * @param string[] $expectedOutputLines
     */
    private function setExpectedOutputLines(array $expectedOutputLines)
    {
        $expectedCallCount = count($expectedOutputLines);
        $invocationMocker = $this->mockOutput->expects($this->exactly($expectedCallCount))->method('writeln');
        $callArgumentListsArray = array_map(function ($line) {
            return [$line];
        }, $expectedOutputLines);
        call_user_func_array([$invocationMocker, 'withConsecutive'], $callArgumentListsArray);
    }

    private function executeCommand()
    {
        $this->preferencesInfoCommand->run($this->mockInput, $this->mockOutput);
    }

    protected function setUp()
    {
        $this->mockInput = $this->getMock(InputInterface::class);
        $this->mockOutput = $this->getMock(OutputInterface::class);
        $this->mockObjectManagerConfig = $this->getMock(ObjectManagerConfig::class, [], [], '', false);
        $this->preferencesInfoCommand = new PreferencesInfo($this->mockObjectManagerConfig);
    }

    /**
     * @test
     */
    public function itShouldFindNothingIfThereIsNoMatch()
    {
        $this->setConfiguredPreferencesFixture([
            'Test\\Configured\\Preference' => 'Test\\Target\\Class'
        ]);
        $this->setRequestedInterfaces(['Non\\Existing\\Preference']);
        $this->setExpectedMatches([]);
        $this->executeCommand();
    }

    /**
     * @test
     */
    public function itShouldFindNothingIfNoInterfacesWhereRequestedAsCommandArguments()
    {
        $this->setConfiguredPreferencesFixture([
            'Test\\Configured\\Preference' => 'Test\\Target\\Class'
        ]);
        $this->setRequestedInterfaces([]);
        $this->setExpectedMatches([]);
        $this->executeCommand();
    }

    /**
     * @test
     * @dataProvider matchingRequestedInterfacesDataProvider
     */
    public function itShouldFindAMatchIfTheRequestedInterfaceMatchesFromTheRight($requestedInterface)
    {
        $preferencesFixture = [
            'Test\\Configured\\Preference' => 'Test\\Target\\Class'
        ];
        $this->setConfiguredPreferencesFixture($preferencesFixture);
        $this->setRequestedInterfaces([$requestedInterface]);
        $this->setExpectedMatches($preferencesFixture);
        $this->executeCommand();
    }

    public function matchingRequestedInterfacesDataProvider()
    {
        return [
            ['\\Test\\Configured\\Preference'],
            ['Test\\Configured\\Preference'],
            ['\\Configured\\Preference'],
            ['Configured\\Preference'],
            ['\\Preference'],
            ['Preference'],
            ['nce'],
        ];
    }

    /**
     * @test
     */
    public function itShouldNotMatchAtTheBeginningOfThePreference()
    {
        $this->setConfiguredPreferencesFixture([
            'Test\\Configured\\Preference' => 'Test\\Target\\Class'
        ]);
        $this->setRequestedInterfaces(['Test']);
        $this->setExpectedMatches([]);
        $this->executeCommand();
    }

    /**
     * @test
     */
    public function itShouldNotMatchInTheMiddleOfThePreference()
    {
        $this->setConfiguredPreferencesFixture([
            'Test\\Configured\\Preference' => 'Test\\Target\\Class'
        ]);
        $this->setRequestedInterfaces(['Configured']);
        $this->setExpectedMatches([]);
        $this->executeCommand();
    }

    /**
     * @test
     */
    public function itShouldFindTwoMatchesForOneRequestedType()
    {
        $preferencesFixture = [
            'First\\Configured\\Preference' => 'Test\\Target\\ClassA',
            'Second\\Configured\\Preference' => 'Test\\Target\\ClassB'
        ];
        $this->setConfiguredPreferencesFixture($preferencesFixture);
        $this->setRequestedInterfaces(['Configured\\Preference']);
        $this->setExpectedMatches($preferencesFixture);
        $this->executeCommand();
    }

    /**
     * @test
     */
    public function itShouldFindOneMatchForBothRequestedInterfaces()
    {
        $preferencesFixture = [
            'Configured\\Preference' => 'Test\\Target\\Class',
        ];
        $this->setConfiguredPreferencesFixture($preferencesFixture);
        $this->setRequestedInterfaces(['Configured\\Preference', 'Preference']);
        $this->setExpectedMatches($preferencesFixture);
        $this->executeCommand();
    }

    /**
     * @test
     */
    public function itShouldFindTwoMatchesForTwoRequestedInterfaces()
    {
        $preferencesFixture = [
            'FixtureA\\Preference' => 'Test\\Target\\ClassA',
            'FixtureB\\Preference' => 'Test\\Target\\ClassB'
        ];
        $this->setConfiguredPreferencesFixture($preferencesFixture);
        $this->setRequestedInterfaces(['FixtureA\\Preference', 'FixtureB\\Preference']);
        $this->setExpectedMatches($preferencesFixture);
        $this->executeCommand();
    }

    /**
     * @test
     */
    public function itShouldFindOnlyTheMatchingPreferences()
    {
        $this->setConfiguredPreferencesFixture([
            'First\\Configured\\Preference' => 'Test\\Target\\Class1',
            'Second\\Configured\\Preference' => 'Test\\Target\\Class2',
            'Third\\Configured\\Preference' => 'Test\\Target\\Class3',
        ]);
        $this->setRequestedInterfaces(['First\\Configured\\Preference', 'Third\\Configured\\Preference']);
        $this->setExpectedMatches([
            'First\\Configured\\Preference' => 'Test\\Target\\Class1',
            'Third\\Configured\\Preference' => 'Test\\Target\\Class3',
        ]);
        $this->executeCommand();
    }
}
