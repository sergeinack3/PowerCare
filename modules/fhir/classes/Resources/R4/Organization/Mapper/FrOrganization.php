<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Organization\Mapper;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeExtension;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Profiles\CFHIRInteropSante;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\Organization\Profiles\InteropSante\CFHIRResourceOrganizationFR;
use Ox\Mediboard\Patients\CExercicePlace;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * Description
 */
class FrOrganization extends Organization
{
    /** @var CExercicePlace */
    protected $object;

    /** @var CFHIRResourceOrganizationFR */
    protected CFHIRResource $resource;

    public function mapExtension(): array
    {
        return [
            CFHIRDataTypeExtension::addExtension(
                'http://interopsante.org/fhir/StructureDefinition/FrOrganizationShortName',
                [
                    'valueString' => $this->object->raison_sociale,
                ]
            ),
        ];
    }

    public function onlyProfiles(): array
    {
        return [CFHIRInteropSante::class];
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function mapIdentifier(): array
    {
        $identifiers = parent::mapIdentifier();
        // SYSTEM
        $system = 'http://interopsante.org/CodeSystem/fr-v2-0203';

        // USE
        $use = 'usual';

        if ($this->object->finess_juridique) {
            // FINEJ
            $finejValue = $this->object->finess_juridique;
            $coding = (new CFHIRDataTypeCoding())
                ->setCode('FINEJ')
                ->setDisplay('FINESS d\'entité juridique')
                ->setSystem($system);
            $type = (new CFHIRDataTypeCodeableConcept())->setCoding($coding);

            // add FINEJ as identifier
            $identifiers[] = CFHIRDataTypeIdentifier::makeIdentifier($finejValue, $system)
                ->setUse(new CFHIRDataTypeCode($use))
                ->setType($type);
        }

        if ($this->object->finess) {
            // FINEG
            $finegValue = $this->object->finess;
            $coding     = (new CFHIRDataTypeCoding())
                ->setCode('FINEG')
                ->setDisplay('FINESS d\'entité géographique')
                ->setSystem($system);
            $type       = (new CFHIRDataTypeCodeableConcept())->setCoding($coding);

            // add FINEG as identifier
            $identifiers[] = CFHIRDataTypeIdentifier::makeIdentifier($finegValue, $system)
                ->setType($type)
                ->setUse(new CFHIRDataTypeCode($use));
        }

        if ($this->object->siren) {
            // SIREN
            $sirenValue = $this->object->siren;
            $coding     = (new CFHIRDataTypeCoding())
                ->setCode('SIREN')
                ->setDisplay('Identification de l\'organisation au SIREN')
                ->setSystem($system);
            $type       = (new CFHIRDataTypeCodeableConcept())->setCoding($coding);

            // add SIREN as identifier
            $identifiers[] = CFHIRDataTypeIdentifier::makeIdentifier($sirenValue, $system)
                ->setUse(new CFHIRDataTypeCode($use))
                ->setType($type);
        }

        if ($this->object->siret) {
            // SIRET
            $siretValue = $this->object->siret;

            $coding     = (new CFHIRDataTypeCoding())
                ->setCode('SIRET')
                ->setDisplay('Identification de l\'organisation au SIRET')
                ->setSystem($system);
            $type       = (new CFHIRDataTypeCodeableConcept())->setCoding($coding);

            // add SIRET as identifier
            $identifiers[] = CFHIRDataTypeIdentifier::makeIdentifier($siretValue, $system)
                ->setType($type)
                ->setUse(new CFHIRDataTypeCode($use));
        }

        return $identifiers;
    }

    public function mapType(): array
    {
        $types = [];

        // organizationType
        $system           = "https://simplifier.net/frenchprofiledfhirar/v2-3307";
        $code             = "GROUP";
        $display          = "Groupe privé/hospitalier";
        $coding           = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        $organizationType = $coding;

        // secteurActiviteRASS
        $system  = "https://mos.esante.gouv.fr/NOS/TRE_R02-SecteurActivite/FHIR/TRE-R02-SecteurActivite";
        $code    = "SA04";
        $display = "Etablissement privé non PSPH";
        //$version             = 20201030120000;
        //$user_selected             = true;
        $coding              = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        $secteurActiviteRASS = $coding;

        // categorieEtablissementRASS
        $system  = "https://mos.esante.gouv.fr/NOS/TRE_R66-CategorieEtablissement/FHIR/TRE-R66-CategorieEtablissement";
        $code    = "106";
        $display = "Centre hospitalier, ex Hôpital local";
        //$version             = 20210528120000;
        //$user_selected             = true;
        $coding                     = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        $categorieEtablissementRASS = $coding;

        $types[] = CFHIRDataTypeCodeableConcept::addCodeable($organizationType, '');
        $types[] = CFHIRDataTypeCodeableConcept::addCodeable($secteurActiviteRASS, '');
        $types[] = CFHIRDataTypeCodeableConcept::addCodeable($categorieEtablissementRASS, '');

        return $types;
    }
}
