<?php


namespace ItemParser;

use ItemParser\Helpers;

class FieldText extends FieldAbstract
{
    protected function parseField($text)
    {
        $result = self::getResultArray($text, $this->getName(), $this->getType());

        $valid = true;
        $value = trim($text);

        if ($this->isRequired() && !$value) {
            $valid = false;
        }

        $result['valid'] = $valid;
        $result['value'] = $value;

        return [$result, null];
    }


}