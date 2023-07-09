<?php

declare(strict_types=1);

namespace Camoo\Config\Writer;

interface WriterFactoryInterface
{
    public function getInstance(): WriterInterface;
}
