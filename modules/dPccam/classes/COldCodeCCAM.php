<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Cache;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObjectSpec;

/**
 * Classe pour gérer le mapping avec la base de données CCAM
 */
class COldCodeCCAM implements IShortNameAutoloadable
{
    public $code;          // Code de l'acte
    public $chapitres;     // Chapitres de la CCAM concernes
    public $libelleCourt;  // Libelles
    public $libelleLong;
    public $place;         // Place dans la CCAM
    public $remarques;     // Remarques sur le code
    public $type;          // Type d'acte (isolé, procédure ou complément)
    public $activites = []; // Activites correspondantes
    public $phases    = []; // Nombre de phases par activités
    public $incomps   = []; // Incompatibilite
    public $assos     = []; // Associabilite
    public $procedure;     // Procedure
    public $remboursement; // Remboursement
    public $forfait;       // Forfait spécifique (SEH1, SEH2, SEH3, SEH4, SEH5)
    public $couleur;       // Couleur du code par rapport à son chapitre

    // Variable calculées
    public $_code7;        // Possibilité d'ajouter le modificateur 7 (0 : non, 1 : oui)
    public $_default;
    public $_sorted_tarif; // Phases classées par ordre de tarif brut
    public $occ;

    // Distant field
    public $class;
    public $favoris_id;
    public $_ref_favori;

    // Activités et phases recuperées depuis le code CCAM
    public $_activite;
    public $_phase;

    /** @var CMbObjectSpec */
    public $_spec;

    public $_couleursChap = [
        0  => "ffffff",
        1  => "669966",
        2  => "6666cc",
        3  => "6699ee",
        4  => "cc6633",
        5  => "ee6699",
        6  => "ff66ee",
        7  => "33cc33",
        8  => "66cc99",
        9  => "99ccee",
        10 => "cccc33",
        11 => "eecc99",
        12 => "ffccee",
        13 => "33ff33",
        14 => "66ff99",
        15 => "99ffee",
        16 => "ccff33",
        17 => "eeff99",
        18 => "ffffee",
        19 => "cccccc",
    ];

    // niveaux de chargement
    const LITE   = 1;
    const MEDIUM = 2;
    const FULL   = 3;

    /** @var CMbObjectSpec */
    static $spec = null;

    /**
     * Get object spec
     *
     * @return CMbObjectSpec
     */
    public static function getSpec(): CMbObjectSpec
    {
        if (self::$spec) {
            return self::$spec;
        }

        $spec      = new CMbObjectSpec();
        $spec->dsn = "ccamV2";
        $spec->init();

        return self::$spec = $spec;
    }

    /**
     * Constructeur à partir du code CCAM
     *
     * @param string $code Le code CCAM
     *
     * @return self
     */
    public function __construct(string $code = null)
    {
        $this->_spec = self::getSpec();

        if (strlen($code) > 7) {
            if (!preg_match("/^[A-Z]{4}[0-9]{3}(-[0-9](-[0-9])?)?$/i", $code)) {
                return "Le code $code n'est pas formaté correctement";
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

        return null;
    }

    /**
     * Methode de pré-serialisation
     *
     * @return array
     */
    public function __sleep(): array
    {
        $fields = get_object_vars($this);
        unset($fields["_spec"]);

        return array_keys($fields);
    }

    /**
     * Méthode de "reveil" après serialisation
     *
     * @return void
     */
    public function __wakeup(): void
    {
        $this->_spec = self::getSpec();
    }

    /**
     * Chargement optimisé des codes CCAM
     *
     * @param string $code Code CCAM
     * @param int    $niv  Niveau du chargement
     *
     * @return COldCodeCCAM
     */
    public static function get(string $code, int $niv = self::MEDIUM): self
    {
        $cache_key = "COldCodeCCAM.get-{$code}-{$niv}";
        $cache = Cache::getCache(Cache::INNER_OUTER)->withCompressor();
        $code_ccam = $cache->get($cache_key);
        if (!$code_ccam) {
            $code_ccam = new COldCodeCCAM($code);
            $code_ccam->load($niv);

            $cache->set($cache_key, $code_ccam);
        }

        return $code_ccam;
    }

    /**
     * Chargement complet d'un code
     * en fonction du niveau de profondeur demandé
     *
     * @param int $niv Niveau de profondeur demandé
     *
     * @return void
     */
    public function load(int $niv = self::MEDIUM): void
    {
        if (!$this->getLibelles()) {
            return;
        }

        if ($niv == self::LITE) {
            $this->getActivite7();
        }

        if ($niv >= self::LITE) {
            $this->getTarification();
            $this->getForfaitSpec();
        }

        if ($niv >= self::MEDIUM) {
            $this->getChaps();
            $this->getRemarques();
            $this->getActivites();
        }

        if ($niv == self::FULL) {
            $this->getActesAsso();
            $this->getActesIncomp();
            $this->getProcedure();
        }
    }

    /**
     * Récuparation des libellés du code
     *
     * @return bool
     */
    public function getLibelles(): bool
    {
        $ds     = $this->_spec->ds;
        $query  = $ds->prepare("SELECT * FROM p_acte WHERE CODE = % AND DATEFIN = '00000000'", $this->code);
        $result = $ds->exec($query);
        if ($ds->numRows($result) == 0) {
            $this->code = "-";
            //On rentre les champs de la table actes
            $this->libelleCourt = "Acte inconnu ou supprimé";
            $this->libelleLong  = "Acte inconnu ou supprimé";
            $this->_code7       = 1;

            return false;
        }

        $row = $ds->fetchArray($result);
        //On rentre les champs de la table actes
        $this->libelleCourt = $row["LIBELLECOURT"];
        $this->libelleLong  = $row["LIBELLELONG"];
        $this->type         = $row["TYPE"];

        return true;
    }

    /**
     * Vérification de l'existence du moficiateur 7 pour l'acte
     *
     * @return void
     */
    public function getActivite7(): void
    {
        $ds = $this->_spec->ds;
        // recherche de la dernière date d'effet
        $query1  = "SELECT MAX(DATEEFFET) as LASTDATE FROM p_activite_modificateur WHERE ";
        $query1  .= $ds->prepare("CODEACTE = %", $this->code);
        $query1  .= " GROUP BY CODEACTE";
        $result1 = $ds->exec($query1);
        // Chargement des modificateurs
        if ($ds->numRows($result1)) {
            $row          = $ds->fetchArray($result1);
            $lastDate     = $row["LASTDATE"];
            $query2       = "SELECT * FROM p_activite_modificateur WHERE ";
            $query2       .= $ds->prepare("CODEACTE = %", $this->code);
            $query2       .= " AND CODEACTIVITE = '4'";
            $query2       .= " AND MODIFICATEUR = '7'";
            $query2       .= " AND DATEEFFET = '$lastDate'";
            $result2      = $ds->exec($query2);
            $this->_code7 = $ds->numRows($result2);
        } else {
            $this->_code7 = 1;
        }
    }

    /**
     * Récupération de la possibilité de remboursement de l'acte
     *
     * @return void
     */
    public function getTarification(): void
    {
        $ds                  = $this->_spec->ds;
        $query               = $ds->prepare(
            "SELECT * FROM p_acte_infotarif WHERE CODEACTE = % ORDER BY DATEEFFET DESC",
            $this->code
        );
        $result              = $ds->exec($query);
        $row                 = $ds->fetchArray($result);
        $this->remboursement = $row["REMBOURSEMENT"];
    }

    /**
     * Récupération du type de forfait de l'acte
     * (forfait spéciaux des listes SEH)
     *
     * @return void
     */
    public function getForfaitSpec(): void
    {
        $ds            = $this->_spec->ds;
        $query         = $ds->prepare("SELECT * FROM forfaits WHERE CODE = %", $this->code);
        $result        = $ds->exec($query);
        $row           = $ds->fetchArray($result);
        $this->forfait = $row["forfait"];
    }

    /**
     * Chargement des chapitres de l'acte
     *
     * @return void
     */
    public function getChaps(): void
    {
        $ds     = $this->_spec->ds;
        $query  = $ds->prepare("SELECT * FROM p_acte WHERE CODE = % AND DATEFIN = '00000000'", $this->code);
        $result = $ds->exec($query);
        $row    = $ds->fetchArray($result);

        // On rentre les champs de la table actes
        $this->couleur            = $this->_couleursChap[intval($row["ARBORESCENCE1"])];
        $this->chapitres[0]["db"] = $row["ARBORESCENCE1"];
        $this->chapitres[1]["db"] = $row["ARBORESCENCE2"];
        $this->chapitres[2]["db"] = $row["ARBORESCENCE3"];
        $this->chapitres[3]["db"] = $row["ARBORESCENCE4"];
        $pere                     = "000001";
        $track                    = "";

        // On rentre les infos sur les chapitres
        foreach ($this->chapitres as $key => $value) {
            $rang   = $this->chapitres[$key]["db"];
            $query  = $ds->prepare("SELECT * FROM c_arborescence WHERE CODEPERE = %1 AND RANG = %2", $pere, $rang);
            $result = $ds->exec($query);
            $row    = $ds->fetchArray($result);

            $query   = $ds->prepare("SELECT * FROM c_notesarborescence WHERE CODEMENU = %", $row["CODEMENU"]);
            $result2 = $ds->exec($query);

            $track                         .= substr($row["RANG"], -2) . ".";
            $this->chapitres[$key]["rang"] = $track;
            $this->chapitres[$key]["code"] = $row["CODEMENU"];
            $this->chapitres[$key]["nom"]  = $row["LIBELLE"];
            $this->chapitres[$key]["rq"]   = "";
            while ($row2 = $ds->fetchArray($result2)) {
                $this->chapitres[$key]["rq"] .= "* " . str_replace("¶", "\n", $row2["TEXTE"]) . "\n";
            }
            $pere = $this->chapitres[$key]["code"];
        }
        $this->place = $this->chapitres[3]["rang"];
    }

    /**
     * Chargement des remarques sur l'acte
     *
     * @return void
     */
    public function getRemarques(): void
    {
        $ds              = $this->_spec->ds;
        $this->remarques = [];
        $query           = $ds->prepare("SELECT * FROM p_acte_notes WHERE CODEACTE = %", $this->code);
        $result          = $ds->exec($query);
        while ($row = $ds->fetchArray($result)) {
            $this->remarques[] = str_replace("¶", "\n", $row["TEXTE"]);
        }
    }

    /**
     * Chargement des activités de l'acte
     *
     * @return array La liste des activités
     */
    public function getActivites(): array
    {
        $this->getChaps();
        $ds = $this->_spec->ds;
        // Extraction des activités
        $query  = "SELECT ACTIVITE AS numero
              FROM p_activite
              WHERE CODEACTE = %";
        $query  = $ds->prepare($query, $this->code);
        $result = $ds->exec($query);
        while ($obj = $ds->fetchObject($result)) {
            $obj->libelle = "";
            // On ne met pas l'activité 1 pour les actes du chapitre 18.01
            if ($this->chapitres[0]["db"] != "000018" || $this->chapitres[1]["db"] != "000001" || $obj->numero != "1") {
                $this->activites[$obj->numero] = $obj;
            }
        }
        // Libellés des activités
        foreach ($this->remarques as $remarque) {
            $match = null;
            if (preg_match("/Activité (\d) : (.*)/i", $remarque, $match)) {
                $this->activites[$match[1]]->libelle = $match[2];
            }
        }
        // Détail des activités
        foreach ($this->activites as &$activite) {
            // Type de l'activité
            $query          = "SELECT LIBELLE AS `type`
                FROM c_activite
                WHERE CODE = %";
            $query          = $ds->prepare($query, $activite->numero);
            $result         = $ds->exec($query);
            $obj            = $ds->fetchObject($result);
            $activite->type = $obj->type;

            $this->getModificateursFromActivite($activite);
            $this->getPhasesFromActivite($activite);
        }
        // Test de la présence d'activité virtuelle
        /**
         * if (isset($this->activites[1]) && isset($this->activites[4])) {
         * if (isset($this->activites[1]->phases[0]) && isset($this->activites[4]->phases[0])) {
         * if ($this->activites[1]->phases[0]->tarif && !$this->activites[4]->phases[0]->tarif) {
         * unset($this->activites[4]);
         * }
         * if (!$this->activites[1]->phases[0]->tarif && $this->activites[4]->phases[0]->tarif) {
         * unset($this->activites[1]);
         * }
         * }
         * }
         **/
        $this->_default = reset($this->activites);
        if (isset($this->_default->phases[0])) {
            $this->_default = $this->_default->phases[0]->tarif;
        } else {
            $this->_default = 0;
        }

        return $this->activites;
    }

    /**
     * Récupération des modificateurs d'une activité
     *
     * @param $activite Activité concernée
     *
     * @return void
     */
    public function getModificateursFromActivite(&$activite): void
    {
        $ds = $this->_spec->ds;
        // recherche de la dernière date d'effet
        $query    = "SELECT MAX(DATEEFFET) AS LASTDATE
              FROM p_activite_modificateur
              LEFT JOIN t_modificateurforfait
                ON p_activite_modificateur.MODIFICATEUR = t_modificateurforfait.CODE
                AND t_modificateurforfait.DATEFIN = 00000000
              WHERE p_activite_modificateur.CODEACTE = %1
                AND t_modificateurforfait.CODE IS NOT NULL
              GROUP BY p_activite_modificateur.CODEACTE";
        $query    = $ds->prepare($query, $this->code, $activite->numero);
        $result   = $ds->exec($query);
        $row      = $ds->fetchArray($result);
        $lastDate = $row["LASTDATE"];
        // Extraction des modificateurs
        $activite->modificateurs = [];
        $modificateurs           =& $activite->modificateurs;
        $query                   = "SELECT p_activite_modificateur.MODIFICATEUR
              FROM p_activite_modificateur
              WHERE p_activite_modificateur.CODEACTE = %1
                AND p_activite_modificateur.CODEACTIVITE = %2
                AND p_activite_modificateur.DATEEFFET = '$lastDate'
              GROUP BY p_activite_modificateur.MODIFICATEUR";
        $query                   = $ds->prepare($query, $this->code, $activite->numero);
        $result                  = $ds->exec($query);

        while ($row = $ds->fetchArray($result)) {
            $query           = "SELECT l_modificateur.CODE AS code, l_modificateur.LIBELLE AS libelle
                FROM l_modificateur
                WHERE CODE = %
                ORDER BY CODE";
            $query           = $ds->prepare($query, $row["MODIFICATEUR"]);
            $_modif          = $ds->fetchObject($ds->exec($query));
            $_modif->_double = "1";
            $modificateurs[] = $_modif;
        }
    }

    /**
     * Récupération des phases d'une activité
     *
     * @param $activite Activité concernée
     *
     * @return void
     */
    public function getPhasesFromActivite(&$activite): void
    {
        $ds = $this->_spec->ds;
        // Extraction des phases
        $activite->phases = [];
        $phases           =& $activite->phases;
        $date             = CMbDT::transform(null, null, '%Y%m%d');
        $query            = "SELECT p_phase_acte.PHASE AS phase,
                p_phase_acte.PRIXUNITAIRE AS tarif,
                p_phase_acte.CHARGESCAB charges
              FROM p_phase_acte
              WHERE p_phase_acte.CODEACTE = %1
                AND p_phase_acte.ACTIVITE = %2
                AND phaseacte.DATE1 <= %3
              GROUP BY p_phase_acte.PHASE
              ORDER BY p_phase_acte.PHASE, p_phase_acte.DATEEFFET DESC";
        $query            = $ds->prepare($query, $this->code, $activite->numero, $date);
        $result           = $ds->exec($query);

        $this->_sorted_tarif = 2;
        while ($obj = $ds->fetchObject($result)) {
            $phases[$obj->phase] = $obj;
            $phase               =& $phases[$obj->phase];

            $phase->tarif        = floatval($obj->tarif) / 100;
            $phase->libelle      = "Phase Principale";
            $phase->charges      = floatval($obj->charges) / 100;
            $phase->nb_dents     = intval("00");
            $phase->dents_incomp = [];

            // Ordre des tarifs décroissants pour l'activité 1
            if ($activite->numero == "1") {
                if ($phase->tarif != 0) {
                    $this->_sorted_tarif = 1 / $phase->tarif;
                } else {
                    $this->_sorted_tarif = 1;
                }
            }

            // Copie des modificateurs pour chaque phase. Utile pour la salle d'op
            $phase->_modificateurs = $phase->tarif ? $activite->modificateurs : [];
        }

        // Libellés des phases
        foreach ($this->remarques as $remarque) {
            if (preg_match("/Phase (\d) : (.*)/i", $remarque, $match)) {
                if (isset($phases[$match[1]])) {
                    $phases[$match[1]]->libelle = $match[2];
                }
            }
        }
    }

    /**
     * Récupération des codes associés d'une activité
     *
     * @param         &$activite Activité concernée
     * @param string  $code     Chaine de caractère à trouver dans les résultats
     * @param int     $limit    Nombre max de codes retournés
     *
     * @return void
     */
    public function getAssoFromActivite(&$activite, string $code = null, int $limit = null): void
    {
        $ds = $this->_spec->ds;
        // Extraction des phases
        $assos = [];
        if ($this->type == 2) {
            $activite->assos = $assos;

            return;
        }
        $queryEffet  = "SELECT MAX(p_activite_associabilite.DATEEFFET) as LASTDATE
      FROM p_activite_associabilite
      WHERE p_activite_associabilite.CODEACTE = %
      GROUP BY p_activite_associabilite.CODEACTE";
        $queryEffet  = $ds->prepare(
            $queryEffet,
            $this->code
        );
        $resultEffet = $ds->exec($queryEffet);
        $rowEffet    = $ds->fetchArray($resultEffet);
        $lastDate    = $rowEffet["LASTDATE"];
        $query       = "SELECT * FROM p_activite_associabilite
       LEFT JOIN p_acte
         ON p_activite_associabilite.ACTEASSO = p_acte.CODE
       WHERE p_activite_associabilite.CODEACTE = %1
         AND p_activite_associabilite.ACTIVITE = %2";
        if ($code) {
            $code_explode = explode(" ", $code);
            $codeLike     = [];
            foreach ($code_explode as $value) {
                $codeLike[] = "LIBELLELONG LIKE '%" . addslashes($value) . "%'";
            }
            $query .= "\nAND (p_acte.CODE LIKE '$code%' OR (" . implode(" OR ", $codeLike) . "))";
        }
        $query .= "\nAND p_activite_associabilite.DATEEFFET = '$lastDate'
       GROUP BY p_activite_associabilite.ACTEASSO";
        if ($limit) {
            $query .= " LIMIT $limit";
        }
        $query  = $ds->prepare($query, $this->code, $activite->numero);
        $result = $ds->exec($query);
        /** @todo utiliser le chargement de codes standard */
        $i = 0;
        while ($row = $ds->fetchArray($result)) {
            $assos[$i]["code"]  = $row["ACTEASSO"];
            $query2             = "SELECT *
        FROM p_acte
        WHERE p_acte.CODE = %
        AND p_acte.DATEFIN = '00000000'";
            $query2             = $ds->prepare($query2, trim($row["ACTEASSO"]));
            $result2            = $ds->exec($query2);
            $row2               = $ds->fetchArray($result2);
            $assos[$i]["texte"] = $row2["LIBELLELONG"];
            $assos[$i]["type"]  = $row2["TYPE"];
            $i++;
        }

        $this->assos     = array_merge($this->assos, $assos);
        $activite->assos = $assos;
    }

    /**
     * Récupération des actes associés (compléments / suppléments
     *
     * @param string $code  Chaine de caractère à trouver dans les résultats
     * @param int    $limit Nombre max de codes retournés
     *
     * @return void
     */
    public function getActesAsso(string $code = null, int $limit = null): void
    {
        foreach ($this->activites as &$activite) {
            $this->getAssoFromActivite($activite, $code, $limit);
        }

        return;
        if ($this->type == 2) {
            return;
        }
        $ds          = $this->_spec->ds;
        $queryEffet  = $ds->prepare(
            "SELECT MAX(DATEEFFET) as LASTDATE FROM p_activite_associabilite WHERE CODEACTE = % GROUP BY CODEACTE",
            $this->code
        );
        $resultEffet = $ds->exec($queryEffet);
        $rowEffet    = $ds->fetchArray($resultEffet);
        $lastDate    = $rowEffet["LASTDATE"];
        if ($code) {
            $code_explode = explode(" ", $code);
            $codeLike     = [];
            foreach ($code_explode as $value) {
                $codeLike[] = "LIBELLELONG LIKE '%" . addslashes($value) . "%'";
            }

            $query = "SELECT * FROM p_activite_associabilite
        LEFT JOIN p_acte
          ON p_activite_associabilite.ACTEASSO = p_acte.CODE
        WHERE p_activite_associabilite.CODEACTE = '$this->code'
        AND p_activite_associabilite.DATEEFFET = '$lastDate'
        AND (p_acte.CODE LIKE '$code%'
          OR (" . implode(" OR ", $codeLike) . "))
        GROUP BY p_activite_associabilite.ACTEASSO";
        } else {
            $query = $ds->prepare(
                "SELECT * FROM p_activite_associabilite
         LEFT JOIN p_acte
           ON p_activite_associabilite.ACTEASSO = p_acte.CODE
         WHERE p_activite_associabilite.CODEACTE = %
           AND p_activite_associabilite.DATEEFFET = '$lastDate'
         GROUP BY p_activite_associabilite.ACTEASSO",
                $this->code
            );
        }
        if ($limit) {
            $query .= " LIMIT $limit";
        }
        $result = $ds->exec($query);
        $i      = 0;
        while ($row = $ds->fetchArray($result)) {
            $this->assos[$i]["code"]  = $row["ACTEASSO"];
            $query2                   = $ds->prepare(
                "SELECT * FROM p_acte WHERE CODE = % AND DATEFIN = '00000000'",
                trim($row["ACTEASSO"])
            );
            $result2                  = $ds->exec($query2);
            $row2                     = $ds->fetchArray($result2);
            $this->assos[$i]["texte"] = $row2["LIBELLELONG"];
            $this->assos[$i]["type"]  = $row2["TYPE"];
            $i++;
        }
    }

    /**
     * Récupération de la liste des actes incompatibles à l'acte
     *
     * @return void
     */
    public function getActesIncomp(): void
    {
        $ds          = $this->_spec->ds;
        $queryEffet  = $ds->prepare(
            "SELECT MAX(DATEEFFET) as LASTDATE FROM p_acte_incompatibilite WHERE CODEACTE = % GROUP BY CODEACTE",
            $this->code
        );
        $resultEffet = $ds->exec($queryEffet);
        $rowEffet    = $ds->fetchArray($resultEffet);
        $lastDate    = $rowEffet["LASTDATE"];
        $query       = $ds->prepare(
            "SELECT * FROM p_acte_incompatibilite WHERE CODEACTE = % AND DATEEFFET = '$lastDate' GROUP BY INCOMPATIBLE",
            $this->code
        );
        $result      = $ds->exec($query);
        $i           = 0;
        while ($row = $ds->fetchArray($result)) {
            $this->incomps[$i]["code"]  = trim($row["INCOMPATIBLE"]);
            $query2                     = $ds->prepare(
                "SELECT * FROM p_acte WHERE CODE = % AND DATEFIN = '00000000'",
                trim($row["INCOMPATIBLE"])
            );
            $result2                    = $ds->exec($query2);
            $row2                       = $ds->fetchArray($result2);
            $this->incomps[$i]["texte"] = $row2["LIBELLELONG"];
            $i++;
        }
    }

    /**
     * Récupération de la procédure liée à l'acte
     *
     * @return void
     */
    public function getProcedure(): void
    {
        $ds     = $this->_spec->ds;
        $query  = $ds->prepare(
            "SELECT * FROM p_acte_procedure WHERE CODEACTE = % GROUP BY CODEACTE ORDER BY DATEEFFET DESC",
            $this->code
        );
        $result = $ds->exec($query);
        if ($ds->numRows($result) > 0) {
            $row                      = $ds->fetchArray($result);
            $this->procedure["code"]  = $row["CODEPROCEDURE"];
            $query2                   = $ds->prepare(
                "SELECT LIBELLELONG FROM p_acte WHERE CODE = % AND DATEFIN = '00000000'",
                $this->procedure["code"]
            );
            $result2                  = $ds->exec($query2);
            $row2                     = $ds->fetchArray($result2);
            $this->procedure["texte"] = $row2["LIBELLELONG"];
        } else {
            $this->procedure["code"]  = "";
            $this->procedure["texte"] = "";
        }
    }

    /**
     * Récupération du forfait d'un modificateur
     *
     * @param string $modificateur Lettre clé du modificateur
     *
     * @return array forfait et coefficient
     */
    public function getForfait(string $modificateur): array
    {
        $ds                    = $this->_spec->ds;
        $query                 = $ds->prepare(
            "SELECT * FROM t_modificateurforfait WHERE CODE = % AND DATEFIN = '00000000'",
            $modificateur
        );
        $result                = $ds->exec($query);
        $row                   = $ds->fetchArray($result);
        $valeur                = [];
        $valeur["forfait"]     = $row["FORFAIT"] / 100;
        $valeur["coefficient"] = $row["COEFFICIENT"] / 10;

        return $valeur;
    }

    /**
     * Récupération du coefficient d'association
     *
     * @param string $code Code d'association
     *
     * @return float
     */
    public function getCoeffAsso(string $code): float
    {
        if ($code == "X") {
            return 0;
        }

        if (!$code) {
            return 100;
        }

        $ds     = $this->_spec->ds;
        $query  = $ds->prepare(
            "SELECT * FROM t_association WHERE CODE = % AND DATEFIN = '00000000'",
            $code
        );
        $result = $ds->exec($query);
        $row    = $ds->fetchArray($result);
        $valeur = $row["COEFFICIENT"] / 10;

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
    public function findCodes(string $code = '', string $keys = '', int $max_length = null, string $where = null): array
    {
        $ds = $this->_spec->ds;

        $query = "SELECT CODE, LIBELLELONG
              FROM p_acte
              WHERE DATEFIN = '00000000' ";

        $keywords = explode(" ", $keys);
        $codes    = explode(" ", $code);
        CMbArray::removeValue("", $keywords);
        CMbArray::removeValue("", $codes);

        if ($keys != "") {
            $listLike = [];
            $codeLike = [];
            foreach ($keywords as $value) {
                $listLike[] = "LIBELLELONG LIKE '%" . addslashes($value) . "%'";
            }
            if ($code != "") {
                // Combiner la recherche de code et libellé
                foreach ($codes as $value) {
                    $codeLike[] = "CODE LIKE '" . addslashes($value) . "%'";
                }
                $query .= " AND ( (";
                $query .= implode(" OR ", $codeLike);
                $query .= ") OR (";
            } else {
                // Ou uniquement le libellé
                $query .= " AND (";
            }
            $query .= implode(" AND ", $listLike);
            if ($code != "") {
                $query .= ") ) ";
            }
        }
        if ($code && !$keys) {
            // Ou uniquement le code
            $codeLike = [];
            foreach ($codes as $value) {
                $codeLike[] = "CODE LIKE '" . addslashes($value) . "%'";
            }
            $query .= "AND " . implode(" OR ", $codeLike);
        }

        if ($max_length) {
            $query .= " AND LENGTH(CODE) < $max_length ";
        }

        if ($where) {
            $query .= "AND " . $where;
        }

        $query .= " ORDER BY CODE LIMIT 0 , 100";

        $result = $ds->exec($query);
        $master = [];
        $i      = 0;
        while ($row = $ds->fetchArray($result)) {
            $master[$i]["LIBELLELONG"] = $row["LIBELLELONG"];
            $master[$i]["CODE"]        = $row["CODE"];
            $i++;
        }

        return ($master);
    }
}
