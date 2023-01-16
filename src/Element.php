<?php

namespace Sax;

class Element
{
    protected $prefix;
    protected $tagName;

    protected $attributeMap = [];
    protected $line;
    protected $column;
    protected $text = "";
    protected $elements = [];

    public function __construct(?string $prefix, string $localName, string $qName, array $attributes, int $line, int $column)
    {
        $this->prefix = $prefix;
        $this->tagName = $prefix === null ? $qName : $localName;
        if (!empty($attributes)) {
            foreach ($attributes as $name => $value) {
                $this->attributeMap[$name] = new Attribute($name, $value);
            }
        }

        $this->line = $line;
        $this->column = $column;
    }

    public function elements(?string $tagName = null): array
    {
        return $this->elementsNS(null, $tagName);
    }

    public function elementsNS($nameSpace, ?string $tagName = null): array
    {
        $elementsNS = [];
        if ($nameSpace instanceof XmlNamespace) {
            $elementsNS = $this->elementsNS($nameSpace->getNamespaceUri(), $tagName);
            if (empty($elementsNS) && $nameSpace->hasAlternativeUri()) {
                $elementsNS = $this->elementsNS($nameSpace->getAlternativeUri(), $tagName);
            }
        } elseif ($nameSpace === null || is_string($nameSpace)) {
            foreach ($this->elements as $element) {
                if ($tagName !== null && strtoupper($tagName) == strtoupper($element->getTagName())) {
                    if ($nameSpace === null || $nameSpace == $element->getUri()) {
                        $elementsNS[] = $element;
                    }
                } elseif ($tagName === null) {
                    $elementsNS[] = $element;
                }
            }
        }

        return $elementsNS;
    }

    public function element(string $tagName): ?Element
    {
        $elements = $this->elements($tagName);
        if (empty($elements)) {
            return null;
        } elseif (count($elements) > 1) {
            throw new \Exception("Parsing exception: multiple elements with tag name " . $tagName . " found");
        }
        return $elements[0];
    }

    public function elementNS(string | XmlNamespace $nameSpace, string $tagName): ?Element
    {
        if (is_string($nameSpace)) {
            $nameSpace = new XmlNamespace($nameSpace);
        }
        $elements = $this->elementsNS($nameSpace, $tagName);
        if (count($elements) == 0) {
            return null;
        } elseif (count($elements) > 1) {
            throw new \Exception("Parsing exception: multiple elements with tag name " . $tagName . " found");
        }
        return $elements[0];
    }

    public function add(Element $element): void
    {
        $this->elements[] = $element;
    }

    public function attribute(string $name, ?string $defaultValue = null): ?string
    {
        if (array_key_exists($name, $this->attributeMap)) {
            return $this->attributeMap[$name]->getValue();
        }
        $lcname = strtolower($name);
        if (array_key_exists($lcname, $this->attributeMap)) {
            return $this->attributeMap[$lcname]->getValue();
        }
        $luname = strtoupper($name);
        if (array_key_exists($luname, $this->attributeMap)) {
            return $this->attributeMap[$luname]->getValue();
        }
        return $defaultValue;
    }

    public function attributeNS($namespace, string $name, string $defaultValue = null): ?string
    {
        $attribute = $this->attribute($this->composeMapKey($namespace, $name));
        if ($attribute === null && ($namespace instanceof XmlNamespace && $namespace->hasAlternativeUri())) {
            $attribute = $this->attribute($this->composeMapKey($namespace->getAlternativeUri(), $name));
        }
        if ($attribute === null) {
            return $defaultValue;
        }
        return $attribute;
    }

    protected function composeMapKey(?string $attributeUri, string $attributeName): string
    {
        $strb = "";
        if (!empty($attributeUri)) {
            $strb .= $attributeUri;
            $strb .= ":";
        }
        $strb .= $attributeName;
        return $strb;
    }

    public function attributes(): array
    {
        return array_keys($this->attributeMap);
    }

    public function __toString()
    {
        return "<"  . $this->tagName . "...";
    }

    public function getTagName(): string
    {
        return $this->tagName;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getColumn(): int
    {
        return $this->column;
    }

    public function appendText(string $text): void
    {
        $this->text .= $text;
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * allows to recursively collect the ids of all elements in the tree.
     */
    public function collectIds(array &$ids): void
    {
        $ids[] = $this->attribute("id");
        foreach ($this->elements as $child) {
            $child->collectIds($ids);
        }
    }
}
