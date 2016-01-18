<?php
namespace Mapbender\DataSourceBundle\Element;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use FOM\CoreBundle\Component\ExportResponse;
use Mapbender\CoreBundle\Component\Application;
use Mapbender\CoreBundle\Element\HTMLElement;
use Mapbender\CoreBundle\Entity\Element;
use Mapbender\DataSourceBundle\Entity\DataItem;
use Mapbender\DataSourceBundle\Entity\QueryBuilderConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DataStoreElement
 *
 * @package Mapbender\DataSourceBundle\Element
 * @author  Andriy Oblivantsev <eslider@gmail.com>
 */
class QueryBuilderElement extends HTMLElement
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
        return "Query builder";
    }

    /**
     * @inheritdoc
     */
    static public function getClassDescription()
    {
        return "Build, list SQL queries and display result, which can be edited to.";
    }

    /**
     * @inheritdoc
     */
    public function getWidgetName()
    {
        return 'mapbender.mbQueryBuilderElement';
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
        $queryBuilderConfig = new QueryBuilderConfig();
        return $queryBuilderConfig->toArray();
    }

    /**
     * @inheritdoc
     */
    public static function getType()
    {
        return 'Mapbender\DataSourceBundle\Element\Type\QueryBuilderAdminType';
    }

    /**
     * @inheritdoc
     */
    public static function getFormTemplate()
    {
        return 'MapbenderDataSourceBundle:ElementAdmin:queryBuilder.html.twig';
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        return /** @lang XHTML */
            '<div
                id="' . $this->getId() . '"
                class="mb-element mb-element-queryBuilder modal-body"
                title="' . _($this->getTitle()) . '"></div>';
    }

    /**
     * @inheritdoc
     */
    static public function listAssets()
    {
        return array(
            'css'   => array(
                '@MapbenderDataSourceBundle/Resources/public/sass/element/queryBuilder.element.scss'
            ),
            'js'    => array(
                '@MapbenderDataSourceBundle/Resources/public/queryBuilder.element.js'
            ),
            'trans' => array(
                '@MapbenderDataSourceBundle/Resources/views/Element/queryBuilder.json.twig'
            )
        );
    }

    /**
     * @return QueryBuilderConfig
     */
    public function getConfig()
    {
        return new QueryBuilderConfig($this->getConfiguration());
    }

    /**
     * @inheritdoc
     */
    public function httpAction($action)
    {
        /** @var DataItem $dataItem */
        /** @var $requestService Request */
        /** @var Registry $doctrine */
        /** @var Connection $connection */
        $configuration   = $this->getConfig();
        $requestService  = $this->container->get('request');
        $defaultCriteria = array();
        $payload         = json_decode($requestService->getContent(), true);
        $request         = $requestService->getContent() ? array_merge($defaultCriteria, $payload ? $payload : $_REQUEST) : array();
        $dataStore       = $this->container->get("data.source")->get($configuration->source);



        switch ($action) {
            case 'select':
                $results = array();
                foreach ($dataStore->search($request) as &$dataItem) {
                    $results[] = $dataItem->toArray();
                }
                break;

            case 'export':
            case 'execute':

                if ($action == "execute" && !$configuration->allowExecute){
                    throw new \Error("Permission denied!");
                }
                if ($action == "export" && !$configuration->allowExport){
                    throw new \Error("Permission denied!");
                }

                $query      = $dataStore->getById(intval($request['id']));
                $sql        = $query->getAttribute($configuration->sqlFieldName);
                $doctrine   = $this->container->get("doctrine");
                $connection = $doctrine->getConnection($query->getAttribute($configuration->connectionFieldName));
                $results    = $connection->fetchAll($sql);

                if ($action == "export") {
                    return new ExportResponse($results, 'export-list', ExportResponse::TYPE_XLS);
                } else {
                    break;
                }

            case 'save':
                if (!$configuration->allowCreate && !$configuration->allowSave) {
                    throw new \Error("Permission denied!");
                }
                $dataItem1       = $dataStore->save($request["item"]);
                if(!$dataItem1){
                    throw new \Error("Can't get object by new ID. Wrong sequence setup?");
                }
                break;

            case 'remove':
                if (!$configuration->allowRemove) {
                    throw new \Error("Permission denied!");
                }
                $results[] = $dataStore->remove($request["id"]);
                break;

            case 'connections':
                $doctrine        = $this->container->get("doctrine");
                $connectionNames = $doctrine->getConnectionNames();
                $names           = array_keys($connectionNames);
                $results         = array_combine($names, $names);
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