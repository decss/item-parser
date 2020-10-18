<?php

namespace ItemParser;

use ItemParser\Helpers;

class Field
{
    const TYPE_TEXT = 'text';
    const TYPE_PARAM = 'param';
    private $name;
    private $type;
    private $required = false;
    private $params = [];
    private $replacements = [];
    private $result = [];
    private $missing = [];

    public function __construct($name, $type = self::TYPE_TEXT, $params = [], $replacements = [])
    {
        $this->name($name);
        $this->type($type);

        if ($type == self::TYPE_PARAM) {
            $this->params($params);
            $this->replacements($replacements);
        }
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

    public function params($params)
    {
        $this->params = $params;
        return $this;
    }

    public function replacements($replacements)
    {
        $this->replacements = $replacements;
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

    public function getParams()
    {
        return $this->params;
    }

    public function getReplacements()
    {
        return $this->replacements;
    }

    public function getParseResult()
    {
        return $this->result;
    }

    public function getParseMissing()
    {
        return $this->missing;
    }

    public function findInReplacements($valText)
    {
        foreach ($this->getReplacements() as $replacement => $id) {
            if ($replacement == $valText) {
                if ($id == -1) {
                    return -1;
                } elseif ($id > 0) {
                    return Helpers::getById($id, $this->getParams());
                }
            }
        }
        return null;
    }

    private function parseText($text)
    {
        $this->result = self::getResultArray($text, $this->getName(), $this->getType());

        $valid = true;
        $value = trim($text);

        if ($this->isRequired() && !$value) {
            $valid = false;
        }

        $this->result['valid'] = $valid;
        $this->result['value'] = $value;
    }

    private function parseParam($text)
    {
        $this->result = self::getResultArray($text, $this->getName(), $this->getType());

        $valid = false;
        $values = [];
        $textArr = Helpers::strToArray($text, ';'); // TODO: make ';' configurable

        $i = 0;
        foreach ($textArr as $valText) {
            $valText = trim($valText);
            if (!$valText) {
                continue;
            }

            $pValid = false;
            $pSkip = false;
            $pReplace = false;
            $param = [];

            $replacement = $this->findInReplacements($valText);
            if ($replacement === -1) {
                $pSkip = true;
            } elseif ($replacement && is_array($replacement)) {
                $pReplace = true;
                $param = $replacement;
            }
            if (!$param) {
                $param = Helpers::findInParams($valText, $this->getParams());
            }

            if ($param) {
                $pValid = true;
            } else {
                $this->missing[] = $valText;
            }

            // Always valid if skipped
            // $pValid = $pSkip ? true : $pValid;

            $values[$i] = [
                'valid' => $pValid,
                'skip' => $pSkip,
                'replace' => $pReplace,
                'id' => $param['id'],
                'value' => $param['value'],
                'text' => $valText,
            ];

            $i++;
        }

        // Check and skip duplicates
        $tmp = [];
        foreach ($values as $i => $value) {
            if ($value['valid'] == false || $value['skip'] == true) {
                continue;
            }
            if (!in_array($value['id'], $tmp)) {
                $tmp[] = $value['id'];
            } else {
                $values[$i]['skip'] = true;
            }
        }

        // Check for at least one valid and not skipped param
        foreach ($values as $i => $value) {
            if ($value['valid'] == true && $value['skip'] == false) {
                $valid = true;
            }
        }

        // Valid
        if (!$this->isRequired()) {
            $valid = true;
        }

        $this->result['valid'] = $valid;
        $this->result['value'] = $values;
    }

    public static function getResultArray($text, $name = null, $type = null)
    {
        return [
            'text' => $text,
            'name' => $name,
            'type' => $type,
        ];
    }

    public static function parse(Field $field = null, $text = '', $opts = [])
    {
        $result = static::getResultArray($text);
        $missing = [];

        if ($field) {
            // Text field
            if ($field->is(self::TYPE_TEXT)) {
                $field->parseText($text);

                // Params field
            } elseif ($field->is(self::TYPE_PARAM)) {
                $field->parseParam($text);
            }

            $result = $field->getParseResult();
            $missing = $field->getParseMissing();
        }

        return [$result, $missing];
    }


}