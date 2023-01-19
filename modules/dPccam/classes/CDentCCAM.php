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
 * Class CDentCCAM
 * Table t_localisationdents
 *
 * Dents dans la CCAM
 */
class CDentCCAM extends CCCAM
{

    public $date_debut;
    public $date_fin;
    public $localisation;
    public $_libelle;

    /**
     * Mapping des donn�es depuis la base de donn�es
     *
     * @param array $row Ligne d'enregistrement de de base de donn�es
     *
     * @return void
     */
    public function map(array $row): void
    {
        $this->date_debut   = $row["DATEDEBUT"];
        $this->date_fin     = $row["DATEFIN"];
        $this->localisation = $row["LOCDENT"];
    }

    /**
     * Chargement de a liste des dents disponibles
     *
     * @return self[] Liste des dents
     */
    public static function loadList(): array
    {
        $cache = Cache::getCache(Cache::INNER_OUTER)->withCompressor();
        $listDents = $cache->get('CDentCCAM.loadList');
        if (!$listDents) {
            $ds = self::getSpec()->ds;

            $query  = "SELECT t_localisationdents.*
                          FROM t_localisationdents
                          ORDER BY t_localisationdents.LOCDENT ASC,
                            t_localisationdents.DATEFIN ASC";
            $result = $ds->exec($query);

            $listDents = [];
            while ($row = $ds->fetchArray($result)) {
                $dent = new CDentCCAM();
                $dent->map($row);
                $dent->loadLibelle();
                $listDents[$row["DATEFIN"]][] = $dent;
            }

            $cache->set('CDentCCAM.loadList', $listDents);
        }

        return $listDents;
    }

    /**
     * Chargement d'une dent � partir de son num�ro
     *
     * @param string $localisation Numero de la dent
     *
     * @return bool r�ussite du chargement
     */
    public function load(string $localisation): bool
    {
        $localisation = (int)$localisation;
        $result       = false;

        $dents = self::getListeDents();

        foreach ($dents as $dent) {
            if ($localisation == $dent['LOCDENT']) {
                $this->map($dent);
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * Chargement du libell� de la dent
     * Table c_dentsincomp
     *
     * @return string libell� de la dent
     */
    public function loadLibelle(): string
    {
        $dents = self::getLibellesDents();

        $code_dent = str_pad($this->localisation, 2, "0", STR_PAD_LEFT);
        if (array_key_exists($code_dent, $dents)) {
            $this->_libelle = $dents[$code_dent];
        }

        return $this->_libelle;
    }

    /**
     * Charge la liste des libell�s des dents � partir du cache ou de la base de donn�es
     *
     * @return array
     */
    public static function getLibellesDents(): array
    {
        $cache = Cache::getCache(Cache::INNER_OUTER)->withCompressor();
        $dents = $cache->get('CDentCCAM.getLibellesDents-c_dentsincomp');
        if (!$dents) {
            self::getSpec();
            $list  = self::$spec->ds->loadList('SELECT * FROM `c_dentsincomp`;');
            $dents = [];

            if ($list) {
                foreach ($list as $dent) {
                    $dents[$dent['CODE']] = $dent['LIBELLE'];
                }
            }

            $cache->set('CDentCCAM.getLibellesDents-c_dentsincomp', $dents);
        }

        return $dents;
    }

    /**
     * Charge la liste des libell�s des dents � partir du cache ou de la base de donn�es
     *
     * @return array
     */
    public static function getListeDents(): array
    {
        $cache = Cache::getCache(Cache::INNER_OUTER);
        $list = $cache->get('CDentCCAM.getListeDents-t_localisationdents');
        if (!$list) {
            self::getSpec();
            $list = self::$spec->ds->loadList('SELECT * FROM `t_localisationdents`;');
            $cache->set('CDentCCAM.getListeDents-t_localisationdents', $list);
        }

        return $list;
    }
}
