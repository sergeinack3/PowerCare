<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

/**
 * Class CPhaseInfoCCAM
 * Table p_phase_acte
 *
 * Informations historisées sur les phases
 * Niveau phase
 */
class CPhaseInfoCCAM extends CCCAM
{
    public $date_effet;
    public $arrete_minist;
    public $publication_jo;
    public $nb_seances;
    public $unite_oeuvre;
    public $coeff_unite_oeuvre;
    public $code_paiement;
    public $prix_unitaire_g01;
    public $prix_unitaire_g02;
    public $prix_unitaire_g03;
    public $prix_unitaire_g04;
    public $prix_unitaire_g05;
    public $prix_unitaire_g06;
    public $prix_unitaire_g07;
    public $prix_unitaire_g08;
    public $prix_unitaire_g09;
    public $prix_unitaire_g10;
    public $prix_unitaire_g11;
    public $prix_unitaire_g12;
    public $prix_unitaire_g13;
    public $prix_unitaire_g14;
    public $prix_unitaire_g15;
    public $prix_unitaire_g16;
    public $charge_cab;
    public $coeff_dom;

    /**
     * Mapping des données depuis la base de données
     *
     * @param array $row Ligne d'enregistrement de de base de données
     *
     * @return void
     */
    public function map(array $row): void
    {
        $this->date_effet         = $row["DATEEFFET"];
        $this->arrete_minist      = $row["DATEARRETE"];
        $this->publication_jo     = $row["DATEPUBJO"];
        $this->nb_seances         = $row["NBSEANCES"];
        $this->unite_oeuvre       = $row["UNITEOEUVRE"];
        $this->coeff_unite_oeuvre = $row["COEFFUOEUVRE"];
        $this->code_paiement      = $row["CODEPAIEMENT"];

        /* Récupération des différentes grilles tarifaires */
        $this->prix_unitaire_g01 = $row["PRIXUNITAIRE_G01"];
        $this->prix_unitaire_g02 = $row["PRIXUNITAIRE_G02"];
        $this->prix_unitaire_g03 = $row["PRIXUNITAIRE_G03"];
        $this->prix_unitaire_g04 = $row["PRIXUNITAIRE_G04"];
        $this->prix_unitaire_g05 = $row["PRIXUNITAIRE_G05"];
        $this->prix_unitaire_g06 = $row["PRIXUNITAIRE_G06"];
        $this->prix_unitaire_g07 = $row["PRIXUNITAIRE_G07"];
        $this->prix_unitaire_g08 = $row["PRIXUNITAIRE_G08"];
        $this->prix_unitaire_g09 = $row["PRIXUNITAIRE_G09"];
        $this->prix_unitaire_g10 = $row["PRIXUNITAIRE_G10"];
        $this->prix_unitaire_g11 = $row["PRIXUNITAIRE_G11"];
        $this->prix_unitaire_g12 = $row["PRIXUNITAIRE_G12"];
        $this->prix_unitaire_g13 = $row["PRIXUNITAIRE_G13"];
        $this->prix_unitaire_g14 = $row["PRIXUNITAIRE_G14"];
        $this->prix_unitaire_g15 = $row["PRIXUNITAIRE_G15"];
        $this->prix_unitaire_g16 = $row["PRIXUNITAIRE_G16"];

        $this->charge_cab   = $row["CHARGESCAB"];
        $this->coeff_dom    = [];
        $this->coeff_dom[1] = floatval($row["COEFFDOM1"]) / 1000;
        $this->coeff_dom[2] = floatval($row["COEFFDOM2"]) / 1000;
        $this->coeff_dom[3] = floatval($row["COEFFDOM3"]) / 1000;
        $this->coeff_dom[4] = floatval($row["COEFFDOM4"]) / 1000;
    }

    /**
     * Chargement de a liste des informations historisées pour une phase
     *
     * @param string $code     Code CCAM
     * @param string $activite Activité CCAM
     * @param string $phase    Phase CCAM
     *
     * @return self[] Liste des informations historisées
     */
    public static function loadListFromCodeActivitePhase(string $code, string $activite, string $phase): array
    {
        $ds = self::$spec->ds;

        $query  = "SELECT p_phase_acte.*
      FROM p_phase_acte
      WHERE p_phase_acte.CODEACTE = %1
      AND p_phase_acte.ACTIVITE = %2
      AND p_phase_acte.PHASE = %3
      ORDER BY p_phase_acte.DATEEFFET DESC";
        $query  = $ds->prepare($query, $code, $activite, $phase);
        $result = $ds->exec($query);

        $list_infos = [];
        while ($row = $ds->fetchArray($result)) {
            $info = new CPhaseInfoCCAM();
            $info->map($row);
            $list_infos[$row["DATEEFFET"]] = $info;
        }

        return $list_infos;
    }
}
