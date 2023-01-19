<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

/**
 * Class CActiviteAssociationCCAM
 *
 * Associations m�dicales pr�vues code � code
 * Niveau activite
 */
class CActiviteAssociationCCAM extends CCCAM
{
    public $date_effet;
    public $acte_asso;
    public $activite_asso;
    public $regle;
    public $_ref_code;

    /**
     * Mapping des donn�es depuis la base de donn�es
     *
     * @param array $row Ligne d'enregistrement de de base de donn�es
     *
     * @return void
     */
    public function map(array $row): void
    {
        $this->date_effet    = $row["DATEEFFET"];
        $this->acte_asso     = $row["ACTEASSO"];
        $this->activite_asso = $row["ACTIVITEASSO"];
        $this->regle         = $row["REGLE"];
        $this->_ref_code     = CCodeCCAM::getCodeInfos($this->acte_asso);
    }

    /**
     * Chargement de a liste des associations pr�vues pour une activite
     *
     * @param string $code     Code CCAM
     * @param string $activite Activit� CCAM
     *
     * @return self[][] Liste des associations pr�vues
     */
    public static function loadListFromCodeActivite(string $code, string $activite): array
    {
        $ds = self::$spec->ds;

        $query  = "SELECT p_activite_associabilite.*
      FROM p_activite_associabilite
      WHERE p_activite_associabilite.CODEACTE = %1
      AND p_activite_associabilite.ACTIVITE = %2
      ORDER BY p_activite_associabilite.DATEEFFET DESC";
        $query  = $ds->prepare($query, $code, $activite);
        $result = $ds->exec($query);

        $list_asso = [];
        while ($row = $ds->fetchArray($result)) {
            $asso = new CActiviteAssociationCCAM();
            $asso->map($row);
            $list_asso[$row["DATEEFFET"]][] = $asso;
        }

        return $list_asso;
    }
}
