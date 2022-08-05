<?php

namespace Sax;

interface DefaultHandlerInterface
{
    public function startElement(string $name, array $attributes): void;

    public function endElement(string $name): void;
}
