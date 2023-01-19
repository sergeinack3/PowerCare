<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Ox\Core\Cache;

/**
 * Class CActiviteModificateurCCAM
 * Table p_activite_modificateur
 *
 * Modificateurs disponibles pour un acte + activité
 * Niveau activite
 */
class CActiviteModificateurCCAM extends CCCAM
{

    public $date_effet;
    public $date_fin;
    public $modificateur;

    public $_libelle;

    /**
     * Mapping des données depuis la base de données
     *
     * @param array $row Ligne d'enregistrement de de base de données
     *
     * @return void
     */
    public function map(array $row): void
    {
        $this->date_effet   = $row["DATEEFFET"];
        $this->modificateur = $row["MODIFICATEUR"];
    }

    /**
     * Retourne la liste des dates d'effet disponible pour les modificateurs d'un acte
     *
     * @param string $code Code CCAM
     *
     * @return array
     */
    public static function loadDateEffetList(string $code): array
    {
        $ds = self::$spec->ds;

        $query     = "SELECT DATEEFFET
      FROM p_activite_modificateur
      WHERE p_activite_modificateur.CODEACTE = %1
      GROUP BY p_activite_modificateur.DATEEFFET
      ORDER BY p_activite_modificateur.DATEEFFET DESC";
        $query     = $ds->prepare($query, $code);
        $result    = $ds->exec($query);
        $listDates = [];
        while ($row = $ds->fetchArray($result)) {
            $listDates[] = $row["DATEEFFET"];
        }

        return $listDates;
    }

    /**
     * Chargement de a liste des modificateurs pour une activité
     *
     * @param string $code     Code CCAM
     * @param string $activite Activité CCAM
     *
     * @return self[][] Liste des modificateurs
     */
    public static function loadListFromCodeActivite(string $code, string $activite): array
    {
        $ds = self::$spec->ds;

        $query  = "SELECT p_activite_modificateur.*
      FROM p_activite_modificateur
      LEFT JOIN t_modificateurinfooc ON t_modificateurinfooc.CODE = p_activite_modificateur.MODIFICATEUR
      WHERE p_activite_modificateur.CODEACTE = %1
      AND p_activite_modificateur.CODEACTIVITE = %2
      ORDER BY p_activite_modificateur.DATEEFFET DESC, p_activite_modificateur.MODIFICATEUR";
        $query  = $ds->prepare($query, $code, $activite);
        $result = $ds->exec($query);

        $list_modifs    = [];
        $listDatesEffet = self::loadDateEffetList($code);

        foreach ($listDatesEffet as $date) {
            $list_modifs[$date] = [];
        }
        while ($row = $ds->fetchArray($result)) {
            $modif = new CActiviteModificateurCCAM();
            $modif->map($row);
            $list_modifs[$row["DATEEFFET"]][] = $modif;
        }

        return $list_modifs;
    }

    /**
     * Chargement du libellé du modificateur
     * Table l_modificateur
     *
     * @return string Le libellé du modificateur
     */
    public function loadLibelle(): ?string
    {
        $modificateurs = self::getListeModificateurs();

        if (array_key_exists($this->modificateur, $modificateurs)) {
            $this->_libelle = $modificateurs[$this->modificateur];
        }

        return $this->_libelle;
    }

    /**
     * Charge la liste des modificateurs à partir du cache ou de la base de données
     *
     * @return array
     */
    public static function getListeModificateurs(): array
    {
        $cache = Cache::getCache(Cache::INNER_OUTER)->withCompressor();
        $modificateurs = $cache->get('CActiviteModificateurCCAM.getListeModificateurs-l_modificateur');

        if (!$modificateurs) {
            self::getSpec();
            $list          = self::$spec->ds->loadList('SELECT * FROM `l_modificateur`;');
            $modificateurs = [];
            if ($list) {
                foreach ($list as $mod) {
                    $modificateurs[$mod['CODE']] = $mod['LIBELLE'];
                }
            }

            $cache->set('CActiviteModificateurCCAM.getListeModificateurs-l_modificateur', $modificateurs);
        }

        return $modificateurs;
    }
}
