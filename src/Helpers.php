<?php
namespace ItemParser;

class Helpers
{
    static function mb_detect_encoding ($string, $enc = null, $ret = null)
    {
        static $enclist = [
            'UTF-8', 'ASCII', 
            'Windows-1251', 'Windows-1252', 'Windows-1254',
            'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5',
            'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10',
            'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'ISO-8859-16',
        ];
       
        $result = false;
       
        foreach ($enclist as $item) {
            $sample = iconv($item, $item, $string);
            if (md5($sample) == md5($string)) {
                if ($ret === NULL) {
                    $result = $item;
                } else {
                    $result = true;
                }
                break;
            }
        }
       
        return $result;
    } 


    /**
     * Convert string into array using costum delimiter
     * @param string $str String to implode
     * @param string $delimiter optional Delimiter
     * @param string $filter optional Filter to apply to values ['int'|'mixed']
     * @return array
     */
    static function strToArray($str, $delimiter = null, $filter = 'mixed') {
        if (is_array($str)) {
            return $str;
        }

        $delimiterArr   = array(';', ',');
        $array          = array();

        if (strlen($str)) {
            if ($delimiter == null) {
                foreach ($delimiterArr AS $symbol) {
                    $num[]  = substr_count($str, $symbol);
                }
                $delimiter  = $delimiterArr[array_keys($num, max($num))[0]];
            }
            $arr    = stristr($str, $delimiter) ? explode($delimiter, $str) : array($str);
            foreach ($arr as $value) {
                $value  = ($filter == 'int') ? intval($value) : $value;
                if (isset($value)) {
                    $array[] = $value;
                }
            }
        }

        return $array;
    }


    public static function findInOptions($text, $options) {
        static $cache   = [];

        if (!$cache[$text]) {
            $cache[$text] = self::normalizeStr($text);
        }

        foreach ($options as $option) {
            $value = $option['value'];
            if (!$cache[$value]) {
                $cache[$value] = self::normalizeStr($value);
            }

            if ($cache[$text] == $cache[$value]) {
                return $option;
            }
            if ($option['alias'] && in_array($cache[$text], $option['alias'])) {
                return $option;
            }
        }
        return false;
    }

    public static function normalizeStr($str) {
        $res = mb_strtolower(trim($str));
        $res = str_replace('ё', 'е', $res);
        $res = preg_replace(['~\s{2,}~', '~[\t\n]~'], ' ', $res);
        return $res;
    }

}