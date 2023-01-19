<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;

/**
 * Gestion des dépistages du dossier de périnatalité
 */
class CDepistageGrossesse extends CMbObject
{
    // DB Table key
    public $depistage_grossesse_id;

    public $grossesse_id;

    public $date;

    public $groupe_sanguin;
    public $aci;
    public $rques_aci;
    public $rhesus;
    public $rhesus_bb;


    /** @var string */
    public $genotypage;
    /** @var string */
    public $date_genotypage;
    /** @var string  */
    public $rques_genotypage;
    /** @var string */
    public $rhophylac;
    /** @var string  */
    public $date_rhophylac;
    /** @var float */
    public $quantite_rhophylac;
    /** @var string */
    public $rques_rhophylac;
    /** @var string */
    public $datetime_1_determination;
    /** @var string */
    public $datetime_2_determination;

    public $rai;
    public $rques_immuno;
    public $test_kleihauer;
    public $val_kleihauer;

    public $rubeole;
    public $toxoplasmose;
    public $varicelle;
    public $syphilis;
    public $vih;
    public $parvovirus;
    public $hepatite_b;
    public $hepatite_c;
    public $cmvg;
    public $cmvm;
    public $htlv;
    public $TPHA;
    public $vrdl;
    public $hb;
    public $marqueurs_seriques_t21;
    public $rques_serologie;
    public $strepto_b;
    public $parasitobacteriologique;
    public $rques_vaginal;
    public $amniocentese;
    public $pvc;
    public $dpni;
    public $dpni_rques;
    public $cbu;
    public $glycosurie;
    public $albuminerie;
    public $albuminerie_24;
    public $t21;
    public $pappa;
    public $hcg1;
    public $rques_t1;
    public $afp;
    public $hcg2;
    public $estriol;
    public $rques_t2;

    public $gr;
    public $gb;
    public $ferritine;
    public $fg;
    public $vgm;
    public $depistage_diabete;
    public $glycemie;
    public $rques_biochimie;

    public $acide_urique;
    public $asat;
    public $alat;
    public $creatininemie;
    public $phosphatase;
    public $brb;
    public $unite_brb;
    public $sel_biliaire;
    public $rques_bacteriologie;

    public $nfs_hb;
    public $nfs_plaquettes;
    public $tp;
    public $tca;
    public $tca_temoin;
    public $electro_hemoglobine_a1;
    public $electro_hemoglobine_a2;
    public $electro_hemoglobine_s;
    public $co_expire;
    public $rques_hemato;

    /** @var CGrossesse */
    public $_ref_grossesse;

    /** @var CDepistageGrossesseCustom */
    public $_ref_depistage_customs;

    public $_libelle_customs      = [];
    public $_valeur_customs       = [];
    public $_depistage_custom_ids = [];
    public $_rewrite_custom       = true;

    public $_sa;
    /** @var string */
    public $_date_depistage;

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'depistage_grossesse';
        $spec->key   = 'depistage_grossesse_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props                 = parent::getProps();
        $props["grossesse_id"] = "ref notNull class|CGrossesse back|depistages";
        $props["date"]         = "date notNull";

        $props["groupe_sanguin"]           = "enum list|a|b|ab|o";
        $props["aci"]                      = "enum list|pos|neg";
        $props["rques_aci"]                = "text";
        $props["rhesus"]                   = "enum list|pos|neg";
        $props["rhesus_bb"]                = "enum list|pos|neg|indetermine";
        $props["genotypage"]               = "enum list|nonfait|fait|controle";
        $props["date_genotypage"]          = "date";
        $props["rques_genotypage"]         = "text";
        $props["rhophylac"]                 = "enum list|nonfait|fait";
        $props["date_rhophylac"]            = "date";
        $props["quantite_rhophylac"]        = "float min|0";
        $props["rques_rhophylac"]           = "text";
        $props["datetime_1_determination"] = "dateTime";
        $props["datetime_2_determination"] = "dateTime";
        $props["rai"]                      = "enum list|neg|pos|nf";
        $props["rques_immuno"]             = "text";
        $props["test_kleihauer"]           = "enum list|pos|neg";
        $props["val_kleihauer"]            = "float min|0";

        //Sérologie
        $props["rubeole"]                = "enum list|nim|im|in|douteux";
        $props["toxoplasmose"]           = "enum list|nim|im|in";
        $props["varicelle"]              = "enum list|nim|im|in";
        $props["syphilis"]               = "enum list|neg|pos|in";
        $props["vih"]                    = "enum list|neg|pos|in";
        $props["parvovirus"]             = "enum list|neg|pos|in";
        $props["hepatite_b"]             = "enum list|neg|pos|in";
        $props["hepatite_c"]             = "enum list|neg|pos|in";
        $props["cmvg"]                   = "enum list|neg|pos|in";
        $props["cmvm"]                   = "enum list|neg|pos|in";
        $props["htlv"]                   = "enum list|neg|pos";
        $props["vrdl"]                   = "enum list|neg|pos";
        $props["hb"]                     = "float min|0";
        $props["TPHA"]                   = "enum list|TPHA|vrdl|BW";
        $props["marqueurs_seriques_t21"] = "str";
        $props["rques_serologie"]        = "text";

        //Biochimie
        $props["gr"]                = "float";
        $props["gb"]                = "float";
        $props["ferritine"]         = "float min|0 max|500";
        $props["fg"]                = "float min|0 max|500";
        $props["vgm"]               = "float min|0 max|500";
        $props["depistage_diabete"] = "str";
        $props["rques_biochimie"]   = "text";
        $props["glycemie"]          = "float min|0 max|4";

        //Bactériologie
        $props["acide_urique"]            = "float";
        $props["asat"]                    = "float";
        $props["alat"]                    = "float";
        $props["creatininemie"]           = "float";
        $props["phosphatase"]             = "float";
        $props["brb"]                     = "float";
        $props["unite_brb"]               = "enum list|mgL|mmolL default|mgL";
        $props["sel_biliaire"]            = "float";
        $props["pvc"]                     = "text";
        $props["rques_bacteriologie"]     = "text";
        $props["strepto_b"]               = "enum list|neg|pos|in";
        $props["parasitobacteriologique"] = "enum list|neg|pos|in";
        $props["rques_vaginal"]           = "text";
        $props["amniocentese"]            = "float";
        $props["dpni"]                    = "enum list|neg|pos|in";
        $props["dpni_rques"]              = "text";
        $props["cbu"]                     = "text";
        $props["glycosurie"]              = "float";
        $props["albuminerie"]             = "float";
        $props["albuminerie_24"]          = "float";
        $props["t21"]                     = "float";
        $props["pappa"]                   = "float";
        $props["hcg1"]                    = "float";
        $props["rques_t1"]                = "text";
        $props["rques_t2"]                = "text";
        $props["afp"]                     = "float";
        $props["hcg2"]                    = "float";
        $props["estriol"]                 = "float";

        $props["nfs_hb"]                 = "float min|0";             // g/dl
        $props["nfs_plaquettes"]         = "numchar maxLength|4 pos"; // (x1000)/mm3
        $props["tp"]                     = "float min|0 max|140";     // %
        $props["tca"]                    = "numchar maxLength|2";     // secondes
        $props["tca_temoin"]             = "numchar maxLength|2";     // secondes
        $props["electro_hemoglobine_a1"] = "float min|0 max|100";     // %
        $props["electro_hemoglobine_a2"] = "float min|0 max|100";     // %
        $props["electro_hemoglobine_s"]  = "float min|0 max|100";     // %
        $props["co_expire"]              = "num min|0 max|100";       // ppm
        $props["rques_hemato"]           = "text";

        $props["_sa"] = "num";

        // For light view perinatal folder
        $props["_date_depistage"] = "date notNull";

        return $props;
    }

    /**
     * Chargement de la grossesse
     *
     * @return CGrossesse
     */
    function loadRefGrossesse()
    {
        return $this->_ref_grossesse = $this->loadFwdRef("grossesse_id", true);
    }

    /**
     * Chargement des periodes d'allaitement d'une grossesse
     *
     * @return CAllaitement[]|null
     */
    function loadRefsDepistageGrossesseCustom()
    {
        return $this->_ref_depistage_customs = $this->loadBackRefs("depistages_customs");
    }

    /**
     * Calcul de la date en semaines d'aménorrhée
     *
     * @return int
     */
    function getSA()
    {
        $this->loadRefGrossesse();
        $sa_comp = $this->_ref_grossesse->getAgeGestationnel($this->date);

        return $this->_sa = $sa_comp["SA"];
    }

    /**
     * @see parent::updateFormFields()
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->loadRefsDepistageGrossesseCustom();
        $i = 0;

        if ($this->_rewrite_custom) {
            foreach ($this->_ref_depistage_customs as $_depistage_customs) {
                $this->_libelle_customs[$i]      = $_depistage_customs->libelle;
                $this->_valeur_customs[$i]       = $_depistage_customs->valeur;
                $this->_depistage_custom_ids[$i] = $_depistage_customs->_id;
                $i++;
            }

            while ($i < 3) {
                $this->_libelle_customs[$i]      = "";
                $this->_valeur_customs[$i]       = "";
                $this->_depistage_custom_ids[$i] = "";
                $i++;
            }
        }

        $this->_date_depistage = $this->date;
    }

    /**
     * @see parent::store()
     */
    function store()
    {
        if ($msg = parent::store()) {
            return $msg;
        }

        foreach ($this->_libelle_customs as $field_key => $_field) {
            $depistage_customs = new CDepistageGrossesseCustom();

            if ($this->_depistage_custom_ids[$field_key]) {
                $depistage_customs->load($this->_depistage_custom_ids[$field_key]);
            } else {
                $depistage_customs->depistage_grossesse_id = $this->_id;
            }

            if ($depistage_customs->_id || ($_field != '' && $this->_valeur_customs[$field_key] != '')) {
                $depistage_customs->libelle = $_field;
                $depistage_customs->valeur  = $this->_valeur_customs[$field_key];

                $depistage_customs->store();
            }
        }
    }

    /**
     * Get the other screenings
     *
     * @param string $keywords
     *
     * @return array
     */
    public function getOtherScreenings(string $keywords = null): array
    {
        $bool_depistage_fields = [
            "aci"                     => CAppUI::tr("CDepistageGrossesse-aci"),
            "test_kleihauer"          => CAppUI::tr("CDepistageGrossesse-test_kleihauer"),
            "varicelle"               => CAppUI::tr("CDepistageGrossesse-varicelle"),
            "parvovirus"              => CAppUI::tr("CDepistageGrossesse-parvovirus"),
            "cmvg"                    => CAppUI::tr("CDepistageGrossesse-cmvg"),
            "cmvm"                    => CAppUI::tr("CDepistageGrossesse-cmvm"),
            "htlv"                    => CAppUI::tr("CDepistageGrossesse-htlv"),
            "vrdl"                    => CAppUI::tr("CDepistageGrossesse-vrdl"),
            "TPHA"                    => CAppUI::tr("CDepistageGrossesse-TPHA"),
            "strepto_b"               => CAppUI::tr("CDepistageGrossesse-strepto_b"),
            "parasitobacteriologique" => CAppUI::tr("CDepistageGrossesse-parasitobacteriologique"),
        ];

        if ($keywords) {
            foreach ($bool_depistage_fields as $depistage_key => $depistage_name) {
                if ($this->$depistage_key) {
                    unset($bool_depistage_fields[$depistage_key]);
                }
            }

            foreach ($bool_depistage_fields as $depistage_key => $depistage_name) {
                if (strpos(strtolower($depistage_name), strtolower($keywords)) === false) {
                    unset($bool_depistage_fields[$depistage_key]);
                }
            }
        }

        return $bool_depistage_fields;
    }
}
