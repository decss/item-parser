<?php


namespace ItemParser;

use ItemParser\Helpers;

class FieldText extends FieldAbstract
{
    protected function parseField($text)
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


}