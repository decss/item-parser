<?php


namespace ItemParser;

use ParseCsv\Csv;
use ItemParser\FieldAbstract as Field;
use ItemParser\Helpers;


class Parser
{
    // TODO: add getMissing() method

    /**
     * @var integer Count of parsed rows
     */
    private $rowsCnt;

    /**
     * @var integer Total columns
     */
    private $colsCnt;

    /**
     * @var array Parser Fields
     */
    private $fields;

    /**
     * @var array Fields corresponding to columns
     */
    private $fieldsOrder;

    /**
     * @var array CSV array
     */
    private $data;

    /**
     * @var array Parser result
     */
    private $result;

    /**
     * @var array Rows to skip (first row = 0)
     */
    private $skipRows = [];

    public function __construct($path = null)
    {
        if ($path) {
            $this->setCsvPath($path);
        }
    }

    /**
     * Add Text field to parser
     *
     * @param string $name Field name for input name and parse result array
     * @param array $opts
     * @return FieldText
     */
    public function textField($name, $opts = [])
    {
        $field = new FieldText($name, Field::TYPE_TEXT);
        $this->fieldsOrder[] = $name;
        $this->fields[$name] = $field;

        return $field;
    }

    /**
     * Add Param field to parser
     *
     * @param string $name Field name for input name and parse result array
     * @param array $params Params array like [$params, $missing] or just [$params]
     * @return FieldParam
     */
    public function paramField($name, $params = [])
    {
        $field = new FieldParam($name, Field::TYPE_PARAM, $params);
        $this->fieldsOrder[] = $name;
        $this->fields[$name] = $field;

        return $field;
    }

    /**
     * Set fields order to apply each field to corresponding column in CSV
     *
     * @param array $order
     */
    public function fieldsOrder($order)
    {
        $this->fieldsOrder = $order;
    }

    /**
     * Get field by index
     *
     * @param int $index
     * @return Field
     */
    public function getField($index)
    {
        $name = $this->fieldsOrder[$index];
        return $this->fields[$name];
    }

    /**
     * Get field by name
     *
     * @param string $name
     * @return Field
     */
    public function getFieldByName($name)
    {
        return $this->fields[$name];
    }

    /**
     * Get all fields
     *
     * @param string $mode Get all fields or fields set by  fieldsOrder()
     * @return array
     */
    public function getFields($mode = 'all')
    {
        if ($mode == 'all') {
            return $this->fields;

        } elseif ($mode == 'selected') {
            $result = [];
            foreach ($this->fields as $field) {
                $name = $field->getName();
                if (in_array($name, $this->fieldsOrder)) {
                    $result[$name] = $field;
                }
            }
            return $result;
        }
    }

    /**
     * Skipped rows will be marked as "skip" in parse results, params will not processed
     *
     * @param integer|array $skipRows Rows to skip, 0 - first row, 1 - second, ...
     */
    public function skipRows($skipRows)
    {
        if (!is_array($skipRows)) {
            $skipRows = [intval($skipRows)];
        }
        $this->skipRows = $skipRows;
    }

    /**
     * Set CSV file path
     *
     * @param string $path Path to CSV file
     */
    public function setCsvPath($path)
    {
        $content = file_get_contents($path);
        $this->setCsvContent($content);
    }

    /**
     * Set CSV file content
     *
     * @param string $content Raw CSV content in text format
     */
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

    /**
     * Parse CSV and return results
     *
     * @return array
     */
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
                    Helpers::mergeMissing($missing[$fieldObj->getName()], $fieldMissing);
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

        // Remove duplicates and set missing property
        foreach ($missing as $name => $opts) {
            $field = $this->getFieldByName($name);
            $field->setMissing(array_values(array_unique($missing[$name])));
        }

        $this->result = $result;

        return $this->result;
    }

    /**
     * Get parse results
     *
     * @return array
     */
    public function result()
    {
        return $this->result;
    }

    /**
     * Get parsed rows count
     *
     * @return integer
     */
    public function rows()
    {
        return $this->rowsCnt;
    }

    /**
     * Get parsed cols count
     *
     * @return integer
     */
    public function cols()
    {
        return $this->colsCnt;
    }

    private function setData($array)
    {
        $this->rowsCnt = count($array);
        $this->colsCnt = count($array[0]);
        $this->data = $array;
    }

}
