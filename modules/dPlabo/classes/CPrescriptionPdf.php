<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Labo;

use Ox\Core\CMbPdf;

/**
 * Class CPrescriptionPdf
 *
 * Classe de gestion des pdf spécifique aux prescriptions
 */
class CPrescriptionPdf extends CMbPdf {
  public $decalage;
  public $praticien;
  public $patient;
  public $sexe;
  public $naissance;
  public $prelevement;

  /**
   * @param string $bc        Code barre
   * @param string $praticien Praticien concerné
   * @param string $patient   Patient concerné
   * @param string $sexe      Sexe du patient
   * @param string $naissance Date de naissance du patient
   * @param string $prelev    Code du prélèvement
   *
   * @return void
   */
  public function setBarcode($bc="", $praticien = "", $patient = "", $sexe = "", $naissance = "", $prelev = "") {
    $this->barcode = $bc;
    $this->praticien = $praticien;
    $this->patient = $patient;
    $this->prelevement = $prelev;
    $this->sexe = $sexe;
    $this->naissance = $naissance;
  }

  /**
   * Création du footer
   *
   * @return void
   */
  public function Footer() {
    // On affiche "ETIQUETTE ...." seulement si un code barre est présent
    if ($this->viewBarcode(15, 230, 5, null, 30, true)) {
      $this->SetFontSize(12);
      $this->SetXY($this->original_lMargin, 290);
      $this->Cell(0, 0, "ETIQUETTE A COLLER SUR LE TUBE AVANT LE PRELEVEMENT", 0, 0, 'C');
    }

    $this->SetXY($this->original_lMargin, 290);

    // Impression du numero des pages
    $this->AliasNbPages();
    $this->SetFontSize(8);

    $this->Cell(0, 0, $this->l['w_page']." ".$this->PageNo().' / {nb}', 0, 0, 'R');
  }

  /**
   * @param string $pratView     Nom du praticien
   * @param string $functionView Nom de la fonction
   * @param string $groupView    Nom de l'établissement
   *
   * @return string
   */
  public function viewPraticien($pratView, $functionView, $groupView){
    return "<b>Medecin:</b> <br />".utf8_encode($pratView).
                    "<br />".utf8_encode($functionView).
                    "<br />".utf8_encode($groupView);
  }

  /**
   * @param string $patientView      Nom du patient
   * @param string $patientNaissance Date de naissance du patient
   * @param string $patientAdresse   Adresse du patient
   * @param string $patientCP        Code Postal du patient
   * @param string $patientVille     Ville du patient
   * @param string $patientTel       Téléphone du patient
   *
   * @return string
   */
  public function viewPatient($patientView, $patientNaissance, $patientAdresse, $patientCP, $patientVille, $patientTel){
    return "<b>Patient:</b> <br />".utf8_encode($patientView).
            "<br />Naissance le ".utf8_encode($patientNaissance).
                    "<br />".utf8_encode($patientAdresse).
                    "<br />".utf8_encode($patientCP).
                    " ".utf8_encode($patientVille).
                    "<br />".utf8_encode($patientTel);
  }

  /**
   * @param string $col1 Contenu de la colonne 1
   * @param string $col2 Contenu de la colonne 2
   *
   * @return void
   */
  public function createTab($col1, $col2){
    $first_column_width = 105;
    $current_y_position = 50;
    $this->writeHTMLCell($first_column_width, 0, 0, $current_y_position, $col1, 0, 0, 0);
    $this->Cell(0);
    $this->writeHTMLCell(0, 0, $first_column_width, $current_y_position, $col2, 0, 0, 0);
  }

  /**
   * Affichage du code barre
   *
   * @param int    $x          position sur l'axe x
   * @param int    $y          position sur l'axe y
   * @param int    $h          hauteur des codes barres
   * @param string $codage     type de codage, par default C128B
   * @param int    $decalage   decalage entre les 2 lignes de codes barres
   * @param bool   $traduction affichage de la traduction des codes barres
   *
   * @return bool
   */
  public function viewBarcode($x,$y,$h,$codage = "C128B", $decalage = 30, $traduction = true){
    $this->decalage = 0;
    if ($this->barcode) {
      $this->Ln();
      $compteur = 0;
      while ($compteur < 4) {
        if ($traduction == true) {
          $this->SetFontSize(7);
          $this->writeHTMLCell(0, 0, $x + $this->decalage, $y - 15, utf8_encode("Dr ".$this->praticien), 0, 0, 0);
          $this->writeHTMLCell(0, 0, $x + $this->decalage, $y - 12, utf8_encode($this->patient), 0, 0, 0);
          $this->writeHTMLCell(0, 0, $x + $this->decalage, $y - 9, strtoupper($this->sexe)." ".$this->naissance, 0, 0, 0);
          $this->writeHTMLCell(0, 0, $x + $this->decalage, $y - 6, utf8_encode($this->prelev), 0, 0, 0);

          $this->writeHTMLCell(0, 0, $x + $this->decalage, $y - 15 + $decalage, utf8_encode("Dr ".$this->praticien), 0, 0, 0);
          $this->writeHTMLCell(0, 0, $x + $this->decalage, $y - 12 + $decalage, utf8_encode($this->patient), 0, 0, 0);
          $this->writeHTMLCell(0, 0, $x + $this->decalage, $y - 9  + $decalage, strtoupper($this->sexe)." ".$this->naissance, 0, 0, 0);
          $this->writeHTMLCell(0, 0, $x + $this->decalage, $y - 6  + $decalage, utf8_encode($this->prelev), 0, 0, 0);

          $this->SetFontSize(10);
          $this->writeHTMLCell(0, 0, $x + $this->decalage + 3, $y + 4, $this->barcode, 0, 0, 0);

          $this->writeHTMLCell(0, 0, $x + $this->decalage + 3, $y + $decalage + 4, $this->barcode, 0, 0, 0);
        }

        $this->writeBarcode($x + $this->decalage, $y, 180, $h, $codage, false, false, 2, $this->barcode);
        $this->writeBarcode($x + $this->decalage, $y + $decalage, 180, $h, $codage, false, false, 2, $this->barcode);
        $this->decalage += 50;
        $compteur++;
      }
    }

    return $this->barcode;
  }
}
