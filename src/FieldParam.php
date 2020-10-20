<?php


namespace ItemParser;

use ItemParser\Helpers;

class FieldParam extends FieldAbstract
{
    private $params = [];
    private $replacements = [];

    public function __construct($name, $type = self::TYPE_TEXT, $params = [], $replacements = [])
    {
        parent::__construct($name, $type, $params, $replacements);

        $this->params($params);
        $this->replacements($replacements);
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

    public function getParams()
    {
        return $this->params;
    }

    public function getReplacements()
    {
        return $this->replacements;
    }

    private function findInReplacements($valText)
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

    protected function parseField($text)
    {
        $result = self::getResultArray($text, $this->getName(), $this->getType());
        $missing = [];
        $values = [];
        $textArr = Helpers::strToArray($text, ';'); // TODO: make ';' configurable

        $i = 0;
        foreach ($textArr as $valText) {
            // dbg($valText);
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
            }

            if (!$param || $pReplace) {
                $missing[] = $valText;
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

        $valid = true;
        $activeValues = 0;
        // Check for at least one valid and not skipped param
        foreach ($values as $i => $value) {
            if ($value['valid'] && !$value['skip']) {
                $activeValues++;
            }

            if (!$value['valid'] && !$value['skip']) {
                $valid = false;
            }
        }

        // There is no one valid value on required field
        if ($this->isRequired() && $activeValues == 0) {
            $valid = false;

        } elseif (!$this->isRequired()) {
            // $valid = true;
        }

        $result['valid'] = $valid;
        $result['value'] = $values;

        return [$result, $missing];
    }


}