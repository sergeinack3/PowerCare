<?php

/**
 * @package Mediboard\api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api\Controllers;

use Exception;
use Ox\Api\Exception\CPWAException;
use Ox\AppFine\Server\Exception\CAppFineException;
use Ox\Core\CMbArray;
use Ox\Core\CStoredObject;
use Ox\Core\Kernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

trait CPWAControllerTrait
{
    /**
     * @param CStoredObject $object
     * @param array|null    $fieldsets
     *
     * @return string[]
     * @throws Exception
     */
    public static function getFields(CStoredObject $object, ?array $fieldsets = null): array
    {
        if ($fieldsets === null) {
            $fieldsets = self::getFieldsets($object->_class);
        }

        return array_keys(
            array_filter(
                $object->getProps(),
                function ($prop, $key) use ($object, $fieldsets) {
                    if ($key === $object->getPrimaryKey()) {
                        return false;
                    }

                    foreach ($fieldsets as $fieldset) {
                        $pattern = str_replace('{fieldset}', $fieldset, "/fieldset\|{fieldset}/");
                        if (preg_match($pattern, $prop)) {
                            return true;
                        }
                    }

                    return false;
                },
                ARRAY_FILTER_USE_BOTH
            )
        );
    }

    /**
     * @param string $class
     *
     * @return array
     * @throws Exception
     */
    protected static function getFieldsets(string $class): array
    {
        // CAppFineUser => AF_USER
        $constant_class = str_replace('AppFine', 'af', substr($class, 1));
        $constant_class = preg_replace('/\B([A-Z])/', '_$1', $constant_class);
        $constant       = "FIELDSETS_" . strtoupper($constant_class);

        return defined(self::class . '::' . $constant) ? constant(self::class . '::' . $constant) : [];
    }

    /**
     * @param mixed $_ = null
     *
     * @return CAppFineException
     * @throws Exception
     */
    protected static function objectNotFoundError($_ = null): CPWAException
    {
        return self::getException(self::getClassException()::OBJECT_NOT_FOUND, Response::HTTP_NOT_FOUND, $_);
    }

    /**
     * @param int   $code
     * @param int   $http_code
     * @param mixed ...$args arguments
     *
     * @return CPWAException
     */
    private static function getException(int $code, int $http_code, ...$args): CPWAException
    {
        $class = self::getClassException();

        return new $class($code, $http_code, ...$args);
    }

    /**
     * @return string
     */
    protected static function getClassException(): string
    {
        return CPWAException::class;
    }

    /**
     * @param mixed $_ = null
     *
     * @return CAppFineException
     * @throws Exception
     */
    protected static function missingParameterError($_ = null): CPWAException
    {
        return self::getException(
            self::getClassException()::MISSING_PARAMETERS,
            Response::HTTP_PRECONDITION_FAILED,
            $_
        );
    }

    /**
     * @param string $msg         = ''
     * @param int    $code        = 0
     * @param int    $status_code = 0
     *
     * @return HttpException
     */
    protected static function genericException(
        string $msg = '',
        int $code = 0,
        int $status_code = 400
    ): HttpException {
        return new HttpException($status_code, $msg, [], $code);
    }

    /**
     * @param mixed $_ = null
     *
     * @return CAppFineException
     * @throws Exception
     */
    protected static function invalidParameterError($_ = null): CPWAException
    {
        return self::getException(self::getClassException()::INVALID_ARGUMENTS, Response::HTTP_PRECONDITION_FAILED, $_);
    }

    /**
     * @param mixed $_ = null
     *
     * @return CAppFineException
     * @throws Exception
     */
    protected static function noPermissionError($_ = null): CPWAException
    {
        return self::getException(self::getClassException()::NO_PERMISSION, Response::HTTP_FORBIDDEN, $_);
    }

    /**
     * @param mixed $_ = null
     *
     * @return CAppFineException
     * @throws Exception
     */
    protected static function invalidStoredObject($_ = null): CPWAException
    {
        return self::getException(self::getClassException()::INVALID_STORE, Response::HTTP_PRECONDITION_FAILED, $_);
    }

    /**
     * @param CStoredObject $object
     * @param string        $field
     *
     * @return bool
     */
    protected static function isRefField(CStoredObject $object, string $field): bool
    {
        $props = $object->getProps();
        $prop  = CMbArray::get($props, $field, "");

        // determine is field is an ref
        $is_ref = strpos($prop, "ref") === 0;

        // determine is field is referenced by an ref (meta)
        if (!$is_ref) {
            $meta_props = array_filter(
                $props,
                function ($prop) use ($field) {
                    return strpos($prop, "meta|$field") !== false;
                }
            );
            $is_meta    = count($meta_props) > 0;
        }

        return $is_ref || $is_meta;
    }

    /**
     * @param string[]|string $attributes
     * @param string[]|string $available_attributes
     *
     * @return bool
     */
    protected function checkAvaillableAttributes($attributes, $available_attributes): bool
    {
        if (!is_array($attributes)) {
            $attributes = [$attributes];
        }

        if (!is_array($available_attributes)) {
            $available_attributes = [$available_attributes];
        }

        foreach ($attributes as $attribute) {
            if (!in_array($attribute, $available_attributes)) {
                return false;
            }
        }

        return true;
    }
}
