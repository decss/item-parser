<?php


namespace ItemParser;

use ItemParser\Helpers;

abstract class FieldAbstract
{
    const TYPE_TEXT = 'text';
    const TYPE_PARAM = 'param';

    protected $name;
    protected $title;
    protected $type;
    protected $required = false;

    public function __construct($name, $type = self::TYPE_TEXT, $opts = [])
    {
        $this->name($name);
        $this->type($type);

        if ($opts['title']) {
            $this->title($opts['title']);
        }
    }

    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    public function title($title)
    {
        $this->title = $title;
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
            list($result, $missing) = $field->parseField($text);
        }

        return [$result, $missing];
    }

    abstract protected function parseField($text);


}