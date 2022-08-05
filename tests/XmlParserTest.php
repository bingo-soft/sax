<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Sax\{
    Parse,
    Parser
};

class XmlParserTest extends TestCase
{
    private $parse;

    public function testParsing(): void
    {
        $inputStream = fopen("tests/Resources/EventDefinitionsTest.xml", "r+");
        $parse = new Parse(Parser::getInstance());
        $parse->sourceInputStream($inputStream);
        $parse->execute();
        $this->assertEquals("DEFINITIONS", $parse->getRootElement()->getTagName());
        $this->assertCount(6, $parse->getRootElement()->elements());
        $this->assertFalse($parse->getRootElement()->element("process") === null);
        $this->assertTrue($parse->getRootElement()->element("FAKE") === null);
        $this->assertCount(2, $parse->getRootElement()->element("PROCESS")->elements());
        $this->assertFalse($parse->getRootElement()->element("PROCESS")->element("userTask") === null);
    }

    public function testExtension(): void
    {
        $inputStream = fopen("tests/Resources/ExtensionsTest.xml", "r+");
        $parse = new Parse(Parser::getInstance());
        $parse->sourceInputStream($inputStream);
        $parse->execute();
        $process = $parse->getRootElement()->element("process");
        $this->assertEquals('user1, ${user2(a, b)}, user3', $process->attribute("extension:candidateStarterUsers"));
    }
}
