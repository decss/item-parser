<?php


namespace ItemParser;

use ItemParser\Parser;
use ItemParser\FieldAbstract as Field;


class Drawer
{
    /**
     * @var \ItemParser\Parser
     */
    private $parser;

    public function __construct(Parser $parser)
    {
        if ($parser) {
            $this->setParser($parser);
        }
    }

    public function setParser(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function head($format = 'html')
    {
        $result = null;
        $items = [];

        $parser = $this->parser;
        for ($i = 0; $i < $parser->cols(); $i++) {
            $field = $parser->getField($i);
            $fieldName = self::fieldName($field);

            $items[] = [
                'index' => $i,
                'value' => (string)$fieldName,
            ];
        }

        if ($format == 'html') {
            $result .= '<td>#</td>';
            foreach ($items as $item) {
                $result .= '<td>' . $item['value'] . '</td>';
            }
            $result = '<tr>' . $result . '</tr>';
        } elseif ($format == 'json') {
            $result = json_encode($items);
        } elseif ($format == 'array') {
            $result = $items;
        }

        return $result;
    }

    public function body()
    {
        $parser = $this->parser;
        $res = ($parser->result())['result'];

        $items = [];
        for ($r = 0; $r < $parser->rows(); $r++) {
            $cells = '<td>' . $res[$r]['row'] . '</td>';
            $valid = true;

            for ($i = 0; $i < $parser->cols(); $i++) {
                $field = $parser->getField($i);
                $value = $res[$r]['fields'][$i];
                $cells .= self::drawCell($value, $field);
            }

            $trCls = null;
            if (!$res[$r]['valid']) {
                $trCls = 'invalid';
            }

            $result .= '<tr' . ($trCls ? ' class="' . $trCls . '"' : '') . '>' . $cells . '</tr>';
        }

        return $result;
    }

    private static function drawCell($value, Field $field = null)
    {
        $tdCls = null;

        if ($field) {
            if ($field->is(Field::TYPE_TEXT)) {
                $text = $value['value'];
            } elseif ($field->is(Field::TYPE_PARAM)) {
                $text = self::drawTags($value['value']);

            }
            if (!$value['valid']) {
                $tdCls = 'invalid';
            }

        } else {
            $text = $value['text'];
            $tdCls = 'skipped';
        }

        $cell = '<td' . ($tdCls ? ' class=' . $tdCls : '') . '>' . $text . '</td>';

        return $cell;
    }

    private static function drawTags($items)
    {
        $tags = '';
        foreach ($items as $item) {
            $tagCls = self::getTagCls($item);

            if ($item['valid']) {
                $text = $item['value'];
            } else {
                $text = $item['text'];
            }
            $tags .= '<span class="tag' . ($tagCls ? ' ' . $tagCls : '') . '">' . $text . '</span>';
        }

        return $tags;
    }

    private static function getTagCls($item)
    {
        $tagCls = null;

        if ($item['skip']) {
            $tagCls = 'skipped';

        } elseif ($item['replace']) {
            $tagCls = 'replaced';

        } elseif (!$item['valid']) {
            $tagCls = 'invalid';
        }

        return $tagCls;
    }


    private static function fieldName(Field $field = null)
    {
        if ($field) {
            return $field->getName();
        }
        return '';
    }


}