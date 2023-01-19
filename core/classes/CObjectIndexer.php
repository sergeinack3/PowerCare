<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use Ox\Core\Mutex\CMbMutex;
use Ox\Mediboard\Search\IIndexableObject;

/**
 * Object indexer for search purpose
 */
class CObjectIndexer
{
    const DEFAULT_TTL = 86400; // 24h in seconds

    private $name;
    private $version;
    private $ttl;
    private $callback;
    private $class;
    private $chrono;

    private $objects_list = [];
    private $objects_size = [];
    private $search_index = [];

    /**
     * CObjectIndexer constructor.
     *
     * @param string   $name     Index name
     * @param string   $class    Class indexed
     * @param string   $version  Cache version
     * @param callable $callback Callback
     * @param int      $ttl      DSHM TTL
     */
    public function __construct($name, $class, $version, callable $callback = null, ?int $ttl = self::DEFAULT_TTL)
    {
        $this->name     = $name;
        $this->class    = $class;
        $this->version  = $version;
        $this->ttl      = $ttl;
        $this->callback = $callback;
    }

    /**
     * Gets SHM keys
     *
     * @return string
     */
    private function getSHMKey()
    {
        return "index-$this->name-$this->class";
    }

    /**
     * Builds the index
     *
     * @param null|CStoredObject[] $objects Objects collection
     *
     * @return void
     * @throws Exception
     */
    public function build(array $objects = null)
    {
        if ($this->exists()) {
            return;
        }

        $this->chrono = new Chronometer();
        $this->chrono->start();
        $mutex = new CMbMutex($this->getSHMKey());
        $mutex->lock();

        if ($objects === null && $this->callback) {
            $objects = call_user_func($this->callback);
        }

        if (empty($objects)) {
            $mutex->release();

            return;
        }

        foreach ($objects as $_object) {
            if ($_object instanceof IIndexableObject) {
                $this->objects_list[] = $_object->getIndexableData();
            }
        }

        foreach ($this->objects_list as $_i => $_object) {
            $tokens = $this->tokenize($_object['body'] . ' ' . $_object['title']);

            foreach ($tokens as $_token) {
                if (!isset($this->search_index[$_token])) {
                    $this->search_index[$_token] = [];
                }

                if (!isset($this->search_index[$_token][$_i])) {
                    $this->search_index[$_token][$_i] = 0;
                }

                $this->search_index[$_token][$_i]++;
            }

            $this->objects_size[$_i] = count($tokens);
        }

        $mutex->release();
        $this->save();
        $this->chrono->stop();
        $this->log();
    }

    /**
     * Checks whether index exists or not
     *
     * @return bool
     */
    public function exists()
    {
        $key = $this->getSHMKey();

        $cache = Cache::getCache(Cache::DISTR);

        if (!$cache->has($key)) {
            return false;
        }

        $index = $cache->get($key);

        if ($index["version"] != $this->version) {
            $this->remove();

            return false;
        }

        return true;
    }

    /**
     * Saves index in distributed shared memory
     *
     * @return void
     */
    private function save()
    {
        $key = $this->getSHMKey();

        $cache = Cache::getCache(Cache::DISTR);

        $index = [
            "version" => $this->version,
            "class"   => $this->class,
            "index"   => $this->search_index,
        ];

        $cache->set($key, $index, $this->ttl);
        $cache->set("$key-object_size", $this->objects_size, $this->ttl);

        foreach ($this->objects_list as $_i => $_object) {
            $cache->set("$key-object-$_i", $_object, $this->ttl);
        }
    }

    /**
     * Searches in index with given string and returns matching objects
     *
     * @param string   $string   String to search
     * @param callable $callback Optionnal callback to order the objects
     *
     * @return array[]
     * @throws Exception
     */
    public function search($string, callable $callback = null)
    {
        $this->build();
        $this->getSearchIndex();

        $objects = [];
        $indices = $this->searchIndices($string);

        if (empty($indices)) {
            return $objects;
        }

        $index_key = $this->getSHMKey();

        $cache = Cache::getCache(Cache::DISTR);

        // Most used case : the object list is empty (no call to build)
        if (empty($this->objects_list)) {
            $keys = array_keys($indices);

            $shm_objects = $cache->getMultiple(
                array_map(
                    function ($v) use ($index_key) {
                        return "$index_key-object-$v";
                    },
                    $keys
                )
            );

            $objects = array_combine($keys, $shm_objects);

            foreach ($objects as $_i => $_result) {
                $objects[$_i]["pertinence"] = $indices[$_i];
            }
        } else {
            foreach ($indices as $_indice => $_occurences) {
                $_result               = $this->objects_list[$_indice];
                $_result["pertinence"] = $_occurences;

                $objects[] = $_result;
            }
        }

        if ($callback) {
            $callback($objects);
        }

        return $objects;
    }

    /**
     * Gets the number of word in documents
     *
     * @return array Objects size
     */
    private function getObjectsSize()
    {
        $cache = Cache::getCache(Cache::DISTR);

        return $cache->get($this->getSHMKey() . "-object_size");
    }

    /**
     * Searches in index and returns an associative array containing the indices and its occurrence count
     *
     * @param string $string String
     *
     * @return array Indices
     */
    private function searchIndices($string)
    {
        $indices      = [];
        $tokens       = $this->tokenize($string);
        $tokens_count = count($tokens);

        if ($tokens_count === 0) {
            return $indices;
        }

        foreach ($tokens as $_token) {
            $filtered_index = $this->searchToken($_token);
            $indices[]      = $this->flattenIndex($indices, $filtered_index, $tokens_count);
        }

        // Gets first array elem if it's a one token search, else computes the intersection
        $indices = (count($indices) <= 1) ? reset($indices) : call_user_func_array('array_intersect_key', $indices);

        return $this->sortByPertinence($indices);
    }

    /**
     * Searches the given token in index
     *
     * @param string $token Token to search
     *
     * @return array
     */
    private function searchToken($token)
    {
        $matches = [];
        foreach ($this->search_index as $_index => $_ids) {
            // Handle exact and partial match
            if (($_index === $token) || (strpos($_index, $token) !== false)) {
                $matches[$_index] = $_ids;
            }
        }

        return $matches;
    }

    /**
     * Flattens the search index by merging indices from different tokens
     *
     * @param array $indices      Indices result array
     * @param array $index        Filtered index
     * @param int   $tokens_count Token count
     *
     * @return array
     */
    private function flattenIndex(&$indices, $index, $tokens_count)
    {
        $values = [];

        foreach ($index as $_token => $_values) {
            $values += $_values;

            // Sums occurrences in first indices array for multi token search
            if ($tokens_count > 1) {
                foreach ($_values as $_index => $_occurrence) {
                    $first_token_result = reset($indices);

                    if (isset($first_token_result[$_index])) {
                        $indices[0][$_index] += $_occurrence;
                    }
                }
            }
        }

        return $values;
    }

    /**
     * Gets the search index
     *
     * @return array
     */
    private function getSearchIndex()
    {
        $cache = Cache::getCache(Cache::DISTR);

        if (empty($this->search_index)) {
            $index              = $cache->get($this->getSHMKey());
            $this->search_index = $index["index"] ?: [];
        }

        return $this->search_index;
    }

    /**
     * Sorts indices by pertinence
     *
     * @param array $indices Indices array
     *
     * @return array
     */
    private function sortByPertinence($indices)
    {
        $objects_size = $this->getObjectsSize();

        foreach ($indices as $_indice => $_occurences) {
            $indices[$_indice] /= $objects_size[$_indice];
        }

        arsort($indices);

        return $indices;
    }

    /**
     * Removes entire index
     *
     * @return int
     */
    public function remove()
    {
        return Cache::deleteKeys(Cache::DISTR, $this->getSHMKey());
    }

    /**
     * Tokenizes a string for search or index purpose
     *
     * @param string $string String to tokenize
     *
     * @return array Tokens
     */
    public function tokenize($string)
    {
        $tokens = CMbString::canonicalize($string);
        $tokens = preg_split("/[^a-z0-9]/", $tokens);

        return array_filter(
            $tokens,
            function ($s) {
                return isset($s[1]);
            }
        );
    }

    /**
     * Removes an index based on its class
     *
     * @param string $name Index name
     *
     * @return int
     */
    public static function removeIndex($name)
    {
        return Cache::deleteKeys(Cache::DISTR, "index-$name");
    }

    /**
     * Removes all CObjectIndexer indexes (usefull for cache cleaning)
     *
     * @return bool
     */
    public static function removeIndexes(): bool
    {
        return Cache::deleteKeys(Cache::DISTR, "index-");
    }

    /**
     * Logs indexing performance
     * (Index construction time, keys count, average object count by key)
     * @return void
     * @throws Exception
     */
    private function log()
    {
        $cache = Cache::getCache(Cache::DISTR);

        $nbKeys               = count($this->search_index);
        $indexed_object_count = 0;
        foreach ($this->search_index as $_values) {
            $indexed_object_count += count($_values);
        }
        $average_objects_by_key = ($nbKeys !== 0 ? round($indexed_object_count / $nbKeys, 1) : 0);

        $key        = $this->getSHMKey();
        $nb_objects = count($this->objects_list);

        $index_infos = [
            'index_key'                   => $key,
            'name'                        => $this->name,
            'class'                       => $this->class,
            'creation_datetime'           => CMbDT::dateTime(),
            'build_time'                  => round($this->chrono->total * 1000),
            'total_size'                  => 'N/A',
            'nb_keys'                     => $nbKeys,
            'nb_objects'                  => $nb_objects,
            'average_object_count_by_key' => $average_objects_by_key,
        ];

        $cache->set("$key-infos", $index_infos);

        $msg = "\n- Built in {$this->chrono->total}s\n";
        $msg .= "- Contains $nbKeys keys\n";
        $msg .= "- Contains " . count($this->objects_list) . " objects\n";
        $msg .= "- Average object count by key is $average_objects_by_key\n";
        CApp::log("Index '$key'", $msg);
    }
}
