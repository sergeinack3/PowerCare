<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Invoicing;

use Ox\Mediboard\Jfse\Api\Message;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\Invoicing\ThirdPartyPaymentAssistant;
use Ox\Mediboard\Jfse\ViewModels\CJfseMessage;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;
use Ox\Mediboard\Jfse\ViewModels\Convention\CConvention;
use Ox\Mediboard\Jfse\ViewModels\Formula\CFormula;

class CThirdPartyPaymentAssistant extends CJfseViewModel
{
    /** @var CConvention[]  */
    public $conventions;

    /** @var CFormula[] */
    public $formulas;

    /** @var Message[] */
    public $messages;

    /** @var int 0: no action expected, 1: convention selection, 2: formula selection, 3: show ACS view */
    public $action;

    /** @var int 0: No choice expected, 1: VitalCard health insurance, 2: VitalCard complementary health insurance
     * 5 : user defined C2S, 6: user defined ACS, 7: user defined AME, 9: user defined health insurance,
     * 10: user defined AMC
     */
    public $choice;

    /** @var bool */
    public $transformation;

    /** @var string */
    public $transformation_label;

    /** @var string */
    public $conventions_service_message;

    /** @var string */
    public $formulas_service_message;

    /** @var array */
    public $idb_urls;

    /** @var array */
    public $clc_urls;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['action'] = "num";
        $props['choice'] = "num";
        $props['transformation'] = "bool";
        $props['transformation_label'] = "str";
        $props['conventions_service_message'] = "str";
        $props['formulas_service_message'] = "str";

        return $props;
    }

    /**
     * Create a new view model and sets its properties from the given entity
     *
     * @param AbstractEntity $entity
     *
     * @return static|null
     */
    public static function getFromEntity(AbstractEntity $entity): ?CJfseViewModel
    {
        /** @var ThirdPartyPaymentAssistant $entity */
        $view_model = parent::getFromEntity($entity);

        $view_model->conventions = [];
        if ($entity->getConventions()) {
            foreach ($entity->getConventions() as $convention) {
                $view_model->conventions[] = CConvention::getFromEntity($convention);
            }
        }

        $view_model->formulas = [];
        if ($entity->getFormulas()) {
            foreach ($entity->getFormulas() as $formula) {
                $view_model->formulas[] = CFormula::getFromEntity($formula);
            }
        }

        $view_model->messages = [];
        if ($entity->getMessages()) {
            foreach ($entity->getMessages() as $message) {
                $view_model->messages[] = CJfseMessage::getFromMessage($message);
            }
        }

        $view_model->idb_urls = $entity->getIdbUrls();
        $view_model->clc_urls = $entity->getClcUrls();

        return $view_model;
    }
}
