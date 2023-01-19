<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Invoicing;

use Ox\Mediboard\Jfse\Api\Message;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\Convention\Convention;
use Ox\Mediboard\Jfse\Domain\Formula\Formula;

final class ThirdPartyPaymentAssistant extends AbstractEntity
{
    /** @var Convention[]  */
    protected $conventions;

    /** @var Formula[] */
    protected $formulas;

    /** @var Message[] */
    protected $messages;

    /** @var int 0: no action expected, 1: convention selection, 2: formula selection, 3: show ACS view */
    protected $action;

    /** @var int 0: No choice expected, 1: VitalCard health insurance, 2: VitalCard complementary health insurance
     * 5 : user defined C2S, 6: user defined ACS, 7: user defined AME, 9: user defined health insurance,
     * 10: user defined AMC
     */
    protected $choice;

    /** @var bool */
    protected $transformation;

    /** @var string */
    protected $transformation_label;

    /** @var string */
    protected $conventions_service_message;

    /** @var string */
    protected $formulas_service_message;

    /** @var array */
    protected $idb_urls;

    /** @var array */
    protected $clc_urls;

    /**
     * @return Convention[]
     */
    public function getConventions(): array
    {
        return $this->conventions;
    }

    /**
     * @return Formula[]
     */
    public function getFormulas(): array
    {
        return $this->formulas;
    }

    /**
     * @return Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return int
     */
    public function getAction(): ?int
    {
        return $this->action;
    }

    /**
     * @return int
     */
    public function getChoice(): ?int
    {
        return $this->choice;
    }

    /**
     * @return bool
     */
    public function isTransformation(): ?bool
    {
        return $this->transformation;
    }

    /**
     * @return string
     */
    public function getTransformationLabel(): ?string
    {
        return $this->transformation_label;
    }

    /**
     * @return string
     */
    public function getConventionsServiceMessage(): ?string
    {
        return $this->conventions_service_message;
    }

    /**
     * @return string
     */
    public function getFormulasServiceMessage(): ?string
    {
        return $this->formulas_service_message;
    }

    /**
     * @return array
     */
    public function getIdbUrls(): array
    {
        return $this->idb_urls;
    }

    /**
     * @return array
     */
    public function getClcUrls(): array
    {
        return $this->clc_urls;
    }

    public function getConventionFromApplicableConventions(string $convention_id): ?Convention
    {
        $selected_convention = null;

        foreach ($this->conventions as $convention) {
            if ($convention->getConventionId() == $convention_id) {
                $selected_convention = $convention;
                break;
            }
        }

        return $selected_convention;
    }

    public function getFormulaFromApplicableFormulas(string $formula_number): ?Formula
    {
        $selected_formula = null;

        foreach ($this->formulas as $formula) {
            if ($formula->getFormulaNumber() == $formula_number) {
                $selected_formula = $formula;
                break;
            }
        }

        return $selected_formula;
    }
}
