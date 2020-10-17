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
    private $params = null;

    public function __construct($name, $type = self::TYPE_TEXT, $params = [])
    {
        $this->name($name);
        $this->type($type);

        if ($type == self::TYPE_PARAM) {
            $this->params($params);
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
                $valid = true;
                $values = [];
                $textArr = Helpers::strToArray($text, ';'); // TODO: make ';' configurable

                $i = 0;
                foreach ($textArr as $valText) {
                    $valText = trim($valText);
                    if (!$valText) {
                        continue;
                    }

                    $values[$i] = [
                        'valid'     => false,
                        'replaced'  => false,
                        'id'        => null,
                        'value'     => null,
                        'text'      => $valText,
                    ];

                    // $valValid = false;
                    $replaced = false;
                    $param = null;

                    // TODO: Unknown opts search
                    // $replaced = true;
                    // $param = [];

                    // Params search
                    if (!$replaced) {
                        $param = Helpers::findInParams($valText, $field->getParams());
                    }

                    if ($param) {
                        $values[$i]['valid'] = true;
                        $values[$i]['replaced'] = $replaced;
                        $values[$i]['id'] = $param['id'];
                        $values[$i]['value'] = $param['value'];

                    // Unknown opts
                    } else {
                        $unknownOpts[] = $valText;
                    }

                    if (!$values[$i]['valid']) {
                        $valid = false;
                    }

                    $i++;
                }

                    // Проверяем на повторки
                    /*
                    foreach ($field['value'] as $i => $value) {
                        if ($valuesArr && in_array($value['value'], $valuesArr)) {
                            $field['value'][$i]['valid'] = false;
                            // Если не стоит флаг "Игнорировать ошибки"
                            if (!$opts['skipOptErr']) {
                                $fieldValid = false;
                            }
                        }
                        $valuesArr[]    = $value['value'];
                    }
                    // Проверяем есть ли хотя бы 1н валидный параметр (валидный и не пропущенный)
                    $goodFields = false;
                    foreach ($field['value'] as $i => $value) {
                        if ($value['valid'] && !$value['skip']) {
                            $goodFields = true;
                        }
                    }
                    if (!$goodFields) {
                        $field['optError'] = true; // Специальный флаг только для полей opts, показывает что нет ни одного валидного параметра или все параметры skipped
                        $fieldValid = false;
                    }

                    // Добавляем optionId
                    $field['optId'] = $optionId;
                    $field['batch'] = $opts['isBatch'];
                    */


                $result['valid'] = $valid;
                $result['value'] = $values;

            }

        }

        return [$result, $unknownOpts];
    }

}