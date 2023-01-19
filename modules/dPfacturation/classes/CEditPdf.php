<?php

/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbPdf;
use Ox\Core\CMbArray;
use Ox\Core\CMbPDFMerger;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Printing\CPrinter;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Classe permettant de créer des factures et justificatifs au format pdf
 */
class CEditPdf implements IShortNameAutoloadable
{
    //Elements du PDF
    public $type_pdf;
    /** @var CMbPdf */
    public $pdf;
    /** @var CFactureEtablissement */
    public $facture;
    /** @var CFactureEtablissement[] */
    public $factures;
    /** @var CRelance */
    public $relance;
    /** @var CEcheance[] */
    public $echeances;
    /** @var CEcheance */
    public $echeance;
    public $font;
    public $fontb;
    public $size;

    //Elements de la facture
    public $adresse_prat;
    public $acompte;
    public $adherent;
    public $destinataire;
    public $auteur;
    public $fourn_presta;
    /** @var CFunctions */
    public $function_prat;
    /** @var CGroups */
    public $group;
    public $group_adrr;
    public $nb_factures;
    public $num_fact;
    /** @var CPatient */
    public $patient;
    /** @var CMediusers */
    public $praticien;
    public $pre_tab = [];
    public $type_rbt;
    public $adherent2;

    //Elements pour le justificatif
    public $colonnes = [20, 28, 25, 75, 30];

    /**
     * Fontion qui traite une adresse dans le cas d'un retour à la ligne
     *
     * @param object $adresse l'adresse a traiter
     *
     * @return array
     */
    function traitements($adresse)
    {
        $tab = ["group1" => "", "group2" => ""];

        $detected_cr = false;
        foreach (["\r\n", "\n", "\r"] as $cr_special_char) {
            if (stristr($adresse, $cr_special_char)) {
                $detected_cr = $cr_special_char;
                break;
            }
        }
        if ($detected_cr) {
            $tab["group1"] = stristr($adresse, $detected_cr, true);
            $tab["group2"] = stristr($adresse, $detected_cr);
            $tab["group2"] = str_replace($detected_cr, '', $tab["group2"]);
        } else {
            $tab["group1"] = substr($adresse, 0, 30);
            $tab["group2"] = substr($adresse, 30);
        }

        return $tab;
    }

    /**
     * Fontion qui permet de positionner le curseur et ecrire une cellule
     *
     * @param int    $x       position du curseur à placer en x
     * @param int    $y       position du curseur à placer en y
     * @param int    $largeur largeur de la cellule
     * @param string $text    text de la cellule
     * @param string $align   alignement à gauche par défault
     * @param string $border  bordure
     * @param int    $hauteur hauteur
     *
     * @return void
     */
    function editCell($x, $y, $largeur, $text, $align = "", $border = null, $hauteur = null)
    {
        $this->pdf->setXY($x, $y);
        $this->pdf->Cell($largeur, $hauteur, $text, $border, null, $align);
    }

    /**
     * Fontion qui récupère l'ensemble des éxécutants des actes de la cotation
     *
     * @return array
     */
    function getDistinctExecutants()
    {
        $executants = [];
        foreach ($this->factures as $facture) {
            $facture->loadRefsItems();
            foreach ($facture->_ref_items as $item) {
                $executants[$item->executant_id] = $item->executant_id;
            }
        }

        return $executants;
    }

    /**
     * Edition de la facture
     *
     * @param bool   $ts     tiers soldant
     * @param string $stream format de sortie
     *
     * @return void|string
     */
    function editFactureBVR($ts = false, $stream = "I")
    {
        $this->type_pdf = $ts ? "BVR_TS" : "BVR";
        if (CAppUI::gconf("dPfacturation CEditPdf separate_by_prat")) {
            $executants = $this->getDistinctExecutants();
            foreach ($executants as $_executant_id) {
                $this->editFacture($_executant_id == reset($executants) ? true : false, $_executant_id);
            }
        } else {
            $this->editFacture();
        }

        //Enregistrement pour chaque facture l'ensemble des factures
        if (count($this->factures)) {
            $file_name = $this->facture->cloture . "_" . $this->patient->nom;
            if ($this->echeances && count($this->echeances) > 1) {
                $file_name .= "_" . CAppUI::tr("CEcheance|pl");
            } elseif ($this->echeances && count($this->echeances) === 1) {
                $file_name .= "_" . end($this->echeances)->date;
            }

            $file_name .= ".pdf";
        } else {
            $file_name = CAppUI::tr("CFacture|pl") . '.pdf';
        }

        if ($this->type_pdf == "BVR") {
            return $this->editBVRVerso($file_name, $stream);
        }

        return $this->pdf->Output($file_name, $stream);
    }

    /**
     * Edition de la facture BVR et du justificatif
     *
     * @param bool $ts tiers soldant
     *
     * @return void
     */
    function editFactureBVRJustif($ts = false)
    {
        $this->type_pdf = $ts ? "BVR_TS" : "BVR";
        $executants     = [];
        if (CAppUI::gconf("dPfacturation CEditPdf separate_by_prat")) {
            $executants = $this->getDistinctExecutants();
            foreach ($executants as $_executant_id) {
                $this->editFacture($_executant_id == reset($executants) ? true : false, $_executant_id);
            }
        } else {
            $this->editFacture();
        }
        $this->type_pdf = $ts ? "justif_TS" : "justif";
        if (CAppUI::gconf("dPfacturation CEditPdf separate_by_prat")) {
            foreach ($executants as $_executant_id) {
                $this->editFacture(false, $_executant_id);
            }
        } else {
            $this->editFacture(false);
        }
        //enregistrement pour chaque facture l'ensemble des factures
        if (count($this->factures)) {
            $this->pdf->Output($this->facture->cloture . "_" . $this->patient->nom . '.pdf', "I");
        } else {
            $this->pdf->Output(CAppUI::tr("CFacture|pl") . '.pdf', "I");
        }
    }

    /**
     * Edition du justifiactif
     *
     * @param bool   $ts     tiers soldant
     * @param string $stream format de sortie
     *
     * @return string
     */
    function editJustificatif($ts = false, $stream = "I")
    {
        $this->type_pdf = $ts ? "justif_TS" : "justif";
        if (CAppUI::gconf("dPfacturation CEditPdf separate_by_prat")) {
            $executants = $this->getDistinctExecutants();
            foreach ($executants as $_executant_id) {
                $this->editFacture($_executant_id == reset($executants) ? true : false, $_executant_id);
            }
        } else {
            $this->editFacture();
        }
        if (count($this->factures)) {
            return $this->pdf->Output($this->facture->cloture . "_" . $this->patient->nom . '.pdf', $stream);
        }

        return $this->pdf->Output(CAppUI::tr("CEditPdf.Justificatif-pl") . '.pdf', $stream);
    }

    /**
     * Edition de la relance
     *
     * @return void
     */
    function editRelance()
    {
        $this->type_pdf = "relance";
        $this->editFacture();
        if (count($this->factures)) {
            $this->pdf->Output(CAppUI::tr("CRelance") . "_" . $this->facture->cloture . "_" . $this->patient->nom . '.pdf', "I");
        } else {
            $this->pdf->Output(CAppUI::tr("CRelance|pl") . '.pdf', "I");
        }
    }

    /**
     * Création du Pdf
     *
     * @return void
     */
    function createPdf()
    {
        // Creation du PDF
        $this->pdf = new CMbPdf('P', 'mm');
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        $this->pdf->SetAutoPageBreak(false);
        $this->font  = "vera";
        $this->fontb = $this->font . "b";
    }

    /**
     * Edition de la facture
     *
     * @param bool $create       Création ou non du pdf
     * @param bool $executant_id Praticien à prendre en compte dans l'impression
     *
     * @return void
     */
    function editFacture($create = true, $executant_id = null)
    {
        if ($create) {
            $this->createPdf();
        }

        foreach ($this->factures as $the_facture) {
            foreach (count($this->echeances) > 0 ? $this->echeances : [false] as $_echeance) {
                $this->echeance = $_echeance;
                $this->facture  = $the_facture;
                $this->facture->loadRefsItems();
                $this->_no_round = false;
                if ($this->facture->cloture && !count($this->facture->_ref_items)) {
                    $this->facture->creationLignesFacture();
                }
                $this->patient = $this->facture->loadRefPatient();
                $this->facture->_ref_patient->loadRefsCorrespondantsPatient();
                $this->praticien = $this->facture->loadRefPraticien();
                $this->facture->loadRefAssurance();
                $this->facture->loadRefsObjects();
                $this->facture->loadRefsReglements();
                $this->facture->loadRefsRelances();
                if ($executant_id) {
                    foreach ($this->facture->_ref_items as $_item) {
                        if ($_item->executant_id != $executant_id) {
                            unset($this->facture->_ref_items[$_item->_id]);
                        }
                    }
                    $this->praticien                         = CMediusers::get($executant_id);
                    $this->facture->_ref_praticien           = $this->praticien;
                    $this->facture->_montant_factures_caisse = [];
                    $this->facture->loadNumerosBVR($executant_id);
                }

                $this->function_prat = $this->praticien->loadRefFunction();
                $this->group         = $this->function_prat->loadRefGroup();

                if ($this->type_pdf === "BVR" || $this->type_pdf === "BVR_TS") {
                    $facture   = $this->facture;
                    $reglement = $facture->getFirstReglement();
                    if ($this->type_pdf === "BVR_TS") {
                        $acompte = $reglement;
                        $montant = sprintf('%0.2f', $this->facture->_montant_avec_remise - $reglement);
                    } elseif ($this->type_pdf === "BVR" && $this->echeance) {
                        $reglement = new CReglement();
                        $reglement->loadObject(
                            [
                                "num_bvr"      => "LIKE '%>" . $this->echeance->num_reference . "+%'",
                                "object_id"    => "= '" . $this->facture->_id . "'",
                                "object_class" => "= '" . $this->facture->_class . "'",
                            ]
                        );
                        $acompte = $reglement->_id ? $reglement->montant : 0;
                    } elseif ($facture->isTiersSoldant() && $reglement >= 0) {
                        $acompte = $this->facture->_montant_avec_remise - $reglement;
                        $montant = sprintf('%0.2f', $reglement);
                    } else {
                        $acompte = 0;
                    }
                }
                if ($this->type_pdf == "BVR") {
                    $this->loadTotaux($executant_id);
                    $this->acompte     = 0;
                    $this->nb_factures = count($this->facture->_montant_factures_caisse);
                    $this->num_fact    = 0;
                    foreach ($this->facture->_montant_factures_caisse as $montant_facture) {
                        if ($this->acompte < $this->facture->_montant_avec_remise) {
                            $current_acompte   = min($acompte, $montant_facture);
                            $acompte           -= $current_acompte;
                            $montant_assurance = $montant_facture - $current_acompte;

                            $this->editHautFacture($montant_assurance, false, $current_acompte);
                            $this->editBVR($montant_facture);
                        }
                    }
                } elseif ($this->type_pdf == "BVR_TS") {
                    $this->loadTotaux($executant_id);
                    $this->acompte     = 0;
                    $this->nb_factures = count($this->facture->_montant_factures_caisse);
                    $this->num_fact    = 0;
                    if ($this->acompte < $this->facture->_montant_avec_remise) {
                        $this->editHautFacture($montant, null, $acompte);
                        $this->editBVR($montant);
                    }
                    $this->type_pdf               = "justif_TS";
                    $this->function_prat->adresse = str_replace("\r\n", ' ', $this->function_prat->adresse);
                    $this->patient->adresse       = str_replace("\r\n", ' ', $this->patient->adresse);
                    $this->editCenterJustificatif(0, $montant);
                } elseif ($this->type_pdf == "justif") {
                    $this->function_prat->adresse = str_replace("\r\n", ' ', $this->function_prat->adresse);
                    $this->patient->adresse       = str_replace("\r\n", ' ', $this->patient->adresse);

                    foreach ($this->facture->_montant_factures_caisse as $cle_facture => $montant_facture) {
                        $this->editCenterJustificatif($cle_facture, $montant_facture);
                    }
                } elseif ($this->type_pdf == "relance") {
                    $this->editRelancePdf();
                }
            }
        }
    }

    /**
     * Edition pdf de la relance
     *
     * @return void
     */
    function editRelancePdf()
    {
        $this->editRelanceEntete();
        $this->editRelanceCorps();
        $this->editRelancePied();
    }

    /**
     * Edition du bas de page du pdf de la relance
     *
     * @param bool $use_bvr Utilisation du BVR
     *
     * @return void
     */
    function editRelancePied($use_bvr = true)
    {
        $footer = CCompteRendu::getSpecialModel($this->praticien, "CRelance", "[PIED DE PAGE RELANCE]");
        if ($footer->_id) {
            $template_footer = new CTemplateManager();
            $footer->loadContent();
            $this->relance->fillTemplate($template_footer);
            $template_footer->renderDocument($footer->_source);
            $this->pdf->setXY(10, 240);
            $this->pdf->writeHTML($template_footer->document);
        }
    }

    /**
     * Edition du centre du justificatif
     *
     * @param int $cle_facture     clé de la facture
     * @param int $montant_facture montant de la facture
     *
     * @return void
     */
    function editCenterJustificatif($cle_facture, $montant_facture)
    {
        $this->loadAllElements();
        $this->pdf->AddPage();
        $pm = $pm_notcoeff = $pt = $pt_notcoeff = $medicaments = 0;

        $this->ajoutEntete1();
        $this->pdf->setFont($this->font, '', 8);
        $tailles_colonnes = [
            "Date"       => 7,
            "Tarif"      => 4,
            "Code"       => 10,
            "Code ref"   => 6,
            "Se Co"      => 5,
            "Quantite"   => 8,
            "Pt PM/Prix" => 7,
            "fPM"        => 4,
            "VPtPM"      => 7,
            "Pt PT"      => 7,
            "fPT"        => 4,
            "VPtPT"      => 4,
            "E"          => 2,
            "R"          => 2,
            "P"          => 2,
            "M"          => 2,
            "Montant"    => 10,
        ];

        $x = 0;
        $this->pdf->setX(15);
        foreach ($tailles_colonnes as $key => $value) {
            $this->editCell($this->pdf->getX() + $x, 140, $value, CAppUI::tr("CEditPdf.cell.$key"), "C");
            $x = $value;
        }
        $ligne                 = 0;
        $debut_lignes          = 140;
        $nb_pages              = 1;
        $montant_intermediaire = 0;

        $autre_temp = 0;

        $montant_intermediaire = CFacture::roundValue($montant_intermediaire, $this->facture->_no_round);

        $pt          = sprintf("%.2f", $pt);
        $pm          = sprintf("%.2f", $pm);
        $pm_notcoeff = sprintf("%.2f", $pm_notcoeff);
        $pt_notcoeff = sprintf("%.2f", $pt_notcoeff);

        $this->pdf->setFont($this->fontb, '', 8);
        $ligne = 265;
        $l     = 35;
        $this->editCell(20, $ligne + 3, $l, CAppUI::tr("CEditPdf.Tarmed_pm"), "R");
        $this->pdf->Cell($l, null, "$pm ($pm_notcoeff)", null, null, "R");

        $this->editCell(20, $ligne + 6, $l, CAppUI::tr("CEditPdf.Tarmed_pt"), "R");
        $this->pdf->Cell($l, null, "$pt ($pt_notcoeff)", null, null, "R");

        $autre_temp = sprintf("%.2f", $autre_temp);
        $autre      = ($autre_temp <= 0.05) ? 0.00 : $autre_temp;

        $this->editCell(80, $ligne + 3, $l, CAppUI::tr("CEditPdf.recap.Medicaments"), "R");
        $this->pdf->Cell(20, null, sprintf("%.2f", $medicaments), null, null, "R");

        $this->editCell(80, $ligne + 6, $l, CAppUI::tr("CEditPdf.recap.Autres"), "R");
        $this->pdf->Cell(20, null, sprintf("%.2f", $autre), null, null, "R");

        $this->editCell(20, $ligne + 9, $l, CAppUI::tr("CEditPdf.recap.Montant_total") . "/CHF", "R");
        $this->pdf->Cell(20, null, sprintf("%.2f", $montant_intermediaire), null, null, "R");

        $acompte = sprintf("%.2f", $this->facture->_reglements_total_patient);
        $this->editCell(80, $ligne + 9, $l, CAppUI::tr("CEditPdf.recap.Acompte"), "R");
        $this->pdf->Cell(20, null, "" . $acompte, null, null, "R");

        $total_temp = $montant_intermediaire - $this->facture->_reglements_total_patient;
        $total      = $total_temp < 0 ? 0.00 : $total_temp;

        $this->editCell(130, $ligne + 9, $l, CAppUI::tr("CEditPdf.recap.Montant_du"), "R");
        $this->pdf->Cell(20, null, number_format(CFacture::roundValue($total, $this->facture->_no_round), 2, '.', ''), null, null, "R");
    }

    /**
     * Calcul des totaux
     *
     * @param bool $executant_id Praticien à prendre en compte
     *
     * @return void
     */
    function loadTotaux($executant_id = null)
    {
        $pm                 = 0;
        $pt                 = 0;
        $medicaments        = 0;

        $pt = sprintf("%.2f", $pt * floatval($this->facture->_coeff));
        $pm = sprintf("%.2f", $pm * floatval($this->facture->_coeff));

        $this->pre_tab["Medical"]     = $pm;
        $this->pre_tab["Tarmed"]      = $pt;
        $this->pre_tab["Medicaments"] = sprintf("%.2f", $medicaments);
        $autres                       = $pm + $pt + $medicaments;
        $total                        = 0;
        foreach ($this->facture->_montant_factures_caisse as $montant_facture) {
            $total += $montant_facture;
        }
        $autres                  = $total - $autres;
        $this->pre_tab["Autres"] = sprintf("%.2f", abs($autres < 0.05) ? 0 : $autres);
    }

    /**
     * Edition du corps de la relance
     *
     * @return void
     */
    function editRelanceCorps()
    {
        $frais             = 0;
        $messages          = [0 => "", 1 => ""];
        $assurance_patient = $this->destinataire[0];
        $type              = isset($assurance_patient->type_pec) ? "assur" : "patient";
        switch ($this->relance->statut) {
            case "first":
            case "":
                $frais    = CAppUI::gconf("dPfacturation CRelance add_first_relance");
                $messages = explode('/*****/', CAppUI::gconf("dPfacturation CRelance message_relance1_$type"));
                break;
            case "second":
                $frais    = CAppUI::gconf("dPfacturation CRelance add_second_relance");
                $messages = explode('/*****/', CAppUI::gconf("dPfacturation CRelance message_relance2_$type"));
                break;
            case "third":
                $frais    = CAppUI::gconf("dPfacturation CRelance add_third_relance");
                $messages = explode('/*****/', CAppUI::gconf("dPfacturation CRelance message_relance3_$type"));
                break;
            default:
                break;
        }

        if (count($messages) == 1) {
            $messages[1] = "";
        }

        $this->pdf->setXY(10, 89);
        $this->pdf->Write(3, CAppUI::tr("CEditPdf.title.civilite"));
        $this->pdf->setXY(10, $this->pdf->getY() + 8);
        $this->pdf->Write(4, $messages[0]);

        $message_lines = explode("\n", $messages[0]);
        $y             = $this->pdf->getY() + 4;
        if (count($message_lines) > 0) {
            $y += (floor(strlen($message_lines[count($message_lines) - 1]) / 120) * 4);
        }
        $col1 = 40;
        $col2 = 80;
        $col3 = 30;
        $this->pdf->setFont($this->fontb, '', 8);
        $this->editCell(20, $y, $col1, CAppUI::tr("CFacture"), "C", 1, 4);
        $this->editCell(20, $y + 4, $col1, CAppUI::tr("Number-court") . ": " . $this->facture->_id, null, "LBR", 15);

        $this->editCell($this->pdf->getX(), $y, 80, CAppUI::tr("CEditPdf.designation"), "C", 1, 4);
        $this->editCell(60, $y + 4, $col2, CAppUI::tr("date.From") . " " . CMbDT::format($this->facture->cloture, "%d %B %Y"), null, "R", 5);
        $this->pdf->setFont($this->font, '', 8);
        $this->editCell(60, $y + 9, $col2, CAppUI::tr("CEditPdf.frais"), null, "R", 4);
        $this->pdf->setFont($this->fontb, '', 8);
        $this->editCell(60, $y + 13, $col2, CAppUI::tr("CEditPdf.solde_to_pay"), null, "BR", 6);

        $this->editCell($this->pdf->getX(), $y, 30, CAppUI::tr("CEditPdf.montant_Chf"), "C", 1, 4);
        $this->editCell(140, $y + 4, $col3, sprintf('%0.2f', $this->relance->_montant - $frais), "R", "R", 5);
        $this->pdf->setFont($this->font, '', 8);
        $this->editCell(140, $y + 9, $col3, sprintf('%0.2f', $frais), "R", "R", 4);
        $this->pdf->setFont($this->fontb, '', 8);
        $this->editCell(140, $y + 13, $col3, sprintf('%0.2f', $this->relance->_montant), "R", "BR", 6);

        $this->pdf->setFont($this->font, '', 8);
        $this->pdf->setXY(10, $this->pdf->getY() + 14);
        $this->pdf->Write(4, $messages[1]);
        $this->pdf->setXY(120, $this->pdf->getY() + 18);
        $this->pdf->Write(3, CAppUI::tr("CEditPdf.service_compta"));
    }

    /**
     * Edition du haut de la relance
     *
     * @return void
     */
    function editRelanceEntete()
    {
        $this->loadAllElements();
        $this->pdf->AddPage();
        $this->pdf->setFont($this->font, '', 8);

        $header = CCompteRendu::getSpecialModel($this->praticien, "CRelance", "[ENTETE RELANCE]");
        if ($header->_id) {
            $this->pdf->setXY(12, 10);
            $template_header = new CTemplateManager();
            $header->loadContent();
            $this->relance->fillTemplate($template_header);
            $template_header->renderDocument($header->_source);
            $this->pdf->writeHTML($template_header->document);
        } else {
            $colonne1 = 10;
            $colonne2 = 120;

            $tab[$colonne1] = [
                "40" => CAppUI::tr("CEditPdf.auteur_facture"),
                $this->auteur["nom"],
                $this->auteur["fct"],
                $this->auteur["adresse1"],
                $this->auteur["adresse2"],
                $this->auteur["cp"] . " " . $this->auteur["ville"],
            ];

            $patient_adrr = $this->traitements($this->patient->adresse);
            //Destinataire de la facture
            $tab[$colonne2] = [
                "40" => CAppUI::tr("CPatient"),
                "n° AVS: " . $this->patient->avs,
                $this->patient->_view,
                $patient_adrr["group1"],
                $patient_adrr["group2"],
                $this->patient->cp . " " . $this->patient->ville,
            ];

            unset($tab[$colonne2][41]);

            // Ecriture de C, D, E, F
            $x = $y = 0;
            foreach ($tab as $k => $v) {
                foreach ($v as $key => $value) {
                    if ($value) {
                        if ($key == "40") {
                            $y = $key;
                            $x = 0;
                        }
                        $this->editCell($k, $y + $x, 30, $value);
                        $x = ($key == "40") ? $x + 5 : $x + 3;
                    }
                }
            }
            $this->editCell(20, $this->pdf->getY() + 20, 35, CAppUI::tr("CRelance.statut." . $this->relance->statut), "C", 1, 4);
            $this->pdf->setX(110);
        }
    }

    /**
     * Edition du corps du document destiné à la dernière consultation du patient
     *
     * @param CRelance[] $relances  Relance
     * @param array      $num_frais Frais
     *
     * @return void
     */
    function editRelanceListCorps($relances, $num_frais)
    {
        $this->pdf->setXY(10, 89);
        $this->pdf->Write(3, CAppUI::tr("CEditPdf.title.civilite"));
        $this->pdf->setXY(10, $this->pdf->getY() + 8);
        $this->pdf->Write(4, "Voici la liste des factures relancées en attente de règlement:");

        $y    = 122;
        $col1 = 40;
        $col2 = 80;
        $col3 = 30;
        $this->pdf->setFont($this->fontb, '', 8);
        //Colonne Facture
        $this->editCell(20, $y, $col1, CAppUI::tr("CFacture"), "C", 1, 4);
        $y_to_add = 0;
        foreach ($relances as $_relance) {
            $this->editCell(20, $y + 4 + $y_to_add, $col1, CAppUI::tr("Number-court") . ": " . $_relance->object_id, null, "LBR", 10);
            $y_to_add += 10;
        }

        //Colonne Désignation
        $this->editCell($this->pdf->getX(), $y, 80, CAppUI::tr("CEditPdf.designation"), "C", 1, 4);
        $y_to_add = 0;
        foreach ($relances as $_relance) {
            $facture = $_relance->loadRefFacture();
            $this->editCell(
                60,
                $y + 4 + $y_to_add,
                $col2,
                CAppUI::tr("date.From") . " " . CMbDT::format($facture->cloture, "%d %B %Y"),
                null,
                "R",
                5
            );
            $this->pdf->setFont($this->font, '', 8);
            $this->editCell(60, $y + 9 + $y_to_add, $col2, CAppUI::tr("CEditPdf.frais"), null, "BR", 5);
            $this->pdf->setFont($this->fontb, '', 8);
            $y_to_add += 10;
        }

        //Colonne Montant
        $this->editCell($this->pdf->getX(), $y, 30, CAppUI::tr("CEditPdf.montant_Chf"), "C", 1, 4);
        $y_to_add = 0;
        $total    = 0;
        foreach ($relances as $_relance) {
            $frais = $num_frais[$_relance->numero];
            $this->editCell(140, $y + 4 + $y_to_add, $col3, sprintf('%0.2f', $_relance->_montant - $frais), "R", "R", 5);
            $this->pdf->setFont($this->font, '', 8);
            $this->editCell(140, $y + 9 + $y_to_add, $col3, sprintf('%0.2f', $frais), "R", "BR", 5);
            $this->pdf->setFont($this->fontb, '', 8);
            $y_to_add += 10;
            $total    += $_relance->_montant;
        }

        $this->editCell(60, $this->pdf->getY() + 5, $col2, CAppUI::tr("CEditPdf.solde_to_pay"), null, "LBR", 6);
        $this->editCell(140, $this->pdf->getY(), $col3, sprintf('%0.2f', $total), "R", "BR", 6);

        $this->pdf->setFont($this->font, '', 8);
        $this->pdf->setXY(120, $this->pdf->getY() + 20);
        $this->pdf->Write(3, CAppUI::tr("CEditPdf.service_compta"));
    }

    /**
     * Edition du haut de la facture
     *
     * @param int  $montant_facture montant de la facture
     * @param bool $relance         si c'est une relance
     *
     * @return void
     */
    function editHautFacture($montant_facture, $relance = false, $acompte = 0)
    {
        $this->loadAllElements();
        //Création de la page de la facture
        $this->pdf->AddPage();
        $colonne1 = 20;
        $colonne2 = 120;

        $this->pdf->setFont($this->fontb, '', 12);
        $this->pdf->WriteHTML("<h4>Facture du patient</h4>");

        $this->pdf->setFont($this->font, '', 6);
        $this->pdf->Text(12, 17, CAppUI::tr("CEditPdf.msg_envoi_assurance1"));
        $this->pdf->Text(12, 20, CAppUI::tr("CEditPdf.msg_envoi_assurance2"));

        $this->pdf->setFont($this->font, '', 8);

        $auteur         = [
            "60"  => CAppUI::tr("CEditPdf.auteur_facture"),
            $this->auteur["nom_dr"],
            $this->auteur["fct"],
            $this->auteur["adresse1"],
            $this->auteur["adresse2"],
            $this->auteur["cp"] . " " . $this->auteur["ville"],
            "100" => CAppUI::tr("CEditPdf.fourn_presta"),
            $this->fourn_presta["nom_dr"],
            $this->fourn_presta["fct"],
            $this->fourn_presta["adresse1"],
            $this->fourn_presta["adresse2"],
            $this->fourn_presta["0"]->cp . " " . $this->fourn_presta["0"]->ville,
        ];
        $tab[$colonne1] = $auteur;

        $patient_adrr = $this->traitements($this->patient->adresse);
        //Destinataire de la facture
        $patient = [
            "60"  => CAppUI::tr("common-Receiver"),
            $this->destinataire["nom"],
            $this->destinataire["adresse1"],
            $this->destinataire["adresse2"],
            $this->destinataire["cp"],
            "100" => CAppUI::tr("CPatient"),
            "n° AVS: " . $this->patient->avs,
            $this->patient->_view,
            $patient_adrr["group1"],
            $patient_adrr["group2"],
            $this->patient->cp . " " . $this->patient->ville,
        ];

        $tab[$colonne2] = $patient;
        $this->pdf->SetTextColor(80, 80, 80);

        if ($relance) {
            $this->pdf->setFont($this->font, '', 25);
            $this->pdf->Text(100, 20, strtoupper(CAppUI::tr("CRelance")));
        }
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->setFont($this->font, '', 8);

        // Ecriture de C, D, E, F
        $x = $y = 0;
        foreach ($tab as $k => $v) {
            foreach ($v as $key => $value) {
                if ($value) {
                    if ($key == "60" || $key == "100") {
                        $y = $key;
                        $x = 0;
                    }
                    $this->editCell($k, $y + $x, 30, $value);
                    $x = ($key == "60" || $key == "100") ? $x + 5 : $x + 3;
                }
            }
        }

        // G : Données de la facture
        $this->pdf->SetDrawColor(0);
        $this->pdf->Line($colonne1, 142, $colonne1 + 40, 142);
        $this->editCell($colonne1, 140, 25, CAppUI::tr("CEditPdf.donnee_facture"), "L");
        $this->editCell($colonne1, $this->pdf->GetY() + 5, 22, CAppUI::tr("CEditPdf.date_facture"), "R");
        $this->pdf->Cell(25, null, CMbDT::format($this->facture->cloture, "%d %B %Y"), null, null, "L");
        if ($relance) {
            $this->editCell($colonne1, $this->pdf->GetY() + 3, 22, CAppUI::tr("CEditPdf.date_relance"), "R");
            $this->pdf->Cell(25, null, CMbDT::format($this->relance->date, "%d %B %Y"), null, null, "L");
        }
        $this->editCell($colonne1, $this->pdf->GetY() + 3, 22, CAppUI::tr("CEditPdf.num_facture"), "R");
        $this->pdf->Cell(25, null, $this->facture->_id, null, null, "L");
        $use_date_consult = CAppUI::gconf("dPfacturation CEditPdf use_date_consult_traitement");
        if ($this->facture->_class == "CFactureCabinet" && count($this->facture->_ref_consults) == 1 && $use_date_consult) {
            $this->editCell($colonne1, $this->pdf->GetY() + 3, 22, CAppUI::tr("CEditPdf.consult_of"), "R");
            $this->pdf->Cell(25, null, CMbDT::format($this->facture->_ref_first_consult->_date, "%d %B %Y"), null, null, "L");
        } else {
            [$first_date, $last_date] = $this->facture->getTraitementPeriode();
            $this->editCell($colonne1, $this->pdf->GetY() + 3, 22, CAppUI::tr("CEditPdf.traitement_of"), "R");
            $this->pdf->Cell(25, null, CMbDT::format($first_date, "%d %B %Y"), null, null, "L");
            $this->editCell($colonne1, $this->pdf->GetY() + 3, 22, CAppUI::tr("date.to") . ":", "R");
            $this->pdf->Cell(25, null, CMbDT::format($last_date, "%d %B %Y"), null, null, "L");
        }
        $montant_facture = sprintf('%0.2f', $montant_facture);
        if ($montant_facture < 0) {
            $montant_facture = sprintf('%0.2f', 0);
        }

        // H : Tarif
        $title_montant = "";
        if ($this->nb_factures > 1) {
            $this->num_fact++;
            $title_montant = "n° " . $this->num_fact;
        }

        $montant_total = 0;
        $tarif         = ["Tarif" => "CHF"];
        $acompte_name  = "Acompte";
        if ($acompte > 0 && $this->type_pdf == "BVR_TS") {
            $acompte_name .= "_assurance";
        } elseif ($acompte > 0) {
            $acompte_name .= "_patient";
        }
        foreach ($this->pre_tab as $cles => $valeur) {
            $tarif[$cles]  = $valeur;
            $montant_total += $valeur;
        }
        if ($relance) {
            $tarif["Relance"] = sprintf('%0.2f', $this->relance->_montant);
        }
        $tarif["Remise"]           = sprintf('%0.2f', -$this->facture->remise);
        $tarif["Montant_total"]    = sprintf('%0.2f', $montant_total);
        $tarif[$acompte_name]      = sprintf('%0.2f', $acompte);
        $tarif["Montant_echeance"] = sprintf('%0.2f', $this->echeance ? $this->echeance->montant : 0);
        $tarif["Montant_du"]       = $tarif["Montant_echeance"] ?
            sprintf('%0.2f', CFacture::roundValue($tarif["Montant_echeance"] - $acompte)) :
            $montant_facture;
        $this->pdf->Line($colonne2, 142, $colonne2 + 50, 142);
        $x = 0;
        foreach ($tarif as $key => $value) {
            $name_cell = CAppUI::tr("CEditPdf.recap.$key");
            if ($key == "Montant_du") {
                $name_cell .= " $title_montant:";
            } else {
                if ($key === "Montant_echeance" && $value) {
                    $name_cell = CAppUI::tr(
                        "CEditPdf.recap.$key",
                        CMbDT::format($this->echeance->date, CAppUI::conf("date"))
                    );
                }
            }
            $this->editCell($colonne2, 140 + $x, 25, $name_cell, "R");
            $this->pdf->Cell(22, null, $value, null, null, "R");

            if ($key == "Tarif" || $key == "Remise:") {
                $x += 5;
                if ($key == "Remise:") {
                    $this->pdf->Line($colonne2, 117 + $x, $colonne2 + 50, 117 + $x);
                    $this->pdf->setFont($this->fontb, '', 8);
                }
            } else {
                $x += 3;
            }
        }

        $delai        = CAppUI::gconf("dPfacturation CRelance nb_days_first_relance");
        $warning_text = CAppUI::tr("CFacture-msg-Please-pay-the-bill-before") . " " . $delai;
        $warning_text .= " " . CAppUI::tr($delai > 1 ? "Days" : "common-Day") . ".";

        $this->pdf->setFont($this->font, '', 8);
        $this->pdf->Text($colonne1, 297 - 106 - 6, $warning_text);
    }

    /**
     * Edition du bas de la facture (partie BVR)
     *
     * @param int $montant_facture montant du BVR
     *
     * @return void
     */
    function editBVR($montant_facture)
    {
        //le 01 sera fixe car il correspond à un "Codes des genres de justificatifs (BC)" ici :01 = BVR en CHF
        $genre           = "01";
        $montant         = sprintf("%010s", $montant_facture * 100);
        $cle             = $this->facture->getNoControle($genre . $montant);

        if ($this->echeance) {
            $_num_reference = str_replace(' ', '', $this->echeance->num_reference);
        } else {
            $_num_reference = str_replace(' ', '', $this->facture->num_reference);
        }
        $bvr = $genre . $montant . $cle . ">" . $_num_reference . "+ " . $this->adherent2 . ">";

        // Dimensions du bvr
        $largeur_bvr = 210;
        $hauteur_bvr = 106;
        $haut_doc    = 297 - $hauteur_bvr;

        // Une ligne = 1/6 pouce = 4.2333 mm
        $h_ligne = 4.2333; // $hauteur_bvr/25;

        // Une colonne = 1/10 pouce = 2.54 mm
        $l_colonne = 2.54; // $largeur_bvr/83;

        $left_offset = 84 * $l_colonne - $largeur_bvr;

        //Boucle utilisée pour dupliquer les Partie1 et 2 avec un décalage de colonnes
        $default_etab_adresse1 = CAppUI::gconf("dPfacturation CEditPdf etab_adresse1");
        $default_etab_adresse2 = CAppUI::gconf("dPfacturation CEditPdf etab_adresse2");
        for ($i = 0; $i <= 1; $i++) {
            $decalage = $i * 24 * $l_colonne + $left_offset;
            $h_add    = !$this->fourn_presta["fct"] ? 3 : 4;
            //Adresse du patient
            $this->pdf->SetTextColor(0);
            $this->pdf->setFont($this->font, '', 8);

            if (!$this->fourn_presta["fct"]) {
                $this->pdf->Text($l_colonne + $decalage, $h_ligne * $h_add + $haut_doc, $this->auteur["nom_dr"]);
            } else {
                $this->pdf->Text($l_colonne + $decalage, $h_ligne * $h_add + $haut_doc, $this->auteur["nom_dr"]);
                $h_add++;
                $this->pdf->Text($l_colonne + $decalage, $h_ligne * $h_add + $haut_doc, $this->fourn_presta["fct"]);
            }

            $h_add++;
            $this->pdf->Text($l_colonne + $decalage, $h_ligne * $h_add + $haut_doc, $this->auteur["adresse1"]);
            $h_add++;
            if ($this->auteur["adresse2"]) {
                $this->pdf->Text($l_colonne + $decalage, $h_ligne * $h_add + $haut_doc, $this->auteur["adresse2"]);
                $h_add++;
            }
            $this->pdf->Text($l_colonne + $decalage, $h_ligne * $h_add + $haut_doc, $this->auteur["cp"] . " " . $this->auteur["ville"]);

            //Numéro adhérent, CHF, Montant1 et Montant2
            $this->pdf->Text($l_colonne * 11 + $decalage, $h_ligne * 10.75 + $haut_doc, $this->adherent);

            $this->pdf->setFont($this->font, '', 10);
            $placement_colonne = $l_colonne * (17 - strlen($montant_facture * 100)) + $decalage - 1.5;
            $this->pdf->Text($placement_colonne, $h_ligne * 12.75 + $haut_doc - 1, sprintf("%d", $montant_facture));

            $cents = floor(sprintf("%.2f", $montant_facture - sprintf("%d", $montant_facture)) * 100);
            if ($cents < 10) {
                $cents = "0" . $cents;
            }
            $this->pdf->Text($l_colonne * 19 + $decalage - 1.5, $h_ligne * 12.75 + $haut_doc - 1, $cents);
        }
        $decalage = $left_offset; // 7.36 // 8;

        //Ecriture de la reference
        $num_reference = preg_replace('/(\d{2})(\d{5})(\d{5})(\d{5})(\d{5})/', '\1 \2 \3 \4 \5 \6', $this->facture->num_reference);
        $this->pdf->setFont($this->font, '', 11);
        $this->pdf->Text(50 * $l_colonne, $h_ligne * 8.5 + $haut_doc, $num_reference);

        $this->pdf->setFont($this->font, '', 8);
        $this->pdf->Text($l_colonne + $decalage, $h_ligne * 15 + $haut_doc, $this->facture->num_reference);
        //Adresse du patient de la facture
        $this->pdf->Text($l_colonne + $decalage, $h_ligne * 16 + $haut_doc, $this->destinataire["nom"]);
        $this->pdf->Text(49 * $l_colonne + $decalage, $h_ligne * 12 + $haut_doc, $this->destinataire["nom"]);

        $this->pdf->Text($l_colonne + $decalage, $h_ligne * 17 + $haut_doc, $this->destinataire["adresse1"]);
        $this->pdf->Text(49 * $l_colonne + $decalage, $h_ligne * 13 + $haut_doc, $this->destinataire["adresse1"]);
        $j = 1;
        if ($this->destinataire["adresse2"]) {
            $this->pdf->Text($l_colonne + $decalage, $h_ligne * (18) + $haut_doc, $this->destinataire["adresse2"]);
            $this->pdf->Text(49 * $l_colonne + $decalage, $h_ligne * 14 + $haut_doc, $this->destinataire["adresse2"]);
            $j = 2;
        }

        $this->pdf->Text($l_colonne + $decalage, $h_ligne * (17 + $j) + $haut_doc, $this->destinataire["cp"]);
        $this->pdf->Text(49 * $l_colonne + $decalage, $h_ligne * (13 + $j) + $haut_doc, $this->destinataire["cp"]);

        //Ecriture du code bvr genere modulo10 recursif
        $this->pdf->setFont("ocrbb", '', 12);

        $function_guid          = "CFunctions-" . $this->praticien->function_id;
        $decalage_right_num_bvr = CAppUI::conf("dPfacturation CFactureCategory decalage_right_num_bvr", $function_guid) ?: 0;
        $w                      = (80 - strlen($bvr)) * $l_colonne - $decalage + $decalage_right_num_bvr;
        $this->pdf->Text($w, $h_ligne * 21 + $haut_doc - 1.2, $bvr);
    }

    /**
     * Création du premier type d'en-tête possible d'un justificatif
     *
     * @return void
     */
    function ajoutEntete1()
    {
        $group = CGroups::loadCurrent();
        $this->ajoutEntete2(1);
        $this->pdf->SetFillColor(255, 255, 255);
        $this->pdf->SetDrawColor(0);
        $this->pdf->Rect(10, 39, 180, 99);

        $_ref_assurance = "";
        $nom_entreprise = "";
        if ($this->facture->type_facture == "accident" && $this->facture->assurance_accident && $this->facture->_ref_assurance_accident->relation == "employeur") {
            $employeur      = $this->facture->_ref_assurance_accident;
            $_ref_assurance = $employeur->num_assure;
            $nom_entreprise = $employeur->nom;
        } elseif ($this->destinataire[0]->_class != "CPatient") {
            $_ref_assurance = $this->destinataire[0]->assure_id;
        } else {
            if ($this->facture->type_facture == "accident" && $this->facture->assurance_accident) {
                $_ref_assurance = $this->facture->_ref_assurance_accident->assure_id;
            } elseif ($this->facture->type_facture == "maladie" && $this->facture->assurance_maladie) {
                $_ref_assurance = $this->facture->_ref_assurance_maladie->assure_id;
            }
        }

        $loi      = $this->facture->type_facture == "accident" ? "LAA" : "LAMal";

        if ($this->facture->statut_pro == "invalide") {
            $loi = "LAI";
        }
        if ($this->facture->statut_pro == "militaire") {
            $loi = "AMF";
        }
        if ($this->facture->statut_pro == "prive") {
            $loi = "LCA";
        }

        $assurance_patient = $this->destinataire[0];
        $assur_nom         = "";
        if ($this->facture->_class != "CFactureCabinet" && $this->facture->dialyse && $this->facture->_ref_assurance_accident) {
            $assur_nom = $this->facture->_ref_assurance_accident->nom . " " . $this->facture->_ref_assurance_accident->prenom;
        }
        if (isset($assurance_patient->type_pec) && $assurance_patient->type_pec == "TS" && $this->type_rbt == "TG avec cession") {
            if (count($this->facture->_ref_reglements) && $this->type_pdf == "justif_TS") {
                $assur_nom = $this->patient->nom . " " . $this->patient->prenom;
            } else {
                $assur_nom = "$assurance_patient->nom $assurance_patient->prenom";
            }
            $assurance_patient = $this->patient;
        }

        $assur             = [];
        $assur["civilite"] = isset($assurance_patient->civilite) ? ucfirst($this->patient->civilite) : "";
        $assur["nom"]      = "$assurance_patient->nom $assurance_patient->prenom";
        $assur["adresse"]  = "$assurance_patient->adresse";
        $assur["cp"]       = "$assurance_patient->cp $assurance_patient->ville";

        $motif = $this->facture->type_facture;

        if ($this->facture->type_facture == "accident" && $this->facture->_coeff == $conf_cab) {
            $motif = CAppUI::tr("CEditPdf.accident_cm");
        }
        if ($this->facture->statut_pro == "enceinte") {
            $motif = CAppUI::tr("CGrossesse");
        }

        $naissance = CMbDT::format($this->patient->naissance, "%d.%m.%Y");
        $colonnes  = [20, 28, 25, 25, 35, 50];
        [$first, $last] = $this->facture->getTraitementPeriode();
        if ($last > $first) {
            $traitement = CMbDT::format($first, "%d.%m.%Y") . " - " . CMbDT::format($last, "%d.%m.%Y");
        } else {
            $traitement = CMbDT::format($last, "%d.%m.%Y") . " - " . CMbDT::format($first, "%d.%m.%Y");
        }

        $date_cas     = ($this->facture->date_cas && $this->facture->type_facture == "accident") ? CMbDT::format(
            $this->facture->date_cas,
            "%d.%m.%Y"
        ) : "";
        $ref_accident = (($this->facture->ref_accident || $this->facture->statut_pro == "invalide")) ? $this->facture->ref_accident : "";

        $ean2 = $this->auteur["EAN"];
        if ($this->facture->_class == "CFactureEtablissement" && $this->facture->_ref_last_sejour->_ref_last_operation) {
            $ean2 = $this->facture->_ref_last_sejour->_ref_last_operation->_ref_anesth->ean;
        }

        $assure = new CIdSante400();
        $assure->setObject($this->patient);
        $assure->tag = "Identifiant Assuré covercard";
        $assure->loadMatchingObject();
        $num_cada = $assure->id400;

        $see_diag_justificatif = CAppUI::gconf("dPfacturation CEditPdf see_diag_justificatif");
        $line_mandataire       = [CAppUI::tr("CEditPdf.mandataire"), CAppUI::tr("CEditPdf.ean_rcc")];
        if (CAppUI::gconf("dPfacturation CEditPdf see_mandataire_justif")) {
            $line_mandataire[] = $this->praticien->ean . " - ";
            $line_mandataire[] = null;
            $line_mandataire[] = $this->praticien->_view;
        }
        $msg_info = $see_diag_justificatif ? CAppUI::tr("CEditPdf.demande_info") . " " . $this->praticien->_view : "";
        $rss      = $this->praticien->loadLastId400(CAppUI::gconf("dPfacturation Other tag_RSS_praticien"))->id400;
        $remarque = "";
        if ($rss) {
            $remarque = "Dr " . $this->praticien->_view . " " . CAppui::tr("CEditPdf.rss") . ": $rss";
        }

        $_last_relance = $this->facture->_ref_last_relance;
        $relance       = $_last_relance && $_last_relance->_id ? CAppUI::tr("CRelance") : "";
        $num_relance   = $relance ? $_last_relance->numero : "";

        $lignes = [
            [CAppUI::tr("CPatient"), CAppUI::tr("CPatient-_p_last_name"), $this->patient->nom, null, CAppUI::tr("CFacture-assurance"), $assur_nom],
            ["", CAppUI::tr("CPatient-_p_first_name"), $this->patient->prenom],
            ["", CAppUI::tr("CEditPdf.rue"), str_replace(["\r\n", "\n", "\r"], " ", $this->patient->adresse)],
            ["", CAppUI::tr("CEditPdf.NPA"), $this->patient->cp, null, $assur["civilite"]],
            ["", CAppUI::tr("CEditPdf.localite"), $this->patient->ville, null, $assur["nom"]],
            ["", CAppUI::tr("CPatient-naissance"), $naissance, null, str_replace(["\r\n", "\n", "\r"], " ", $assur["adresse"])],
            ["", CAppUI::tr("CPatient-sexe"), strtoupper($this->patient->sexe), null, $assur["cp"]],
            ["", CAppUI::tr("CEditPdf.date_cas"), $date_cas],
            ["", CAppUI::tr("CEditPdf.num_cas"), $ref_accident],
            ["", CAppUI::tr("CEditPdf.num_avs"), $this->patient->avs],
            ["", CAppUI::tr("CEditPdf.num_cada"), $num_cada],
            ["", CAppUI::tr("CEditPdf.num_assure"), $_ref_assurance],
            ["", CAppUI::tr("CEditPdf.name_entreprise"), $nom_entreprise],
            ["", CAppUI::tr("CEditPdf.canton"), CAppUI::gconf("dPfacturation CEditPdf canton")],
            ["", CAppUI::tr("CEditPdf.copie"), "Non"],
            ["", CAppUI::tr("CEditPdf.type_rbt"), $this->type_rbt],
            ["", CAppUI::tr("CTarmed.loi"), $loi],
            ["", CAppUI::tr("CEditPdf.num_contrat"), ""],
            ["", CAppUI::tr("CEditPdf.motif_ttt"), $motif, null, CAppUI::tr("CEditPdf.num_facture"), $this->facture->_id],
            [
                "",
                CAppUI::tr("CEditPdf.ttt"),
                $traitement,
                null,
                CAppUI::tr("CEditPdf.date_facture"),
                CMbDT::format($this->facture->cloture, "%d.%m.%Y"),
            ],
            ["", CAppUI::tr("CEditPdf.role_localite"), "-", null, $relance, $num_relance],
            $line_mandataire,
            [CAppUI::tr("CEditPdf.diagnostic"), null, $msg_info],
            [CAppUI::tr("CEditPdf.list_ean"), "", "1/" . $this->praticien->ean . " 2/" . $ean2],
            [CAppUI::tr("CEditPdf.commentaire"), substr(str_replace("\r\n", ' ', $this->facture->remarque) . " " . $remarque, 0, 110)],
        ];

        foreach ($lignes as $ligne) {
            $this->pdf->setXY(10, $this->pdf->getY() + 4);
            foreach ($ligne as $key => $value) {
                $this->pdf->Cell($colonnes[$key], null, $value);
            }
        }
        $this->pdf->Line(10, 119 + 3, 190, 119 + 3);
        $this->pdf->Line(10, 123 + 3, 190, 123 + 3);
        $this->pdf->Line(10, 127 + 3, 190, 127 + 3);
        $this->pdf->Line(10, 131 + 3, 190, 131 + 3);
    }

    /**
     * Création du second type d'en-tête possible d'un justificatif, celui-ci étant plus léger
     *
     * @param int $nb le numéro de la page
     *
     * @return void
     */
    function ajoutEntete2($nb)
    {
        $this->pdf->setFont($this->fontb, '', 12);
        if ($this->type_rbt == "TP") {
            $this->pdf->WriteHTML("<h4>Facture Tiers Payant</h4>");
        } else {
            $this->pdf->WriteHTML("<h4>Justificatif de remboursement</h4>");
        }

        $this->pdf->setFont($this->font, '', 8);
        $this->pdf->SetFillColor(255, 255, 255);
        $this->pdf->SetDrawColor(0);
        $this->pdf->Rect(10, 19, 180, 20);
        $auteur         = substr($this->auteur["adresse1"], 0, 29) . " " . $this->auteur["cp"] . " " . $this->auteur["ville"];
        $presta         = $this->fourn_presta;
        $presta_adresse = substr($presta["adresse1"], 0, 29) . " " . $presta["0"]->cp . " " . $presta["0"]->ville;
        $lignes         = [
            [
                CAppUI::tr("CEditPdf.document"),
                CAppUI::tr("CEditPdf.identification"),
                $this->facture->_id . " " . CMbDT::format(null, "%d.%m.%Y %H:%M:%S"),
                "",
                "Page $nb",
            ],
            [
                CAppUI::tr("CEditPdf.auteur"),
                CAppUI::tr("CEditPdf.num_ean") . "(B)",
                $this->auteur["EAN"],
                $this->auteur["nom_dr"],
                CAppUI::tr("CMedecin-tel") . " : " . $this->auteur["tel"],
            ],
            [
                CAppUI::tr("CFacture"),
                CAppUI::tr("CEditPdf.num_rcc") . "(B)",
                $this->auteur["RCC"],
                $auteur,
                CAppUI::tr("CMedecin-fax") . ": " . $this->auteur["fax"],
            ],
            [
                CAppUI::tr("CEditPdf.fourn_of"),
                CAppUI::tr("CEditPdf.num_ean") . "(P)",
                $presta["EAN"],
                $presta["nom_dr"],
                CAppUI::tr("CMedecin-tel") . ":  " . $presta["0"]->tel,
            ],
            [
                CAppUI::tr("CEditPdf.prest"),
                CAppUI::tr("CEditPdf.num_rcc") . "(B)",
                $presta["RCC"],
                $presta_adresse,
                CAppUI::tr("CMedecin-fax") . ": " . $presta["0"]->fax,
            ],
        ];

        $this->pdf->setXY(10, $this->pdf->getY() - 4);
        foreach ($lignes as $ligne) {
            $this->pdf->setXY(10, $this->pdf->getY() + 4);
            foreach ($ligne as $key => $value) {
                $this->pdf->Cell($this->colonnes[$key], null, $value);
            }
        }
    }

    /**
     * Chargement de tous les éléments communs
     *
     * @param CFacture   $facture   Facture
     * @param CMediusers $praticien Praticien
     * @param string     $type_pdf  Type de document
     *
     * @return array
     */
    static function loadAllElementBill($facture, $praticien, $type_pdf = null)
    {
        $bill_ttt      = new self;
        $function_prat = $praticien->_ref_function;
        $group         = $function_prat->_ref_group;

        //Auteur de la facture
        $adresse_prat = $bill_ttt->traitements($function_prat->adresse);
        $group_adrr   = $bill_ttt->traitements($group->adresse);

        //Assurance
        $assur             = [];
        $assurance_patient = null;
        $view              = "_longview";
        $type_rbt          = "TG";
        $assurance_select  = null;

        // TP uniquement pour accident
        // TP/TG/TS      pour maladie
        if ($facture->assurance_maladie && !$facture->send_assur_base && $facture->_ref_assurance_maladie->type_pec != "TG"
            && $facture->type_facture == "maladie"
        ) {
            $assurance_patient = $facture->_ref_assurance_maladie;
            $type_rbt          = $facture->_ref_assurance_maladie->type_pec;
            $assurance_select  = $assurance_patient;
        } elseif ($facture->assurance_accident && !$facture->send_assur_compl && $facture->type_facture == "accident") {
            $type_rbt = "TP";
            $assurance_patient = $facture->_ref_assurance_accident;
            $assurance_select  = $facture->_ref_assurance_accident;
        } else {
            $assurance_patient = $facture->_ref_patient;
            $view              = "_view";
        }
        if (count($facture->_ref_reglements) && $type_pdf == "BVR_TS") {
            $assurance_patient = $facture->_ref_patient;
            $view              = "_view";
        }

        $type_rbt = $type_rbt == "" ? "TG" : $type_rbt;
        $type_rbt = $type_rbt == "TS" ? "TG avec cession" : $type_rbt;

        $assur["nom"]     = $assurance_patient->$view;
        $assur["adresse"] = $assurance_patient->adresse;
        $assur["cp"]      = $assurance_patient->cp . " " . $assurance_patient->ville;

        $assur_adrr   = $bill_ttt->traitements($assur["adresse"]);
        $destinataire = [
            "0"        => $assurance_patient,
            "nom"      => $assur["nom"],
            "adresse1" => $assur_adrr["group1"],
            "adresse2" => $assur_adrr["group2"],
            "cp"       => $assur["cp"],
        ];

        $acteur["etablissement"]        = [
            "0"        => $group,
            "nom"      => $group->raison_sociale,
            "prenom"   => "",
            "nom_dr"   => $group->raison_sociale,
            "adresse1" => $group_adrr["group1"],
            "adresse2" => $group_adrr["group2"],
            "cp"       => $group->cp,
            "ville"    => $group->ville,
            "EAN"      => $group->ean,
            "EAN_XML"  => $group->ean,
            "RCC"      => $group->rcc,
            "tel"      => $group->tel,
            "fax"      => $group->fax,
        ];
        $acteur["praticien"]            = [
            "0"        => $function_prat,
            "nom"      => $praticien->_user_last_name,
            "prenom"   => $praticien->_user_first_name,
            "nom_dr"   => "Dr. " . $praticien->_view,
            "fct"      => $function_prat->_view,
            "adresse1" => $adresse_prat["group1"],
            "adresse2" => $adresse_prat["group2"],
            "EAN"      => $praticien->ean,
            "EAN_XML"  => $praticien->ean,
            "RCC"      => "",
            "cp"       => $function_prat->cp,
            "ville"    => $function_prat->ville,
            "tel"      => $function_prat->tel,
            "fax"      => $function_prat->fax,
        ];
        $acteur["etablissement"]["fct"] = $acteur["praticien"]["fct"] = "";

        $acteur["cabinet"]            = $acteur["praticien"];
        $acteur["cabinet"]["nom"]     = $function_prat->_view;
        $acteur["cabinet"]["prenom"]  = $type_pdf == "XML" ? $function_prat->_view : "";
        $acteur["cabinet"]["nom_dr"]  = $function_prat->_view;
        $acteur["cabinet"]["EAN"]     = $function_prat->ean;
        $acteur["cabinet"]["EAN_XML"] = $function_prat->ean;
        if (!$function_prat->ean) {
            $acteur["cabinet"]["EAN"] = $function_prat->loadLastId400(CAppUI::gconf("dPfacturation Other tag_EAN_fct"))->id400;
        }
        $acteur["cabinet"]["RCC"] = $function_prat->rcc;
        if (!$function_prat->rcc) {
            $acteur["cabinet"]["RCC"] = $function_prat->loadLastId400(CAppUI::gconf("dPfacturation Other tag_RCC_fct"))->id400;
        }

        $auteur       = $acteur["etablissement"];
        $fourn_presta = $acteur["praticien"];

        return [$type_rbt, $destinataire, $auteur, $fourn_presta, $assurance_patient, $assurance_select];
    }

    /**
     * Chargement de tous les éléments communs
     *
     * @return void
     */
    function loadAllElements()
    {
        [$this->type_rbt, $this->destinataire, $this->auteur, $this->fourn_presta] =
            CEditPdf::loadAllElementBill($this->facture, $this->praticien, $this->type_pdf);
        if (!$this->facture->_host_config) {
            $this->facture->loadRefPraticien();
        }
    }

    /**
     * Impression des factures
     *
     * @param bool $ts tiers soldant
     *
     * @return void
     */
    function printBill($ts = false)
    {
        if (count($this->factures)) {
            $user                     = CMediusers::get();
            $printer_bvr              = new CPrinter();
            $printer_bvr->function_id = $user->function_id;
            $printer_bvr->label       = "bvr";
            $printer_bvr->loadMatchingObject();

            $printer_justif              = new CPrinter();
            $printer_justif->function_id = $user->function_id;
            $printer_justif->label       = "justif";
            $printer_justif->loadMatchingObject();

            if (!$printer_bvr->_id || !$printer_justif->_id) {
                CAppUI::setMsg(CAppUI::tr("CEditPdf.printer_no_param"), UI_MSG_ERROR);
                echo CAppUI::getMsg();

                return;
            }
            $file = new CFile();

            foreach ($this->factures as $facture) {
                $facture_pdf           = new CEditPdf();
                $facture_pdf->factures = [$facture];
                $pdf                   = $facture_pdf->editFactureBVR($ts, "S");
                $file_path             = tempnam("tmp", "facture");
                $file->_file_path      = $file_path;
                file_put_contents($file_path, $pdf);
                $printer_bvr->loadRefSource()->sendDocument($file);
                unlink($file_path);

                $pdf              = $facture_pdf->editJustificatif($ts, "S");
                $file_path        = tempnam("tmp", "facture");
                $file->_file_path = $file_path;
                file_put_contents($file_path, $pdf);
                $printer_justif->loadRefSource()->sendDocument($file);
                unlink($file_path);
            }
        }
    }

    /**
     * Ajout du verso de BVR (modele)
     *
     * @param $file_name
     * @param $stream
     *
     * @return mixed|void
     */
    function editBVRVerso($file_name, $stream)
    {
        $model = CCompteRendu::getSpecialModel(CMediusers::get(), "CFactureCabinet", "[FACTURE BVR]");

        $pdf_content = $this->pdf->OutPut($file_name, $model->_id ? "S" : "I");

        if (!$model->_id) {
            return;
        }

        $pdf_file = tempnam("./tmp", "FACTUREBVR");
        file_put_contents($pdf_file, $pdf_content);


        $model_source = $model->getFullContentFromModel();
        $htmltopdf    = new CHtmlToPDF();
        $pdf_add      = $htmltopdf->generatePDF($model_source, 0, $model, new CFile(), false);


        $pdf_add_file = tempnam("./tmp", "FACTUREBVR");
        file_put_contents($pdf_add_file, $pdf_add);

        $pdf_merger = new CMbPDFMerger();
        $pdf_merger->addPDF($pdf_file);
        $pdf_merger->addPDF($pdf_add_file);

        return $pdf_merger->merge(
            $stream === "I" ? "Browser" : "string",
            $file_name
        );
    }
}
