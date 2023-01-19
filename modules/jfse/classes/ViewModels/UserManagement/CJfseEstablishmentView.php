<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\UserManagement;

use Ox\Core\CMbObject;
use Ox\Mediboard\Jfse\DataModels\CJfseEstablishment;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\UserManagement\Establishment;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CJfseEstablishmentView extends CJfseViewModel
{
    /** @var int */
    public $id;

    /** @var string */
    public $type;

    /** @var string */
    public $exoneration_label;

    /** @var string */
    public $health_center_number;

    /** @var string */
    public $name;

    /** @var string */
    public $category;

    /** @var string */
    public $status;

    /** @var string */
    public $invoicing_mode;

    /** @var int */
    public $jfse_establishment_id;

    /** @var CEstablishmentConfiguration */
    public $configuration;

    /** @var CJfseUserView[] */
    public $users;

    /** @var CEmployeeCard[] */
    public $employee_cards;

    /** @var string */
    public $_object_class;

    /** @var int */
    public $_object_id;

    /** @var CMbObject */
    public $_object;

    /**
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['id']                    = 'num notNull';
        $props['type']                  = 'num';
        $props['exoneration_label']     = 'str';
        $props['health_center_number']  = 'str';
        $props['name']                  = 'str';
        $props['category']              = 'str';
        $props['status']                = 'num';
        $props['invoicing_mode']        = 'str';
        $props['jfse_establishment_id'] = 'ref class|CJfseEstablishment';
        $props['_object_class']         = 'enum list|CFunctions|CGroups';
        $props['_object_id']            = 'ref meta|_object_class';

        return $props;
    }

    public static function getFromEntity(AbstractEntity $entity): CJfseViewModel
    {
        /** @var Establishment $entity */
        $view_model = parent::getFromEntity($entity);

        $data_model                        = $entity->loadDataModel();
        $view_model->jfse_establishment_id = $data_model->_id;
        $view_model->_object_class         = $data_model->object_class;
        $view_model->_object_id            = $data_model->object_id;
        $view_model->_object               = $data_model->loadLinkedObject();

        if ($entity->hasConfiguration()) {
            $view_model->configuration = CEstablishmentConfiguration::getFromEntity($entity->getConfiguration());
        }

        if ($entity->hasUsers()) {
            $view_model->users = [];
            foreach ($entity->getUsers() as $user) {
                $view_model->users[] = CJfseUserView::getFromEntity($user);
            }
        }

        if ($entity->hasEmployeeCards()) {
            $view_model->employee_cards = [];
            foreach ($entity->getEmployeeCards() as $card) {
                $view_model->employee_cards[] = CEmployeeCard::getFromEntity($card);
            }
        }

        return $view_model;
    }

    /**
     * @param Establishment[] $establishments
     * @param bool            $slice
     * @param int             $start
     *
     * @return CJfseEstablishmentView[]
     */
    public static function getFromEstablishments(array $establishments, bool $slice = false, int $start = 0): array
    {
        if ($slice && count($establishments) > 20) {
            $establishments = array_slice($establishments, $start, 20);
        }

        $establishment_views = [];
        foreach ($establishments as $establishment) {
            $establishment_views[] = CJfseEstablishmentView::getFromEntity($establishment);
        }

        return $establishment_views;
    }
}
