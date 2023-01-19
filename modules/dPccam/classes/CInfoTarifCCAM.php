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
 * Class CInfoTarifCCAM
 * Table p_acte_infotarif
 *
 * Elements historisés des informations
 * Niveau acte
 */
class CInfoTarifCCAM extends CCCAM
{
    public $admission_rbt;
    public $entente;
    public $date_effet;
    public $arrete_minist;
    public $publication_jo;

    public $code_exo;
    public $prescripteur;
    public $forfait;

    /**
     * Mapping des données depuis la base de données
     *
     * @param array $row Ligne d'enregistrement de de base de données
     *
     * @return void
     */
    public function map(array $row): void
    {
        $this->admission_rbt  = $row["REMBOURSEMENT"];
        $this->entente        = $row["ENTENTE"];
        $this->date_effet     = $row["DATEEFFET"];
        $this->arrete_minist  = $row["DATEARRETE"];
        $this->publication_jo = $row["DATEPUBLICATION"];

        $this->code_exo          = [];
        $this->code_exo[1]["db"] = $row["EXOTICKET1"];
        $this->code_exo[2]["db"] = $row["EXOTICKET2"];
        $this->code_exo[3]["db"] = $row["EXOTICKET3"];
        $this->code_exo[4]["db"] = $row["EXOTICKET4"];
        $this->code_exo[5]["db"] = $row["EXOTICKET5"];

        $this->prescripteur           = [];
        $this->prescripteur[1]["db"]  = $row["PRESCRIPTEUR1"];
        $this->prescripteur[2]["db"]  = $row["PRESCRIPTEUR2"];
        $this->prescripteur[3]["db"]  = $row["PRESCRIPTEUR3"];
        $this->prescripteur[4]["db"]  = $row["PRESCRIPTEUR4"];
        $this->prescripteur[5]["db"]  = $row["PRESCRIPTEUR5"];
        $this->prescripteur[6]["db"]  = $row["PRESCRIPTEUR6"];
        $this->prescripteur[7]["db"]  = $row["PRESCRIPTEUR7"];
        $this->prescripteur[8]["db"]  = $row["PRESCRIPTEUR8"];
        $this->prescripteur[9]["db"]  = $row["PRESCRIPTEUR9"];
        $this->prescripteur[10]["db"] = $row["PRESCRIPTEUR10"];

        $this->forfait           = [];
        $this->forfait[1]["db"]  = $row["FORFAIT1"];
        $this->forfait[2]["db"]  = $row["FORFAIT2"];
        $this->forfait[3]["db"]  = $row["FORFAIT3"];
        $this->forfait[4]["db"]  = $row["FORFAIT4"];
        $this->forfait[5]["db"]  = $row["FORFAIT5"];
        $this->forfait[6]["db"]  = $row["FORFAIT6"];
        $this->forfait[7]["db"]  = $row["FORFAIT7"];
        $this->forfait[8]["db"]  = $row["FORFAIT8"];
        $this->forfait[9]["db"]  = $row["FORFAIT9"];
        $this->forfait[10]["db"] = $row["FORFAIT10"];
    }

    /**
     * Chargement de a liste des infos historisées pour un code
     *
     * @param string $code Code CCAM
     *
     * @return self[] Liste des info historisées
     */
    public static function loadListFromCode(string $code): array
    {
        $ds     = self::$spec->ds;
        $query  = "SELECT p_acte_infotarif.*
      FROM p_acte_infotarif
      WHERE p_acte_infotarif.CODEACTE = %
      ORDER BY p_acte_infotarif.DATEEFFET DESC";
        $query  = $ds->prepare($query, $code);
        $result = $ds->exec($query);

        $listInfotarif = [];
        while ($row = $ds->fetchArray($result)) {
            $infoTarif = new CInfoTarifCCAM();
            $infoTarif->map($row);
            $listInfotarif[$row["DATEEFFET"]] = $infoTarif;
        }

        return $listInfotarif;
    }

    /**
     * Chargement des infos historisées pour un code en fonction de sa date
     *
     * @param string $code Code CCAM
     * @param string $date Date
     *
     * @return self[] Liste des info historisées pour une date donnée
     */
    public static function loadFromCodeAndDate(string $code, string $date): array
    {
        $ds = self::$spec->ds;

        $query  = "SELECT p_acte_infotarif.*
      FROM p_acte_infotarif
      WHERE p_acte_infotarif.CODEACTE = %
      AND p_acte_infotarif.DATEEFFET = $date";
        $query  = $ds->prepare($query, $code);
        $result = $ds->exec($query);
        $row    = $ds->fetchArray($result);

        return $row;
    }

    /**
     * Chargement des libellés d'exonération
     * Table c_exotm
     *
     * @return array liste des exos
     */
    public function loadLibelleExo(): array
    {
        $codes = self::getListeCodesExoneration();

        foreach ($this->code_exo as &$exo) {
            if (!$exo["db"]) {
                continue;
            }

            if (array_key_exists($exo['db'], $codes)) {
                $exo['libelle'] = $codes[$exo['db']];
            }
        }

        return $this->code_exo;
    }

    /**
     * Chargement des libellés de prescripteurs
     * Table  c_categoriespecialite
     *
     * @return array liste des prescripteurs
     */
    public function loadLibellePresc(): array
    {
        $categories = self::getListeCategoriesSpecialites();

        foreach ($this->prescripteur as &$presc) {
            if (!$presc['db']) {
                continue;
            }

            if (array_key_exists($presc['db'], $categories)) {
                $presc['libelle'] = $categories[$presc['db']];
            }
        }

        return $this->prescripteur;
    }

    /**
     * Chargement des libellés des forfaits
     * Table  c_typeforfait
     *
     * @return array liste des forfaits
     */
    public function loadLibelleForfait(): array
    {
        $forfaits = self::getListeTypesForfait();

        foreach ($this->forfait as &$forfait) {
            if (!$forfait['db']) {
                continue;
            }

            if (array_key_exists($forfait['db'], $forfaits)) {
                $forfait['libelle'] = $forfaits[$forfait['db']];
            }
        }

        return $this->forfait;
    }

    /**
     * Charge la liste des catégories de spécialités à partir du cache ou de la base de données
     *
     * @return array
     */
    public static function getListeCategoriesSpecialites(): array
    {
        $cache = Cache::getCache(Cache::INNER_OUTER)->withCompressor();
        $categories = $cache->get('CInfoTarifCCAM.getListeCategoriesSpecialites-c_categoriespecialite');
        if (!$categories) {
            self::getSpec();
            $list       = self::$spec->ds->loadList('SELECT * FROM `c_categoriespecialite`;');
            $categories = [];
            if ($list) {
                foreach ($list as $categorie) {
                    $categories[$categorie['CODE']] = $categorie['LIBELLE'];
                }
            }
            $cache->set('CInfoTarifCCAM.getListeCategoriesSpecialites-c_categoriespecialite', $categories);
        }

        return $categories;
    }

    /**
     * Charge la liste des codes d'exonération à partir du cache ou de la base de données
     *
     * @return array
     */
    public static function getListeCodesExoneration(): array
    {
        $cache = Cache::getCache(Cache::INNER_OUTER);
        $exonerations = $cache->get('CInfoTarifCCAM.getListeCodesExoneration-c_exotm');
        if (!$exonerations) {
            self::getSpec();
            $list         = self::$spec->ds->loadList('SELECT * FROM `c_exotm`;');
            $exonerations = [];
            if ($list) {
                foreach ($list as $exo) {
                    $exonerations[$exo['CODE']] = $exo['LIBELLE'];
                }
            }

            $cache->set('CInfoTarifCCAM.getListeCodesExoneration-c_exotm', $exonerations);
        }

        return $exonerations;
    }

    /**
     * Charge la liste des types de forfaits à partir du cache ou de la base de données
     *
     * @return array
     */
    public static function getListeTypesForfait(): array
    {
        $cache = Cache::getCache(Cache::INNER_OUTER);
        $types = $cache->get('CInfoTarifCCAM.getListeTypesForfait-c_typeforfait');
        if (!$types) {
            self::getSpec();
            $list  = self::$spec->ds->loadList('SELECT * FROM `c_typeforfait`;');
            $types = [];

            if ($list) {
                foreach ($list as $type) {
                    $types[$type['CODE']] = $type['LIBELLE'];
                }
            }
            $cache->set('CInfoTarifCCAM.getListeTypesForfait-c_typeforfait', $type);
        }

        return $types;
    }
}
