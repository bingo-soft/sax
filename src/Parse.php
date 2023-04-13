<?php

namespace Sax;

class Parse
{
    protected $parser;
    protected $name;
    protected $streamSource;
    protected $rootElement = null;
    protected $errors = [];
    protected $warnings = [];

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function addError(string $errorMessage, Element $element = null, ...$elementIds): void
    {
        $this->errors[] = new ProblemImpl($errorMessage, $element, $elementIds);
    }

    public function addWarning(string $errorMessage, Element $element = null, ...$elementIds): void
    {
        $this->warnings[] = new ProblemImpl($errorMessage, $element, $elementIds);
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
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

    public function logWarnings(): void
    {
        if (!empty($this->warnings)) {
            $message = implode("\n", array_map(function ($warning) {
                return $warning->getMessage() . " | resource " . $this->name;
            }, $this->warnings));
            fwrite(STDERR, $message . "\n");
        }
    }

    public function throwExceptionForErrors(): void
    {
        if (!empty($this->errors)) {
            $message = implode("\n", array_map(function ($error) {
                return $error->getMessage() . " | resource " . $this->name;
            }, $this->errors));
            throw new \Exception($message);
        }
    }
}
