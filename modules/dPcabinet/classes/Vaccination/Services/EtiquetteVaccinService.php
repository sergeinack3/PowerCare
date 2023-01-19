<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Vaccination\Services;

use Ox\Core\CMbDT;
use Ox\Mediboard\Hospi\CModeleEtiquette;

class EtiquetteVaccinService
{
    private $modele_etiquette;

    public function __construct()
    {
        $this->modele_etiquette = new CModeleEtiquette();
        $this->modele_etiquette->font = "dejavusansmono";
        $this->modele_etiquette->object_class = "CInjection";
        $this->modele_etiquette->largeur_page = "21";
        $this->modele_etiquette->hauteur_page = "29.7";
        $this->modele_etiquette->marge_horiz = "0.3";
        $this->modele_etiquette->marge_vert = "1.3";
        $this->modele_etiquette->marge_horiz_etiq = "0";
        $this->modele_etiquette->marge_vert_etiq = "0";
        $this->modele_etiquette->hauteur_ligne = "8";
        $this->modele_etiquette->nb_colonnes = "2";
    }

    /**
     * Print a number of etiquettes for a vaccine
     * @param int $nbEtiquette
     * @param array $fields
     * @param array $params
     * @return void
     */
    public function generateNbEtiquetteForVaccin(int $nbEtiquette, array $fields, array $params): void
    {
        $texte  = "[CODE BARRE CIP PRODUIT] \r\n";
        $texte .= "*NOM UTILISE* *PREMIER PRENOM NAISSANCE* \r\n";
        $texte .= "[AGE], [SEXE] \r\n";
        $texte .= "[NOM PRODUIT ADMINISTRE] ([NOM VACCIN]) \r\n";
        $texte .= "Lot N°[LOT VACCIN] \r\n";
        $texte .= "Injection le [DATE INJECTION] \r\n";
        $texte .= "Expiration le [DATE EXPIRATION]";

        $name_etiquette = $fields['NOM UTILISE']."_".str_replace([" ", ":", "-"], "", CMbDT::dateTime());

        $this->modele_etiquette->texte = $texte;

        $this->modele_etiquette->nb_lignes = (int)ceil($nbEtiquette / $this->modele_etiquette->nb_colonnes);
        $this->modele_etiquette->_width_etiq  = round(($this->modele_etiquette->largeur_page - 2 * $this->modele_etiquette->marge_horiz) / $this->modele_etiquette->nb_colonnes, 2);
        $this->modele_etiquette->_height_etiq = round(($this->modele_etiquette->hauteur_page - 2 * $this->modele_etiquette->marge_vert) / $this->modele_etiquette->nb_lignes, 2);
        $this->modele_etiquette->nom = "etiquette_vaccin_".$name_etiquette;

        $this->modele_etiquette->completeLabelFields($fields, $params);
        $this->modele_etiquette->replaceFields($fields);
        ob_end_clean();
        $this->modele_etiquette->printEtiquettes();
    }
}
