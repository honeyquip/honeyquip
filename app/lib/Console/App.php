<?php

namespace Honeybee\FrameworkBinding\Equip\Console;

use stdClass;
use Auryn\Injector;
use Equip\Configuration\ConfigurationSet;
use Honeybee\FrameworkBinding\Equip\ConfigBag\ConfigBagInterface;
use Shrink0r\Monatic\Many;
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

    public function __construct(ConfigBagInterface $configBag, array $appCommands, Injector $injector)
    {
        parent::__construct('honeyquip', $configBag->get('app_version', 'dev-master'));

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

    public static function bootstrap(Injector $injector, array $configurations, array $appCommands)
    {
        $configSet = new ConfigurationSet($configurations);
        $configSet->apply($injector);
        $appClass = static::class;

        return $injector->make($appClass, [ ':appCommands' => $appCommands, ':injector' => $injector ]);
    }
}
