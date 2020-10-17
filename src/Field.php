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

    public static function parse(Field $field = null, $text = '', $opts = [])
    {
        $result = [
            'text'  => $text,
            'name'  => null,
            'type'  => null,
        ];
        $unknownOpts = [];

        if ($field) {
            $result['name'] = $field->getName();
            $result['type'] = $field->getType();

            // Text field
            if ($field->is('text')) {
                $valid = true;
                $value = trim($text);

                if ($field->isRequired() && !$value) {
                    $valid = false;
                }

                $result['valid'] = $valid;
                $result['value'] = $value;

            // Params field
            } elseif ($field->is(self::TYPE_PARAM)) {
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

                    $replacement = $field->findInReplacements($valText);
                    if ($replacement === -1) {
                        $pSkip = true;
                    } elseif ($replacement && is_array($replacement)) {
                        $pReplace = true;
                        $param = $replacement;
                    }
                    if (!$param) {
                        $param = Helpers::findInParams($valText, $field->getParams());
                    }

                    if ($param) {
                        $pValid = true;
                    } else {
                        $unknownOpts[] = $valText;
                    }

                    // Always valid if skipped
                    // $pValid = $pSkip ? true : $pValid;

                    $values[$i] = [
                        'valid'     => $pValid,
                        'skip'      => $pSkip,
                        'replace'   => $pReplace,
                        'id'        => $param['id'],
                        'value'     => $param['value'],
                        'text'      => $valText,
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

                $result['valid'] = $valid;
                $result['value'] = $values;
            }

        }

        return [$result, $unknownOpts];
    }

}