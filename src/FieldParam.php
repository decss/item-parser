<?php


namespace ItemParser;

use ItemParser\Helpers;

class FieldParam extends FieldAbstract
{
    /**
     * @var array Field params (values)
     */
    private $params = [];

    /**
     * @var array Params (values) from CSV that was not found in $params
     */
    private $missing = [];

    /**
     * @var string[] Parameter values delimiters
     */
    private $delimiters = [';', ','];

    public function __construct($name, $type, $params = [])
    {
        parent::__construct($name, $type);

        if ($params[0]) {
            $this->params($params[0]);
        }
        if ($params[1]) {
            $this->missing($params[1]);
        }
    }

    /**
     * Set params
     *
     * @param array $params
     * @return $this
     */
    public function params($params)
    {
        $this->params = $params;
        $this->normalizeAlias();
        return $this;
    }

    /**
     * Set missing array while adding field
     *
     * @param array $missing
     * @return $this
     */
    public function missing($missing)
    {
        $this->missing = $missing;
        return $this;
    }

    /**
     * Set parameter values delimiters.
     * If set 2 or more delimiters - the most frequently occurring in the cell will be chosen
     *
     * @param array|string $delimiters
     * @return $this
     */
    public function delimiters($delimiters)
    {
        if (is_string($delimiters)) {
            $delimiters = [$delimiters];
        }
        $this->delimiters = $delimiters;

        return $this;
    }

    /**
     * Get params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get missing array
     *
     * @return array
     */
    public function getMissing()
    {
        return $this->missing;
    }

    /**
     * Set missing after CSV was processed
     *
     * @param $array
     */
    public function setMissing($array)
    {
        // Filter missing params (from $_POST) by real missing from SCV
        foreach ($this->missing as $param => $val) {
            if (!in_array($param, $array)) {
                unset($this->missing[$param]);
            }
        }

        foreach ($array as $param) {
            if (!isset($this->missing[$param])) {
                $this->missing[$param] = 0;
            }
        }
    }

    /**
     * Check if Field has missing
     *
     * @return bool
     */
    public function hasMissing()
    {
        return count($this->missing) ? true : false;
    }

    /**
     * Try to search Value in $this->missing array
     *
     * @param $valText
     * @return int|null
     */
    private function findInMissing($valText)
    {
        foreach ($this->getMissing() as $replacement => $id) {
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
        $textArr = Helpers::strToArray($text, $this->delimiters); // TODO: make ';' configurable

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

            $replacement = $this->findInMissing($valText);
            if ($replacement === -1) {
                $pSkip = true;
            } elseif ($replacement && is_array($replacement)) {
                $pReplace = true;
                $param = $replacement;
            }
            if (!$param) {
                $param = Helpers::findInParams($valText, $this->getParams(), $this->getName());
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

    /**
     * Normalize $this->params aliases
     */
    private function normalizeAlias()
    {
        foreach ($this->params as $i => $param) {
            if ($param['alias']) {
                foreach ($param['alias'] as $a => $alias) {
                    $this->params[$i]['alias'][$a] = Helpers::normalizeStr($alias);
                }
            }
        }
    }

}