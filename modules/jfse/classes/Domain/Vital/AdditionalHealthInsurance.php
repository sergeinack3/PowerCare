<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

class AdditionalHealthInsurance extends Period
{
    use InsuranceTrait;

    /** @var string */
    protected $number_b2;

    /** @var string */
    protected $number_edi;

    /** @var string */
    protected $subscriber_number;

    /** @var string */
    protected $treatment_indicator;

    /** @var string */
    protected $routing_code;

    /** @var string */
    protected $host_id;

    /** @var string */
    protected $domain_name;

    /** @var string */
    protected $services_type;

    /** @var AdditionalHealthInsuranceRuf[] */
    protected $rufs;

    /** @var InvoicingTla */
    protected $invoicing_tla;

    /** @var string */
    protected $contract_type;

    /** @var string */
    protected $pec;

    /** @var string */
    protected $secondary_criteria;

    /** @var string */
    protected $convention_type;

    /** @var bool */
    protected $paper_mode;

    /** @var bool */
    protected $rights_forcing;

    /** @var int */
    protected $type;

    /** @var string */
    protected $id;

    /** @var int */
    protected $reference_date;

    // Next, specific to the Adri service

    /** @var string */
    protected $management_code_mode;

    /** @var string */
    protected $guarantees_code;

    /**
     * @return ?string
     */
    public function getNumberB2(): ?string
    {
        return $this->number_b2;
    }

    /**
     * @return ?string
     */
    public function getNumberEdi(): ?string
    {
        return $this->number_edi;
    }

    /**
     * @return ?string
     */
    public function getSubscriberNumber(): ?string
    {
        return $this->subscriber_number;
    }

    /**
     * @return ?string
     */
    public function getTreatmentIndicator(): ?string
    {
        return $this->treatment_indicator;
    }

    /**
     * @return ?string
     */
    public function getRoutingCode(): ?string
    {
        return $this->routing_code;
    }

    /**
     * @return ?string
     */
    public function getHostId(): ?string
    {
        return $this->host_id;
    }

    /**
     * @return ?string
     */
    public function getDomainName(): ?string
    {
        return $this->domain_name;
    }

    /**
     * @return ?string
     */
    public function getServicesType(): ?string
    {
        return $this->services_type;
    }

    /**
     * @return AdditionalHealthInsuranceRuf[]
     */
    public function getRufs(): ?array
    {
        return $this->rufs;
    }

    /**
     * @return InvoicingTla
     */
    public function getInvoicingTla(): ?InvoicingTla
    {
        return $this->invoicing_tla;
    }

    /**
     * @return ?string
     */
    public function getContractType(): ?string
    {
        return $this->contract_type;
    }

    /**
     * @return ?string
     */
    public function getPec(): ?string
    {
        return $this->pec;
    }

    /**
     * @return ?string
     */
    public function getSecondaryCriteria(): ?string
    {
        return $this->secondary_criteria;
    }

    /**
     * @return ?string
     */
    public function getConventionType(): ?string
    {
        return $this->convention_type;
    }

    /**
     * @return ?bool
     */
    public function getPaperMode(): ?bool
    {
        return $this->paper_mode;
    }

    /**
     * @return ?bool
     */
    public function getRightsForcing(): ?bool
    {
        return $this->rights_forcing;
    }

    /**
     * @return ?int
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @return ?string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return ?int
     */
    public function getReferenceDate(): ?int
    {
        return $this->reference_date;
    }

    /**
     * @return ?string
     */
    public function getManagementCodeMode(): ?string
    {
        return $this->management_code_mode;
    }

    /**
     * @return ?string
     */
    public function getGuaranteesCode(): ?string
    {
        return $this->guarantees_code;
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        $data['label']                        = utf8_encode($this->label);
        $data['associated_services_contract'] = utf8_encode($this->associated_services_contract);
        $data['referral_sts_code']            = utf8_encode($this->referral_sts_code);
        $data['number_b2']                    = utf8_encode($this->number_b2);
        $data['number_edi']                   = utf8_encode($this->number_edi);
        $data['subscriber_number']            = utf8_encode($this->subscriber_number);
        $data['treatment_indicator']          = utf8_encode($this->treatment_indicator);
        $data['routing_code']                 = utf8_encode($this->routing_code);
        $data['host_id']                      = utf8_encode($this->host_id);
        $data['domain_name']                  = utf8_encode($this->domain_name);
        $data['services_type']                = utf8_encode($this->services_type);
        $data['rufs']                         = $this->rufs;
        $data['contract_type']                = utf8_encode($this->contract_type);
        $data['pec']                          = utf8_encode($this->pec);
        $data['secondary_criteria']           = utf8_encode($this->secondary_criteria);
        $data['convention_type']              = utf8_encode($this->convention_type);
        $data['paper_mode']                   = $this->paper_mode;
        $data['rights_forcing']               = $this->rights_forcing;
        $data['type']                         = $this->type;
        $data['id']                           = utf8_encode($this->id);
        $data['management_code_mode']         = utf8_encode($this->management_code_mode);
        $data['guarantees_code']              = utf8_encode($this->guarantees_code);

        return $data;
    }
}
