<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

/**
 * Class CIncompatibiliteCCAM
 *
 * Incompatibilités médicales code à code
 * Niveau acte
 */
class CIncompatibiliteCCAM extends CCCAM
{
    public $date_effet;
    public $code_incomp;
    public $_ref_code;

    /**
     * Mapping des données depuis la base de données
     *
     * @param array $row Ligne d'enregistrement de de base de données
     *
     * @return void
     */
    public function map(array $row): void
    {
        $this->date_effet  = $row["DATEEFFET"];
        $this->code_incomp = trim($row["INCOMPATIBLE"]);
        $this->_ref_code   = CCodeCCAM::getCodeInfos($this->code_incomp);
    }

    /**
     * Chargement de a liste des incompatibilités pour un code
     *
     * @param string $code Code CCAM
     *
     * @return self[][] Liste des incompatibilités
     */
    public static function loadListFromCode(string $code): array
    {
        $ds = self::$spec->ds;

        $query  = "SELECT p_acte_incompatibilite.*
      FROM p_acte_incompatibilite
      WHERE p_acte_incompatibilite.CODEACTE = %
      ORDER BY p_acte_incompatibilite.DATEEFFET DESC, p_acte_incompatibilite.INCOMPATIBLE ASC";
        $query  = $ds->prepare($query, $code);
        $result = $ds->exec($query);

        $list_incompatibilites = [];
        while ($row = $ds->fetchArray($result)) {
            $incompatibilite = new CIncompatibiliteCCAM();
            $incompatibilite->map($row);
            $list_incompatibilites[$row["DATEEFFET"]][] = $incompatibilite;
        }

        return $list_incompatibilites;
    }
}
