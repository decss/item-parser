<?php 
namespace ItemParser;

use ParseCsv\Csv;
use ItemParser\Field;
use ItemParser\Helpers;


class Parser
{
    const FIELD_TEXT    = 1;
    const FIELD_OPTION  = 2;
    const FIELD_IMAGE   = 3;

    private $rowsCnt;
    private $colsCnt;
    private $fields;
    private $data;
    private $skipFirst  = false;

    // public function textField($index, $name)
    // {
    //     return $this->field($index, $name, 'text');
    // }
    // public function optionField($index, $name, $options = [])
    // {
    //     return $this->field($index, $name, 'option', $options);
    // }
    // public function imageField($index, $name)
    // {
    //     return $this->field($index, $name, 'image');
    // }
    public function field($index, $name = null, $type = 'text', $options = [])
    {
        $field = new Field($name, $type, $options);
        $this->fields[$index] = $field;

        return $field;
    }
    public function getFields()
    {
        return $this->fields;
    }


    public function setCsvPath($path)
    {
        $content = file_get_contents($path);
        $this->setCsvContent($content);
    }

    public function setCsvContent($content)
    {
        $csv            = new Csv();
        $encoding       = Helpers::mb_detect_encoding($content);
        $csv->heading   = false;
        $csv->use_mb_convert_encoding = true;
        $csv->encoding($encoding, 'UTF-8');
        $csv->auto($content, true, null, ';,');

        $this->setArray($csv->data);
    }

    public function setArray($array)
    {
        $this->rowsCnt  = count($array);
        $this->colsCnt  = count($array[0]);
        $this->data     = $array;
    }


    public function parse()
    {
        $result = [];

        for ($r = 0; $r < $this->rowsCnt; $r++) { 
            $rowFields  = $this->data[$r];
            $valid      = true;
            $skip       = false;
            $fields     = [];

            foreach ($rowFields as $f => $field) {
                $fields[$f] = Field::parse($this->fields[$f], $field);
             
                if ($this->fields[$f] && !$fields[$f]['valid']) {
                    $valid = false;
                }

                // $fields[$f] = $this->fields[$f]->parse($field);
            }
            // dbg($fields);

            $result[] = [
                'row'       => $r,
                'valid'     => $valid,
                'skip'      => $skip,
                'fields'    => $fields,
            ];

        }

        return $result;
    }


}
