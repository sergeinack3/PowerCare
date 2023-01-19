<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\UserManagement;

use Exception;
use Ox\Mediboard\Jfse\DataModels\CJfseEstablishment;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Exceptions\UserManagement\EstablishmentException;

class Establishment extends AbstractEntity
{
    /** @var int */
    protected $id;

    /** @var int */
    protected $type;

    /** @var string */
    protected $exoneration_label;

    /** @var string */
    protected $health_center_number;

    /** @var string */
    protected $name;

    /** @var string */
    protected $category;

    /** @var string */
    protected $status;

    /** @var string */
    protected $invoicing_mode;

    /** @var CJfseEstablishment */
    protected $data_model;

    /** @var EstablishmentConfiguration */
    protected $configuration;

    /** @var User[] */
    protected $users;

    /** @var EmployeeCard[] */
    protected $employee_cards;

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
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getExonerationLabel(): ?string
    {
        return $this->exoneration_label;
    }

    /**
     * @return string
     */
    public function getHealthCenterNumber(): ?string
    {
        return $this->health_center_number;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getInvoicingMode(): ?string
    {
        return $this->invoicing_mode;
    }

    /**
     * @return CJfseEstablishment
     */
    public function getDataModel(): ?CJfseEstablishment
    {
        return $this->data_model;
    }

    /**
     * @return EstablishmentConfiguration
     */
    public function getConfiguration(): EstablishmentConfiguration
    {
        return $this->configuration;
    }

    public function hasConfiguration(): bool
    {
        return $this->configuration instanceof EstablishmentConfiguration;
    }

    /**
     * @param EstablishmentConfiguration $configuration
     */
    public function setConfiguration(EstablishmentConfiguration $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @param User[] $users
     *
     * @return Establishment
     */
    public function setUsers(array $users): Establishment
    {
        $this->users = $users;

        return $this;
    }

    public function hasUsers(): bool
    {
        return is_array($this->users) && count($this->users) >= 1;
    }

    /**
     * @return EmployeeCard[]
     */
    public function getEmployeeCards(): array
    {
        return $this->employee_cards;
    }

    /**
     * @param EmployeeCard[] $employee_cards
     *
     * @return Establishment
     */
    public function setEmployeeCards(array $employee_cards): Establishment
    {
        $this->employee_cards = $employee_cards;

        return $this;
    }

    public function hasEmployeeCards(): bool
    {
        return is_array($this->employee_cards) && count($this->employee_cards) >= 1;
    }

    /**
     * Loads the data object linked to the Jfse user's id
     *
     * @return CJfseEstablishment
     *
     * @throws Exception
     */
    public function loadDataModel(): CJfseEstablishment
    {
        if (!$this->data_model || !$this->data_model->_id) {
            $this->data_model          = new CJfseEstablishment();
            if ($this->id) {
                $this->data_model->jfse_id = $this->id;

                try {
                    $this->data_model->loadMatchingObjectEsc();
                } catch (Exception $e) {
                    throw EstablishmentException::persistenceError($e->getMessage(), $e);
                }
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
                throw EstablishmentException::persistenceError($error);
            }
        } catch (Exception $e) {
            throw EstablishmentException::persistenceError($e->getMessage(), $e);
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
     * @param int    $object_id
     * @param string $object_class
     *
     * @return bool
     * @throws Exception
     */
    public function linkDataModelToObject(int $object_id, string $object_class): bool
    {
        $this->loadDataModel();

        if (
            $this->data_model->object_id && $this->data_model->object_class
            && $this->data_model->object_id != $object_id && $this->data_model->object_class !== $object_class
        ) {
            throw EstablishmentException::establishmentAlreadyLinked($this->name, $this->id, $object_class);
        }

        $this->data_model->setLinkedObject($object_id, $object_class);

        return $this->storeDataModel();
    }

    /**
     * @return bool
     *
     * @throws Exception
     */
    public function unlinkDataModelFromObject(): bool
    {
        $this->loadDataModel();
        $this->data_model->unsetLinkedObject();

        return $this->storeDataModel();
    }

    /**
     * @return bool
     *
     * @throws EstablishmentException
     */
    public function deleteDataModel(): bool
    {
        $this->loadDataModel();

        try {
            if ($error = $this->data_model->delete()) {
                throw EstablishmentException::persistenceError($error);
            }
        } catch (Exception $e) {
            throw EstablishmentException::persistenceError($e->getMessage(), $e);
        }

        return true;
    }
}
