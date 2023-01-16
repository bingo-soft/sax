<?php

namespace Sax;

class SaxParser
{
    private static $instance;

    private static $uris = [];

    private $xmlParser;

    private $handler;

    private $isFree;

    private function __construct($xmlParser)
    {
        $this->xmlParser = $xmlParser;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new SaxParser(xml_parser_create());
            self::$instance->free(false);
        }
        return self::$instance;
    }

    public function isFree(): bool
    {
        return $this->isFree;
    }

    public function free(bool $flag = true): void
    {
        if ($flag && $this->xmlParser !== null) {
            xml_parser_free($this->xmlParser);
            $this->xmlParser = null;
            $this->isFree = $flag;
        } elseif (!$flag) {
            $this->isFree = false;
        }
    }

    public function init(): void
    {
        $this->xmlParser = xml_parser_create();
        $this->isFree = false;
    }

    public function getXmlParser()
    {
        if ($this->xmlParser === null) {
            $this->init();
        }
        return $this->xmlParser;
    }

    public function parse($streamSource, DefaultHandlerInterface $handler): void
    {
        $meta = stream_get_meta_data($streamSource);
        $data = fread($streamSource, filesize($meta['uri']));

        xml_set_element_handler($this->getXmlParser(), function ($parser, $qName, $attribs) use ($handler) {
            $this->parseUris($attribs);
            $parts = explode(':', $qName);
            $localName = array_pop($parts);
            $uri = null;
            if (!empty($parts) && array_key_exists($parts[0], self::$uris)) {
                $uri = self::$uris[$parts[0]];
            }
            $handler->startElement($uri, $localName, $qName, $attribs);
        }, function ($parser, $qName) use ($handler) {
            $parts = explode(':', $qName);
            $localName = array_pop($parts);
            $uri = null;
            if (!empty($parts) && array_key_exists($parts[0], self::$uris)) {
                $uri = self::$uris[$parts[0]];
            }
            $handler->endElement($uri, $localName, $qName);
        });

        xml_set_character_data_handler($this->getXmlParser(), function ($parser, $data) use ($handler) {
            $handler->characters($data);
        });

        xml_parse($this->getXmlParser(), $data);

        fclose($streamSource);
    }

    private function parseUris(array $attributes): void
    {
        foreach ($attributes as $qName => $value) {
            $parts = explode(':', $qName);
            if (strtoupper($parts[0]) == 'XMLNS') {
                if (count($parts) == 2) {
                    self::$uris[strtoupper($parts[1])] = $value;
                } else {
                    self::$uris[""] = $value;
                }
            }
        }
    }
}
