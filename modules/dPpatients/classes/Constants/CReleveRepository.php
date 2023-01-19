<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Constants;

use Exception;
use Ox\Core\CMbArray;

class CReleveRepository
{
    /**
     * @param CConstantReleve   $releve
     * @param CAbstractConstant[]|CAbstractConstant $constants
     */
    public function addConstant(CConstantReleve $releve, $constants): CActionReport
    {
        $data = ['releve' => $releve, 'constants' => $constants];
        return $this->addConstants([$data]);
    }

    /**
     * @param $data
     */
    public function addConstants(array $data): CActionReport
    {
        CConstantReleve::$report = new CActionReport();
        $releves = [];
        foreach ($data as $datum) {
            try {
                /** @var $releve CConstantReleve */
                if (!$releve = CMbArray::get($datum, 'releve')) {
                    throw new CConstantException(CConstantException::INVALID_ARGUMENT, "releve");
                }

                if (!$releve instanceof CConstantReleve) {
                    throw new CConstantException(
                        CConstantException::INVALID_ARGUMENT,
                        "argument releve is not " . CConstantReleve::class
                    );
                }

                if (!$constants = CMbArray::get($datum, 'constants')) {
                    throw new CConstantException(CConstantException::INVALID_ARGUMENT, "constants");
                }

                if (!is_array($constants)) {
                    $constants = [$constants];
                }

                if ($releve->type === null) {
                    $releve->type = $this->determineType($constants);
                }

                // save releve
                $releve->save();
                $releves[$releve->_id] = $releve;

                // save constants
                $releve->saveConstants($constants);
            } catch (CConstantException $exception) {
                CConstantReleve::$report->addException($exception);
            }
        }

        foreach ($releves as $releve) {
            if (!$releve->isCalculatedConstantsActive()) {
                continue;
            }

            try {
                $releve->updateCalculatedConstants();
            } catch (CConstantException $exception) {
                CConstantReleve::$report->addException($exception);
            }
        }

        return CConstantReleve::extractReport();
    }

    /**
     * @param CConstantReleve $releve
     *
     * @return bool
     * @throws CConstantException
     */
    public function delete(CConstantReleve $releve): bool {
        return $releve->storeInactive();
    }


    /**
     * @param CAbstractConstant[]|CAbstractConstant $constants
     *
     * @return bool
     * @throws CConstantException
     */
    public function deleteConstants($constants): bool
    {
        if (!is_array($constants)) {
            $constants = [$constants];
        }

        $result = count($constants) > 0;
        foreach ($constants as $constant) {
            if (!$constant || !$result) {
                $result = false;
                continue;
            }

            if (!$constant->storeInactive(true)) {
                $result = $constant->_ref_releve->storeInactive(false);
            }
        }

        return $result;
    }

    /**
     * @param CConstantReleve     $releve
     * @param CAbstractConstant[] $constants
     */
    private function determineType(array $constants): ?int
    {
        $types = array_unique(
            array_map(
                function ($constant) {
                    return $constant->getRefSpec()->period;
                },
                $constants
            )
        );

        if (count($types) !== 1) {
            return null;
        }

        return reset($types);
    }

    /**
     * @param CConstantFilter $filter
     * @param int|null        $offset
     *
     * @return CAbstractConstant|null
     * @throws Exception
     */
    public function loadConstant(CConstantFilter $filter, ?int $offset = null): ?CAbstractConstant
    {
        if ($offset === null) {
            $offset = $filter->getOffset();
        }

        $limit = $offset !== null ? "$offset,1" : "1";
        $filter->setLimit($limit);
        $constants = $filter->getResults(true);

        return !$constants ? null : reset($constants);
    }

    /**
     * @param CConstantFilter $filter
     * @param bool            $merge
     *
     * @return CAbstractConstant[]
     * @throws Exception
     */
    public function loadConstants(CConstantFilter $filter, bool $merge = false): array
    {
        return $filter->getResults($merge);
    }

    /**
     * @param CConstantFilter $filter
     *
     * @return int
     * @throws Exception
     */
    public function countConstants(CConstantFilter $filter): int
    {
        return $filter->countResults();
    }
}
