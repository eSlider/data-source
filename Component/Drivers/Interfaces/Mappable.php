<?php

namespace Mapbender\DataSourceBundle\Component\Drivers\Interfaces;

/**
 * Interface Mappable
 *
 * @package Mapbender\DataSourceBundle\Component\Drivers
 */
interface Mappable
{
    /**
     * @param $mappingId int Target ID
     * @param $id int Source ID
     * @return array|null
     */
    public function getTroughMapping($mappingId, $id);
}