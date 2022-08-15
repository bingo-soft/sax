<?php

namespace Sax;

interface EntityResolverInterface
{
    public function resolveEntity(string $publicId, string $systemId);
}
