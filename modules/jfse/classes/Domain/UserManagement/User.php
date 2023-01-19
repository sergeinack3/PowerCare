<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\UserManagement;

use DateTime;
use Exception;
use Ox\Mediboard\Jfse\DataModels\CJfseEstablishment;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\Cps\Situation;
use Ox\Mediboard\Jfse\Exceptions\UserManagement\UserException;

/**
 * Class User
 *
 * @package Ox\Mediboard\Jfse\Domain\UserManagement
 */
final class User extends AbstractEntity
{
    /** @var string The id of the user */
    protected $id;

    /** @var int The id of the establishment the user belongs to */
    protected $establishment_id;

    /** @var string The user's login */
    protected $login;

    /** @var string */
    protected $password;

    /** @var int The code of the type of national identification number */
    protected $national_identification_type_code;

    /** @var string */
    protected $national_identification_number;

    /** @var int The civility code */
    protected $civility_code;

    /** @var string The civility label */
    protected $civility_label;

    /** @var string The last name */
    protected $last_name;

    /** @var string The first name */
    protected $first_name;

    /** @var string */
    protected $address;

    /** @var DateTime */
    protected $installation_date;

    /** @var DateTime */
    protected $installation_zone_under_medicalized_date;

    /** @var bool */
    protected $ccam_activation;

    /** @var string The health insurance agency which is attached to the user */
    protected $health_insurance_agency;

    /** @var bool Indicate if the user makes fse in health center mode */
    protected $health_center;

    /** @var bool */
    protected $cnda_mode;

    /** @var bool Indicate if the user can make fses without his cps card */
    protected $cardless_mode;

    /** @var int Indicate if the user must set the care path of the patient when making an fse */
    protected $care_path;

    /** @var int The type of CPX card */
    protected $card_type;

    /** @var int */
    protected $last_fse_number;

    /** @var bool */
    protected $formatting;

    /** @var string */
    protected $substitute_number;

    /** @var string */
    protected $substitute_last_name;

    /** @var string */
    protected $substitute_first_name;

    /** @var int */
    protected $substitute_situation_number;

    /** @var string */
    protected $substitute_rpps_number;

    /** @var int */
    protected $substitution_session;

    /** @var UserParameter[] */
    protected $parameters;

    /** @var Situation */
    protected $situation;

    /** @var string Only used in the list of users by establishments */
    protected $invoicing_number;

    /** @var int Only used in the list of users by establishments */
    protected $situation_number;

    /** @var int The id of the JfseUser data model */
    protected $mediuser_id;

    /** @var Establishment */
    protected $establishment;

    /** @var CJfseUser */
    protected $data_model;

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getEstablishmentId(): ?int
    {
        return $this->establishment_id;
    }

    /**
     * @return string
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @return int
     */
    public function getNationalIdentificationTypeCode(): ?int
    {
        return $this->national_identification_type_code;
    }

    /**
     * @return string
     */
    public function getNationalIdentificationNumber(): ?string
    {
        return $this->national_identification_number;
    }

    /**
     * @return int
     */
    public function getCivilityCode(): ?int
    {
        return $this->civility_code;
    }

    /**
     * @return string
     */
    public function getCivilityLabel(): ?string
    {
        return $this->civility_label;
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    /**
     * @return string
     */
    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    /**
     * @return string
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @return DateTime
     */
    public function getInstallationDate(): ?DateTime
    {
        return $this->installation_date;
    }

    /**
     * @return DateTime
     */
    public function getInstallationZoneUnderMedicalizedDate(): ?DateTime
    {
        return $this->installation_zone_under_medicalized_date;
    }

    /**
     * @return bool
     */
    public function isCcamActivation(): ?bool
    {
        return $this->ccam_activation;
    }

    /**
     * @return string
     */
    public function getHealthInsuranceAgency(): ?string
    {
        return $this->health_insurance_agency;
    }

    /**
     * @return bool
     */
    public function isHealthCenter(): ?bool
    {
        return $this->health_center;
    }

    /**
     * @return bool
     */
    public function isCndaMode(): ?bool
    {
        return $this->cnda_mode;
    }

    /**
     * @return bool
     */
    public function isCardlessMode(): ?bool
    {
        return $this->cardless_mode;
    }

    /**
     * @return int
     */
    public function getCarePath(): ?int
    {
        return $this->care_path;
    }

    /**
     * @return int
     */
    public function getCardType(): ?int
    {
        return $this->card_type;
    }

    /**
     * @return int
     */
    public function getLastFseNumber(): ?int
    {
        return $this->last_fse_number;
    }

    /**
     * @return bool
     */
    public function isFormatting(): ?bool
    {
        return $this->formatting;
    }

    /**
     * @return string
     */
    public function getSubstituteNumber(): ?string
    {
        return $this->substitute_number;
    }

    /**
     * @return string
     */
    public function getSubstituteLastName(): ?string
    {
        return $this->substitute_last_name;
    }

    /**
     * @return string
     */
    public function getSubstituteFirstName(): ?string
    {
        return $this->substitute_first_name;
    }

    /**
     * @return int
     */
    public function getSubstituteSituationNumber(): ?int
    {
        return $this->substitute_situation_number;
    }

    /**
     * @return string
     */
    public function getSubstituteRppsNumber(): ?string
    {
        return $this->substitute_rpps_number;
    }

    /**
     * @return int
     */
    public function getSubstitutionSession(): ?int
    {
        return $this->substitution_session;
    }

    /**
     * @return UserParameter[]
     */
    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    /**
     * @return bool
     */
    public function hasParameters(): bool
    {
        return !empty($this->parameters);
    }

    /**
     * @return Situation
     */
    public function getSituation(): ?Situation
    {
        return $this->situation;
    }

    /**
     * @return int
     */
    public function getMediuserId(): ?int
    {
        return $this->mediuser_id;
    }

    public function getInvoicingNumber(): ?string
    {
        return $this->invoicing_number;
    }

    public function getSituationNumber(): ?int
    {
        return $this->situation_number;
    }

    /**
     * @return CJfseUser
     */
    public function getDataModel(): ?CJfseUser
    {
        return $this->data_model;
    }

    public function setIdFromSituation(): void
    {
        $this->id = $this->situation->getPractitionerId();
    }

    public function getEstablishment(): ?Establishment
    {
        return $this->establishment;
    }

    public function hasEstablishment(): bool
    {
        return !is_null($this->establishment);
    }

    public function setEstablishment(Establishment $establishment): User
    {
        $this->establishment = $establishment;

        return $this;
    }

    /**
     * Loads the data object linked to the Jfse user's id
     *
     * @return CJfseUser
     *
     * @throws Exception
     */
    public function loadDataModel(): CJfseUser
    {
        if (!$this->data_model || !$this->data_model->_id) {
            $this->data_model          = new CJfseUser();
            $this->data_model->jfse_id = $this->id;

            try {
                $this->data_model->loadMatchingObjectEsc();
                $this->mediuser_id = $this->data_model->mediuser_id;
            } catch (Exception $e) {
                throw UserException::persistenceError($e->getMessage(), $e);
            }
        }

        return $this->data_model;
    }

    /**
     * Stores the given data object.
     *
     * Throws a UserExceptions in case of error
     *
     * @return bool
     */
    protected function storeDataModel(): bool
    {
        try {
            if ($error = $this->data_model->store()) {
                throw UserException::persistenceError($error);
            }
        } catch (Exception $e) {
            throw UserException::persistenceError($e->getMessage(), $e);
        }

        return true;
    }

    /**
     * Creates the data model object, set the jfse user id and stores it
     *
     * @return true
     *
     * @throws Exception
     */
    public function createDataModel(): bool
    {
        $this->loadDataModel();

        return $this->storeDataModel();
    }

    /**
     * Link the data object
     *
     * @param int $mediuser_id
     *
     * @return bool
     * @throws Exception
     */
    public function linkDataModelToMediuser(int $mediuser_id): bool
    {
        $this->loadDataModel();

        if ($this->data_model->mediuser_id && $this->data_model->mediuser_id != $mediuser_id) {
            throw UserException::userAlreadyLinked("{$this->last_name} {$this->first_name}", $this->id);
        }

        $this->data_model->setMediuserId($mediuser_id);

        return $this->storeDataModel();
    }

    /**
     * @return bool
     *
     * @throws Exception
     */
    public function unlinkDataModelFromMediuser(): bool
    {
        $this->loadDataModel();
        $this->data_model->mediuser_id = '';

        return $this->storeDataModel();
    }

    /**
     * Link the data object
     *
     * @param string $establishment_id
     *
     * @return bool
     * @throws Exception
     */
    public function linkDataModelToEstablishment(string $establishment_id): bool
    {
        $this->loadDataModel();

        $establishment = CJfseEstablishment::getFromJfseId($establishment_id);

        if (!$establishment->_id) {
            throw UserException::establishmentNotFound($establishment_id);
        }
        if (
            $this->data_model->jfse_establishment_id
            && $this->data_model->jfse_establishment_id != $establishment->_id
        ) {
            throw UserException::userAlreadyLinked("{$this->last_name} {$this->first_name}", $this->id);
        }

        $this->data_model->setEstablishmentId($establishment->_id);

        return $this->storeDataModel();
    }

    /**
     * @return bool
     *
     * @throws Exception
     */
    public function unlinkDataModelFromEstablishment(): bool
    {
        $this->loadDataModel();
        $this->data_model->jfse_establishment_id = '';

        return $this->storeDataModel();
    }

    /**
     * @return bool
     *
     */
    public function deleteDataModel(): bool
    {
        $this->loadDataModel();

        try {
            if ($error = $this->data_model->delete()) {
                throw UserException::persistenceError($error);
            }
        } catch (Exception $e) {
            throw UserException::persistenceError($e->getMessage(), $e);
        }

        return true;
    }
}
