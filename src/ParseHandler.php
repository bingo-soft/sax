<?php

namespace Sax;

class ParseHandler implements DefaultHandlerInterface
{
    protected $parse;

    protected $elementStack = [];

    public function __construct(Parse $parse)
    {
        $this->parse = $parse;
    }

    public function startElement(?string $uri, string $localName, string $qName, array $attributes): void
    {
        $element = new Element(
            $uri,
            $localName,
            $qName,
            $attributes,
            xml_get_current_line_number($this->parse->getXmlParser()),
            xml_get_current_column_number($this->parse->getXmlParser())
        );
        if (empty($this->elementStack)) {
            $this->parse->setRootElement($element);
        } else {
            $this->elementStack[0]->add($element);
        }

        array_unshift($this->elementStack, $element);
    }

    public function endElement(?string $uri, string $localName, string $qName): void
    {
        array_shift($this->elementStack);
    }

    public function characters(string $data): void
    {
        $this->elementStack[0]->appendText($data);
    }
}
