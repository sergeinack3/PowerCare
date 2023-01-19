<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Api\Client;
use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\Physician;
use Ox\Mediboard\Jfse\Mappers\PrescribingPhysicianMapper;

class PrescribingPhysicianClient extends AbstractApiClient
{
    /** @var PrescribingPhysicianMapper */
    private $mapper;

    public function __construct(?Client $client = null, ?PrescribingPhysicianMapper $mapper = null)
    {
        parent::__construct($client);

        $this->mapper = $mapper ?? new PrescribingPhysicianMapper();
    }

    public function getPhysicianTypes(): Response
    {
        return $this->sendRequest(Request::forge('MED-getListeTypesMedecin'));
    }

    public function getPhysicianSpecialities(): Response
    {
        return $this->sendRequest(Request::forge('MED-getListeSpecialitesMedecin'));
    }

    public function getPrescribingPhysicianList(): Response
    {
        return $this->sendRequest(Request::forge('MED-getListeMedecinsPrescripteurs'));
    }

    public function getPhysicianList(): Response
    {
        return $this->sendRequest(Request::forge('MED-getListeMedecins'));
    }

    public function addOrUpdatePhysician(Physician $physician): Response
    {
        $physician_array = $this->mapper->makeStoreArrayFromPhysician($physician);

        return $this->sendRequest(Request::forge('MED-updateMedecinPrescripteur', $physician_array));
    }

    public function deletePhysician(int $physician_id): Response
    {
        $delete_data = $this->mapper->makeDeleteArray($physician_id);

        return $this->sendRequest(Request::forge('MED-deleteMedecinPrescripteur', $delete_data));
    }

    public function setPrescribingPhysician(
        string $invoice_id,
        DateTimeImmutable $date,
        string $origin,
        Physician $physician
    ): Response {
        $data = $this->mapper->makeSetPrescribingPhysicianArray($invoice_id, $date, $origin, $physician);

        return $this->sendRequest(Request::forge('FDS-setMedecinPrescripteur', $data));
    }
}
