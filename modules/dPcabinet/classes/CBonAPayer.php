<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Facturation\CReglement;
use Ox\Mediboard\Mediusers\CMediusers;

class CBonAPayer extends CMbObject
{
    public const RELATIONS_DEFAULT = [];

    // API Resource Name
    public const RESOURCE_TYPE = 'bonAPayer';

    // Relations
    public const FIELDSET_UID      = 'uid';
    public const FIELDSET_DATETIME = 'datetime';
    public const FIELDSET_PAYMENT  = 'payment';

    /** @var int */
    public $bon_a_payer_id;

    /** @var CMediusers */
    public $praticien_id;
    /** @var string */
    public $context_class;
    /** @var int */
    public $context_id;
    /** @var string */
    public $creation_datetime;
    /** @var string */
    public $paiement_datetime;
    /** @var float */
    public $montant;
    /** @var CConsultation */
    public $_ref_context;
    /**
     * Remote server acknowledgement about payment process. Valued if payment request is successful (OK), or if payment
     * request has failed (with internationalized error message).
     *
     * @var string
     */
    public $ack;

    /**
     * @var bool
     */
    public $_no_synchro_eai = false;

    /**
     * @inheritDoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table = 'bon_a_payer';
        $spec->key   = 'bon_a_payer_id';

        return $spec;
    }

    /**
     * @inheritDoc
     */
    public function getProps(): array
    {
        $props                      = parent::getProps();
        $props['praticien_id']      = 'ref notNull class|CMediusers back|bon_a_payer fieldset|uid';
        $props['context_class']     = 'enum list|CConsultation notNull fieldset|uid';
        $props['context_id']        = 'ref notNull class|CMbObject meta|context_class back|bon_a_payer fieldset|uid';
        $props['creation_datetime'] = 'dateTime fieldset|datetime';
        $props['paiement_datetime'] = 'dateTime fieldset|payment';
        $props['montant']           = 'currency fieldset|payment';
        $props['ack']               = 'text fieldset|payment';

        return $props;
    }

    /**
     * @return string|void|null
     * @throws Exception
     */
    public function store(): ?string
    {
        $consult = $this->loadRefContext();
        $facture = $consult->loadRefFacture();

        //Création du réglement lors du paiement signalé par l'interop
        if ($this->fieldModified('paiement_datetime')) {
            $reglement               = new CReglement();
            $reglement->date         = CMbDT::dateTime();
            $reglement->montant      = $this->montant;
            $reglement->object_class = $facture->_class;
            $reglement->object_id    = $facture->_id;
            $reglement->emetteur     = 'patient';
            $reglement->mode         = 'CB';
            if ($msg = $reglement->store()) {
                return $msg;
            }
        }

        return parent::store();
    }

    /**
     * Load context
     *
     * @return CConsultation|CStoredObject
     * @throws Exception
     */
    public function loadRefContext(): CConsultation
    {
        return $this->_ref_context = $this->loadFwdRef('context_id');
    }

    /**
     * Gets `CBonAPayer` objects default API fieldsets.
     *
     * @return string[]
     */
    public static function getFieldsets(): array
    {
        return [
            self::FIELDSET_UID,
            self::FIELDSET_PAYMENT,
            self::FIELDSET_DATETIME,
        ];
    }
}
