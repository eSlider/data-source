<?php
namespace Mapbender\DataSourceBundle\Entity;

use Mapbender\DataSourceBundle\Component\DataStore;

/**
 * Class DataStoreSchemaConfig
 *
 * @package Mapbender\DataSourceBundle\Entity
 * @author  Andriy Oblivantsev <eslider@gmail.com>
 */
class DataStoreSchemaConfig extends BaseConfiguration
{

    /** @var string Data source id or name */
    public $source = "default";

    /** @var  mixed Data store info */
    public $dataStore;

    /**
     * Permissions
     */

    /** @var boolean Allow remove SQL */
    public $allowRemove = false;

    /** @var boolean Allow open edit form */
    public $allowEdit = false;

    /** @var boolean Allow save SQL */
    public $allowSave = false;

    /** @var boolean Allow create SQL */
    public $allowCreate = false;

    /** @var boolean Allow print */
    public $allowPrint = true;

    /** @var boolean Allow print */
    public $allowSearch = false;

    /** @var boolean Allow print */
    public $allowRefresh = false;

    public $idFieldName = "id";
    public $formItems   = array();
}