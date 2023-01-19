<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;

/**
 * Class CCodeCCAM
 * Table p_acte
 *
 * Informations sur l'acte CCAM
 * Niveau acte
 */
class CCodeCCAM extends CCCAM
{
    /** @var string */
    const RESOURCE_TYPE = 'codeCcam';

    static $cache_layers = Cache::INNER_OUTER;

    // Infos sur le code
    public $code;
    public $libelle_court;
    public $libelle_long;
    public $type_acte;
    public $_type_acte;
    public $sexe_comp;
    public $place_arbo;
    public $date_creation;
    public $date_fin;
    public $frais_dep;

    /** @var string[] Nature d'assurance permises */
    public $assurance;

    /** @var string Classification du code dans l'arborescence */
    public $arborescence;

    /** @var string Forfait spécifique permis par le code (table forfaits) */
    public $_forfait;

    /** @var CInfoTarifCCAM[] Infos historisées sur le code */
    public $_ref_infotarif;

    /** @var CProcedureCCAM[] Procédures historisées */
    public $_ref_procedures;

    /** @var CNoteCCAM[] Notes */
    public $_ref_notes;

    /** @var CIncompatibiliteCCAM[] Incompatibilités médicales */
    public $_ref_incompatibilites;

    /** @var CActiviteCCAM[] Activités */
    public $_ref_activites;

    /** @var CExtensionPMSI[] */
    public $_ref_extensions;

    // Elements de référence pour la récupération d'informations
    public $_activite;
    public $_phase;

    /**
     * Constructeur à partir du code CCAM
     *
     * @param string $code Le code CCAM
     */
    public function __construct(string $code = null)
    {
        if (strlen($code) > 7) {
            // Le code $code n'est pas formaté correctement
            if (!preg_match('/^[A-Z]{4}\d{3}(\d(-\d)?)?$/i', $code)) {
                return;
            }

            // Cas ou l'activite et la phase sont indiquées dans le code (ex: BFGA004-1-0)
            $detailCode      = explode("-", $code);
            $this->code      = strtoupper($detailCode[0]);
            $this->_activite = $detailCode[1];
            if (count($detailCode) > 2) {
                $this->_phase = $detailCode[2];
            }
        } else {
            $this->code = strtoupper($code);
        }
    }

    /**
     * @param string $code The code
     *
     * @return CCodeCCAM
     */
    public static function get(?string $code): self
    {
        $cache = Cache::getCache(self::$cache_layers)->withCompressor();
        $code_ccam = $cache->get("CCodeCCAM.get-{$code}");
        if (!$code_ccam) {
            $code_ccam = new CCodeCCAM($code);
            $code_ccam->load();

            $cache->set("CCodeCCAM.get-{$code}", $code_ccam);
        }

        return $code_ccam;
    }

    /**
     * Chargement des informations liées à l'acte
     * Table p_acte
     *
     * @return bool Existence ou pas du code CCAM
     */
    public function load(): bool
    {
        $ds = self::getSpec()->ds;

        $query  = "SELECT p_acte.*
                  FROM p_acte
                  WHERE p_acte.CODE = ?";
        $query  = $ds->prepare($query, $this->code);
        $result = $ds->exec($query);
        if ($ds->numRows($result) == 0) {
            $this->code = "-";

            return false;
        }

        $row                 = $ds->fetchArray($result);
        $this->libelle_court = $row["LIBELLECOURT"];
        $this->libelle_long  = $row["LIBELLELONG"];
        $this->type_acte     = $row["TYPE"];
        $this->sexe_comp     = $row["SEXE"];
        $this->place_arbo    = $row["PLACEARBORESCENCE"];
        $this->date_creation = $row["DATECREATION"];
        $this->date_fin      = $row["DATEFIN"];
        $this->frais_dep     = $row["DEPLACEMENT"];

        $this->assurance           = [];
        $this->assurance[1]["db"]  = $row["ASSURANCE1"];
        $this->assurance[2]["db"]  = $row["ASSURANCE2"];
        $this->assurance[3]["db"]  = $row["ASSURANCE3"];
        $this->assurance[4]["db"]  = $row["ASSURANCE4"];
        $this->assurance[5]["db"]  = $row["ASSURANCE5"];
        $this->assurance[6]["db"]  = $row["ASSURANCE6"];
        $this->assurance[7]["db"]  = $row["ASSURANCE7"];
        $this->assurance[8]["db"]  = $row["ASSURANCE8"];
        $this->assurance[9]["db"]  = $row["ASSURANCE9"];
        $this->assurance[10]["db"] = $row["ASSURANCE10"];

        $this->arborescence           = [];
        $this->arborescence[1]["db"]  = $row["ARBORESCENCE1"];
        $this->arborescence[2]["db"]  = $row["ARBORESCENCE2"];
        $this->arborescence[3]["db"]  = $row["ARBORESCENCE3"];
        $this->arborescence[4]["db"]  = $row["ARBORESCENCE4"];
        $this->arborescence[5]["db"]  = $row["ARBORESCENCE5"];
        $this->arborescence[6]["db"]  = $row["ARBORESCENCE6"];
        $this->arborescence[7]["db"]  = $row["ARBORESCENCE7"];
        $this->arborescence[8]["db"]  = $row["ARBORESCENCE8"];
        $this->arborescence[9]["db"]  = $row["ARBORESCENCE9"];
        $this->arborescence[10]["db"] = $row["ARBORESCENCE10"];

        $this->loadTypeLibelle();
        $this->getForfaitSpec();
        $this->loadRefProcedures();
        $this->loadRefNotes();
        $this->loadRefIncompatibilites();

        $this->loadArborescence();
        $this->loadAssurance();
        $this->loadRefInfoTarif();
        $this->loadExtensionsPMSI();

        foreach ($this->_ref_infotarif as $_info_tarif) {
            $_info_tarif->loadLibelleExo();
            $_info_tarif->loadLibellePresc();
            $_info_tarif->loadLibelleForfait();
        }
        $this->loadRefActivites();
        foreach ($this->_ref_activites as $_activite) {
            $_activite->loadLibelle();
            // Ne pas charger les associations possibles des codes complémentaires (des milliers)
            $_activite->_ref_associations = [];
            if ($this->type_acte != 2) {
                $_activite->loadRefAssociations();
            }

            $_activite->loadRefModificateurs();
            foreach ($_activite->_ref_modificateurs as $_date_modif) {
                foreach ($_date_modif as $_modif) {
                    $_modif->loadLibelle();
                }
            }
            $_activite->loadRefClassif();
            foreach ($_activite->_ref_classif as $_classif) {
                $_classif->loadCatMed();
                $_classif->loadRegroupement();
            }
            $_activite->loadRefPhases();
            foreach ($_activite->_ref_phases as $_phase) {
                $_phase->loadRefInfo();
                $_phase->loadRefDentsIncomp();
                foreach ($_phase->_ref_dents_incomp as $_dent) {
                    $_dent->loadRefDent();
                    $_dent->_ref_dent->loadLibelle();
                }
            }
        }

        return true;
    }

    /**
     * Chargement des informations historisées de l'acte
     * Table p_acte_infotarif
     *
     * @return CInfoTarifCCAM[] La liste des informations historisées
     */
    public function loadRefInfoTarif(): array
    {
        return $this->_ref_infotarif = CInfoTarifCCAM::loadListFromCode($this->code);
    }

    /**
     * Chargement des procédures de l'acte
     * Table p_acte_procedure
     *
     * @return CProcedureCCAM[] La liste des procédures
     */
    public function loadRefProcedures(): array
    {
        return $this->_ref_procedures = CProcedureCCAM::loadListFromCode($this->code);
    }

    /**
     * Chargement des notes de l'acte
     * Table p_acte_notes
     *
     * @return CNoteCCAM[] La liste des notes
     */
    public function loadRefNotes(): array
    {
        return $this->_ref_notes = CNoteCCAM::loadListFromCode($this->code);
    }

    /**
     * Chargement des incompatibilités de l'acte
     * Table p_acte_incompatibilite
     *
     * @return CIncompatibiliteCCAM[] La liste des incompatibilités
     */
    public function loadRefIncompatibilites(): array
    {
        return $this->_ref_incompatibilites = CIncompatibiliteCCAM::loadListFromCode($this->code);
    }

    /**
     * Chargement des activités de l'acte
     * Table p_activite
     *
     * @return CActiviteCCAM[] La liste des activités
     */
    public function loadRefActivites(): array
    {
        $exclude = [];
        if ($this->arborescence[1]["db"] === "000018" && $this->arborescence[2]["db"] === "000001") {
            $exclude[] = "'1'";
        }

        return $this->_ref_activites = CActiviteCCAM::loadListFromCode($this->code, $exclude);
    }

    /**
     * Chargement du libellé du type
     * Table c_typeacte
     *
     * @return string Libellé du type
     */
    public function loadTypeLibelle(): ?string
    {
        $types = self::getListeTypesActe();

        if (array_key_exists($this->type_acte, $types)) {
            $this->_type_acte = $types[$this->type_acte];
        }

        return $this->_type_acte;
    }

    /**
     * Récupération du type de forfait de l'acte
     * (forfait spéciaux des listes SEH)
     * Table forfaits
     *
     * @return void
     */
    public function getForfaitSpec(): void
    {
        $forfaits = self::getListeForfaits();

        if (array_key_exists($this->code, $forfaits)) {
            $this->_forfait = $forfaits[$this->code];
        }
    }

    /**
     * Chargement des libellés des assurances
     * Table c_natureassurance
     *
     * @return array Liste des assurances
     */
    public function loadAssurance(): array
    {
        $nat_assurances = self::getListeAssurances();

        $ds = self::getSpec()->ds;
        foreach ($this->assurance as &$assurance) {
            if (!$assurance["db"]) {
                continue;
            }

            if (array_key_exists($assurance['db'], $nat_assurances)) {
                $assurance['libelle'] = $nat_assurances[$assurance['db']];
            }
        }

        return $this->assurance;
    }

    /**
     * Chargement des informations de l'arborescence du code
     * Table c_arborescence
     *
     * @return array Arborescence complète
     */
    public function loadArborescence(): array
    {
        $ds    = self::getSpec()->ds;
        $pere  = '000001';
        $track = '';
        foreach ($this->arborescence as &$chapitre) {
            $rang = $chapitre['db'];
            if ($rang == '00000') {
                break;
            }

            $chapters = CCCAM::getListChapters($pere);
            if (array_key_exists($rang, $chapters)) {
                $row = $chapters[$rang];

                if (!substr($row['RANG'], -2)) {
                    break;
                }

                $track .= substr($row['RANG'], -2) . '.';

                $chapitre['rang'] = $track;
                $chapitre['code'] = $row['CODEMENU'];
                $chapitre['nom']  = $row['LIBELLE'];
                $chapitre['rq']   = [];

                $chapter_notes = CCCAM::getNotesChapters();
                if (array_key_exists($chapitre['code'], $chapter_notes)) {
                    foreach ($chapter_notes[$chapitre['code']] as $note) {
                        $chapitre['rq'][] = str_replace('¶', "\n", $note['TEXTE']);
                    }
                }
            }

            $pere = $chapitre['code'];
        }

        return $this->arborescence;
    }

    /**
     * Load the PMSI extensions
     *
     * @return CExtensionPMSI[]
     */
    public function loadExtensionsPMSI(): array
    {
        return $this->_ref_extensions = CExtensionPMSI::loadList($this->code);
    }

    /**
     * Récupération des informations minimales d'un code
     * Non caché
     *
     * @param string $code Code CCAM
     *
     * @return array
     */
    public static function getCodeInfos(string $code): array
    {
        $cache = Cache::getCache(self::$cache_layers)->withCompressor();
        $code_ccam = $cache->get("CCodeCCAM.getCodeInfos-{$code}");
        if (!$code_ccam) {
            // Chargement
            $ds = self::getSpec()->ds;

            $query     = "SELECT p_acte.CODE, p_acte.LIBELLELONG, p_acte.TYPE
                            FROM p_acte
                            WHERE p_acte.CODE = %";
            $query     = $ds->prepare($query, $code);
            $result    = $ds->exec($query);
            $code_ccam = $ds->fetchArray($result);

            $cache->set("CCodeCCAM.getCodeInfos-{$code}", $code_ccam);
        }

        return $code_ccam;
    }

    /**
     * Récupération des modificateurs actifs pour une date donnée
     *
     * @param string $date Date de référence
     *
     * @return string Liste des modificateurs actifs
     */
    public static function getModificateursActifs(string $date = null): string
    {
        if (!$date) {
            $date = CMbDT::date();
        }
        $date = CMbDT::format($date, "%Y%m%d");

        $modifs = '';
        foreach (self::getListeForfaitsModificateurs() as $modificateur => $forfaits) {
            foreach ($forfaits as $forfait) {
                if (
                    $forfait['DATEDEBUT'] <= $date
                    && ($forfait['DATEFIN'] == '00000000' || $forfait['DATEFIN'] >= $date)
                ) {
                    $modifs .= $modificateur;
                    break;
                }
            }
        }

        return $modifs;
    }

    /**
     * Récupération du forfait d'un modificateur
     *
     * @param string $modificateur Lettre clé du modificateur
     * @param string $grille       La grille de tarif a utiliser
     * @param string $date         Date de référence
     *
     * @return array forfait et coefficient
     */
    public static function getForfait(string $modificateur, string $grille = '14', string $date = null): array
    {
        if (!$date) {
            $date = CMbDT::date();
        }

        /* Surcharge de la date dans le mode test de Pyxvital */
        if (CModule::getActive('oxPyxvital') && CAppUI::gconf('pyxVital General mode') == 'test') {
            $date = CMbDT::date();

            if (CAppUI::gconf('pyxVital General date_ccam')) {
                $date = CAppUI::gconf('pyxVital General date_ccam');
            }
        }

        $date   = CMbDT::format($date, "%Y%m%d");
        $valeur = ['forfait' => 0, 'coefficient' => 0];

        $forfaits_mods = self::getListeForfaitsModificateurs();
        if (array_key_exists($modificateur, $forfaits_mods)) {
            foreach ($forfaits_mods[$modificateur] as $forfait_mod) {
                if (
                    $forfait_mod['GRILLE'] == "0{$grille}" && $forfait_mod['DATEDEBUT'] <= $date
                    && ($forfait_mod['DATEFIN'] == '00000000' || $forfait_mod['DATEFIN'] >= $date)
                ) {
                    $valeur['forfait']     = $forfait_mod['FORFAIT'] / 100;
                    $valeur['coefficient'] = $forfait_mod['COEFFICIENT'] / 10;
                    break;
                }
            }
        }

        return $valeur;
    }

    /**
     * Récupération du coefficient d'association
     *
     * @param string $code Code d'association
     *
     * @return float
     */
    public static function getCoeffAsso(string $code = null): float
    {
        $valeur = 100.0;

        if ($code == 'X') {
            $valeur = 0.0;
        } elseif ($code) {
            $associations = self::getListeCodesAssociation();
            foreach ($associations as $association) {
                if ($code == $association['CODE'] && $association['DATEFIN'] == '00000000') {
                    $valeur = $association['COEFFICIENT'] / 10;
                }
            }
        }

        return $valeur;
    }

    /**
     * Recherche de codes CCAM
     *
     * @param string $code       Codes partiels à chercher
     * @param string $keys       Mot clés à chercher
     * @param int    $max_length Longueur maximum du code
     * @param string $where      Autres paramètres where
     *
     * @return array Tableau d'actes
     */
    public static function findCodes(
        string $code = null,
        string $keys = null,
        int $max_length = null,
        string $where = null,
        string $access = null,
        string $topo1 = null,
        string $topo2 = null,
        string $chap1_rank = null,
        string $chap2_rank = null,
        string $chap3_rank = null,
        string $chap4_rank = null,
        string $limit = null
    ): array {
        $ds = self::getSpec()->ds;

        $query = "SELECT CODE, LIBELLELONG
                FROM p_acte
                WHERE 1 ";

        $query .= self::prepareSearchQuery(
            $code,
            $keys,
            $max_length,
            $where,
            $access,
            $topo1,
            $topo2,
            $chap1_rank,
            $chap2_rank,
            $chap3_rank,
            $chap4_rank
        );

        $query .= " ORDER BY CODE ";
        $query .= $limit ? $limit : 'LIMIT 0, 100';

        $result = $ds->exec($query);
        $master = [];
        $i      = 0;
        while ($row = $ds->fetchArray($result)) {
            $master[$i]["LIBELLELONG"] = $row["LIBELLELONG"];
            $master[$i]["CODE"]        = $row["CODE"];
            $i++;
        }

        return $master;
    }

    /**
     * Recherche de codes CCAM
     *
     * @param string $code       Codes partiels à chercher
     * @param string $keys       Mot clés à chercher
     * @param int    $max_length Longueur maximum du code
     * @param string $where      Autres paramètres where
     *
     * @return array Tableau d'actes
     */
    public static function countCodes(
        string $code = null,
        string $keys = null,
        int $max_length = null,
        string $where = null,
        string $access = null,
        string $topo1 = null,
        string $topo2 = null,
        string $chap1_rank = null,
        string $chap2_rank = null,
        string $chap3_rank = null,
        string $chap4_rank = null
    ): int {
        $ds = self::getSpec()->ds;

        $query = "SELECT CODE, LIBELLELONG
                FROM p_acte
                WHERE 1 ";

        $query .= self::prepareSearchQuery(
            $code,
            $keys,
            $max_length,
            $where,
            $access,
            $topo1,
            $topo2,
            $chap1_rank,
            $chap2_rank,
            $chap3_rank,
            $chap4_rank
        );

        return $ds->countRows($query);
    }

    protected static function prepareSearchQuery(
        string $code = null,
        string $keys = null,
        int $max_length = null,
        string $where = null,
        string $access = null,
        string $topo1 = null,
        string $topo2 = null,
        string $chap1_rank = null,
        string $chap2_rank = null,
        string $chap3_rank = null,
        string $chap4_rank = null
    ): string {
        $query = '';
        $ds = self::getSpec()->ds;
        $keywords = $keys ? explode(" ", $keys) : [];
        $codes    = $code ? explode(" ", $code) : [];
        CMbArray::removeValue("", $keywords);
        CMbArray::removeValue("", $codes);

        $codeLike = [];
        foreach ($codes as $value) {
            $codeLike[] = $ds->prepare("CODE LIKE %", "{$value}%");
        }
        $listLike = [];
        foreach ($keywords as $value) {
            $listLike[] = $ds->prepare("LIBELLELONG LIKE %", "%{$value}%");
        }

        if ($keys && $keys != "") {
            $query .= count($codeLike) ? " AND ((" . implode(" OR ", $codeLike) . ") OR (" : " AND (";
            $query .= implode(" AND ", $listLike);
            $query .= $code != "" ? ')) ' : ')';
        } elseif ($code) {
            $query .= "AND " . implode(" OR ", $codeLike);
            if (count($codeLike) > 1) {
                $query .= ')';
            }
        }

        if ($max_length) {
            $query .= $ds->prepare(" AND LENGTH(CODE) < %", $max_length);
        }

        if ($access) {
            $query .= $ds->prepare(" AND CODE LIKE %", "___{$access}___");
        }

        if ($topo1) {
            $query .= $ds->prepare(" AND CODE LIKE %", "{$topo1}______");
        }

        if ($topo2) {
            $query .= $ds->prepare(" AND CODE LIKE %", "{$topo2}_____");
        }

        if ($chap4_rank) {
            $query .= $ds->prepare(" AND ARBORESCENCE4 = %", "0000{$chap4_rank}");
        }
        // On filtre selon le chapitre 3
        if ($chap3_rank) {
            $query .= $ds->prepare(" AND ARBORESCENCE3 = %", "0000{$chap3_rank}");
        }
        // On filtre selon le chapitre 2
        if ($chap2_rank) {
            $query .= $ds->prepare(" AND ARBORESCENCE2 = %", "0000{$chap2_rank}");
        }
        // On filtre selon le chapitre 1
        if ($chap1_rank) {
            $query .= $ds->prepare(" AND ARBORESCENCE1 = %", "0000{$chap1_rank}");
        }

        if ($where) {
            $query .= " AND " . $where;
        }

        return $query;
    }

    /**
     * Charge la liste des natures d'assurance à partir du cache ou de la base de données
     *
     * @return array
     */
    public static function getListeAssurances(): array
    {
        $cache = Cache::getCache(Cache::INNER_OUTER)->withCompressor();
        $assurances = $cache->get('CCodeCCAM.getListeAssurances-c_natureassurance');
        if (!$assurances) {
            self::getSpec();
            $list       = self::$spec->ds->loadList('SELECT * FROM `c_natureassurance`;');
            $assurances = [];
            if ($list) {
                foreach ($list as $assurance) {
                    $assurances[$assurance['CODE']] = $assurance['LIBELLE'];
                }
            }

            $cache->set('CCodeCCAM.getListeAssurances-c_natureassurance', $assurances);
        }

        return $assurances;
    }

    /**
     * Charge la liste des types d'actes à partir du cache ou de la base de données
     *
     * @return array
     */
    public static function getListeTypesActe(): array
    {
        $cache = Cache::getCache(Cache::INNER_OUTER)->withCompressor();
        $types = $cache->get('CCodeCCAM.getListeTypesActe-c_typeacte');
        if (!$types) {
            self::getSpec();
            $list  = self::$spec->ds->loadList('SELECT * FROM `c_typeacte`;');
            $types = [];
            if ($list) {
                foreach ($list as $type) {
                    $types[$type['CODE']] = $type['LIBELLE'];
                }
            }

            $cache->set('CCodeCCAM.getListeTypesActe-c_typeacte', $types);
        }

        return $types;
    }

    /**
     * Charge la liste des codes d'association et leurs coefficients à partir du cache ou de la base de données
     *
     * @return array
     */
    public static function getListeCodesAssociation(): array
    {
        $cache = Cache::getCache(Cache::INNER_OUTER)->withCompressor();
        $list = $cache->get('CCodeCCAM.getListeCodesAssociation-t_association');
        if (!$list) {
            self::getSpec();
            $list = self::$spec->ds->loadList('SELECT * FROM `t_association`;');
            $cache->set('CCodeCCAM.getListeCodesAssociation-t_association', $list);
        }

        return $list;
    }

    /**
     * Charge la liste des forfaits de modificateurs à partir du cache ou de la base de données
     *
     * @return array
     */
    public static function getListeForfaitsModificateurs(): array
    {
        $cache = Cache::getCache(Cache::INNER_OUTER)->withCompressor();
        $modificateurs = $cache->get('CCodeCCAM.getListeForfaitsModificateurs-t_modificateurforfait');
        if (!$modificateurs) {
            self::getSpec();
            $list          = self::$spec->ds->loadList('SELECT * FROM `t_modificateurforfait`;');
            $modificateurs = [];
            foreach ($list as $modificateur) {
                if (!array_key_exists($modificateur['CODE'], $modificateurs)) {
                    $modificateurs[$modificateur['CODE']] = [];
                }

                $modificateurs[$modificateur['CODE']][] = $modificateur;
            }

            $cache->set('CCodeCCAM.getListeForfaitsModificateurs-t_modificateurforfait', $modificateurs);
        }

        return $modificateurs;
    }

    /**
     * Retourne les forfaits (SEH, FSD, FFM) par code
     *
     * @return array
     */
    public static function getListeForfaits(): array
    {
        $cache = Cache::getCache(Cache::INNER_OUTER)->withCompressor();
        $forfaits = $cache->get('CCodeCCAM.getListeForfaits-forfaits');
        if (!$forfaits) {
            self::getSpec();
            $list     = self::$spec->ds->loadList('SELECT * FROM `forfaits`;');
            $forfaits = [];
            foreach ($list as $row) {
                $forfaits[$row['code']] = $row['forfait'];
            }

            $cache->set('CCodeCCAM.getListeForfaits-forfaits', $forfaits);
        }

        return $forfaits;
    }
}
