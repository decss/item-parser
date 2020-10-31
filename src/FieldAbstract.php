<?php


namespace ItemParser;

use ItemParser\Helpers;

abstract class FieldAbstract
{
    const TYPE_TEXT = 'text';
    const TYPE_PARAM = 'param';

    /**
     * @var string Field uniq name, required
     */
    protected $name;

    /**
     * @var string Field type: 'text' or 'param'
     */
    protected $type;

    /**
     * @var string Field title (for Drawing purposes)
     */
    protected $title;

    /**
     * @var string Field display mode, empty (simple text), 'text' (cropped text) or 'link' or 'image'
     */
    protected $display;

    /**
     * @var bool Is Field required
     */
    protected $required = false;


    public function __construct($name, $type = self::TYPE_TEXT, $opts = [])
    {
        $this->name($name);
        $this->type($type);
    }

    /**
     * Set Field name
     *
     * @param string $name
     * @return $this
     */
    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set Field type
     *
     * @param string $type 'text' or 'param'
     * @return $this
     */
    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Set Field title (for drawing)
     *
     * @param string $title
     * @return $this
     */
    public function title($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Set Field display mode: 'text' or 'link' or 'image'
     * 'text' - shorten text, 'link' and 'image' - transform URL
     *
     * @param string $display
     * @return $this
     */
    public function display($display)
    {
        $this->display = $display;
        return $this;
    }

    /**
     * Set is Field required or not. Required fields will be invalid if empty
     *
     * @param bool $required
     * @return $this
     */
    public function required($required = true)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Set Field type 'text'
     *
     * @return $this
     */
    public function text()
    {
        $this->type('text');
        return $this;
    }

    /**
     * Set Field type 'param'
     *
     * @return $this
     */
    public function param()
    {
        $this->type(self::TYPE_PARAM);
        return $this;
    }

    /**
     * Check Field type
     *
     * @param string $type Field type: 'text' or 'param'
     * @return bool
     */
    public function is($type)
    {
        if ($this->type === $type) {
            return true;
        }

        return false;
    }

    /**
     * Check Field is required
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Get Field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get Field type: 'text' or 'param'
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get Field title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get Field display: 'text' or 'link' or 'image'
     *
     * @return string
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * Base template of parsed cell array
     *
     * @param string $text
     * @param string $name
     * @param string $type
     * @return array
     */
    public static function getResultArray($text, $name = null, $type = null)
    {
        return [
            'text' => $text,
            'name' => $name,
            'type' => $type,
        ];
    }

    /**
     * Parse cell value with it's Field config
     *
     * @param FieldAbstract|null $field
     * @param string $text
     * @return array
     */
    public static function parse(FieldAbstract $field = null, $text = '')
    {
        $result = static::getResultArray($text);
        $missing = [];

        if ($field) {
            list($result, $missing) = $field->parseField($text);
        }

        return [$result, $missing];
    }

    /**
     * Parse cell value
     *
     * @param $text
     * @return mixed
     */
    abstract protected function parseField($text);


}