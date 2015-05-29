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
    public function itShouldOutputNothingIfThereIsNoMatch()
    {
        $this->setConfiguredPreferencesFixture([
            'Test\\Configured\\Preference' => 'Test\\Target\\Class'
        ]);
        $this->setRequestedInterfaces(['Non\\Existing\\Preference']);
        $this->setExpectedOutputLines([]);
        $this->executeCommand();
    }

    /**
     * @test
     */
    public function itShouldOutputNothingIfWithoutArguments()
    {
        $this->setConfiguredPreferencesFixture([
            'Test\\Configured\\Preference' => 'Test\\Target\\Class'
        ]);
        $this->setRequestedInterfaces([]);
        $this->setExpectedOutputLines([]);
        $this->executeCommand();
    }

    /**
     * @test
     * @dataProvider matchingRequestedInterfacesDataProvider
     */
    public function itShouldOutputAMatchAtTheEnd($requestedInterface)
    {
        $this->setConfiguredPreferencesFixture([
            'Test\\Configured\\Preference' => 'Test\\Target\\Class'
        ]);
        $this->setRequestedInterfaces([$requestedInterface]);
        $this->setExpectedOutputLines(['<info>Test\\Configured\\Preference => Test\\Target\\Class</info>']);
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
        $this->setExpectedOutputLines([]);
        $this->executeCommand();
    }

    /**
     * @test
     */
    public function itShouldOutputTwoMatches()
    {
        $this->setConfiguredPreferencesFixture([
            'First\\Configured\\Preference' => 'Test\\Target\\ClassA',
            'Second\\Configured\\Preference' => 'Test\\Target\\ClassB'
        ]);
        $this->setRequestedInterfaces(['Configured\\Preference']);
        $this->setExpectedOutputLines([
            '<info>First\\Configured\\Preference => Test\\Target\\ClassA</info>',
            '<info>Second\\Configured\\Preference => Test\\Target\\ClassB</info>',
        ]);
        $this->executeCommand();
    }

    /**
     * @test
     */
    public function itShouldOutputEachMatchedPreferenceOnce()
    {
        $this->setConfiguredPreferencesFixture([
            'Configured\\Preference' => 'Test\\Target\\Class',
        ]);
        $this->setRequestedInterfaces(['Configured\\Preference', 'Preference']);
        $this->setExpectedOutputLines([
            '<info>Configured\\Preference => Test\\Target\\Class</info>',
        ]);
        $this->executeCommand();
    }

    /**
     * @test
     */
    public function itShouldOutputTwoMatchesForTwoRequestedInterfaces()
    {
        $this->setConfiguredPreferencesFixture([
            'FixtureA\\Preference' => 'Test\\Target\\ClassA',
            'FixtureB\\Preference' => 'Test\\Target\\ClassB'
        ]);
        $this->setRequestedInterfaces(['FixtureA\\Preference', 'FixtureB\\Preference']);
        $this->setExpectedOutputLines([
            '<info>FixtureA\\Preference => Test\\Target\\ClassA</info>',
            '<info>FixtureB\\Preference => Test\\Target\\ClassB</info>',
        ]);
        $this->executeCommand();
    }

    /**
     * @test
     */
    public function itShouldOutputOnlyMatchingPreferences()
    {
        $this->setConfiguredPreferencesFixture([
            'First\\Configured\\Preference' => 'Test\\Target\\Class1',
            'Second\\Configured\\Preference' => 'Test\\Target\\Class2',
            'Third\\Configured\\Preference' => 'Test\\Target\\Class3',
        ]);
        $this->setRequestedInterfaces(['First\\Configured\\Preference', 'Third\\Configured\\Preference']);
        $this->setExpectedOutputLines([
            '<info>First\\Configured\\Preference => Test\\Target\\Class1</info>',
            '<info>Third\\Configured\\Preference => Test\\Target\\Class3</info>',
        ]);
        $this->executeCommand();
    }
}
