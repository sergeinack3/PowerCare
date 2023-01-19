<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

/**
 * Class CProcedureCCAM
 *
 * Procédures
 * Niveau Acte
 */
class CProcedureCCAM extends CCCAM
{
    public $date_effet;
    public $code_procedure;
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
        $this->date_effet     = $row["DATEEFFET"];
        $this->code_procedure = $row["CODEPROCEDURE"];
        $this->_ref_code      = CCodeCCAM::getCodeInfos($this->code_procedure);
    }

    /**
     * Chargement de a liste des procédures pour un code
     *
     * @param string $code Code CCAM
     *
     * @return self[] Liste des procédures
     */
    public static function loadListFromCode(string $code): array
    {
        $ds = self::$spec->ds;

        $query  = "SELECT p_acte_procedure.*
      FROM p_acte_procedure
      WHERE p_acte_procedure.CODEACTE = %
      ORDER BY p_acte_procedure.DATEEFFET DESC, p_acte_procedure.CODEPROCEDURE ASC";
        $query  = $ds->prepare($query, $code);
        $result = $ds->exec($query);

        $list_procedures = [];
        while ($row = $ds->fetchArray($result)) {
            $procedure = new CProcedureCCAM();
            $procedure->map($row);
            $list_procedures[$row["DATEEFFET"]][] = $procedure;
        }

        return $list_procedures;
    }
}
