<?php
namespace Mapbender\DataSourceBundle\Component\Drivers\Interfaces;

use Mapbender\DataSourceBundle\Entity\DataItem;

/**
 * Interface Treeable
 *
 * @package Mapbender\DataSourceBundle\Component\Drivers
 */
interface Treeable
{
    /**
     * @param DataItem $dataItem
     * @return DataItem|null
     */
    public function getParent(DataItem $dataItem);

    /**
     * @param null $parentId
     * @param bool $recursive
     * @return DataItem|null
     */
    public function getTree($parentId = null, $recursive = true);

}