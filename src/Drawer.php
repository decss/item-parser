<?php


namespace ItemParser;

use ItemParser\Parser;
use ItemParser\FieldAbstract as Field;
use ItemParser\FieldParam;


class Drawer
{
    /**
     * @var \ItemParser\Parser
     */
    private $parser;
    private $options;
    private $hidden = [];
    private static $orderInput = 'parseOrdering';
    private static $missingInput = 'parseMissing';
    private static $textLen = 50;
    private static $cropSkipped = true;


    public function __construct(Parser $parser, $options = [])
    {
        if ($parser) {
            $this->setParser($parser);
        }

        if ($options) {
            $this->options = $options;
            foreach ($options as $name => $option) {
                $field = $this->parser->getFieldByName($name);
                if ($field) {
                    $field->title($option['title']);
                    $field->display($option['display']);
                }
            }
        }
    }

    public function setParser(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function setTextLen($len)
    {
        self::$textLen = intval($len);
    }

    public function cropSkipped($crop = true)
    {
        self::$cropSkipped = $crop;
    }

    /**
     * Set name attribute value for ordering select
     *
     * @param string $name ordering Select element name
     */
    public function setOrderInputName($name)
    {
        self::$orderInput = $name;
    }

    /**
     * Set name attribute value for missing select
     *
     * @param string $name missing Select element name
     */
    public function setMissingInputName($name)
    {
        self::$missingInput = $name;
    }

    public function head($format = 'html', $opts = [])
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

        } elseif ($format == 'select') {
            $result .= '<td>#</td>';
            for ($i = 0; $i < $parser->cols(); $i++) {
                $result .= '<td>' . $this->drawSelect($i) . '</td>';
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
        $res = $parser->result();

        $hidden = [];
        for ($r = 0; $r < $parser->rows(); $r++) {
            if (in_array($r, $this->hidden)) {
                $hidden['cnt']++;
                $hidden['min'] = !$hidden['min'] ? $res[$r]['row'] : $hidden['min'];
                $hidden['max'] = $res[$r]['row'];
                continue;
            }
            if ($hidden) {
                $result .= self::drawHiddenRow($hidden, $parser->cols());
            }

            $cells = '<td>' . $res[$r]['row'] . '</td>';
            $valid = true;

            for ($i = 0; $i < $parser->cols(); $i++) {
                $field = $parser->getField($i);
                $value = $res[$r]['fields'][$i];
                $cells .= self::drawCell($value, $field, ['skipRow' => $res[$r]['skip']]);
            }

            $trCls = null;

            if ($res[$r]['skip']) {
                $trCls = 'skipped';

            } elseif (!$res[$r]['valid']) {
                $trCls = 'invalid';
            }

            $result .= '<tr' . ($trCls ? ' class="' . $trCls . '"' : '') . '>' . $cells . '</tr>';
        }
        if ($hidden) {
            $result .= self::drawHiddenRow($hidden, $parser->cols());
        }

        return $result;
    }

    private static function drawHiddenRow(&$hidden, $cols)
    {
        if ($hidden['min'] != $hidden['max']) {
            $num = $hidden['min'] . '..' . $hidden['max'];
        } else {
            $num = $hidden['min'];
        }

        $result = '<tr><td>' . $num . '</td><td class="hidden" colspan="' . ($cols) . '">'
            . '... hidden <b>' . $hidden['cnt'] . '</b> line(s) ...</td></tr>';
        $hidden = [];
        return $result;
    }

    public function missing()
    {
        $table = '';

        $fields = $this->parser->getFields('selected');
        foreach ($fields as $field) {
            if ($field instanceof FieldParam && $field->hasMissing()) {
                $table .= '<table>';
                $table .= '<tr><td colspan="2"><b>' . self::fieldName($field) . '</b></td></tr>';
                foreach ($field->getMissing() as $name => $value) {
                    $table .= '<tr><td>' . $name . '</td>'
                        . '<td>' . self::drawValues($field, $name, $value) . '</td></tr>';
                }
                $table .= '</table>';

            }
        }

        return $table;
    }

    public function hideValid()
    {
        foreach ($this->parser->result() as $r => $row) {
            if ($row['valid']) {
                $rows[] = $r;
            }
        }
        $this->hideRows($rows);
    }

    public function hideInvalid()
    {
        foreach ($this->parser->result() as $r => $row) {
            if (!$row['valid']) {
                $rows[] = $r;
            }
        }
        $this->hideRows($rows);
    }

    /**
     * Set rows that will not be displayed
     *
     * @param array $rows
     */
    public function hideRows($rows)
    {
        if ($rows && !is_array($rows)) {
            $rows = [$rows];
        }
        $this->hidden = $rows;
    }

    private static function drawValues(FieldParam $field, $name, $value)
    {
        $options = '<option value="0">-</option>'
            . '<option value="-1" ' . ($value == -1 ? 'selected' : '') . '>-- Skip --</option>';
        foreach ($field->getParams() as $param) {
            $select = $param['id'] == $value ? 'selected' : '';
            $options .= '<option value="' . $param['id'] . '" ' . $select . '>' . $param['value'] . '</option>';
        }
        $name = htmlspecialchars($name);
        $select = '<select name="' . self::$missingInput . '[' . $field->getName() . '][' . $name . ']">'
            . $options
            . '</select>';

        return $select;
    }

    private function drawSelect($index)
    {
        $parser = $this->parser;
        $currentField = $parser->getField($index);
        $options = '';

        foreach ($parser->getFields() as $field) {
            $optCls = '';
            $selected = $field === $currentField ? 'selected' : '';
            $name = self::fieldName($field);
            if ($field->isRequired()) {
                $name .= ' *';
                $optCls = 'class="required"';
            }
            $options .= "<option value=\"{$field->getName()}\" {$optCls} {$selected}>{$name}</option>";
        }


        $select = '<select name="' . self::$orderInput . '[' . $index . ']">'
            . '<option value="">-</option>'
            . $options
            . '</select>';

        return $select;
    }

    private static function drawCell($value, Field $field = null, $opts = [])
    {
        $tdCls = null;

        if (!$field || $opts['skipRow']) {
            $text = $value['text'];
            if (self::$cropSkipped) {
                $text = self::drawCellText($text, 'text');
            }
            $tdCls = 'skipped';

        } elseif ($field) {
            if ($field->is(Field::TYPE_TEXT)) {
                $text = self::drawCellText($value['value'], $field->getDisplay());
            } elseif ($field->is(Field::TYPE_PARAM)) {
                $text = self::drawTags($value['value']);

            }
            if (!$value['valid']) {
                $tdCls = 'invalid';
            }
        }

        return '<td' . ($tdCls ? ' class=' . $tdCls : '') . '>' . $text . '</td>';
    }

    private static function drawCellText($value, $display)
    {
        $text = '';

        if ($display == 'link') {
            if ($value) {
                $text = '<a href="' . $value . '" target="_blank">Link</a>';
            }

        } elseif ($display == 'image') {
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                $link = basename(parse_url($value, PHP_URL_PATH));
            } else {
                $link = $value;
            }
            $text = '<a href="' . $value . '" target="_blank">' . $link . '</a>';

        } elseif ($display == 'text') {
            if (mb_strlen($value) > self::$textLen) {
                $text = substr($value, 0, self::$textLen) . ' ...';
            } else {
                $text = $value;
            }

        } else {
            $text = $value;
        }

        return $text;
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
            return $field->getTitle() ? $field->getTitle() : $field->getName();
        }
        return '';
    }

}