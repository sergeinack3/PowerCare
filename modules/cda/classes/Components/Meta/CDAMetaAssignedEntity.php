<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Meta;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CEntity;
use Ox\Core\CMbArray;
use Ox\Core\CPerson;
use Ox\Core\CStoredObject;
use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Datatypes\Base\CCDAAD;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDATEL;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_AssignedAuthor;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_AssignedEntity;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Organization;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Person;
use Ox\Interop\Eai\CItemReport;
use Ox\Mediboard\Etablissement\CEtabExterne;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CExercicePlace;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientINSNIR;
use phpseclib3\File\ASN1\Maps\ExtKeyUsageSyntax;


/**
 * Class CDAMetaAssignedEntity
 * @package Ox\Interop\Cda\Components\Meta
 */
class CDAMetaAssignedEntity extends CDAMetaAssigned
{
    /**
     * CDAMetaAssignedEntity constructor.
     *
     * @param CCDAFactory $factory
     * @param             $entity
     * @param array       $override_options
     */
    public function __construct(CCDAFactory $factory, $entity, array $override_options = [])
    {
        parent::__construct($factory, $entity, $override_options);

        $this->content = new CCDAPOCD_MT000040_AssignedEntity();
    }
}
