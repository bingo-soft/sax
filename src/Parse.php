<?php

namespace Sax;

class Parse
{
    protected $parser;
    protected $name;
    protected $streamSource;
    protected $rootElement = null;
    protected $errors = [];

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function addError(string $errorMessage, Element $element, $elementIds): void
    {
        $this->errors[] = new ProblemImpl($errorMessage, $element, $elementIds);
    }

    public function getParser(): Parser
    {
        return $this->parser;
    }

    public function getXmlParser()
    {
        return $this->parser->getXmlParser();
    }

    public function name(string $name): Parse
    {
        $this->name = $name;
        return $this;
    }

    public function sourceInputStream($inputStream): Parse
    {
        if ($this->name === null) {
            $this->name("inputStream");
        }
        $this->streamSource = $inputStream;
        return $this;
    }

    public function sourceResource(string $resource): Parse
    {   try {
            return $this->sourceInputStream(fopen($resource, 'r+'));
        } catch (\Exception $e) {
            throw new \Exception(sprintf("Exception while loading resource file %s", $resource));
        }
    }

    public function setRootElement(Element $rootElement): void
    {
        $this->rootElement = $rootElement;
    }

    public function getRootElement(): ?Element
    {
        return $this->rootElement;
    }

    public function execute(): Parse
    {
        $this->parser->parse($this->streamSource, new ParseHandler($this));
        return $this;
    }
}
