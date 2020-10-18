<?php


namespace ItemParser;

use ItemParser\Helpers;

abstract class FieldAbstract
{
    const TYPE_TEXT = 'text';
    const TYPE_PARAM = 'param';

    protected $name;
    protected $type;
    protected $required = false;
    protected $result = [];
    protected $missing = [];

    public function __construct($name, $type = self::TYPE_TEXT, $params = [], $replacements = [])
    {
        $this->name($name);
        $this->type($type);
    }

    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    public function required($required = true)
    {
        $this->required = $required;
        return $this;
    }

    public function text()
    {
        $this->type('text');
        return $this;
    }

    public function param()
    {
        $this->type(self::TYPE_PARAM);
        return $this;
    }

    public function is($type)
    {
        if ($this->type === $type) {
            return true;
        }

        return false;
    }

    public function isRequired()
    {
        return $this->required;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getParseResult()
    {
        return $this->result;
    }

    public function getParseMissing()
    {
        return $this->missing;
    }

    public static function getResultArray($text, $name = null, $type = null)
    {
        return [
            'text' => $text,
            'name' => $name,
            'type' => $type,
        ];
    }

    public static function parse(FieldAbstract $field = null, $text = '')
    {
        $result = static::getResultArray($text);
        $missing = [];

        if ($field) {
            $field->parseField($text);
            $result = $field->getParseResult();
            $missing = $field->getParseMissing();
        }

        return [$result, $missing];
    }

    abstract protected function parseField($text);


}