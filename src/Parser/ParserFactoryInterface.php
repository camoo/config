<?php

declare(strict_types=1);

namespace Camoo\Config\Parser;

interface ParserFactoryInterface
{
    public function getInstance(): ParserInterface;
}
