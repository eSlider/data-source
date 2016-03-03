<?php
namespace Mapbender\DataSourceBundle\Element;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use FOM\CoreBundle\Component\ExportResponse;
use Mapbender\CoreBundle\Asset\NamedAssetCache;
use Mapbender\CoreBundle\Component\Application;
use Mapbender\CoreBundle\Element\HTMLElement;
use Mapbender\CoreBundle\Entity\Element;
use Mapbender\DataSourceBundle\Component\DataStore;
use Mapbender\DataSourceBundle\Entity\DataItem;
use Mapbender\DataSourceBundle\Entity\DataStoreSchemaConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DataManagerElement
 *
 * @package Mapbender\DataSourceBundle\Element
 * @author  Andriy Oblivantsev <eslider@gmail.com>
 */
class DataManagerElement extends HTMLElement
{
    /**
     * The constructor.
     *
     * @param Application        $application The application object
     * @param ContainerInterface $container   The container object
     * @param Element            $entity
     */
    public function __construct(Application $application, ContainerInterface $container, Element $entity)
    {
        parent::__construct($application, $container, $entity);
    }

    /**
     * @inheritdoc
     */
    static public function getClassTitle()
    {
        return "Data manager";
    }

    /**
     * @inheritdoc
     */
    static public function getClassDescription()
    {
        return "Data manager element";
    }

    /**
     * @inheritdoc
     */
    public function getWidgetName()
    {
        return 'mapbender.mbDataManager';
    }

    /**
     * @inheritdoc
     */
    static public function getTags()
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public static function getDefaultConfiguration()
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public static function getType()
    {
        return 'Mapbender\DataSourceBundle\Element\Type\DataManagerAdminType';
    }

    /**
     * @inheritdoc
     */
    public static function getFormTemplate()
    {
        return 'MapbenderDataSourceBundle:ElementAdmin:dataManager.html.twig';
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        return /** @lang XHTML */ '<div id="' . $this->getId() . '"  class="mb-element mb-element-data-manager modal-body" title="' . _($this->getTitle()) . '"></div>';
    }

    /**
     * @inheritdoc
     */
    static public function listAssets()
    {
        return array(
            'css' => array(
                '/bundles/mapbenderdatasource/sass/element/data.store.element.scss'
            ),
            'js'  => array(
                'datastore.element.js'
            ),
            'trans' => array(
                'MapbenderDataSourceBundle:Element:datastore.json.twig'
            )
        );
    }

    /**
     * Prepare form items for each scheme definition
     * Optional: get featureType by name from global context.
     *
     * @inheritdoc
     */
    public function getConfiguration()
    {
        $configuration            = parent::getConfiguration();
        $configuration['debug']   = isset($configuration['debug']) ? $configuration['debug'] : false;
        $configuration['fileUri'] = $this->container->getParameter("mapbender.uploads_dir") . "/data-store";

        if (isset($configuration["schemes"]) && is_array($configuration["schemes"])) {
            foreach ($configuration["schemes"] as $key => &$scheme) {
                if (is_string($scheme['dataStore'])) {
                    $storeId                   = $scheme['dataStore'];
                    $dataStore                 = $this->container->getParameter('dataStores');
                    $scheme['dataStore']       = $dataStore[ $storeId ];
                    $scheme['dataStore']["id"] = $storeId;
                    //$dataStore = new DataStore($this->container, $configuration['source']);
                }
                if (isset($scheme['formItems'])) {
                    $scheme['formItems'] = $this->prepareItems($scheme['formItems']);
                }
            }
        }
        return $configuration;
    }

    /**
     * @inheritdoc
     */
    public function httpAction($action)
    {
        /** @var $requestService Request */
        $configuration   = $this->getConfiguration();
        $requestService  = $this->container->get('request');
        $request         = json_decode($requestService->getContent(), true);
        $schemas         = $configuration["schemes"];
        $debugMode       = $configuration['debug'] || $this->container->get('kernel')->getEnvironment() == "dev";
        $schemaName      = isset($request["schema"]) ? $request["schema"] : $requestService->get("schema");
        $defaultCriteria = array('returnType' => 'FeatureCollection',
                                 'maxResults' => 2500);
        $schema          = $schemas[ $schemaName ];
        $schemaConfig    = new DataStoreSchemaConfig($schemas[ $schemaName ]);


        if (is_array($schemaConfig->dataStore)) {
            $dataStore = new DataStore($this->container, $schemaConfig->dataStore);
        } else {
            throw new \Exception("DataStore setup is not correct");
        }

        $results = array();

        switch ($action) {
            case 'select':
                foreach ($dataStore->search(array_merge($defaultCriteria, $request)) as $dataItem){
                    $results[] = $dataItem->toArray();
                }
                break;

            case 'save':
                //try {
                if(!$schemaConfig->allowEdit){
                    $results["errors"] = array(
                        array(
                            'message' => "Access denied!",
                            'code' => "1"
                        )
                    );
                }

                $uniqueIdKey = $dataStore->getDriver()->getUniqueId();
                if(empty($request['dataItem'][ $uniqueIdKey ])){
                    unset($request['dataItem'][ $uniqueIdKey ]);
                }

                $dataItem = $dataStore->create($request['dataItem']);
                $result   = $dataStore->save($dataItem);
                if (!is_object($result) && isset($result["exception"])
                    && is_object($result["exception"])
                    && $result["exception"] instanceof \Exception
                ) {
                    /** @var \Exception $exception */
                    $exception         = $result["exception"];
                    $results["errors"] = array(
                        array(
                            'message' => $exception->getMessage(),
                            'code'    => $exception->getCode()
                        )
                    );
                }

                //var_dump($newDataItem);
                //die();
                $results["dataItem"] = $dataItem->toArray();
                //} catch (DBALException $e) {
                //    $message = $debugMode ? $e->getMessage() : "Feature can't be saved. Maybe something is wrong configured or your database isn't available?\n" .
                //        "For more information have a look at the webserver log file. \n Error code: " . $e->getCode();
                //    $results = array('errors' => array(
                //        array('message' => $message, 'code' => $e->getCode())
                //    ));
                //}

                break;

            case 'delete':
                //try {
                if(!$schemaConfig->allowRemove){
                    $results["errors"] = array(
                        array(
                            'message' => "Access denied!",
                            'code' => "1"
                        )
                    );
                }
                $id      = intval($request['id']);
                $results = $dataStore->remove($id);
                break;

            case 'file-upload':
                if(!$schemaConfig->allowEdit){
                    $results["errors"] = array(
                        array(
                            'message' => "Access denied!",
                            'code' => "1"
                        )
                    );
                }
                $fieldName                  = $requestService->get('field');
                $urlParameters              = array('schema' => $schemaName,
                                                    'fid'    => $requestService->get('fid'),
                                                    'field'  => $fieldName);
                $serverUrl                  = preg_replace('/\\?.+$/', "", $_SERVER["REQUEST_URI"]) . "?" . http_build_query($urlParameters);
                $uploadDir                  = $dataStore->getFilePath($fieldName);
                $uploadUrl                  = $dataStore->getFileUrl($fieldName) . "/";
                $urlParameters['uploadUrl'] = $uploadUrl;
                $uploadHandler              = new Uploader(array(
                    'upload_dir'                   => $uploadDir . "/",
                    'script_url'                   => $serverUrl,
                    'upload_url'                   => $uploadUrl,
                    'accept_file_types'            => '/\.(gif|jpe?g|png)$/i',
                    'print_response'               => false,
                    'access_control_allow_methods' => array(
                        'OPTIONS',
                        'HEAD',
                        'GET',
                        'POST',
                        'PUT',
                        'PATCH',
                        //                        'DELETE'
                    ),
                ));
                $results                    = array_merge($uploadHandler->get_response(), $urlParameters);

                break;

            default:
                $results = array(
                    array('errors' => array(
                        array('message' => $action . " not defined!")
                    ))
                );
        }

        return new JsonResponse($results);

    }
}