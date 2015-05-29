<?php

namespace DavidAndVinai\PreferencesInfoCommand\Model\Shell\Command;

use Magento\Framework\ObjectManager\ConfigInterface as ObjectManagerConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PreferencesInfo extends Command
{
    const INPUT_KEY_INTERFACE = 'interface';

    /**
     * @var string[]
     */
    private $outputList = [];
    
    /**
     * @var ObjectManagerConfig
     */
    private $objectManagerConfig;

    public function __construct(ObjectManagerConfig $objectManagerConfig)
    {
        parent::__construct();
        $this->objectManagerConfig = $objectManagerConfig;
    }

    protected function configure()
    {
        $this->addArgument(
            self::INPUT_KEY_INTERFACE,
            InputArgument::IS_ARRAY,
            'List of interfaces or class names to list the preference for.'
        );

        $this->setName('preferences:info')
            ->setDescription('Displays configured preference config for given interface(s)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputList = [];
        $this->collectPreferencesForGivenInterfaces($input);
        $this->displayMatchedPreferences($output);
    }

    private function collectPreferencesForGivenInterfaces(InputInterface $input)
    {
        foreach ($this->getRequestedInterfaces($input) as $interfaceName) {
            $this->collectPreferencesForInterface(ltrim($interfaceName, '\\'));
        }
    }

    private function collectPreferencesForInterface($interfaceName)
    {
        $preferences = $this->objectManagerConfig->getPreferences();
        foreach ($preferences as $type => $targetClass) {
            if ($this->isMatchingPreference($interfaceName, $type)) {
                $this->outputList[$type] = $targetClass;
            }
        }
    }

    private function displayMatchedPreferences(OutputInterface $output)
    {
        foreach ($this->outputList as $type => $targetClass) {
            $output->writeln(sprintf('<info>%s => %s</info>', $type, $targetClass));
        }
    }

    private function getRequestedInterfaces(InputInterface $input)
    {
        $argument = $input->getArgument(self::INPUT_KEY_INTERFACE);
        $requestedInterfaces = array_map('trim', $argument);
        return array_filter($requestedInterfaces, 'strlen');
    }

    private function isMatchingPreference($interfaceName, $type)
    {
        $len = strlen($interfaceName);
        return substr($type, $len * -1) === $interfaceName;
    }
}
