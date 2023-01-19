<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Entity;

use Ox\Import\Framework\Exception\ExternalReferenceException;

/**
 * Description
 */
class ExternalReference
{
    /** @var string */
    private $name;

    /** @var mixed */
    private $id;

    /** @var bool */
    private $mandatory;

    /** @var string */
    public const UTILISATEUR = "utilisateur";

    /** @var string */
    public const PATIENT = "patient";

    /** @var string */
    public const PLAGE_CONSULTATION = "plage_consultation";

    /** @var string */
    public const PLAGE_CONSULTATION_AUTRE = "plage_consultation_autre";

    /** @var string */
    public const SEJOUR = "sejour";

    /** @var string */
    public const CONSULTATION = "consultation";

    /** @var string */
    public const CONSULTATION_AUTRE = "consultation_autre";

    /** @var string */
    public const MEDECIN = "medecin";

    /** @var string */
    public const MEDECIN_USER = "medecin_user";

    /** @var string */
    public const EVENEMENT = "evenement_patient";

    /** @var string */
    public const INJECTION = "injection";

    /** @var string */
    public const INTERVENTION = "intervention";

    /** @var string */
    public const OBSERVATION_RESULT_SET = "observation_result_set";

    /** @var string */
    public const IDENTIFIER = "observation_identifier";

    /** @var string */
    public const FILE = "file";

    /** @var string */
    public const LABEL = "label";

    /** @var string */
    public const VALIDATOR = "validator";

    /** @var string */
    public const RESPONSIBLE = "responsible";

    /** @var string */
    public const OBSERVATION_RESULT = "observation_result";

    /** @var string */
    public const UNIT = "observation_value_unit";

    /** @var string */
    public const OBSERVATION_EXAM = "observation_exam";

    /** @var string */
    public const OBSERVATION_RESPONSIBLE = "observation_responsible";

    /** @var string */
    public const OBSERVATION_PATIENT = "observation_patient";

    /**
     * ExternalReference constructor.
     *
     * @param string $name
     * @param mixed  $id
     * @param bool   $mandatory
     *
     * @throws ExternalReferenceException
     */
    public function __construct(string $name, $id, bool $mandatory)
    {
        if (is_null($id) && $mandatory) {
            throw new ExternalReferenceException('ExternalReference-error-Id is null for %s', $name);
        }

        $this->name      = $name;
        $this->id        = $id;
        $this->mandatory = $mandatory;
    }

    /**
     * @param string $name
     * @param mixed  $id
     *
     * @return self
     * @throws ExternalReferenceException
     */
    public static function getMandatoryFor(string $name, $id): self
    {
        return new self($name, $id, true);
    }

    /**
     * @param string $name
     * @param mixed  $id
     *
     * @return self
     * @throws ExternalReferenceException
     */
    public static function getNotMandatoryFor(string $name, $id): self
    {
        return new self($name, $id, false);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isMandatory(): bool
    {
        return $this->mandatory;
    }
}
