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
 * Class CActiviteClassifCCAM
 * Table p_activite_classif
 *
 * Classification des actes
 * Niveau activite
 */
class CActiviteClassifCCAM extends CCCAM
{

    public $date_effet;
    public $arrete_minist;
    public $publication_jo;
    public $categorie_medicale;
    public $_categorie_medicale;
    public $code_regroupement;
    public $_regroupement;
    public $specialites = [];

    /**
     * Mapping des donn�es depuis la base de donn�es
     *
     * @param array $row Ligne d'enregistrement de de base de donn�es
     *
     * @return void
     */
    public function map(array $row): void
    {
        $this->date_effet         = $row["DATEEFFET"];
        $this->arrete_minist      = $row["DATEARRETE"];
        $this->publication_jo     = $row["DATEPUBJO"];
        $this->categorie_medicale = $row["CATMED"];
        $this->code_regroupement  = $row["REGROUP"];

        for ($i = 1; $i <= 10; $i++) {
            if ($row["SPECIALITE$i"] !== null) {
                $this->specialites[] = $row["SPECIALITE$i"];
            }
        }
    }

    /**
     * Chargement de a liste des classifications pour une activite
     *
     * @param string $code     Code CCAM
     * @param string $activite Activit� CCAM
     *
     * @return self[] Liste des classifications historis�es
     */
    public static function loadListFromCodeActivite(string $code, string $activite): array
    {
        $ds = self::$spec->ds;

        $query  = "SELECT p_activite_classif.*
      FROM p_activite_classif
      WHERE p_activite_classif.CODEACTE = %1
      AND p_activite_classif.ACTIVITE = %2
      ORDER BY p_activite_classif.DATEEFFET DESC";
        $query  = $ds->prepare($query, $code, $activite);
        $result = $ds->exec($query);

        $list_classif = [];
        while ($row = $ds->fetchArray($result)) {
            $classif = new CActiviteClassifCCAM();
            $classif->map($row);
            $list_classif[$row["DATEEFFET"]] = $classif;
        }

        return $list_classif;
    }

    /**
     * Chargement du libell� de la cat�gorie m�dicale
     * Table c_categoriemedicale
     *
     * @return string le libell� de la cat�gorie
     */
    public function loadCatMed(): ?string
    {
        $categories = self::getListeCategoriesMedicales();

        if (array_key_exists($this->categorie_medicale, $categories)) {
            $this->_categorie_medicale = $categories[$this->categorie_medicale];
        }

        return $this->_categorie_medicale;
    }

    /**
     * Chargement du libell� de regroupement
     * Table c_coderegroupement
     *
     * @return string le libell� du regroupement
     */
    public function loadRegroupement(): ?string
    {
        $codes = self::getListeCodesRegroupement();

        if (array_key_exists($this->code_regroupement, $codes)) {
            $this->_regroupement = $codes[$this->code_regroupement];
        }

        return $this->_regroupement;
    }

    /**
     * Charge la liste des cat�gories m�dicales � partir du cache ou de la base de donn�es
     *
     * @return array
     */
    public static function getListeCategoriesMedicales(): array
    {
        $cache = Cache::getCache(Cache::INNER_OUTER)->withCompressor();
        $categories = $cache->get('CActiviteClassifCCAM.getListeCategoriesMedicales-c_categoriemedicale');

        if (!$categories) {
            self::getSpec();
            $list       = self::$spec->ds->loadList('SELECT * FROM `c_categoriemedicale`;');
            $categories = [];
            if ($list) {
                foreach ($list as $categorie) {
                    $categories[$categorie['CODE']] = $categorie['LIBELLE'];
                }
            }

            $cache->set('CActiviteClassifCCAM.getListeCategoriesMedicales-c_categoriemedicale', $categories);
        }

        return $categories;
    }

    /**
     * Charge la liste des codes de regroupement m�dicales � partir du cache ou de la base de donn�es
     *
     * @return array
     */
    public static function getListeCodesRegroupement(): array
    {
        $cache = Cache::getCache(Cache::INNER_OUTER);
        $codes = $cache->get('CActiviteClassifCCAM.getListeCodesRegroupement-c_coderegroupement');

        if (!$codes) {
            self::getSpec();
            $list  = self::$spec->ds->loadList('SELECT * FROM `c_coderegroupement`;');
            $codes = [];
            if ($list) {
                foreach ($list as $code) {
                    $codes[$code['CODE']] = $code['LIBELLE'];
                }
            }

            $cache->set('CActiviteClassifCCAM.getListeCodesRegroupement-c_coderegroupement', $codes);
        }

        return $codes;
    }
}
