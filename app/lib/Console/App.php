<?php

namespace Honeybee\FrameworkBinding\Equip\Console;

use Auryn\Injector;
use Equip\Configuration\ConfigurationSet;
use Honeybee\FrameworkBinding\Equip\ConfigBag\ConfigBagInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;

class App extends Application
{
    public static function getLogo()
    {
        return <<<ASCII
 _                                        _
| |__   ___  _ __   ___ _   _  __ _ _   _(_)_ __
| '_ \ / _ \| '_ \ / _ \ | | |/ _` | | | | | '_ \
| | | | (_) | | | |  __/ |_| | (_| | |_| | | |_) |
|_| |_|\___/|_| |_|\___|\__, |\__, |\__,_|_| .__/
                        |___/    |_|       |_|

ASCII;
    }

    public function __construct(array $appCommands, Injector $injector, ConfigurationSet $configuration)
    {
        parent::__construct('honeyquip', 'dev-master');

        $configuration->apply($injector);

        $this->getDefinition()->addOption(
            new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev')
        );
        foreach (array_map([ $injector, 'make'], $appCommands) as $command) {
            $this->add($command);
        }
    }

    public function getHelp()
    {
        return self::getLogo() . parent::getHelp();
    }
}
