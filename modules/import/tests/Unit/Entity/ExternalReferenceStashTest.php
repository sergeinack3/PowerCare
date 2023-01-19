<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Tests\Unit\Entity;

use Ox\Import\Framework\Entity\ExternalReference;
use Ox\Import\Framework\Entity\ExternalReferenceStash;
use Ox\Import\Framework\Entity\Medecin;
use Ox\Import\Framework\Tests\Unit\GeneratorEntityTrait;
use Ox\Import\Framework\Transformer\DefaultTransformer;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Tests\OxUnitTestCase;

class ExternalReferenceStashTest extends OxUnitTestCase
{
    use GeneratorEntityTrait;

    /**
     * @var ExternalReferenceStash
     */
    private $external_reference_stash;

    /**
     * @var ExternalReference
     */
    private $external_reference;
    /**
     * @var Medecin
     */
    private $medecin;

    /**
     * @var DefaultTransformer
     */
    private $transformer;

    /**
     * @var CMedecin
     */
    private $c_medecin_after;

    public function setUp(): void
    {
        $this->transformer = new DefaultTransformer();

        //initialisation des médecins
        $this->medecin         = new Medecin();
        $this->medecin         = $this->generateExternalMedecin();
        $this->c_medecin_after = CMedecin::getSampleObject();

        //création du lien entre CMedecin et Medecin
        $this->external_reference_stash = new ExternalReferenceStash();
        $this->external_reference       = new ExternalReference('medecin', $this->medecin->getExternalId(), false);
    }

    public function testGetMbByExternalIdIfExist(): void
    {
        $this->external_reference_stash->addReference($this->external_reference, $this->c_medecin_after);
        $mb_object_id = $this->external_reference_stash->getMbIdByExternalId(
            'medecin',
            $this->medecin->getExternalId()
        );

        $this->assertEquals($this->c_medecin_after->_id, $mb_object_id);
    }

    public function testGetMbByExternalIdNotExist(): void
    {
        $this->external_reference_stash->addReference($this->external_reference, $this->c_medecin_after);
        $mb_object_id = $this->external_reference_stash->getMbIdByExternalId(
            'medecin',
            uniqid()
        );

        $this->assertNull($mb_object_id);
    }
}
