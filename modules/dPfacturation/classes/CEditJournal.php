<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbPdf;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Printing\CPrinter;

/**
 * Classe permettant de créer les journaux au format pdf
 */
class CEditJournal implements IShortNameAutoloadable {
  //Elements du PDF
  public $type_pdf;
  /** @var CMbPdf*/
  public $pdf;
  public $font;
  public $fontb;
  public $size;
  public $page;
  public $date_min;
  public $date_max;
  public $journal_id;

  /** @var CReglement[] $reglements */
  public $reglements;
  /** @var CRelance[] $relances*/
  public $relances;
  /** @var CFactureEtablissement[] $factures*/
  public $factures;

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
  function editCell($x, $y, $largeur, $text, $align = "", $border = "", $hauteur = "") {
    $this->pdf->setXY($x, $y);
    $this->pdf->Cell($largeur, $hauteur, $text, $border, null, $align);
  }

  /**
   * Fontion qui permet de créer une nouvelle page
   *
   * @param array $colonnes les donnees des colonnes
   *
   * @return int
   */
  function ajoutPage($colonnes) {
    $this->editEntete();
    $this->editTableau($colonnes, 5, 25);
    return 0;
  }

  /**
   * Fontion qui permet l'écriture des tableaux de données
   *
   * @param array $colonnes les noms et largeurs de colonnes
   * @param int   $_x       position du curseur à placer en x
   * @param int   $y        position du curseur à placer en y
   *
   * @return void
   */
  function editTableau($colonnes, $_x, $y) {
    $x = 0;
    $this->pdf->setXY($_x, $y);
    foreach ($colonnes as $key => $value) {
      $this->editCell($this->pdf->getX()+$x, $y, $value, $key);
      $x = $value;
    }
  }

  /**
   * Edition des journaux selon le type
   *
   * @param bool $read           lecture
   * @param bool $create_journal Création du journal
   *
   * @return void
   */
  function editJournal($read = true, $create_journal = true) {
    if ($create_journal) {
      $journal = new CJournalBill();
      $journal->type  = $this->type_pdf;
      $journal->nom   = "Journal_".$this->type_pdf."_".CMbDT::date();
      $journal->_factures = $this->factures;
      if ($msg = $journal->store()) {
        CApp::log($msg);
      }
      else {
        $this->journal_id = $journal->_id;
      }
    }

      // Creation du PDF
    $this->pdf = new CMbPdf('l', 'mm');
    $this->pdf->setPrintHeader(false);
    $this->pdf->setPrintFooter(false);
    $this->font = "vera";
    $this->fontb = $this->font."b";
    $this->pdf->setFont($this->font, '', 8);

    $this->page = 0;
    $this->editEntete();

    switch ($this->type_pdf) {
      case "paiement" :
        $this->editPaiements();
        break;
      case "debiteur" :
        $this->editDebiteur();
        break;
      case "rappel" :
        $this->editRappel();
        break;
      case "checklist" :
        $this->editCheckList();
        break;
    }

    if ($create_journal) {
      $file = new CFile();
      $file->file_name = $journal->nom.".pdf";
      $file->file_type  = "application/pdf";
      $file->author_id = CMediusers::get()->_id;
      $file->file_category_id = 1;
      $file->setObject($journal);
      $file->fillFields();
      $file->setContent($this->pdf->Output('Factures.pdf', "S"));

      if ($msg = $file->store()) {
        echo $msg;
      }
      if ($this->type_pdf == "checklist") {
        $user = CMediusers::get();
        $printer = new CPrinter();
        $printer->function_id = $user->function_id;
        $printer->label = "justif";
        $printer->loadMatchingObject();

        if (!$printer->_id) {
          CAppUI::setMsg("Les imprimantes ne sont pas paramétrées", UI_MSG_ERROR);
          echo CAppUI::getMsg();
          return false;
        }

        $file = new CFile();
        $pdf = $this->pdf->Output('Factures.pdf', "S");
        $file_path = tempnam("tmp", "facture");
        $file->_file_path = $file_path;
        file_put_contents($file_path, $pdf);
        $printer->loadRefSource()->sendDocument($file);
        unlink($file_path);
      }
    }
    if ($read) {
      //Affichage du fichier pdf
      $this->pdf->Output('Factures.pdf', "I");
    }
  }

  /**
   * Edition de l'entete des journaux
   *
   * @return void
   */
  function editEntete() {
    $this->page ++;
    $this->pdf->AddPage();
    $this->pdf->setFont($this->font, '', 10);
    $nom_journal = "";
    switch ($this->type_pdf) {
      case "paiement" :
        $nom_journal = "Journal des paiements";
        break;
      case "debiteur" :
        $nom_journal = "Journal de facturation";
        break;
      case "rappel" :
        $nom_journal = "Journal rappels/contentieux";
        break;
      case "checklist" :
        $nom_journal = "Liste factures arrétées au ".CMbDT::transform("", CMbDT::date(), CAppUI::conf("date"));
        break;
    }
    $this->editCell(10, 10, 70, CGroups::loadCurrent()->text);
    $this->pdf->Cell(160, "", $nom_journal, null, null, "C");
    $this->pdf->Cell(67, "", "Page: ".$this->page);
    $this->editCell(10, 15, 70, "Date : ".CMbDT::transform("", CMbDT::dateTime(), '%d/%m/%Y - %H:%M'));
    $this->editCell(240, 15, 70, "Numéro journal: ".$this->journal_id);

    //Les lignes
    $this->pdf->Line(5, 20, 293, 20);
    $this->pdf->Line(5, 30, 293, 30);
    $this->pdf->Line(5, 5, 5, 205);
    $this->pdf->Line(5, 5, 293, 5);
    $this->pdf->Line(293, 5, 293, 205);
    $this->pdf->Line(5, 205, 293, 205);
    $this->pdf->setFont($this->font, '', 9);
  }

  /**
   * Edition du journal des paiements
   *
   * @return void
   */
  function editPaiements() {
    $colonnes = array(
      "Date"        => 10,  "Nom"       => 25,
      "Garant"      => 25,  "Libellé"   => 25,
      "Facture"     => 15,  "Débit"     => 15,
      "Crédit C/C"  => 15,  "R" => 5,
      "Solde fact." => 15);
    $this->editTableau($colonnes, 5, 25);
    $colonnes_x = array(125, 205, 235);
    $debut_lignes = 30;
    $ligne = 0;
    $debiteur_nom = "";
    $total_reglement = $totaux_reglement = 0.00;
    $totaux = array();
    foreach ($this->reglements as $reglement) {
      $reglement->_ref_facture->loadRefsReglements();
      $reglement->_ref_facture->loadRefsRelances();
      $reglement->loadRefDebiteur();
      if (!$reglement->_ref_debiteur->nom) {
        $reglement->_ref_debiteur->nom = " ";
      }
      if (!strstr($debiteur_nom, $reglement->_ref_debiteur->nom)) {
        $debiteur_nom = $reglement->_ref_debiteur->numero." - ".$reglement->_ref_debiteur->nom;
        $totaux[$debiteur_nom] = array("Débit" => 0.00, "Crédit" => 0.00, "Solde" => 0.00);
        if ($ligne != 0) {
          $ligne +=2;
          $pos_ligne = $debut_lignes + $ligne*4;
          $this->editCell($colonnes_x[0], $pos_ligne, 45, "Total contre-partie", "L");
          $this->editCell($colonnes_x[1], $pos_ligne, 15, "0.00", "R");
          $this->editCell($colonnes_x[2], $pos_ligne, 15, sprintf("%.2f", $total_reglement), "R");
          $total_reglement = 0.00;
          $ligne = $this->ajoutPage($colonnes);
        }
        $this->pdf->setFont($this->font, '', 10);
        $this->editCell(80, 15, 160, $debiteur_nom, "C");
        $this->pdf->setFont($this->font, '', 8);
      }

      $this->pdf->setX(5);
      $ligne++;
      $restant = $reglement->_ref_facture->_du_restant_patient;
      $valeurs = array(
        "Date"    => CMbDT::transform("", $reglement->date, CAppUI::conf("date")),
        "Nom"     => $reglement->_ref_facture->_ref_patient->nom." ".$reglement->_ref_facture->_ref_patient->prenom,
        "Garant"  => $this->loadGarant($reglement->_ref_facture),
        "Libellé" => $reglement->mode,
        "Facture" => $reglement->_ref_facture->_view,
        "Débit"   => "",
        "Crédit C/C" => sprintf("%.2f", $reglement->montant),
        "R" => count($reglement->_ref_facture->_ref_relances),
        "Solde fact." => sprintf("%.2f", $restant < "0.05" ? 0 : $restant));
      $totaux[$debiteur_nom]["Débit"] += 0.00;
      $totaux[$debiteur_nom]["Crédit"] += sprintf("%.2f", $reglement->montant);
      $totaux[$debiteur_nom]["Solde"] += sprintf("%.2f", $reglement->montant);

      if ($reglement->debiteur_desc) {
        $valeurs["Libellé"] .= " ($reglement->debiteur_desc)";
      }
      $x = 0;
      foreach ($colonnes as $key => $value) {
        $cote = ($key == "Crédit C/C" || $key == "Solde fact." || $key == "Débit") ? "R" : "L";
        $this->editCell($this->pdf->getX()+$x, $debut_lignes + $ligne*4, $value, $valeurs[$key], $cote);
        $x = $value;
      }
      $total_reglement += sprintf("%.2f", $reglement->montant);
      $totaux_reglement += sprintf("%.2f", $reglement->montant);
      if ($debut_lignes + $ligne*4 >= 200) {
        $ligne = $this->ajoutPage($colonnes);
      }
    }
    $ligne +=2;
    $pos_ligne = $debut_lignes + $ligne*4;
    $this->editCell($colonnes_x[0], $pos_ligne, 45, "Total contre-partie", "L");
    $this->editCell($colonnes_x[1], $pos_ligne, 15, "0.00", "R");
    $this->editCell($colonnes_x[2], $pos_ligne, 15, sprintf("%.2f", $total_reglement), "R");
    $ligne +=2;
    $pos_ligne = $debut_lignes + $ligne*4;
    $this->editCell($colonnes_x[0], $pos_ligne, 45, "Total général", "L");
    $this->editCell($colonnes_x[1], $pos_ligne, 15, "0.00", "R");
    $this->editCell($colonnes_x[2], $pos_ligne, 15, sprintf("%.2f", $totaux_reglement), "R");

    $colonnes = array("Contre-partie comptable" => 80,  "Débit"     => 25,
                      "Crédit"  => 25, "Solde" => 25);
    $colonnes_x = array(5, 125, 215, 245);

    $this->editEntete();

    $this->pdf->setXY(5, 25);
    $this->editCell($this->pdf->getX()    , 25, 80, "Contre-partie comptable");
    $this->editCell($this->pdf->getX()+80 , 25, 25, "Débit" , "R");
    $this->editCell($this->pdf->getX()+25 , 25, 25, "Crédit", "R");
    $this->editCell($this->pdf->getX()+25 , 25, 25, "Solde" , "R");

    $this->pdf->setFont($this->font, '', 10);
    $this->editCell(80, 15, 160, "Récapitulatif par contre-parties", "C");
    $this->pdf->setFont($this->font, '', 9);
    $ligne =0;
    foreach ($totaux as $compte => $valeurs) {
      $ligne++;
      $pos_ligne = $debut_lignes + $ligne*4;
      $this->editCell($colonnes_x[0], $pos_ligne, 80, $compte, "L");
      $x = 80;
      foreach ($valeurs as $key => $value) {
        $cote = ($key == "Contre-partie comptable") ? "L" : "R";
        $this->editCell($this->pdf->getX()+$x, $pos_ligne, $colonnes[$key], sprintf("%.2f", $value), $cote);
        $x = $colonnes[$key];
      }
    }
    $ligne +=2;
    $pos_ligne = $debut_lignes + $ligne*4;
    $this->editCell(5  , $pos_ligne, 80, "Total général", "L");
    $this->editCell(165, $pos_ligne, 25, "0.00", "R");
    $this->editCell(215, $pos_ligne, 25, sprintf("%.2f", $totaux_reglement), "R");
    $this->editCell(265, $pos_ligne, 25, sprintf("%.2f", $totaux_reglement), "R");
  }

  /**
   * Edition du journal des débiteurs / de facturation
   *
   * @return void
   */
  function editDebiteur() {
    $colonnes = array(
      "Facture"     => 15,  "Date Fact."  => 10,
      "T.adm."     => 6,    "Nom"         => 30,
      "Séjour du"   => 10, "Séjour au"    => 10,
      "Total Fact." => 15, "Acomptes"     => 15,
      "Net à payer" => 15, "Echéance"     => 10,
      "Extourne"    => 5);
    $this->editTableau($colonnes, 5, 25);

    $debut_lignes = 30;
    $ligne = 0;
    $totaux = array("Fact" => 0, "Acompte" => 0, "Net" => 0);
    foreach ($this->factures as $facture) {
      $this->pdf->setX(5);
      $ligne++;
      $valeurs = array(
        "Facture"     => $facture->_view,
        "Date Fact."  => CMbDT::transform("", $facture->cloture, CAppUI::conf("date")),
        "T.adm."      => "AMBU",
        "Nom"         => $facture->_ref_patient->_view,
        "Séjour du"   => CMbDT::transform("", $facture->_ref_last_sejour->entree_prevue, CAppUI::conf("date")),
        "Séjour au"   => CMbDT::transform("", $facture->_ref_last_sejour->sortie_prevue, CAppUI::conf("date")),
        "Total Fact." => sprintf("%.2f", $facture->_montant_avec_remise),
        "Acomptes"    => sprintf("%.2f", $facture->_reglements_total_patient),
        "Net à payer" => sprintf("%.2f", $facture->_du_restant_patient),
        "Echéance"    => CMbDT::transform("", $facture->_echeance, CAppUI::conf("date")),
        "Extourne"    => $facture->annule ? "Oui" : "");

      $x = 0;
      foreach ($colonnes as $key => $value) {
        $cote = ($key == "Net à payer" || $key == "Total Fact." || $key == "Acomptes") ? "R" : "L";
        $this->editCell($this->pdf->getX()+$x, $debut_lignes + $ligne*4, $value, $valeurs[$key], $cote);
        $x = $value;
      }
      if ($debut_lignes + $ligne*4 >= 200) {
        $ligne = $this->ajoutPage($colonnes);
      }
      $totaux["Fact"]    += $facture->_montant_avec_remise;
      $totaux["Acompte"] += $facture->_reglements_total_patient;
      $totaux["Net"]     += $facture->_du_restant_patient;
    }

    $pos_colonne = array(67, 157, 187, 217);
    $ligne +=2;
    $pos_ligne = $debut_lignes + $ligne*4;
    $this->editCell($pos_colonne[0], $pos_ligne, 80, "Total général", "L");
    $this->editCell($pos_colonne[1], $pos_ligne, 25, sprintf("%.2f", $totaux["Fact"]), "R");
    $this->editCell($pos_colonne[2], $pos_ligne, 25, sprintf("%.2f", $totaux["Acompte"]), "R");
    $this->editCell($pos_colonne[3], $pos_ligne, 25, sprintf("%.2f", $totaux["Net"]), "R");
  }

  /**
   * Edition du journal des rappels
   *
   * @return void
   */
  function editRappel() {
    $colonnes = array("Concerne" => 25, "Destinataire"  => 25,
      "N° fact." => 15, "Débit"     => 15,
      "Crédit"   => 15, "Solde"     => 15,
      "Echéance" => 10, "Pas de rappel jusqu'au" => 15);
    $this->editTableau($colonnes, 5, 25);
    $pos_colonne = array(5, 55, 135, 165, 195);
    $rappel_nom = " ";
    $totaux_rappel = array();
    $debut_lignes = 30;
    $ligne = 0;
    foreach ($this->relances as $relance) {
      // Une page par type de rappel
      if (!strstr($rappel_nom, CAppUI::tr("CRelance.statut.".$relance->statut))) {
        if ($ligne != 0) {
          $ligne ++;
          $pos_ligne = $debut_lignes + $ligne*4;
          // Total à chaque fin de type de rappel
          $this->editCell($pos_colonne[0], $pos_ligne, 45, "Total $rappel_nom", "L");
          $this->editCell($pos_colonne[1], $pos_ligne, 15, "(".$totaux_rappel[$rappel_nom]["Nombre"]." rappels)", "L");
          $this->editCell($pos_colonne[2], $pos_ligne, 15, sprintf("%.2f", $totaux_rappel[$rappel_nom]["Debit"]), "R");
          $this->editCell($pos_colonne[3], $pos_ligne, 15, sprintf("%.2f", $totaux_rappel[$rappel_nom]["Credit"]), "R");
          $this->editCell($pos_colonne[4], $pos_ligne, 15, sprintf("%.2f", $totaux_rappel[$rappel_nom]["Solde"]), "R");
          $ligne = $this->ajoutPage($colonnes);
        }
        $rappel_nom = CAppUI::tr("CRelance.statut.".$relance->statut);
        if ($relance->poursuite) {
          $rappel_nom .= " - ".$relance->poursuite;
        }
        $totaux_rappel[$rappel_nom] = array("Nombre" => 0.00, "Debit" => 0.00, "Credit" => 0.00, "Solde" => 0.00);
        $this->pdf->setFont($this->font, '', 10);
        $this->editCell(80, 15, 160, $rappel_nom, "C");
        $this->pdf->setFont($this->font, '', 8);
      }
      $this->pdf->setX(5);
      $ligne++;
      $totaux_rappel[$rappel_nom]["Nombre"] ++;
      $totaux_rappel[$rappel_nom]["Debit"] += $relance->_ref_object->_montant_avec_remise;
      $totaux_rappel[$rappel_nom]["Credit"] += $relance->_ref_object->_reglements_total_patient;
      $totaux_rappel[$rappel_nom]["Solde"] += $relance->_ref_object->_du_restant_patient;
      $valeurs = array(
        "Concerne"      => $relance->_ref_object->_ref_patient->nom." ".$relance->_ref_object->_ref_patient->prenom,
        "Destinataire"  => $this->loadGarant($relance->_ref_object),
        "N° fact."      => $relance->_ref_object->_view,
        "Débit"         => sprintf("%.2f", $relance->_ref_object->_montant_avec_remise),
        "Crédit"        => sprintf("%.2f", $relance->_ref_object->_reglements_total_patient),
        "Solde"         => sprintf("%.2f", $relance->_ref_object->_du_restant_patient),
        "Echéance"      => CMbDT::transform("", $relance->_ref_object->_echeance, CAppUI::conf("date")),
        "Pas de rappel jusqu'au" => CMbDT::transform("", CMbDT::date("+1 DAY", $relance->date), CAppUI::conf("date")));
      $x = 0;
      foreach ($colonnes as $key => $value) {
        $cote = ($key == "Débit" || $key == "Crédit" || $key == "Solde") ? "R" : "L";
        $this->editCell($this->pdf->getX()+$x, $debut_lignes + $ligne*4, $value, $valeurs[$key], $cote);
        $x = $value;
      }
      if ($debut_lignes + $ligne*4 >= 200) {
        $ligne = $this->ajoutPage($colonnes);
      }
    }
    $ligne ++;
    $pos_ligne = $debut_lignes + $ligne*4;
    // Total à chaque fin de type de rappel
    if ($rappel_nom != " ") {
      $this->editCell($pos_colonne[0], $pos_ligne, 45, "Total $rappel_nom", "L");
      $this->editCell($pos_colonne[1], $pos_ligne, 15, "(".$totaux_rappel[$rappel_nom]["Nombre"]." rappels)", "L");
      $this->editCell($pos_colonne[2], $pos_ligne, 15, sprintf("%.2f", $totaux_rappel[$rappel_nom]["Debit"]), "R");
      $this->editCell($pos_colonne[3], $pos_ligne, 15, sprintf("%.2f", $totaux_rappel[$rappel_nom]["Credit"]), "R");
      $this->editCell($pos_colonne[4], $pos_ligne, 15, sprintf("%.2f", $totaux_rappel[$rappel_nom]["Solde"]), "R");
    }
    // Un récapitulatif par type
    $colonnes = array("Code rappel" => 50,  "Nombre"  => 25, "Debit"  => 25,  "Credit"  => 25, "Solde" => 25);
    $colonnes_debut = array("Code rappel" => 5, "Nombre"  => 105, "Debit" => 155, "Credit"  => 205, "Solde" => 255);

    $this->ajoutPage($colonnes);
    $this->pdf->setFont($this->font, '', 10);
    $this->editCell(80, 15, 160, "Récapitulatif", "C");
    $this->pdf->setFont($this->font, '', 9);
    $ligne =0;
    $total_final = array("Nombre" => 0.00, "Debit" => 0.00, "Credit" => 0.00, "Solde" => 0.00);
    foreach ($totaux_rappel as $compte => $valeurs) {
      $ligne++;
      $pos_ligne = $debut_lignes + $ligne*4;
      $this->editCell(5, $pos_ligne, 80, $compte, "L");
      foreach ($valeurs as $key => $value) {
        $cote = ($key == "Code rappel") ? "L" : "R";
        $valeur = ($key == "Code rappel" || $key == "Nombre") ? $value : sprintf("%.2f", $value);
        $x = $colonnes_debut[$key];
        $this->editCell($x, $pos_ligne, $colonnes[$key], $valeur, $cote);
        if ($key != "Code rappel") {
          $total_final[$key] += $valeur;
        }
      }
    }
    $pos_colonne = array(5, 105, 155, 205, 255);
    $ligne +=2;
    $pos_ligne = $debut_lignes + $ligne*4;
    $this->editCell($pos_colonne[0] , $pos_ligne, 80, "Total général", "L");
    $this->editCell($pos_colonne[1], $pos_ligne, 25, $total_final["Nombre"], "R");
    $this->editCell($pos_colonne[2], $pos_ligne, 25, sprintf("%.2f", $total_final["Debit"]), "R");
    $this->editCell($pos_colonne[3], $pos_ligne, 25, sprintf("%.2f", $total_final["Credit"]), "R");
    $this->editCell($pos_colonne[4], $pos_ligne, 25, sprintf("%.2f", $total_final["Solde"]), "R");
  }

  /**
   * Edition de la liste de contrôle
   *
   * @return void
   */
  function editCheckList() {
    $colonnes = array(
      "Nom"     => 20 , "Prenom"  => 20,
      "Dossier" => 25 , "Type"    => 15,
      "Entree"  => 15 , "Sortie"  => 15,
      "Statut"  => 20 , "Montant" => 25);
    $this->editTableau($colonnes, 5, 25);

    $debut_lignes = 30;
    $ligne = 0;
    $montant_total = 0;
    foreach ($this->factures as $facture) {
      $this->pdf->setX(5);
      $ligne++;
      $valeurs = array(
        "Nom"     => $facture->_ref_patient->nom,
        "Prenom"  => $facture->_ref_patient->prenom,
        "Dossier" => $facture->_view." ".$facture->_ref_last_sejour->type,
        "Type"    => $facture->type_facture,
        "Entree"  => CMbDT::transform("", $facture->_ref_last_sejour->entree_prevue, CAppUI::conf("date")),
        "Sortie"  => CMbDT::transform("", $facture->_ref_last_sejour->sortie_prevue, CAppUI::conf("date")),
        "Statut"  => "",
        "Montant" => sprintf("%.2f", $facture->_montant_avec_remise));
      $x = 0;
      foreach ($colonnes as $key => $value) {
        $cote = ($key == "Montant") ? "R" : "L";
        $this->editCell($this->pdf->getX()+$x, $debut_lignes + $ligne*4, $value, $valeurs[$key], $cote);
        $x = $value;
      }
      if ($debut_lignes + $ligne*4 >= 200) {
        $ligne = $this->ajoutPage($colonnes);
      }
      $montant_total += $facture->_montant_avec_remise;
    }

    $ligne +=2;
    $pos_ligne = $debut_lignes + $ligne*4;
    $this->editCell(180 , $pos_ligne, 80, "Montant total:", "R");
    $this->editCell(210 , $pos_ligne, 80, $montant_total, "R");

  }

  /**
   * Chargement du garant de la facture
   *
   * @param CFactureCabinet|CFactureEtablissement $facture la facture
   *
   * @return string
   */
  function loadGarant($facture) {
    $patient = $facture->_ref_patient;
    $facture->loadRefAssurance();
    if (strlen($patient->cp)>4) {
      $patient->cp =  substr($patient->cp, 1);
    }

    $assurance_patient = null;
    $view = "_longview";
    $send_assur = !$facture->send_assur_base && $facture->type_facture == "maladie";
    if ($facture->assurance_maladie && $send_assur && $facture->_ref_assurance_maladie->type_pec != "TG" ) {
      $assurance_patient = $facture->_ref_assurance_maladie;
    }
    elseif ($facture->assurance_accident && !$facture->send_assur_compl && $facture->type_facture == "accident") {
      $assurance_patient = $facture->_ref_assurance_accident->type_pec == "TG" ? $patient : $facture->_ref_assurance_accident;
    }
    else {
      $assurance_patient = $patient;
      $view = "_view";
    }
    return $assurance_patient->$view;
  }
}
