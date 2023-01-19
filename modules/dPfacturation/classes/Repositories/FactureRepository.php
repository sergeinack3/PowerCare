<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation\Repositories;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CPDODataSource;
use Ox\Core\CSQLDataSource;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\CFactureEtablissement;
use Ox\Mediboard\Jfse\DataModels\CJfseInvoice;
use Ox\Mediboard\Jfse\Domain\Invoicing\InvoiceStatusEnum;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\OxPyxvital\CPyxvitalFSE;

/**
 * Repository to fetch CSampleMovie objects.
 */
class FactureRepository
{
    /** @var CPDODataSource|CSQLDataSource */
    private          $ds;
    private CFacture $facture;

    public function __construct($is_cabinet = false)
    {
        $this->facture = $is_cabinet ? new CFactureEtablissement() : new CFactureCabinet();
        $this->ds      = $this->facture->getDS();
    }

    /**
     * Count unsigned documents
     * @Param CMediusers[] $users
     *
     * @throws Exception
     */
    public function countRejectedByNoemie(array $users = []): int
    {
        $count_pyx = 0;
        $count_fse = 0;

        $date_min = CMbDT::date("first day of +0 month");
        $date_max = CMbDT::date("last day of +0 month");

        if (CModule::getActive('oxPyxvital')) {
            $fse = new CPyxvitalFSE();

            $where     = [
                "plageconsult.chir_id"         => $this->ds->prepareIn(array_column($users, "_id")),
                "ox_pyxvital_fse.state"        => "= 'rsp_ko' OR ox_pyxvital_fse.state = 'ack_ko'",
                "ox_pyxvital_fse.facture_date" => $this->ds->prepareBetween($date_min, $date_max),
            ];
            $ljoin     = [
                "consultation" => "consultation.consultation_id = ox_pyxvital_fse.consult_id",
                "plageconsult" => "consultation.plageconsult_id = plageconsult.plageconsult_id",
            ];
            $count_pyx = $fse->countList($where, null, $ljoin);
        }

        if (CModule::getActive('jfse')) {
            $invoice = new CJfseInvoice();

            $where     = [
                "jfse_users.mediuser_id" => $this->ds->prepareIn(array_column($users, "_id")),
                "jfse_invoices.creation" => $this->ds->prepareBetween($date_min . ' 00:00:00', $date_max . ' 23:59:59'),
                "jfse_invoices.status"   => "= '" . InvoiceStatusEnum::REJECTED()->getValue() .
                    "' OR jfse_invoices.status = '" . InvoiceStatusEnum::PAYMENT_REJECTED()->getValue() . "'",
            ];
            $ljoin     = [
                "jfse_users" => "jfse_users.jfse_user_id = jfse_invoices.jfse_user_id",
            ];
            $count_fse = $invoice->countList($where, null, $ljoin);
        }

        return $count_fse + $count_pyx;
    }
}
