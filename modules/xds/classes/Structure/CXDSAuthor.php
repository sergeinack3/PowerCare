<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

use Ox\Interop\Xds\CXDSTools;

class CXDSAuthor implements XDSElementInterface
{
    /** @var array */
    public $author_institution;

    /** @var array */
    public $author_person;

    /** @var string */
    public $author_role;

    /** @var array */
    public $author_speciality;

    /**
     * @param array $author_institution
     */
    public function setAuthorInstitution(array $author_institution): void
    {
        $this->author_institution = $author_institution;
    }

    /**
     * @param array $author_person
     */
    public function setAuthorPerson(array $author_person): void
    {
        $this->author_person = $author_person;
    }

    /**
     * @param string $author_role
     */
    public function setAuthorRole(string $author_role): void
    {
        $this->author_role = $author_role;
    }

    /**
     * @param array $author_speciality
     */
    public function setAuthorSpeciality(array $author_speciality): void
    {
        $this->author_speciality = $author_speciality;
    }

    /**
     * @return string
     */
    public function getAuthorInstitution(): ?string
    {
        if (!$this->author_institution) {
            return null;
        }

        return CXDSTools::serializeHL7v2Components($this->author_institution);
    }

    /**
     * @return string
     */
    public function getAuthorPerson(): ?string
    {
        if (!$this->author_person) {
            return null;
        }

        return CXDSTools::serializeHL7v2Components($this->author_person);
    }

    /**
     * @return string
     */
    public function getAuthorSpeciality(): ?string
    {
        if (!$this->author_speciality) {
            return null;
        }

        return CXDSTools::serializeHL7v2Components($this->author_speciality);
    }

    /**
     * @return string
     */
    public function getAuthorRole(): ?string
    {
        return $this->author_role;
    }
}
