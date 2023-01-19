<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files\Tests\Fixtures;

use Ox\Core\CMbDT;
use Ox\Core\CModelObjectException;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CReadFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * Fixtures for testing the MailReceiverService
 */
class ReadFileFixtures extends Fixtures implements GroupFixturesInterface
{
    public const GROUP_NAME = 'read_file';

    public const GROUP_TAG     = 'read_file_group';
    public const PATIENT_TAG   = 'read_file_patient';
    public const SEJOUR_TAG    = 'read_file_sejour';
    public const FILE1_TAG     = 'read_file_file1';
    public const FILE2_TAG     = 'read_file_file2';
    public const READ_FILE_TAG = 'read_file_read_file';

    /** @var CGroups */
    protected $group;

    /** @var CPatient */
    protected $patient;

    /** @var CSejour */
    protected $sejour;

    /** @var CMediusers */
    protected $user;

    /** @var CFile */
    protected $file1;

    /** @var CFile */
    protected $file2;

    /**
     * @return string[]
     */
    public static function getGroup(): array
    {
        return [self::GROUP_NAME];
    }

    public function load(): void
    {
        $this->generateGroup();
        $this->generatePatient();
        $this->generateUser();
        $this->generateSejour();
        $this->generateFiles();
        $this->generateReadfile();
    }

    /**
     * @return void
     * @throws FixturesException
     */
    protected function generateGroup(): void
    {
        $this->group                 = new CGroups();
        $this->group->_name          = 'Test';
        $this->group->raison_sociale = $this->group->_name;
        $this->group->code           = 'Test';

        $this->store($this->group, self::GROUP_TAG);
    }

    /**
     * @return void
     * @throws FixturesException
     * @throws CModelObjectException
     */
    protected function generatePatient(): void
    {
        $this->patient = CPatient::getSampleObject();

        $this->store($this->patient, self::PATIENT_TAG);
    }

    protected function generateUser(): void
    {
        $this->user = self::getUser();
    }

    /**
     * @return void
     * @throws FixturesException
     */
    protected function generateSejour(): void
    {
        $this->sejour                = new CSejour();
        $this->sejour->patient_id    = $this->patient->_id;
        $this->sejour->praticien_id  = $this->user->_id;
        $this->sejour->group_id      = $this->group->_id;
        $this->sejour->type          = 'ambu';
        $this->sejour->entree_prevue = CMbDT::format(null, '%Y-%m-%d 08:00:00');
        $this->sejour->sortie_prevue = CMbDT::format(null, '%Y-%m-%d 12:00:00');
        $this->sejour->libelle       = 'test';

        $this->store($this->sejour, self::SEJOUR_TAG);
    }

    protected function generateFiles(): void
    {
        $this->file1 = $this->generateFile(self::FILE1_TAG);
        $this->file2 = $this->generateFile(self::FILE2_TAG);
    }

    protected function generateFile(string $tag): CFile
    {
        $file               = new CFile();
        $file->object_id    = $this->sejour->_id;
        $file->object_class = $this->sejour->_class;
        $file->file_name    = $tag . '.txt';
        $file->setContent('test');
        $file->fillFields();
        $file->updateFormFields();

        $this->store($file, $tag);

        return $file;
    }

    protected function generateReadfile(): void
    {
        $read_file               = new CReadFile();
        $read_file->object_class = $this->file1->_class;
        $read_file->object_id    = $this->file1->_id;
        $read_file->user_id      = $this->user->_id;
        $read_file->datetime     = 'now';

        $this->store($read_file, self::READ_FILE_TAG);
    }
}
