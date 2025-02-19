<?php

declare(strict_types=1);

namespace Camoo\Config\Test\Fixture;

use Camoo\Config\AbstractConfig;

class SimpleConfig extends AbstractConfig
{
    protected function getDefaults(): array
    {
        return [
            'host' => 'localhost',
            'port' => 80,
            'servers' => [
                'host1',
                'host2',
                'host3',
            ],
            'application' => [
                'name' => 'configuration',
                'secret' => 's3cr3t',
            ],
        ];
    }
}
