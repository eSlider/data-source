<?php
namespace Mapbender\DataSourceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class DataSource
 *
 * @package   Mapbender\CoreBundle\Entity
 * @author    Andriy Oblivantsev <eslider@gmail.com>
 */
class DataItem
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * Meta data
     *
     * @ORM\Column(type="json_array")
     */
    protected $attributes;

    /**
     * Meta data unique field key name
     *
     * @var string
     */
    protected $uniqueIdField;

    /**
     * @param mixed  $args string|array|null Optional JSON string or array
     * @param string $uniqueIdField ID field name
     * @param bool   $fill array|null Fill array
     */
    public function __construct($args = null, $uniqueIdField = 'id', $fill = false)
    {
        $this->uniqueIdField = $uniqueIdField;

        // decode JSON
        if (is_string($args)) {
            $args = json_decode($args, true);
        }

        // Is JSON DataSource array?
        if ($fill && is_array($args) && isset($args['attributes'])) {
            $attributes = $args["attributes"];

            if (isset($args['id'])) {
                $attributes[$uniqueIdField] = $args['id'];
            }

            $args = $attributes;
        }

        // set ID
        if (isset($args[$uniqueIdField])) {
            $this->setId($args[$uniqueIdField]);
            unset($args[$uniqueIdField]);
        }

        // set attributes
        $this->setAttributes($args);
    }

    /**
     * Return string
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this);
    }

    /**
     * Return array
     *
     * @return mixed
     */
    public function toArray()
    {
        $data = $this->getAttributes();

        if (!$this->hasId()) {
            unset($data[$this->uniqueIdField]);
        } else {
            $data[$this->uniqueIdField] = $this->getId();
        }

        return $data;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Is id set
     *
     * @return bool
     */
    public function hasId()
    {
        return !is_null($this->id);
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get attributes (parameters)
     *
     * @return mixed
     */
    public function getAttributes()
    {
        $this->attributes[$this->uniqueIdField] = $this->getId();
        return $this->attributes;
    }

    /**
     * @param mixed $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * TODO: implement
     *
     * @return DataItem
     */
    public function getParent()
    {
        return new DataItem();
    }

    /**
     * TODO: implement
     *
     * @param DataItem $dataItem
     * @return DataItem
     */
    public function setParent(DataItem $dataItem = null)
    {
        return $dataItem;
    }
}