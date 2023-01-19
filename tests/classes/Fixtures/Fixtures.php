<?php

/**
 * @package Mediboard\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Tests\Fixtures;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CStoredObject;
use Ox\Erp\SourceCode\CFixturesReference;
use Ox\Mediboard\Mediusers\CMediusers;
use ReflectionClass;

/**
 * Fixtures are used to load a "fake" set of data into a database that can then be used for testing
 * or to help give you some interesting data while you're developing your application.
 */
abstract class Fixtures
{
    /**
     * Sharing Objects between Fixtures
     * @var array
     */
    protected static $references = [];

    /**
     * load full references is used dev-time to load many fixture
     * without improve ci-time performance
     */
    protected $full_mode = false;

    /**
     * @var array
     */
    protected static $logs = [
        'delete' => [],
        'store'  => [],
    ];

    /** @var CMediusers[] */
    protected static $users = [];

    /** @var ReflectionClass */
    private $rc;

    /**
     * Adding datas into database
     * @return void
     * @throw Exception
     */
    abstract public function load();

    /**
     * Purge previously existing data (ignored in option append is true)
     * Default purge object with idex
     *
     * @return void
     * @throw Exception
     * @throws FixturesException
     * @throws Exception
     */
    public function purge()
    {
        $fr   = new CFixturesReference();
        $refs = $fr->loadList(
            [
                'fixtures_class' => $fr->getDS()->prepareLike(static::class),
            ],
            "{$fr->getSpec()->key} DESC"
        );

        foreach ($refs as $ref) {
            try {
                $target = $ref->loadTarget();
                $this->delete($target);
            } catch (Exception $e) {
                // Object not found ? May be already purged ...
            }

            $msg = $ref->purge();
            if ($msg) {
                throw new FixturesException($msg);
            }
        }
    }

    private function logStore(CStoredObject $object)
    {
        static::$logs['store'][static::class][] = $object->_guid;
    }

    private function logDelete(CStoredObject $object)
    {
        static::$logs['delete'][static::class][] = $object->_guid;
    }

    /**
     * Reference (bdd & static) used to get objects in tests/fixtures and to purge objects
     *
     * @param CStoredObject $object
     * @param string|null   $tag
     *
     * @throws FixturesException
     * @throws Exception
     */
    protected function addReference(CStoredObject $object, string $tag = null)
    {
        // static
        if ($tag !== null) {
            $object_class = get_class($object);

            if (
                array_key_exists($object_class, static::$references)
                && array_key_exists($tag, static::$references[$object_class])
            ) {
                throw new FixturesException("Duplicate reference {$object_class} with tag {$tag}");
            }

            static::$references[$object_class][$tag] = $object;
        }

        // bdd
        $ref                 = new CFixturesReference();
        $ref->fixtures_class = static::class;
        $ref->object_class   = $object->_class;
        $ref->object_id      = $object->_id;
        $ref->tag            = $tag;
        $msg                 = $ref->store();
        if ($msg) {
            throw new FixturesException($msg);
        }
    }

    /**
     * Return an object if one with this tag exists in references
     *
     * @param string $object_class
     * @param string $tag
     *
     * @return mixed
     * @throws FixturesException
     */
    protected function getReference(string $object_class, string $tag)
    {
        if (!array_key_exists($tag, static::$references[$object_class] ?? [])) {
            throw new FixturesException("Unknown reference {$object_class} with tag {$tag}");
        }

        return static::$references[$object_class][$tag];
    }

    /**
     * Return true if a reference with this tag exists
     *
     * @param string $object_class
     * @param string $tag
     *
     * @return bool
     */
    protected function hasReference(string $object_class, string $tag): bool
    {
        return array_key_exists($tag, static::$references[$object_class]);
    }

    /**
     * @return mixed|null
     */
    public function getDescription()
    {
        $rc = $this->getReflectionClass();

        $re = "/(?:@description (?'description'[\w|[[:blank:]]+))/m";
        preg_match($re, $rc->getDocComment(), $matches);

        return array_key_exists('description', $matches) ? $matches['description'] : null;
    }

    /**
     * @return false|string
     */
    public function getLastChange()
    {
        $rc  = $this->getReflectionClass();
        $cmd = "git log -1 --pretty='format:%ci' " . $rc->getFileName();

        return exec($cmd);
    }

    /**
     * @return ReflectionClass
     */
    private function getReflectionClass(): ReflectionClass
    {
        if ($this->rc === null) {
            $this->rc = new ReflectionClass($this);
        }

        return $this->rc;
    }

    /**
     * @param CStoredObject $object
     * @param string|null   $tag
     * @param bool          $add_reference
     *
     * @throws FixturesException
     * @throws Exception
     */
    protected function store(CStoredObject $object, string $tag = null, bool $add_reference = true)
    {
        $msg = $object->store();
        if ($msg) {
            throw new FixturesException($msg);
        }
        $this->logStore($object);

        if ($add_reference) {
            $this->addReference($object, $tag);
        }
    }

    /**
     * @param CStoredObject $object
     *
     * @return void
     * @throws FixturesException
     * @throws Exception
     */
    protected function delete(CStoredObject $object): void
    {
        $this->logDelete($object);

        $msg = $object->purge();
        if ($msg) {
            throw new FixturesException($msg);
        }
    }

    public static function dumpLogs(): void
    {
        dump(static::$logs);
    }

    public function countLogsDelete(): int
    {
        return static::countLogs('delete');
    }

    public function countLogsStore(): int
    {
        return static::countLogs('store');
    }

    private function countLogs(string $type): int
    {
        $fixtures_class = static::class;

        return array_key_exists($fixtures_class, static::$logs[$type]) ?
            count(static::$logs[$type][$fixtures_class]) : 0;
    }

    /**
     * Mark a fixture as skipped
     *
     * @param string|null $msg
     *
     * @return void
     * @throws FixturesSkippedException
     */
    protected function markSkipped(string $msg = null): void
    {
        throw new FixturesSkippedException($msg);
    }

    /**
     * Generate a specific number of Mediusers for fixtures
     *
     * @param int  $nb                  How many users to create
     * @param bool $allow_keep_in_cache Get $nb users in static::$users else force create and don't store in
     *                                  static::$users
     * @return array
     * @throws FixturesException
     */
    public function getUsers(int $nb, bool $allow_keep_in_cache = true): array
    {
        // force create users and don't cache in static::$users
        if (!$allow_keep_in_cache) {
            return $this->generateUsers($nb, false);
        }

        if ($nb <= 0) {
            throw new FixturesException("Can't do nothing for you !");
        }

        $count_users_in_cache = count(static::$users);
        $count_missing_users  = $nb - $count_users_in_cache;

        // nothing to create -> return all cached users
        if ($count_missing_users <= 0) {
            return static::$users;
        }

        // users to create
        if ($count_missing_users > 0) {
            $this->generateUsers($count_missing_users);

            return static::$users;
        }

        // return one or many users
        return CMbArray::arrayRandValues(static::$users, $nb);
    }

    /**
     * Generate and return only one user
     *
     * @param bool $allow_keep_in_cache
     *
     * @return CMediusers
     * @throws FixturesException
     */
    public function getUser(bool $allow_keep_in_cache = true): CMediusers
    {
        return $this->getUsers(1, $allow_keep_in_cache)[0];
    }

    /**
     * @param int  $nb
     * @param bool $add_in_cache
     *
     * @return array
     * @throws FixturesException
     * @throws Exception
     */
    private function generateUsers(int $nb, bool $add_in_cache = true): array
    {
        $users = FixturesUsersGenerator::generate($nb);

        /** @var CMediusers $user */
        foreach ($users as $user) {
            // add reference for each users
            $this->addReference($user);

            // add in static::users if $add_in_cache
            if ($add_in_cache) {
                static::$users[] = $user;
            }
        }

        return $users;
    }

    /**
     * @return bool
     */
    public function isFullMode(): bool
    {
        return $this->full_mode;
    }

    /**
     * @param bool $full_mode
     */
    public function setFullMode(bool $full_mode): void
    {
        $this->full_mode = $full_mode;
    }


}
