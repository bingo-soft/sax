<?php

namespace Sax;

interface DefaultHandlerInterface
{
    public function startElement(?string $uri, string $localName, string $qName, array $attributes): void;

    public function endElement(?string $uri, string $localName, string $qName): void;

    public function characters(string $data): void;
}
