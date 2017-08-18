<?php
namespace Pentagonal\Phalcon\Application\Globals\Model;

use Phalcon\Mvc\Model;

/**
 * Class Option
 * @package Pentagonal\Phalcon\Application\Globals\Model
 */
class Option extends Model
{
    const TABLE_NAME = 'options';

    /**
     * @var int
     */
    public $id;

    /**
     * @return Option
     */
    public function initialize() : Option
    {
        /**
         * @var Option $model
         */
        $model = $this->setSource(self::TABLE_NAME);
        return $model;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getOptionName(): string
    {
        return $this->option_name;
    }

    /**
     * @param string $option_name
     */
    public function setOptionName(string $option_name)
    {
        $this->option_name = $option_name;
    }

    /**
     * @return mixed
     */
    public function getOptionValue()
    {
        return $this->option_value;
    }

    /**
     * @param mixed $option_value
     */
    public function setOptionValue($option_value)
    {
        $this->option_value = $option_value;
    }
}
