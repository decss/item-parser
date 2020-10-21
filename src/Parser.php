<?php


namespace ItemParser;

use ParseCsv\Csv;
use ItemParser\FieldAbstract as Field;
use ItemParser\Helpers;


class Parser
{
    private $rowsCnt;
    private $colsCnt;
    private $fields;
    private $fieldsOrder;
    private $data;
    private $result;
    private $skipRows = [];

    public function textField($name, $opts = [])
    {
        $field = new FieldText($name, Field::TYPE_TEXT);
        $this->fieldsOrder[] = $name;
        $this->fields[$name] = $field;

        return $field;
    }
    public function paramField($name, $params = [])
    {
        $field = new FieldParam($name, Field::TYPE_PARAM, $params);
        $this->fieldsOrder[] = $name;
        $this->fields[$name] = $field;

        return $field;
    }

    public function fieldsOrder($order)
    {
        $this->fieldsOrder = $order;

    }

    public function getField($index)
    {
        $name = $this->fieldsOrder[$index];
        return $this->fields[$name];
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function skipRows($skipRows)
    {
        if (!is_array($skipRows)) {
            $skipRows = [intval($skipRows)];
        }
        $this->skipRows = $skipRows;
    }

    public function setCsvPath($path)
    {
        $content = file_get_contents($path);
        $this->setCsvContent($content);
    }

    public function setCsvContent($content)
    {
        $csv = new Csv();
        $encoding = Helpers::mbDetectEncoding($content);
        $csv->heading = false;
        $csv->use_mb_convert_encoding = true;
        $csv->encoding($encoding, 'UTF-8');
        $csv->auto($content, true, null, ';,');

        $this->setData($csv->data);
    }

    public function setData($array)
    {
        $this->rowsCnt = count($array);
        $this->colsCnt = count($array[0]);
        $this->data = $array;
    }

    public function parse()
    {
        $result = [];
        $missing = [];

        for ($r = 0; $r < $this->rowsCnt; $r++) {
            $rowFields = $this->data[$r];
            $valid = true;
            $skip = in_array($r, $this->skipRows) ? true : false;
            $fields = [];

            foreach ($rowFields as $f => $text) {
                $fieldObj = $this->getField($f);
                list($fields[$f], $fieldMissing) = Field::parse($fieldObj, $text);

                if (!$skip && $fieldMissing) {
                    Helpers::mergeMissing($missing[$f], $fieldMissing);
                }

                if ($fieldObj && !$fields[$f]['valid']) {
                    $valid = false;
                }
            }

            $result[] = [
                'row' => ($r + 1),
                'valid' => $valid,
                'skip' => $skip,
                'fields' => $fields,
            ];
        }

        // Remove duplicates
        foreach ($missing as $f => $opts) {
            $missing[$f] = array_values(array_unique($missing[$f]));
        }

        $this->result = [
            'result' => $result,
            'missing' => $missing,
        ];

        return $this->result;
    }

    public function result()
    {
        return $this->result;
    }

    public function rows()
    {
        return $this->rowsCnt;
    }

    public function cols()
    {
        return $this->colsCnt;
    }

}
