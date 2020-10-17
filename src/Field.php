<?php 
namespace ItemParser;

use ItemParser\Helpers;

class Field
{
    private $name;
    private $type;
    private $required = false;
    private $options = null;

    public function __construct($name, $type = 'text', $options = [])
    {
        $this->name($name);
        $this->type($type);

        if ($type == 'option') {
            $this->options($options);
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
    public function options($options)
    {
        $this->options = $options;
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
    public function option()
    {
        $this->type('option');
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
    public function getOptions()
    {
        return $this->options;
    }

    public static function parse(Field $field = null, $text)
    {
        $result = [
            'text'  => $text,
            'name'  => null,
            'type'  => null,
        ];

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

            // Options field
            } elseif ($field->is('option')) {
                $valid = true;
                $values = [];
                $textArr = Helpers::strToArray($text, ';'); // TODO: make ';' configurable

                $i = 0;
                foreach ($textArr as $valText) {
                    $values[$i] = [
                        'valid'     => false,
                        'replaced'  => false,
                        'id'        => null,
                        'value'     => null,
                        'text'      => $valText,
                    ];

                    // $valValid = false;
                    $replaced = false;
                    $option = null;

                    // TODO: Unknown opts search
                    // $replaced = true;
                    // $option = [];

                    // options search
                    if (!$replaced) {
                        $option = Helpers::findInOptions($valText, $field->getOptions());
                    }

                    if ($option) {
                        $values[$i]['valid'] = true;
                        $values[$i]['replaced'] = $replaced;
                        $values[$i]['id'] = $option['id'];
                        $values[$i]['value'] = $option['value'];
                    }

                    if (!$values[$i]['valid']) {
                        $valid = false;
                    }
                    
                    $i++;
                }


                $result['valid'] = $valid;
                $result['value'] = $values;

            }

        }

        return $result;
    }

}