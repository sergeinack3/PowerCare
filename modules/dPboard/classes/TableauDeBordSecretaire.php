<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Board;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Récupération des éléments du tableau de bord pour la secrétaire
 */
class TableauDeBordSecretaire
{
    private array $total_documents_by_status = [
        "attente_validation_praticien" => 0,
        "a_corriger"                   => 0,
        "envoye"                       => 0,
        "a_envoyer"                    => 0,
    ];

    private array $documents_by_status = [
        "attente_validation_praticien" => [],
        "a_corriger"                   => [],
        "envoye"                       => [],
        "a_envoyer"                    => [],
    ];

    private array $praticiens = [];

    private CFunctions $function;

    /**
     * Getter total_documents_by_status
     */
    public function getTotalDocumentsByStatus(): array
    {
        return $this->total_documents_by_status;
    }

    /**
     * Getter documents_by_status
     */
    public function getDocumentsByStatus(): array
    {
        return $this->documents_by_status;
    }

    /**
     * @return array
     */
    public function getPraticiens(): array
    {
        return $this->praticiens;
    }

    /**
     * @return CFunctions
     */
    public function getFunction(): CFunctions
    {
        return $this->function;
    }

    /**
     * @throws Exception
     */
    public function loadChirsDocumentsFromDate(?array $chir_ids, string $date_min, string $function_id = null): void
    {
        $documents = $this->loadDocuments($chir_ids, $function_id);

        $this->sortDocumentsByStatus($documents, $date_min);
        $this->sortDocumentsSentByDate();
    }

    /**
     * @throws Exception
     */
    private function loadDocuments(?array $chir_ids, $function_id = null): array
    {
        $cr = new CCompteRendu();
        $where = ["signature_mandatory" => "= '1'"];
        if ($chir_ids && count($chir_ids)) {
            $where["signataire_id"] = $cr->getDS()->prepareIn($chir_ids);
        } else {
            $user       = CMediusers::get();
            $praticiens = $user->loadPraticiens(PERM_EDIT, $function_id, null, false, true, 1);
            $where["signataire_id"]     = $cr->getDS()->prepareIn(array_keys($praticiens));
        }
        $crs = $cr->loadList($where, 'creation_date DESC', 100);
        CStoredObject::massLoadFwdRef($crs, "object_id");
        CStoredObject::massLoadFwdRef($crs, "content_id");

        return $crs;
    }

    /**
     * @throws Exception
     */
    private function sortDocumentsByStatus(array $documents, string $date_min): void
    {
        /** @var CCompteRendu $_cr */
        foreach ($documents as $_cr) {
            $context = $_cr->loadTargetObject();
            $date    = 0;
            switch ($context->_class) {
                default:
                    $context_cancelled = false;
                    break;
                case "CConsultation":
                    /** @var CConsultation $context */
                    $context_cancelled = $context->annule;
                    $context->loadRefPlageConsult();
                    $date = $context->_ref_plageconsult->date < $date_min;
                    break;
                case "CSejour":
                    /** @var  $context CSejour */
                    $context_cancelled = $context->annule;
                    $date              = $date_min > $context->_date_sortie_prevue;
                    break;
                case "CConsultAnesth":
                    /** @var  $context CConsultAnesth */
                    $context_cancelled = $context->loadRefConsultation()->annule;
                    $date              = $context->_ref_consultation->_date < $date_min;
                    break;
                case "COperation":
                    /** @var  $context COperation */
                    $date              = $context->date < $date_min;
                    $context_cancelled = $context->annulee;
            }

            if ($_cr->isAutoLock() || $context_cancelled || $date) {
                unset($documents[$_cr->_id]);
                continue;
            }
            $_cr->_ref_patient = $_cr->getIndexablePatient();
            $_cr->loadLastRefStatutCompteRendu();

            if ($_cr->_ref_last_statut_compte_rendu->_id) {
                $_cr->_ref_last_statut_compte_rendu->loadRefUtilisateur();
                $_cr->_ref_last_statut_compte_rendu->getDelaiAttenteCorrection();
            } else {
                unset($documents[$_cr->_id]);
                continue;
            }
            $cat_id = $_cr->file_category_id ?: 0;
            if (
                $_cr->_ref_last_statut_compte_rendu->statut === "brouillon" ||
                $_cr->_ref_last_statut_compte_rendu->statut === "attente_correction_secretariat"
            ) {
                $this->documents_by_status["a_corriger"][$cat_id]["items"][$_cr->nom . "-$_cr->_guid"] = $_cr;

                $this->total_documents_by_status["a_corriger"] += 1;
            } else {
                $this->documents_by_status[$_cr->_ref_last_statut_compte_rendu->statut][$cat_id]["items"]
                [$_cr->nom . "-$_cr->_guid"]                                                  = $_cr;
                $this->total_documents_by_status[$_cr->_ref_last_statut_compte_rendu->statut] += 1;
            }

            if (!isset($this->documents_by_status[$_cr->_ref_last_statut_compte_rendu->statut][$cat_id]["name"])) {
                if (
                    $_cr->_ref_last_statut_compte_rendu->statut === "brouillon" ||
                    $_cr->_ref_last_statut_compte_rendu->statut === "attente_correction_secretariat"
                ) {
                    $this->documents_by_status["a_corriger"][$cat_id]["name"] =
                        $cat_id ? $_cr->_ref_category->nom : CAppUI::tr("CFilesCategory.none");
                } else {
                    $this->documents_by_status[$_cr->_ref_last_statut_compte_rendu->statut][$cat_id]["name"] =
                        $cat_id ? $_cr->_ref_category->nom : CAppUI::tr(
                            "CFilesCategory.none"
                        );
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function loadPraticiensTdb(?array $chir_ids): void
    {
        $user = new CMediusers();

        if ($chir_ids && $chir_ids[0] != -1) {
            $where            = [
                "user_id" => $user->getDS()->prepareIn($chir_ids),
            ];
            $this->praticiens = $user->loadList($where);
        }
    }

    private function sortDocumentsSentByDate(): void
    {
        foreach ($this->documents_by_status['envoye'] as $key => $_documents_cat) {
            usort(
                $_documents_cat["items"],
                function ($a, $b) {
                    return $a->_ref_last_statut_compte_rendu->datetime < $b->_ref_last_statut_compte_rendu->datetime;
                }
            );
            $this->documents_by_status['envoye'][$key] = $_documents_cat;
        }
    }
    /**
     * @param string|null $function_id
     *
     * @return void
     * @throws Exception
     */
    public function loadFunctionTdb(?string $function_id): void
    {
        $function = new CFunctions();
        if ($function_id) {
            $function = $function->load($function_id);
        }
        $this->function = $function;
    }
}
