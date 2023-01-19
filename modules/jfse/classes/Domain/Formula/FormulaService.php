<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Formula;

use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\ApiClients\FormulaClient;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Exceptions\Formula\FormulaException;
use Ox\Mediboard\Jfse\Mappers\FormulaMapper;
use Ox\Mediboard\Jfse\Mappers\FormulaOperandMapper;

/**
 * Class FormulaService
 *
 * @package Ox\Mediboard\Jfse\Domain\Formula
 */
class FormulaService extends AbstractService
{
    /** @var FormulaClient */
    protected $client;

    /**
     * FormulaService constructor.
     *
     * @param FormulaClient|null $client
     */
    public function __construct(FormulaClient $client = null)
    {
        $this->client = $client ?? new FormulaClient();
    }

    /**
     * @return array
     */
    public function listFormulaOperands(): array
    {
        $results  = [];
        $response = $this->client->getListeOperandes();
        $data     = FormulaOperandMapper::getOperandsFromResponse($response);
        foreach ($data as $operand) {
            $results[] = FormulaOperand::hydrate($operand);
        }

        return $results;
    }

    /**
     * @return array
     */
    public function listFormulas(): array
    {
        $results  = [];
        $response = $this->client->getListeFormulesHorsSts();

        return FormulaMapper::getFormulasFromResponse($response->getContent());
    }

    public function save(
        string $nom,
        float $multiplicateur,
        float $plafond,
        string $operande1,
        string $operande2,
        string $operateur,
        int $idFormule = 0
    ): bool {
        $this->client->save(
            $nom,
            $multiplicateur,
            $plafond,
            $operande1,
            $operande2,
            $operateur,
            $idFormule
        );

        return true;
    }

    public function delete(int $formule_id): Response
    {
        return $this->client->delete($formule_id);
    }

    /**
     * @return Formula[]
     */
    public function listFormulasFromFSE(string $invoice_id): array
    {
        $formulas_response = $this->client->getFormulasFromInvoice($invoice_id);

        return FormulaMapper::arrayToFormulas($formulas_response->getContent());
    }
}
