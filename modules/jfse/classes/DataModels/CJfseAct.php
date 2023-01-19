<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\DataModels;

use Exception;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbSecurity;
use Ox\Mediboard\Ccam\CActe;
use Ox\Mediboard\Jfse\Exceptions\Invoice\InvoiceException;
use Ox\Mediboard\Jfse\Exceptions\MedicalAct\MedicalActException;
use Ox\Mediboard\Jfse\ViewModels\Invoicing\CJfseActView;

/**
 * Link an act (CActeNGAP, CActeCCAM, CActeLPP) to an invoice
 */
final class CJfseAct extends CMbObject
{
    /** @var int Primary key */
    public $jfse_act_id;

    /** @var string The id of the act in jFSE */
    public $jfse_id;

    /** @var int */
    public $jfse_invoice_id;

    /** @var string */
    public $act_class;

    /** @var int */
    public $act_id;

    /** @var CActe */
    public $_act;

    /** @var CJfseInvoice */
    public $_invoice;

    /** @var CJfseActView */
    public $_medical_act;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table = 'jfse_acts';
        $spec->key   = 'jfse_act_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['jfse_id']   = 'str notNull';
        $props['jfse_invoice_id'] = 'ref class|CJfseInvoice notNull back|jfse_acts cascade';
        $props['act_class'] = 'enum list|CActeNGAP|CActeCCAM|CActeLPP notNull';
        $props['act_id']    = 'ref meta|act_class notNull back|jfse_act_link cascade';

        return $props;
    }

    public function loadAct(): CActe
    {
        if (!$this->_act) {
            $this->_act = $this->loadFwdRef('act_id');
        }

        return $this->_act;
    }

    public function loadInvoice(): ?CJfseInvoice
    {
        if (!$this->_invoice) {
            $this->_invoice = $this->loadFwdRef('jfse_invoice_id');
        }

        return $this->_invoice;
    }

    /**
     * @param string $class
     * @param int    $id
     *
     * @return self
     *
     * @throws InvoiceException
     */
    public function setAct(string $class, int $id): self
    {
        try {
            $act = CActe::loadFromGuid("{$class}-{$id}");
            if (!in_array($act->_class, ['CActeNGAP', 'CActeCCAM', 'CActeLPP'])) {
                throw MedicalActException::invalidActType($act->_class);
            } elseif (!$act->_id) {
                throw MedicalActException::actNotFound("{$class}-{$id}");
            }
        } catch (Exception $e) {
            throw MedicalActException::actNotFound("{$class}-{$id}", $e);
        }

        $this->act_class = $act->_class;
        $this->act_id    = $act->_id;

        return $this;
    }

    public function setInvoiceId(string $invoice_id): self
    {
        $invoice = CJfseInvoice::getFromJfseId($invoice_id);
        if (!$invoice->_id) {
            throw MedicalActException::invoiceNotFound($invoice_id);
        }

        $this->jfse_invoice_id = $invoice->_id;

        return $this;
    }

    public static function generateId(): string
    {
        $link = new self();
        $link->_id = '';
        while ($link->_id !== null) {
            $link->_id = null;
            $link->jfse_id = CMbSecurity::generateUUID();
            $link->loadMatchingObject();
        }

        return $link->jfse_id;
    }

    public static function actExists(string $id, int $invoice_id = null): bool
    {
        $act = new self();
        $act->jfse_id = $id;
        if ($invoice_id) {
            $act->jfse_invoice_id = $invoice_id;
        }

        $act->loadMatchingObject();

        return isset($act->_id);
    }

    public static function getFromJfseId(string $id, int $invoice_id = null): self
    {
        $act = new self();
        $act->jfse_id = $id;
        if ($invoice_id) {
            $act->jfse_invoice_id = $invoice_id;
        }

        $act->loadMatchingObject();

        return $act;
    }
}
