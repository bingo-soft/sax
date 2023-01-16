<?php

namespace Sax;

class Parser
{
    private static $instance;

    private function __construct()
    {
    }

    public static function getInstance(): Parser
    {
        if (self::$instance === null) {
            self::$instance = new Parser();
        }
        return self::$instance;
    }

    public function createParse(): Parse
    {
        return new Parse($this);
    }

    public function parse($streamSource, DefaultHandlerInterface $handler): void
    {
        if (SaxParser::getInstance()->isFree()) {
            SaxParser::getInstance()->init();
        }

        SaxParser::getInstance()->parse($streamSource, $handler);

        SaxParser::getInstance()->free();
    }

    public function getXmlParser()
    {
        return SaxParser::getInstance()->getXmlParser();
    }
}
