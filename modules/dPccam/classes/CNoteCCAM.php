<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

/**
 * Class CNoteCCAM
 *
 * Notes
 * Niveau acte
 */
class CNoteCCAM extends CCCAM
{
    public $type;
    public $texte;

    /**
     * Mapping des données depuis la base de données
     *
     * @param array $row Ligne d'enregistrement de de base de données
     *
     * @return void
     */
    public function map(array $row): void
    {
        $this->type  = $row["TYPE"];
        $this->texte = $row["TEXTE"];
    }

    /**
     * Chargement de a liste des notes pour un code
     *
     * @param string $code Code CCAM
     *
     * @return self[] Liste des notes
     */
    public static function loadListFromCode(string $code): array
    {
        $ds = self::$spec->ds;

        $query  = "SELECT p_acte_notes.*
      FROM p_acte_notes
      WHERE p_acte_notes.CODEACTE = %
      ORDER BY p_acte_notes.TYPE ASC";
        $query  = $ds->prepare($query, $code);
        $result = $ds->exec($query);

        $list_notes = [];
        while ($row = $ds->fetchArray($result)) {
            $note = new CNoteCCAM();
            $note->map($row);
            $list_notes[] = $note;
        }

        return $list_notes;
    }
}
