<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam\Controllers\Legacy;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbPath;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Ccam\CCCAM;
use Ox\Mediboard\Ccam\CCCAMImport;
use Ox\Mediboard\Ccam\CCodeCCAM;
use Ox\Mediboard\Ccam\CDatedCodeCCAM;
use Ox\Mediboard\Ccam\CFavoriCCAM;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSpecCPAM;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\SalleOp\CActeCCAM;

class CcamLegacyController extends CLegacyController
{
    public function autocompleteAssociatedCcamCodes(): void
    {
        $this->checkPermRead();

        $code     = CView::get('code', 'str notNull');
        $keywords = CView::post('keywords', 'str');

        CView::checkin();
        CView::enableSlave();

        $code_ccam = CDatedCodeCCAM::get($code);
        $code_ccam->getActesAsso($keywords, 30);

        $codes = [];

        foreach ($code_ccam->assos as $_code) {
            $_code_value         = $_code['code'];
            $codes[$_code_value] = CDatedCodeCCAM::get($_code_value);
        }

        $this->renderSmarty('code_ccam_autocomplete.tpl', [
            'keywords' => $keywords,
            'codes'    => $codes,
        ]);
    }

    public function autocompleteCcamCodes(): void
    {
        $this->checkPermRead();

        $input_field = CView::request("input_field", "str default|_codes_ccam");
        $keywords    = CView::request($input_field, "str default|%%");
        /* Can be a date or a datetime */
        $date       = CView::request("date", 'str default|' . CMbDT::date());
        $user_id    = CView::request('user_id', 'ref class|CMediusers');
        $patient_id = CView::request('patient_id', 'ref class|CPatient');

        CView::checkin();
        CView::enableSlave();

        $user = null;
        if ($user_id) {
            $user = CMediusers::loadFromGuid("CMediusers-{$user_id}");
        }
        $patient = null;
        if ($patient_id) {
            $patient = CPatient::loadFromGuid("CPatient-{$patient_id}");
        }

        $codes = [];
        $code  = new CDatedCodeCCAM(null, CMbDT::date($date));
        foreach ($code->findCodes($keywords, $keywords) as $_code) {
            $_code_value = $_code["CODE"];
            $code        = CDatedCodeCCAM::get($_code_value, $date);
            if ($code->code != "-") {
                $codes[$_code_value] = $code;
            }
            $code->getPrice($user, $patient, $date);
        }

        $this->renderSmarty('code_ccam_autocomplete.tpl', [
            'keywords' => $keywords,
            'codes'    => $codes,
        ]);
    }

    public function searchCcamCodes(): void
    {
        $this->checkPermRead();

        $object_class = CView::get("object_class", 'str', true);
        $keywords     = CView::get("clefs", 'str', true);
        $code         = CView::get("code", 'str', true);
        $selacces     = CView::get("selacces", 'str', true);
        $seltopo1     = CView::get("seltopo1", 'str', true);
        $seltopo2     = CView::get("seltopo2", 'str', true);

        $chap1 = CView::get("chap1", 'str');
        $chap2 = $chap1 ? CView::get("chap2", 'str') : null;
        $chap3 = $chap2 ? CView::get("chap3", 'str') : null;
        $chap4 = $chap2 ? CView::get("chap4", 'str') : null;

        CView::checkin();

        $access = CCCAM::getAccesses();
        $topo1  = CCCAM::getFirstLevelTopographies();
        $topo2  = $seltopo1 ? CCCAM::getSecondLevelTopographies($seltopo1) : [];

        $listChap1 = CCCAM::getChapters();
        $listChap2 = $chap1 ? CCCAM::getChapters($chap1) : [];
        $listChap3 = $chap2 ? CCCAM::getChapters($chap2) : [];
        $listChap4 = $chap3 ? CCCAM::getChapters($chap3) : [];

        $codes = [];
        if ($code || $keywords || $selacces || $seltopo1 || $chap1 || $chap2 || $chap3 || $chap4) {
            $results = (new CDatedCodeCCAM(null, CMbDT::date()))->findCodes(
                $code,
                $keywords,
                null,
                null,
                $selacces,
                $seltopo1,
                $seltopo2,
                $chap1 ? $listChap1[$chap1]["rank"] : null,
                $chap2 ? $listChap2[$chap2]["rank"] : null,
                $chap3 ? $listChap3[$chap3]["rank"] : null,
                $chap4 ? $listChap4[$chap4]["rank"] : null
            );

            foreach ($results as $result) {
                $codes[] = CDatedCodeCCAM::get($result["CODE"]);
            }
        }

        $this->renderSmarty('vw_find_code.tpl', [
            'object_class' => $object_class,
            'clefs'        => $keywords,
            'selacces'     => $selacces,
            'seltopo1'     => $seltopo1,
            'seltopo2'     => $seltopo2,
            'chap1'        => $chap1,
            'chap2'        => $chap2,
            'chap3'        => $chap3,
            'chap4'        => $chap4,
            'code'         => $code,
            'acces'        => $access,
            'topo1'        => $topo1,
            'topo2'        => $topo2,
            'listChap1'    => $listChap1,
            'listChap2'    => $listChap2,
            'listChap3'    => $listChap3,
            'listChap4'    => $listChap4,
            'codes'        => $codes,
            'numcodes'     => count($codes),
        ]);
    }

    public function viewCcamCode(): void
    {
        $this->checkPermRead();

        $user         = CMediusers::get();
        $default_spec = 1;
        if ($user->spec_cpam_id) {
            $default_spec = $user->spec_cpam_id;
        }

        $default_sector = '1';
        if ($user->secteur) {
            $default_sector = $user->secteur;
        }

        $default_contract = '1';
        if ($user->pratique_tarifaire) {
            $default_contract = $user->pratique_tarifaire;
        }
        $codeacte          = CView::get('_codes_ccam', 'str', true);
        $object_class      = CView::get(
            "object_class",
            'enum list|COperation|CSejour|CConsultation default|COperation'
        );
        $hideSelect        = CView::get("hideSelect", 'bool default|0');
        $situation_patient = CView::get('situation_patient', 'enum list|c2s|acs|none default|none');
        $speciality        = CView::get('speciality', "num min|1 max|80 default|$default_spec");
        $contract          = CView::get('contract', "enum list|optam|optamco|none default|$default_contract");
        $sector            = CView::get('sector', "enum list|1|1dp|2|nc default|$default_sector");
        $date              = CView::get('date', 'date');

        CView::checkin();

        $date = CMbDT::date($date);

        $patient = new CPatient();
        if ($situation_patient == 'c2s') {
            $patient->c2s = '1';
        } elseif ($situation_patient == 'acs') {
            $patient->acs = '1';
        }

        $user->spec_cpam_id       = $speciality;
        $user->secteur            = $sector;
        $user->pratique_tarifaire = $contract;

        $code = new CDatedCodeCCAM();
        if ($codeacte) {
            $code = CDatedCodeCCAM::get($codeacte, $date);
        }

        // Variable permettant de savoir si l'affichage du code complet est necessaire
        $codeComplet = false;
        if ($code) {
            $codeacte = $code->code;

            if ($code->_activite != "") {
                $codeComplet = true;
                $codeacte    .= "-$code->_activite";
                if ($code->_phase != "") {
                    $codeacte .= "-$code->_phase";
                }
            }

            $code->getPrice($user, $patient, $date);
        }
        $codeacte = strtoupper($codeacte);

        $favoris = new CFavoriCCAM();

        $this->renderSmarty('vw_full_code.tpl', [
            'code'              => $code,
            'codeComplet'       => $codeComplet,
            'favoris'           => $favoris,
            'codeacte'          => $codeacte,
            'object_class'      => $object_class,
            'hideSelect'        => $hideSelect,
            'situation_patient' => $situation_patient,
            'speciality'        => $speciality,
            'specialities'      => CSpecCPAM::getList(),
            'contract'          => $contract,
            'sector'            => $sector,
            'date'              => $date,
            'user'              => $user,
        ]);
    }

    public function viewDetailCodeCcam(): void
    {
        $this->checkPermRead();

        $codeacte     = CView::get("codeacte", 'str');
        $object_class = CView::get("object_class", 'str');

        CView::checkin();

        $code    = CDatedCodeCCAM::get($codeacte);
        $favoris = new CFavoriCCAM();

        $this->renderSmarty('inc_vw_detail_ccam.tpl', [
            'code'         => $code,
            'favoris'      => $favoris,
            'object_class' => $object_class,
        ]);
    }

    public function showCcamCode(): void
    {
        $this->checkPermRead();

        $user         = CMediusers::get();
        $default_spec = 1;
        if ($user->spec_cpam_id) {
            $default_spec = $user->spec_cpam_id;
        }

        $default_sector = '1';
        if ($user->secteur) {
            $default_sector = $user->secteur;
        }

        $default_contract = '1';
        if ($user->pratique_tarifaire) {
            $default_contract = $user->pratique_tarifaire;
        }

        $code_ccam         = CView::get("code_ccam", 'str notNull');
        $date_version      = CView::get("date_version", 'str');
        $date_demandee     = CView::get("date_demandee", 'str');
        $situation_patient = CView::get('situation_patient', 'enum list|c2s|acs|none default|none');
        $speciality        = CView::get('speciality', "num min|1 max|80 default|$default_spec");
        $contract          = CView::get('contract', "enum list|optam|optamco|none default|$default_contract");
        $sector            = CView::get('sector', "enum list|1|1dp|2|nc default|$default_sector");

        CView::checkin();

        $patient = new CPatient();
        if ($situation_patient == 'c2s') {
            $patient->c2s = '1';
        } elseif ($situation_patient == 'acs') {
            $patient->acs = '1';
        }

        $user->spec_cpam_id       = $speciality;
        $user->secteur            = $sector;
        $user->pratique_tarifaire = $contract;

        $date_version_to = null;
        if ($date_demandee) {
            $date_version_to = CDatedCodeCCAM::mapDateToDash($date_demandee);
            $date_demandee   = CDatedCodeCCAM::mapDateFrom($date_version_to);
        }
        if ($date_version) {
            $date_version_to = CDatedCodeCCAM::mapDateToSlash($date_version);
        }


        $date = CMbDT::dateFromLocale($date_version);

        $date_versions = [];
        $code_complet  = CDatedCodeCCAM::get($code_ccam, $date_version_to);
        foreach ($code_complet->_ref_code_ccam->_ref_infotarif as $_infotarif) {
            $date_versions[] = $code_complet->mapDateFrom($_infotarif->date_effet);
        }
        foreach ($code_complet->activites as $_activite) {
            $code_complet->_count_activite += count($_activite->assos);
        }

        $code_complet->getPrice($user, $patient, $date);
        $acte_voisins = $code_complet->loadActesVoisins();

        $parameters = [
            'code_complet'       => $code_complet,
            "numberAssociations" => $code_complet->_count_activite,
            'date_versions'      => $date_versions,
            'date_version'       => $date_version,
            'date_demandee'      => $date_demandee,
            'code_ccam'          => $code_ccam,
            'acte_voisins'       => $acte_voisins,
            'situation_patient'  => $situation_patient,
            'speciality'         => $speciality,
            'specialities'       => CSpecCPAM::getList(),
            'contract'           => $contract,
            'sector'             => $sector,
        ];

        if (!in_array($date_demandee, $date_versions) && $date_demandee) {
            $parameters["no_date_found"] = "CDatedCodeCCAM-msg-No date found for date searched";
        }

        $this->renderSmarty("inc_show_code.tpl", $parameters);
    }

    public function viewSearchCodeCcamHistory(): void
    {
        $this->checkPermRead();

        $page = intval(CView::get('page', 'num default|0'));

        CView::checkin();

        $listChap1 = CCCAM::getChapters();

        $this->renderSmarty('vw_find_acte.tpl', [
            'listChap1' => $listChap1,
            'page'      => $page,
        ]);
    }

    public function searchCodeCcamHistory(): void
    {
        $this->checkPermRead();

        $page          = intval(CView::get('page', 'num default|0'));
        $step          = 22;
        $keywords      = CView::get('keywords', 'str');
        $code          = CView::get('code', 'str');
        $date_demandee = CView::get('date_demandee', 'date');
        $result_chap1  = CView::get('result_chap1', 'str');
        $result_chap2  = CView::get('result_chap2', 'str');
        $result_chap3  = CView::get('result_chap3', 'str');
        $result_chap4  = CView::get('result_chap4', 'str');

        CView::checkin();

        $date_version = null;
        if ($date_demandee) {
            $date_version = CDatedCodeCCAM::mapDateToDash($date_demandee);
        }

        if (!$code && $date_demandee) {
            $this->renderSmarty('inc_result_search_acte.tpl', [
                "no_code_for_date" => "CDatedCodeCCAM-msg-No code for date",
            ]);
            CApp::rip();
        }

        if (!$code && !$keywords && !$result_chap1 && !$date_demandee) {
            $this->renderSmarty('inc_result_search_acte.tpl', [
                "no_filter" => "CDatedCodeCCAM-msg-No filter",
            ]);
            CApp::rip();
        }

        $where = "DATEFIN = '00000000'";
        $limit = "LIMIT {$page}, {$step}";
        if ($code && $date_version) {
            $where = "DATEEFFET >= '{$date_version}'";
            $limit = "LIMIT 0, 1";
        }

        $total = CCodeCCAM::countCodes(
            $code,
            $keywords,
            null,
            $where,
            null,
            null,
            null,
            $result_chap1,
            $result_chap2,
            $result_chap3,
            $result_chap4
        );

        $results = CCodeCCAM::findCodes(
            $code,
            $keywords,
            null,
            $where,
            null,
            null,
            null,
            $result_chap1,
            $result_chap2,
            $result_chap3,
            $result_chap4,
            $limit
        );


        $codes = [];
        foreach ($results as $result) {
            $code                                = CDatedCodeCCAM::get($result["CODE"]);
            $code->_ref_code_ccam->date_creation = CDatedCodeCCAM::mapDateFrom($code->_ref_code_ccam->date_creation);
            foreach ($code->_ref_code_ccam->_ref_infotarif as $_infotarif) {
                $_infotarif->date_effet = CDatedCodeCCAM::mapDateFrom($_infotarif->date_effet);
            }
            $codes[] = $code;
        }

        $this->renderSmarty('inc_result_search_acte.tpl', [
            'keywords_multiple' => $keywords,
            'codes'             => $codes,
            'nbResultat'        => $total,
            'page'              => $page,
            'step'              => $step,
            'date_demandee'     => $date_demandee,
        ]);
    }

    public function selectorCodeCcam(): void
    {
        $this->checkPermRead();

        $chir           = CView::get("chir", 'ref class|CMediusers');
        $anesth         = CView::get("anesth", 'ref class|CMediusers');
        $_keywords_code = CView::get("_keywords_code", 'str');
        $date           = CMbDT::date(null, CView::get("date", 'str'));
        $object_class   = CView::get("object_class", 'str');
        $only_list      = CView::get("only_list", 'bool default|0');
        $tag_id         = CView::get("tag_id", 'ref class|CTagItem');
        $access         = CView::get('access', 'str');
        $appareil       = CView::get('appareil', 'str');
        $system         = CView::get('system', 'str');
        $chapter_1      = CView::get('chapter_1', 'str');
        $chapter_2      = CView::get('chapter_2', 'str');
        $chapter_3      = CView::get('chapter_3', 'str');
        $chapter_4      = CView::get('chapter_4', 'str');
        $ged            = CView::get('ged', 'bool default|0');

        CView::checkin();

        $user     = CUser::get();
        $profiles = [
            "chir"   => $chir,
            "anesth" => $anesth,
            "user"   => $user->_id,
        ];

        if ($profiles["user"] == $profiles["anesth"] || $profiles["user"] == $profiles["chir"]) {
            unset($profiles["user"]);
        }

        if (!$profiles["anesth"]) {
            unset($profiles["anesth"]);
        }

        if (!$profiles["chir"]) {
            unset($profiles["chir"]);
        }

        $ds = CSQLDataSource::get('ccamV2');

        /* Récupération des voies d'accès */
        $access_ways = CCCAM::getAccesses();

        /* Récupération des appareils */
        $appareils = CCCAM::getFirstLevelTopographies();

        /* Récupération des systèmes */
        $systems = CCCAM::getSecondLevelTopographies();

        /* Récupération des chapitres*/
        $chapters_1 = CCCAM::getChapters();
        $chapters_2 = $chapter_1 ? CCCAM::getChapters($chapter_1) : [];
        $chapters_3 = $chapter_2 ? CCCAM::getChapters($chapter_2) : [];
        $chapters_4 = $chapter_3 ? CCCAM::getChapters($chapter_3) : [];

        if (
            $_keywords_code || $access || $system || $appareil
            || $chapter_1 || $chapter_2 || $chapter_3 || $chapter_4
        ) {
            $profiles['search'] = null;
        }

        $listByProfile = [];
        $users         = [];
        foreach ($profiles as $profile => $_user_id) {
            $_user           = $profile != 'search' ? CMediusers::get($_user_id) : new CMediusers();
            $users[$profile] = $_user;

            $list        = [];
            $codes_stats = [];

            // Statistiques
            if (!$tag_id && $_user_id) {
                $actes       = new CActeCCAM();
                $codes_stats = $actes->getFavoris($_user_id, $object_class);

                foreach ($codes_stats as $key => $_code) {
                    $codes_stats[$_code["code_acte"]] = $_code;
                    unset($codes_stats[$key]);
                }
            }

            // Favoris
            $codes_favoris = [];
            if ($profile != 'search') {
                $_user = CMediusers::get($_user_id);
                $code  = new CFavoriCCAM();
                $where = [
                    "ccamfavoris.favoris_user = '$_user->_id' OR ccamfavoris.favoris_function = $_user->function_id",
                    "ccamfavoris.object_class" => " = '$object_class'",
                ];

                $ljoin = [];
                if ($tag_id) {
                    $where["tag_item.tag_id"]       = "= '$tag_id'";
                    $where['tag_item.object_class'] = " = 'CFavoriCCAM'";
                    $ljoin["tag_item"]              = "tag_item.object_id = ccamfavoris.favoris_id";
                }

                // - rang DESC permet de mettre les rang null à la fin de la liste
                /** @var CFavoriCCAM[] $codes_favoris */
                $codes_favoris = $code->loadList($where, "rang DESC", 100, null, $ljoin);

                foreach ($codes_favoris as $key => $_code) {
                    $codes_favoris[$_code->favoris_code] = $_code;
                    unset($codes_favoris[$key]);
                }
            }

            // Seek sur les codes, avec ou non l'inclusion de tous les codes
            $code  = new CDatedCodeCCAM("");
            $where = null;

            if ($profile != 'search' && (count($codes_stats) || count($codes_favoris))) {
                // Si on a la recherche par tag, on n'utilise pas les stats (les tags sont mis sur les favoris)
                $codes_keys = $tag_id ? array_keys($codes_favoris)
                    : array_keys(array_merge($codes_stats, $codes_favoris));
                $where      = "CODE " . $ds->prepareIn($codes_keys);
            }

            if ($profile != 'search' && count($codes_stats) == 0 && count($codes_favoris) == 0) {
                // Si pas de stat et pas de favoris, et que la recherche se fait sur ceux-ci,
                // alors le tableau de résultat est vide
                $codes = [];
            } else {
                // Sinon recherche de codes
                $codes = $code->findCodes(
                    $_keywords_code,
                    $_keywords_code,
                    null,
                    $where,
                    $access,
                    $appareil,
                    $system,
                    $chapter_1 ? $chapters_1[$chapter_1]["rank"] : null,
                    $chapter_2 ? $chapters_2[$chapter_2]["rank"] : null,
                    $chapter_3 ? $chapters_3[$chapter_3]["rank"] : null,
                    $chapter_4 ? $chapters_4[$chapter_4]["rank"] : null,
                );
            }

            $list_fav = $list_fav_no_rang = [];
            foreach ($codes as $value) {
                $val_code = $value["CODE"];
                $code     = CDatedCodeCCAM::get($val_code, $date);

                if ($profile != 'search' && $code->code != "-") {
                    $list[$val_code] = $code;
                    $nb_acte         = 0;
                    if (isset($codes_favoris[$val_code])) {
                        $list[$val_code]->nb_acte  = 0.5;
                        $list[$val_code]->_favoris = $codes_favoris[$val_code];

                        $chapitre              =& $code->chapitres[0];
                        $list[$val_code]->chap = $chapitre["nom"];

                        if (isset($list[$val_code]->_favoris) && !$list[$val_code]->_favoris->rang) {
                            $list_fav_no_rang[$val_code] = $list[$val_code];
                        } else {
                            $list_fav[$val_code] = $list[$val_code];
                        }
                    }

                    $nb_acte = isset($codes_stats[$val_code]) ? $codes_stats[$val_code]["nb_acte"] : 0;

                    $list[$val_code]->nb_acte = $nb_acte;
                    if (!$nb_acte) {
                        $list_fav_no_rang[$val_code] = $list[$val_code];
                        unset($list[$val_code]);
                    }
                } elseif ($code->code != "-") {
                    $list[$val_code] = $code;
                }
            }

            if ($tag_id || $profile == 'search') {
                $sorter = CMbArray::pluck($list, "code");
                array_multisort($sorter, SORT_ASC, $list);
            } else {
                $sorter = CMbArray::pluck($list, "nb_acte");
                array_multisort($sorter, SORT_DESC, $list);
            }

            $order_1_list = CMbArray::pluck($list_fav, "_favoris", "rang");
            $order_2_list = CMbArray::pluck($list_fav, "code");
            array_multisort(
                $order_1_list,
                SORT_ASC,
                $order_2_list,
                SORT_ASC,
                $list_fav
            );
            $list_fav = array_merge($list_fav, $list_fav_no_rang);

            $listByProfile[$profile]["favoris"] = $codes_favoris;
            $listByProfile[$profile]["stats"]   = $codes_stats;
            $listByProfile[$profile]["list"]    = ["favoris" => $list_fav, "stats" => $list];
            $listByProfile[$profile]['total']   = count($list_fav) + count($list);
        }

        $tag_tree = CFavoriCCAM::getTree($user->_id);

        $template   = 'inc_code_selector_ccam.tpl';
        $parameters = [
            'listByProfile'  => $listByProfile,
            'users'          => $users,
            'object_class'   => $object_class,
            'date'           => $date,
            '_keywords_code' => $_keywords_code,
            'tag_tree'       => $tag_tree,
            'tag_id'         => $tag_id,
            'curr_user'      => CMediusers::get(),
            'ged'            => $ged,
        ];

        if (!$only_list) {
            $parameters['chir']       = $chir;
            $parameters['anesth']     = $anesth;
            $parameters['access']     = $access_ways;
            $parameters['appareils']  = $appareils;
            $parameters['systems']    = $systems;
            $parameters['chapters_1'] = $chapters_1;
            $parameters['chapters_2'] = $chapters_2;
            $parameters['chapters_3'] = $chapters_3;
            $parameters['chapters_4'] = $chapters_4;

            $template = "code_selector_ccam.tpl";
        }

        $this->renderSmarty($template, $parameters);
    }

    public function refreshChapters(): void
    {
        $this->checkPermRead();

        $value_selected = CView::get("value_selected", 'str');
        $codePere       = CView::get("codePere", 'str');

        CView::checkin();

        $list = CCCAM::getChapters($codePere);

        $this->renderSmarty('inc_select_codes.tpl', [
            'value_selected' => $value_selected,
            'list'           => $list,
        ]);
    }

    public function listChapters(): void
    {
        $this->checkPermRead();

        $parent = CView::get('parent', 'str');
        CView::checkin();

        $this->renderJson(CCCAM::getChapters($parent));
    }

    /**
     * @throws Exception
     */
    public function importCcamDatabase(): void
    {
        $this->checkPermAdmin();
        CView::checkin();

        CApp::setTimeLimit(360);

        $import = new CCCAMImport();
        $import->importDatabase(['ccamv2']);

        foreach ($import->getMessages() as $message) {
            CAppUI::stepAjax(...$message);
        }

        CApp::rip();
    }

    /**
     * @throws Exception
     */
    public function importCccamForfaitsDatabase(): void
    {
        $this->checkPermAdmin();
        CView::checkin();

        CApp::setTimeLimit(360);
        ini_set("memory_limit", "128M");

        $import = new CCCAMImport();
        $import->importDatabase(['ccamv2_forfaits']);

        foreach ($import->getMessages() as $message) {
            CAppUI::stepAjax(...$message);
        }

        CApp::rip();
    }

    public function configure(): void
    {
        $this->checkPermAdmin();
        $this->renderSmarty('configure.tpl');
    }
}
