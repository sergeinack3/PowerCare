<?php

/**
 * @package Mediboard\Core\Elastic
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic;

use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Elasticsearch\Common\Exceptions\InvalidArgumentException as ElasticInvalidArgumentException;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Elasticsearch\Common\Exceptions\RuntimeException as ElasticsearchRuntimeException;
use Exception;
use Ox\Core\CStoredObject;
use Ox\Core\Elastic\Exceptions\ElasticBadRequest;
use Ox\Core\Elastic\Exceptions\ElasticClientException;
use Ox\Core\Elastic\Exceptions\ElasticException;
use Ox\Core\Elastic\Exceptions\ElasticObjectException;
use Ox\Core\Elastic\IndexLifeManagement\Exceptions\ElasticIndexLifecycleManagementException;
use Ox\Core\Elastic\IndexLifeManagement\Exceptions\ElasticPhaseConfigurationException;
use Throwable;

/**
 * ElasticObjectManager can :
 * - Store / Update / Delete -- ElasticObject
 * - Create / Delete Indexes
 * - Load References
 */
final class ElasticObjectManager
{
    private const ELASTIC_DATE_TIME_FORMAT = "yyyy-MM-dd'T'HH:mm:ss.nnnnnn VV '('XXX')'";

    private static ?ElasticObjectManager $_instance = null;

    // TODO: ref with cache
    /** @var ElasticIndexManager[] */
    private static array $datasources = [];

    /** @var ElasticClient[] */
    private static array $clients = [];


    private function __construct()
    {
    }

    /**
     * Return the singleton of this object
     */
    public static function getInstance(): ElasticObjectManager
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new ElasticObjectManager();
        }

        return self::$_instance;
    }

    /**
     * Get the concern datasource for the specified object
     *
     * @param ElasticObject $object
     *
     * @return ElasticIndexManager
     * @throws ElasticClientException
     */
    public static function getDsn(ElasticObject $object): ElasticIndexManager
    {
        $class_name = get_class($object);
        if (!array_key_exists($class_name, self::$datasources)) {
            self::$datasources[$class_name] = ElasticIndexManager::get($object->getSettings()->getConfigDsn());
        }

        return self::$datasources[$class_name];
    }


    /**
     * This return the specific Elastic Client (with connection information) for any ElasticObject
     *
     * @param ElasticObject $object
     *
     * @return ElasticClient
     * @throws Exception
     */
    public static function getClient(ElasticObject $object): ElasticClient
    {
        $class_name = get_class($object);
        if (!isset(self::$clients[$class_name])) {
            if (!self::checkIndexExists($object)) {
                self::$clients[$class_name] = false;
                throw new ElasticClientException("ElasticIndexManager-error-Connection failed");
            }
            $datasource                 = self::getDsn($object);
            self::$clients[$class_name] = $datasource->getClient();
        }
        if (!self::$clients[$class_name]) {
            throw new ElasticClientException("ElasticIndexManager-error-Connection failed");
        }

        return self::$clients[$class_name];
    }

    /**
     * This function create the template and then the ILM if the object as one and then the first index
     *
     * @param ElasticObject $obj
     * @param bool          $with_ilm
     *
     * @return void
     * @throws ElasticBadRequest
     * @throws ElasticClientException
     * @throws ElasticPhaseConfigurationException
     * @throws Exception
     */
    public static function init(ElasticObject $obj): void
    {
        self::createTemplate($obj);
        if ($obj->getSettings()->hasIndexLifeManagement()) {
            self::createILM($obj);
        }
        self::createFirstIndex($obj);
    }


    /**
     * This function deletes all the index then deletes the template and finally deletes the ILM
     *
     * @param ElasticObject $obj
     *
     * @return void
     * @throws ElasticBadRequest
     * @throws ElasticClientException
     * @throws ElasticException
     * @throws Exception
     */
    public function clear(ElasticObject $obj): void
    {
        $this->deleteIndex($obj);
        if (self::checkTemplateExists($obj)) {
            $this->deleteTemplate($obj);
        }
        if ($this->getILM($obj) !== []) {
            $this->deleteILM($obj);
        }
    }

    /**
     * Create an index based on an object settings and mapping
     * And return response or throw an error
     *
     * @param ElasticObject $obj
     *
     * @return array
     * @throws Exception
     */
    public static function createFirstIndex(ElasticObject $obj): array
    {
        $settings   = $obj->getSettings();
        $datasource = self::getDsn($obj);
        if ($settings->hasIndexLifeManagement()) {
            $index_name = $settings->getFirstIndexName();
        } else {
            $index_name = $settings->getIndexNameAlone();
        }

        $query = [
            "index" => $index_name,
            "body"  => [
                "aliases" => [
                    $settings->getAliasName() => [
                        "is_write_index" => true,
                    ],
                ],
            ],
        ];

        try {
            return $datasource->getClient()->indices()->create($query);
        } catch (BadRequest400Exception $e) {
            throw new ElasticBadRequest($e->getMessage());
        }
    }

    /**
     * @param ElasticObject $obj
     *
     * @return array
     * @throws Exception
     */
    public static function createTemplate(ElasticObject $obj): array
    {
        $settings = $obj->getSettings();
        // TODO : Implements mappings
        $mappings   = $obj->getMappings();
        $datasource = self::getDsn($obj);

        $query = [
            "name" => $settings->getTemplateName(),
            "body" => [
                "index_patterns" => [
                    $settings->getIndexPattern(),
                ],
                "template"       => [
                    "settings" => $settings->getElasticSettings(),
                    "mappings" => [
                        "dynamic_date_formats" => [
                            self::ELASTIC_DATE_TIME_FORMAT,
                        ],
                        "dynamic"              => true,
                        "properties"           => [],
                    ],
                ],
            ],
        ];

        foreach ($mappings->getDateFields() as $_date_field) {
            $data = [
                "type"   => "date_nanos",
                "format" => self::ELASTIC_DATE_TIME_FORMAT,
            ];

            $query["body"]["template"]["mappings"]["properties"][$_date_field["name"]] = $data;
        }

        foreach ($mappings->getStringFields() as $_string_field) {
            if ($_string_field["isSeekable"] === true) {
                $data = [
                    "type"      => "text",
                    "fielddata" => true,
                    "fields"    => [
                        "keyword" => [
                            "type" => "keyword",
                        ],
                    ],
                ];

                $query["body"]["template"]["mappings"]["properties"][$_string_field["name"]] = $data;
            }
        }

        try {
            return $datasource->getClient()->indices()->putIndexTemplate($query);
        } catch (BadRequest400Exception $e) {
            throw new ElasticBadRequest($e->getMessage());
        }
    }

    /**
     * @param ElasticObject $obj
     *
     * @return array
     * @throws Exception
     */
    public static function createILM(ElasticObject $obj): array
    {
        $client   = self::getDsn($obj)->getClient();
        $settings = $obj->getSettings();
        if (!$settings->hasIndexLifeManagement()) {
            throw new ElasticIndexLifecycleManagementException(
                "ElasticIndexLifecycleManagementException-msg-Could not create ILM without any configured"
            );
        }
        $ilm = $obj->getSettings()->getIndexLifeManagement()->build();

        try {
            return $client->ilm()->putLifecycle($ilm);
        } catch (BadRequest400Exception $e) {
            throw new ElasticBadRequest($e->getMessage());
        }
    }

    /**
     * Delete an index based on an object
     *
     * @param ElasticObject $obj
     *
     * @return array
     * @throws Exception
     */
    public function deleteIndex(ElasticObject $obj): array
    {
        $client   = self::getClient($obj);
        $settings = $obj->getSettings();

        $query = [
            "index" => $settings->getIndexPattern(),
        ];
        try {
            return $client->indices()->delete($query);
        } catch (Missing404Exception $e) {
            throw new ElasticBadRequest($e->getMessage());
        }
    }

    /**
     * @param ElasticObject $obj
     *
     * @return array
     * @throws Exception
     */
    public function deleteTemplate(ElasticObject $obj): array
    {
        $client   = self::getDsn($obj)->getClient();
        $settings = $obj->getSettings();

        $query = [
            "name" => $settings->getTemplateName(),
        ];
        try {
            return $client->indices()->deleteIndexTemplate($query);
        } catch (Missing404Exception $e) {
            throw new ElasticBadRequest($e->getMessage());
        }
    }

    public function deleteILM(ElasticObject $obj): array
    {
        $client   = self::getDsn($obj)->getClient();
        $settings = $obj->getSettings();

        $query = [
            "policy" => $settings->getILMName(),
        ];
        try {
            return $client->ilm()->deleteLifecycle($query);
        } catch (Missing404Exception $e) {
            throw new ElasticBadRequest($e->getMessage());
        }
    }

    /**
     * This verify if an index exists by using an object
     *
     * @param ElasticObject $obj
     *
     * @return bool
     * @throws Exception
     */
    public static function checkIndexExists(ElasticObject $obj): bool
    {
        $datasource = self::getDsn($obj);
        $settings   = $obj->getSettings();

        $query = [
            "index"            => $settings->getIndexPattern(),
            "allow_no_indices" => false,
        ];
        try {
            return $datasource->getClient()->indices()->exists($query);
        } catch (BadRequest400Exception $e) {
            return false;
        }
    }

    /**
     * @param ElasticObject $obj
     *
     * @return bool
     * @throws ElasticClientException
     */
    public static function checkTemplateExists(ElasticObject $obj): bool
    {
        $datasource = self::getDsn($obj);
        $settings   = $obj->getSettings();

        $query = [
            "name" => $settings->getTemplateName(),
        ];
        try {
            return $datasource->getClient()->indices()->existsIndexTemplate($query);
        } catch (BadRequest400Exception $e) {
            return false;
        }
    }

    /**
     * @throws ElasticClientException
     */
    public function getILM(ElasticObject $obj): array
    {
        $datasource = self::getDsn($obj);
        $settings   = $obj->getSettings();

        $query = [
            "policy" => $settings->getILMName(),
        ];
        try {
            return $datasource->getClient()->ilm()->getLifecycle($query);
        } catch (Missing404Exception $e) {
            return [];
        }
    }

    public function getIndexMappings(ElasticObject $obj): array
    {
        $datasource = self::getDsn($obj);
        $settings   = $obj->getSettings();

        $query = [
            "index" => $settings->getIndexPattern(),
        ];
        try {
            return $datasource->getClient()->indices()->getMapping($query);
        } catch (Missing404Exception $e) {
            return [];
        }
    }

    public function getIndexTemplate(ElasticObject $obj): array
    {
        $datasource = self::getDsn($obj);
        $settings   = $obj->getSettings();

        $query = [
            "name" => $settings->getTemplateName(),
        ];
        try {
            return $datasource->getClient()->indices()->getIndexTemplate($query);
        } catch (Missing404Exception $e) {
            return [];
        }
    }


    /**
     * @param ElasticObject $obj
     * @param string        $ref_name
     *
     * @return CStoredObject|null
     * @throws ElasticObjectException
     */
    public function loadRef(ElasticObject $obj, string $ref_name): ?CStoredObject
    {
        /** @var CStoredObject $ref_class */
        $ref_class = $obj->getMappings()->getReference($ref_name);

        // If the ref is not loaded
        if (!$obj->hasRef($ref_name)) {
            $ref_id = $obj->getRefValue($ref_name);
            // SQL Request to get the specific ref
            try {
                $object = $ref_class::find($ref_id);
            } catch (Exception $e) {
                throw new ElasticObjectException(
                    'ElasticObjectManager-error-Can not find the reference for the class %s with the id %s',
                    $ref_class,
                    $ref_id
                );
            }
            if ($object == false) {
                return null;
            }
            $obj->addRef($object, $ref_name);
        }

        return $obj->getRef($ref_name);
    }

    /**
     * @param ElasticObject[] $objs
     * @param string          $ref_name
     *
     * @return CStoredObject[]|null
     */
    public function massLoadRefs(array $objs, string $ref_name): ?array
    {
        if (!count($objs)) {
            return [];
        }

        $refs_ids = [];
        $obj      = reset($objs);
        /** @var CStoredObject $ref_class */
        $ref_class = $obj->getMappings()->getReference($ref_name);
        // Each object gather their ref_id they need
        foreach ($objs as $obj) {
            if ($obj->hasRef($ref_name)) {
                continue;
            }
            $id         = $obj->getRefValue($ref_name);
            $refs_ids[] = $id;
        }
        // Regroup the ref_ids
        $refs_ids = array_unique($refs_ids);

        // Load All CStoredObject from SQL
        $refs_objs = (new $ref_class())->loadAll($refs_ids);

        // Remapping refs to each objects
        foreach ($objs as $obj) {
            if ($obj->hasRef($ref_name)) {
                continue;
            }
            $id = $obj->getRefValue($ref_name);
            if (array_key_exists($id, $refs_objs)) {
                $obj->addRef($refs_objs[$id], $ref_name);
            }
        }

        return $refs_objs;
    }

    /**
     * This function insert an object to the corresponding DS in Elastic
     *
     * @param ElasticObject $obj
     *
     * @return string The id of the inserted object
     * @throws ElasticClientException
     * @throws ElasticException
     */
    private function insert(ElasticObject $obj, string $refresh = "false"): ElasticObject
    {
        $client = self::getClient($obj);

        $data   = $this->prepareDataToIndex($obj, $refresh);
        $result = $client->index($data);

        if (array_key_exists('errors', $result) && $result['errors'] === true) {
            throw $this->createExceptionFromResponses($result);
        }

        $obj->setId($result["_id"]);

        // Todo : SQL(Output inserted) Load all fields from elastic ?

        return $obj;
    }

    /**
     * This function insert multiple objects to the corresponding DS in Elastic
     *
     * @param ElasticObject[] $objs
     *
     * @return ElasticObject[] The list the inserted objects with id
     * @throws ElasticBadRequest
     * @throws ElasticClientException
     * @throws ElasticException
     */
    private function bulk(array $objs, string $refresh = "false"): array
    {
        // If there are different kind of ElasticObject split them for each specific ElasticObject
        $objs = $this->splitObjectsToBulk($objs);

        $stored_objects = [];
        foreach ($objs as $specified_objects) {
            $client = self::getClient($specified_objects[0]);

            $data   = $this->prepareDataToBulk($specified_objects, $refresh);
            $result = $client->bulk($data);

            if (array_key_exists('errors', $result) && $result['errors'] === true) {
                throw $this->createExceptionFromResponses($result);
            }

            // Mappings Id with each objects
            // Todo : SQL(Output inserted) Load all fields from elastic ?
            $i = 0;
            foreach ($result["items"] as $item) {
                $specified_objects[$i]->setId($item["index"]["_id"]);
                $i++;
            }
            $stored_objects = array_merge($stored_objects, $specified_objects);
        }

        return $stored_objects;
    }

    public function refresh(ElasticObject $object): void
    {
        $client = self::getClient($object);

        $query = [
            'index' => $object->getSettings()->getAliasName(),
        ];

        $client->indices()->refresh($query);
    }

    /**
     * @param ElasticObject[] $objects
     *
     * @return array
     */
    private function splitObjectsToBulk(array $objects): array
    {
        $objs = [];
        foreach ($objects as $_obj) {
            if ($_obj instanceof ElasticObject) {
                $objs[get_class($_obj)][] = $_obj;
            }
        }

        return $objs;
    }

    /**
     * This function deletes the specified ElasticObject from his Elastic Datasource
     *
     * @param ElasticObject $obj
     *
     * @return array
     * @throws ElasticException
     * @throws ElasticClientException
     * @throws ElasticBadRequest
     */
    public function delete(ElasticObject $obj): array
    {
        $id = $obj->getID();
        if ($id == "") {
            throw new ElasticBadRequest("ElasticObjectManager-error-Can not delete ElasticObject without id");
        }

        $client   = self::getClient($obj);
        $settings = $obj->getSettings();

        return $client->delete(
            [
                "index" => $settings->getAliasName(),
                "id"    => $obj->getID(),
            ]
        );
    }

    /**
     * This function deletes multiple ElasticObject from his Elastic Datasource
     *
     * @param ElasticObject[] $objs
     *
     * @return array
     * @throws ElasticClientException
     * @throws ElasticException
     */
    public function deleteBulk(array $objs): array
    {
        $obj      = reset($objs);
        $client   = self::getClient($obj);
        $settings = $obj->getSettings();

        $ids = [];
        foreach ($objs as $_obj) {
            if (!$_obj instanceof ElasticObject) {
                continue;
            }

            $id = $_obj->getID();

            if ($id == "") {
                continue;
            }

            $ids[] = $id;
        }

        return $client->deleteByQuery(
            [
                "index" => $settings->getAliasName(),
                "body"  => [
                    "query" => [
                        "bool" => [
                            "minimum_should_match" => 1,
                            "should"               => [
                                "ids" => [
                                    "values" => $ids,
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * This function deletes multiple ElasticObject from ids
     *
     * @param array         $ids
     * @param ElasticObject $obj
     *
     * @return array
     * @throws ElasticBadRequest
     * @throws ElasticClientException
     * @throws ElasticException
     */
    public function deleteListIds(array $ids, ElasticObject $obj): array
    {
        $client   = self::getClient($obj);
        $settings = $obj->getSettings();

        return $client->deleteByQuery(
            [
                "index" => $settings->getIndexPattern(),
                "body"  => [
                    "query" => [
                        "terms" => [
                            "_id" => $ids,
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * This function updates the specified ElasticObject from his Elastic Datasource
     *
     * @param ElasticObject $obj
     *
     * @return array
     * @throws ElasticClientException
     * @throws ElasticException
     * @throws ElasticBadRequest
     */
    public function update(ElasticObject $obj): array
    {
        $id = $obj->getID();
        if ($id === "") {
            throw new ElasticBadRequest("ElasticObjectManager-error-Can not update ElasticObject without id");
        }

        $client   = self::getClient($obj);
        $settings = $obj->getSettings();

        return $client->update(
            [
                "index" => $settings->getAliasName(),
                "id"    => $id,
                "body"  => [
                    "doc" => $obj->toArray(),
                ],
            ]
        );
    }

    /**
     * Store permits to select the appropriate function to insert data into Elastic
     *
     * @param ElasticObject | ElasticObject[] $objs
     *
     * @return ElasticObject | ElasticObject[]
     * @throws ElasticBadRequest
     * @throws ElasticClientException
     * @throws ElasticException
     * @throws ElasticObjectException
     */
    public function store($objs)
    {
        if ($objs instanceof ElasticObject) {
            return $this->insert($objs);
        } elseif (is_array($objs)) {
            return $this->bulk($objs);
        }

        throw new ElasticObjectException(
            "ElasticObjectException-error-The current object are not a ElasticObject or an Array"
        );
    }

    /**
     * WARNING - READ THE FUNCTION DESCRIPTION BEFORE USING IT.
     * This function should be used in specific cases only.
     * This function execution time is depends on Elasticsearch.
     * If you don't known what you are doing you may you the store() function directly.
     *
     * @param ElasticObject | ElasticObject[] $objs
     *
     * @return ElasticObject|ElasticObject[]|string
     * @throws ElasticBadRequest
     * @throws ElasticClientException
     * @throws ElasticException
     * @throws ElasticObjectException
     */
    public function storeAndWait($objs)
    {
        if ($objs instanceof ElasticObject) {
            return $this->insert($objs, "wait_for");
        } elseif (is_array($objs)) {
            return $this->bulk($objs, "wait_for");
        }

        throw new ElasticObjectException(
            "ElasticObjectException-error-The current object are not a ElasticObject or an Array"
        );
    }

    /**
     * Prepare data to ElasticSearch index format.
     *
     * @param ElasticObject $obj
     * @param string        $refresh
     *
     * @return array
     * @throws ElasticObjectException
     */
    private function prepareDataToIndex(ElasticObject $obj, string $refresh = "false"): array
    {
        $settings = $obj->getSettings();

        return [
            'index'   => $settings->getAliasName(),
            'body'    => $obj->toArray(),
            'refresh' => $refresh,
        ];
    }

    /**
     * Prepare data to ElasticSearch bulk format.
     *
     * @param ElasticObject[] $objs
     * @param string          $refresh
     *
     * @return array
     * @throws ElasticObjectException
     */
    private function prepareDataToBulk(array $objs, string $refresh = "false"): array
    {
        $dataToBulk = [];
        foreach ($objs as $obj) {
            $settings              = $obj->getSettings();
            $dataToBulk["refresh"] = $refresh;
            $dataToBulk["body"][]  = ["index" => ["_index" => $settings->getAliasName()]];
            $dataToBulk["body"][]  = $obj->toArray();
        }

        return $dataToBulk;
    }

    /**
     * Creates elasticsearch exception from responses array
     *
     * Only the first error is converted into an exception.
     *
     * @param mixed[]|Elasticsearch $responses returned by $this->client->bulk()
     */
    protected function createExceptionFromResponses($responses): Throwable
    {
        foreach ($responses['items'] ?? [] as $item) {
            if (isset($item['index']['error'])) {
                return $this->createExceptionFromError($item['index']['error']);
            }
        }

        if (class_exists(ElasticInvalidArgumentException::class)) {
            return new ElasticInvalidArgumentException('Elasticsearch failed to index one or more records.');
        }

        return new ElasticsearchRuntimeException('Elasticsearch failed to index one or more records.');
    }

    /**
     * Creates elasticsearch exception from error array
     *
     * @param mixed[] $error
     */
    protected function createExceptionFromError(array $error): Throwable
    {
        $previous = isset($error['caused_by']) ? $this->createExceptionFromError($error['caused_by']) : null;

        if (class_exists(ElasticInvalidArgumentException::class)) {
            return new ElasticInvalidArgumentException($error['type'] . ': ' . $error['reason'], 0, $previous);
        }

        return new ElasticsearchRuntimeException($error['type'] . ': ' . $error['reason'], 0, $previous);
    }
}
