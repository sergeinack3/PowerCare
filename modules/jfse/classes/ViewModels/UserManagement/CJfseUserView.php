<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\UserManagement;

use DateTime;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\UserManagement\User;
use Ox\Mediboard\Jfse\ViewModels\CCpsSituation;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;
use Ox\Mediboard\Mediusers\CMediusers;

class CJfseUserView extends CJfseViewModel
{
    /** @var int The id of the user */
    public $id;

    /** @var int The id of the establishment the user belongs to */
    public $establishment_id;

    /** @var string The user's login */
    public $login;

    /** @var string */
    public $password;

    /** @var int The code of the type of national identification number */
    public $national_identification_type_code;

    /** @var string */
    public $national_identification_number;

    /** @var int The civility code */
    public $civility_code;

    /** @var string The civility label */
    public $civility_label;

    /** @var string The last name */
    public $last_name;

    /** @var string The first name */
    public $first_name;

    /** @var string */
    public $address;

    /** @var string */
    public $installation_date;

    /** @var string */
    public $installation_zone_under_medicalized_date;

    /** @var bool */
    public $ccam_activation;

    /** @var string The health insurance agency which is attached to the user */
    public $health_insurance_agency;

    /** @var bool Indicate if the user makes fse in health center mode */
    public $health_center;

    /** @var bool */
    public $cnda_mode;

    /** @var bool Indicate if the user can make fses without his cps card */
    public $cardless_mode;

    /** @var int Indicate if the user must set the care path of the patient when making an fse */
    public $care_path;

    /** @var int The type of CPX card */
    public $card_type;

    /** @var int */
    public $last_fse_number;

    /** @var bool */
    public $formatting;

    /** @var string */
    public $substitute_number;

    /** @var string */
    public $substitute_last_name;

    /** @var string */
    public $substitute_first_name;

    /** @var int */
    public $substitute_situation_number;

    /** @var string */
    public $substitute_rpps_number;

    /** @var int */
    public $substitution_session;

    /** @var int */
    public $invoicing_number;

    /** @var CJfseUserParameter[] */
    public $parameters;

    /** @var CCpsSituation */
    public $situation;

    /** @var int The id of the JfseUser data model */
    public $mediuser_id;

    /** @var int */
    public $jfse_user_id;

    /** @var CMediusers */
    public $_mediuser;

    /** @var CJfseEstablishmentView */
    public $_establishment;

    /** @var CJfseUser */
    public $_data_model;

    /**
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['id']                                       = 'num notNull';
        $props['establishment_id']                         = 'num';
        $props['login']                                    = 'str';
        $props['password']                                 = 'str';
        $props['national_identification_type_code']        = 'num';
        $props['national_identification_number']           = 'str';
        $props['civility_code']                            = 'num';
        $props['civility_label']                           = 'str';
        $props['last_name']                                = 'str';
        $props['first_name']                               = 'str';
        $props['address']                                  = 'str';
        $props['installation_date']                        = 'date';
        $props['installation_zone_under_medicalized_date'] = 'date';
        $props['ccam_activation']                          = 'bool';
        $props['health_insurance_agency']                  = 'str';
        $props['health_center']                            = 'bool';
        $props['cnda_mode']                                = 'bool';
        $props['cardless_mode']                            = 'bool';
        $props['care_path']                                = 'num';
        $props['card_type']                                = 'num';
        $props['last_fse_number']                          = 'num';
        $props['formatting']                               = 'bool';
        $props['substitute_number']                        = 'str';
        $props['substitute_last_name']                     = 'str';
        $props['substitute_first_name']                    = 'str';
        $props['substitute_situation_number']              = 'num';
        $props['substitute_rpps_number']                   = 'str';
        $props['substitution_session']                     = 'num';
        $props['invoicing_number']                         = 'num';
        $props['mediuser_id']                              = 'ref class|CMediusers';
        $props['jfse_user_id']                             = 'ref class|CJfseUser';

        return $props;
    }

    /**
     * @return CMediusers
     */
    public function loadMediuser(): CMediusers
    {
        if (!$this->_mediuser) {
            if ($this->mediuser_id) {
                $this->_mediuser = CMediusers::get($this->mediuser_id);
                $this->_mediuser->loadRefFunction();
            } else {
                $this->_mediuser = new CMediusers();
            }
        }

        return $this->_mediuser;
    }

    /**
     * @param AbstractEntity $entity
     *
     * @return self|null
     */
    public static function getFromEntity(AbstractEntity $entity): ?CJfseViewModel
    {
        if (!$entity instanceof User) {
            return null;
        }

        $user             = parent::getFromEntity($entity);
        $user->parameters = [];

        if ($entity->hasParameters()) {
            $parameters = $entity->getParameters();
            foreach ($parameters as $parameter) {
                $user->parameters[] = CJfseUserParameter::getFromEntity($parameter);
            }
        }

        if ($user->installation_date instanceof DateTime) {
            $user->installation_date = $user->installation_date->format('Y-m-d');
        }

        if (
            $user->installation_zone_under_medicalized_date instanceof DateTime
        ) {
            $user->installation_zone_under_medicalized_date =
                $user->installation_zone_under_medicalized_date->format('Y-m-d');
        }

        if ($entity->getSituation()) {
            $user->situation = CCpsSituation::getFromEntity($entity->getSituation());
        }

        $jfse_user          = $entity->loadDataModel();
        $user->mediuser_id  = $jfse_user->mediuser_id;
        $user->jfse_user_id = $jfse_user->_id;
        $user->_data_model = $jfse_user;

        if ($entity->hasEstablishment()) {
            $user->_establishment = CJfseEstablishmentView::getFromEntity($entity->getEstablishment());
        }

        $user->loadMediuser();

        return $user;
    }

    /**
     * @param User[] $users
     * @param bool   $slice
     * @param int    $start
     *
     * @return CJfseUserView[]
     */
    public static function getFromUsers(array $users, bool $slice = false, int $start = 0): array
    {
        if ($slice && count($users) > 20) {
            $users = array_slice($users, $start, 20);
        }

        $user_views = [];
        foreach ($users as $user) {
            $user_views[] = CJfseUserView::getFromEntity($user);
        }

        return $user_views;
    }
}
