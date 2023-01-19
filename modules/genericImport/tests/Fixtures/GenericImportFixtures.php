<?php

/**
 * @package Mediboard\Admin\Tests
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Import\GenericImport\Tests\Fixtures;

use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Import\Framework\Entity\CImportCampaign;
use Ox\Import\Framework\Entity\CImportEntity;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * @description Generic import fixtures
 */
class GenericImportFixtures extends Fixtures implements GroupFixturesInterface
{
    public const TAG_SERVICE     = 'import-service1';
    public const TAG_CHAMBRE     = 'import-chambre1';
    public const TAG_LIT         = 'import-lit1';
    public const TAG_UF          = 'impuf1';
    public const TAG_ME          = 'impme1';
    public const TAG_MS          = 'impms1';
    public const TAG_SEJOUR      = 'import-sejour1';
    public const TAG_PATIENT     = 'import-patient1';
    public const TAG_AFFECTATION = 'import-affectation1';
    public const TAG_OPERATION   = 'import-operation-1';

    public const TAG_IMPORT_CAMPAIGN = 'import-campaign';

    public const TAG_REF_ENTITIE_PATIENT_1 = 'import-ref-entitie-pat-1';
    public const TAG_REF_ENTITIE_PATIENT_2 = 'import-ref-entitie-pat-2';
    public const TAG_REF_ENTITIE_PATIENT_3 = 'import-ref-entitie-pat-3';
    public const TAG_REF_ENTITIE_PATIENT_4 = 'import-ref-entitie-pat-4';
    public const TAG_REF_ENTITIE_PATIENT_5 = 'import-ref-entitie-pat-5';

    public const TAGS_REF_ENTITIES_PAT = [
        self::TAG_REF_ENTITIE_PATIENT_1,
        self::TAG_REF_ENTITIE_PATIENT_2,
        self::TAG_REF_ENTITIE_PATIENT_3,
        self::TAG_REF_ENTITIE_PATIENT_4,
        self::TAG_REF_ENTITIE_PATIENT_5,
    ];

    /**
     * @inheritDoc
     */
    public function load()
    {
        // Structural data for Sejour + Affectation
        $service = $this->generateService();
        $chambre = $this->generateChambre($service);
        $lit     = $this->generateLit($chambre);

        $this->generateUniteFonctionnelle();
        $this->generateModeEntreeSejour();
        $this->generateModeSortieSejour();

        $patient = $this->generatePatient(self::TAG_PATIENT);
        $sejour  = $this->generateSejour($patient);
        $this->generateAffectation($sejour, $service, $lit);
        $this->generateOperation($sejour);

        // Patients with ref entities
        $campaign       = new CImportCampaign();
        $campaign->name = uniqid();
        $this->store($campaign, self::TAG_IMPORT_CAMPAIGN);

        foreach (self::TAGS_REF_ENTITIES_PAT as $tag) {
            $patient = $this->generatePatient();
            $this->generateImportEntity($campaign, $patient, 'PATI', $tag);
        }
    }

    /**
     * @return string[]
     */
    public static function getGroup(): array
    {
        return ['genericimport_data'];
    }

    private function generateService(): CService
    {
        /** @var CService $service */
        $service            = CService::getSampleObject();
        $service->group_id  = CGroups::loadCurrent()->_id;
        $service->cancelled = 0;
        $this->store($service, self::TAG_SERVICE);

        return $service;
    }

    private function generateChambre(CService $service): CChambre
    {
        /** @var CChambre $chambre */
        $chambre             = CChambre::getSampleObject();
        $chambre->service_id = $service->service_id;
        $chambre->annule     = 0;
        $this->store($chambre, self::TAG_CHAMBRE);

        return $chambre;
    }

    private function generateLit(CChambre $chambre): CLit
    {
        /** @var CLit $lit */
        $lit             = CLit::getSampleObject();
        $lit->chambre_id = $chambre->_id;
        $lit->annule     = 0;
        $this->store($lit, self::TAG_LIT);

        return $lit;
    }

    private function generateUniteFonctionnelle(): void
    {
        /** @var CUniteFonctionnelle $uf */
        $uf           = CUniteFonctionnelle::getSampleObject();
        $uf->type     = "medicale";
        $uf->group_id = CGroups::loadCurrent()->_id;
        $this->store($uf, self::TAG_UF);
    }

    private function generateModeEntreeSejour(): void
    {
        /** @var CModeEntreeSejour $me */
        $me           = CModeEntreeSejour::getSampleObject();
        $me->group_id = CGroups::loadCurrent()->_id;
        $this->store($me, self::TAG_ME);
    }

    private function generateModeSortieSejour(): void
    {
        /** @var CModeSortieSejour $ms */
        $ms           = CModeSortieSejour::getSampleObject();
        $ms->group_id = CGroups::loadCurrent()->group_id;
        $this->store($ms, self::TAG_MS);
    }

    private function generatePatient(?string $tag = null): CPatient
    {
        /** @var CPatient $patient */
        $patient = CPatient::getSampleObject();
        $this->store($patient, $tag);

        return $patient;
    }

    private function generateSejour(CPatient $patient): CSejour
    {
        $sejour                = new CSejour();
        $sejour->patient_id    = $patient->_id;
        $sejour->praticien_id  = $this->getUser(true)->_id;
        $sejour->group_id      = CGroups::loadCurrent()->group_id;
        $sejour->type          = "comp";
        $sejour->libelle       = self::TAG_SEJOUR;
        $sejour->entree_prevue = CMbDT::dateTime();
        $sejour->sortie_prevue = CMbDT::dateTime("+ 3 DAYS");
        $this->store($sejour, self::TAG_SEJOUR);

        return $sejour;
    }

    private function generateAffectation(CSejour $sejour, CService $service, CLit $lit): void
    {
        $affectation             = new CAffectation();
        $affectation->sejour_id  = $sejour->_id;
        $affectation->service_id = $service->_id;
        $affectation->lit_id     = $lit->_id;
        $affectation->rques      = self::TAG_AFFECTATION;
        $affectation->entree     = CMbDT::dateTime();
        $affectation->sortie     = CMbDT::dateTime("+2 DAYS", $affectation->entree);

        $this->store($affectation, self::TAG_AFFECTATION);
    }

    private function generateImportEntity(
        CImportCampaign $campaign,
        CStoredObject $object,
        string $ext_class,
        string $tag
    ): void {
        $entity                     = new CImportEntity();
        $entity->import_campaign_id = $campaign->_id;
        $entity->external_class     = $ext_class;
        $entity->external_id        = uniqid();
        $entity->internal_class     = $object->_class;
        $entity->internal_id        = $object->_id;
        $this->store($entity, $tag);
    }

    private function generateOperation(CSejour $sejour): COperation
    {
        $operation                 = new COperation();
        $operation->sejour_id      = $sejour->_id;
        $operation->chir_id        = $this->getUser(true)->_id;
        $operation->date           = CMbDT::date();
        $operation->time_operation = CMbDT::time();
        $this->store($operation, self::TAG_OPERATION);

        return $operation;
    }
}
