<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

/**
 * Classe utilitaire permettant de récupérer le contexte tarifaire utilisé pour définir le prix d'un acte CCAM
 * en fonction du patient, du praticien, et de la date
 *
 * Contient également la liste de tous les contextes (patient et praticien)
 */
class CContexteTarifaireCCAM implements IShortNameAutoloadable
{
    /** @var array The list of patient context */
    public static $patient_context = [
        1 => 'Tout context (avant 01/01/18)',
        2 => 'C2S',
        3 => 'Hors C2S/ACS',
        4 => 'ACS',
    ];

    /** @var array The list of patient context */
    public static $practitioner_context = [
        /*
         * Contexts used before the 2018-01-01
         * The first thirteenth context are normally used before the 2018-01-01,
         * but since there is only 2 price grid, we only need 2
         */
        1  => 'Secteur 1 ou Secteur 2 avec OPTAM/OPTAM-CO',
        2  => 'Secteur 2 ou Secteur 1 DP',
        /* Contexts used after the 2018-01-01 */
        10 => 'Conventionnés, chirurgiens dentistes',
        11 => 'Non conventionnés, chirurgiens dentistes',
        12 => 'Conventionnés, sages-femmes',
        13 => 'Non conventionnés, sages-femmes',
        14 => 'Secteur 1 OPTAM-CO, spécialités de chirurgie et de gynéco-obstétrique',
        15 => 'Secteur 1 DP OPTAM-CO, spécialités de chirurgie et de gynéco-obstétrique',
        16 => 'Secteur 2 OPTAM-CO, spécialités de chirurgie et de gynéco-obstétrique',
        17 => 'Secteur 1 OPTAM, spécialités de chirurgie et de gynéco-obstétrique',
        18 => 'Secteur 1 OPTAM, anesthésistes',
        19 => 'Secteur 1 OPTAM, pédiatres',
        20 => 'Secteur 1 OPTAM, généralistes',
        21 => 'Secteur 1 OPTAM, autres spécialités',
        22 => 'Secteur 1 DP OPTAM, spécialités de chirurgie et de gynéco-obstétrique',
        23 => 'Secteur 1 DP OPTAM, anesthésistes',
        24 => 'Secteur 1 DP OPTAM, pédiatres',
        25 => 'Secteur 1 DP OPTAM, généralistes',
        26 => 'Secteur 1 DP OPTAM, autres spécialités',
        27 => 'Secteur 2 OPTAM, spécialités de chirurgie et de gynéco-obstétrique',
        28 => 'Secteur 2 OPTAM, anesthésistes',
        29 => 'Secteur 2 OPTAM, pédiatres',
        30 => 'Secteur 2 OPTAM, généralistes',
        31 => 'Secteur 2 OPTAM, autres spécialités',
        32 => 'Secteur 1, spécialités de chirurgie et de gynéco-obstétrique',
        33 => 'Secteur 1, anesthésistes',
        34 => 'Secteur 1, pédiatres',
        35 => 'Secteur 1, généralistes',
        36 => 'Secteur 1, autres spécialités',
        37 => 'Secteur 1 DP, spécialités de chirurgie et de gynéco-obstétrique',
        38 => 'Secteur 1 DP, anesthésistes',
        39 => 'Secteur 1 DP, pédiatres',
        40 => 'Secteur 1 DP, généralistes',
        41 => 'Secteur 1 DP, autres spécialités',
        42 => 'Secteur 2, spécialités de chirurgie et de gynéco-obstétrique',
        43 => 'Secteur 2, anesthésistes',
        44 => 'Secteur 2, pédiatres',
        45 => 'Secteur 2, généralistes',
        46 => 'Secteur 2, autres spécialités',
        47 => 'Non conventionné, spécialités de chirurgie et de gynéco-obstétrique',
        48 => 'Non conventionné, anesthésistes',
        49 => 'Non conventionné, pédiatres',
        50 => 'Non conventionné, généralistes',
        51 => 'Non conventionné, autres spécialités',
    ];

    /** @var array Fisrt key is the practitioner's context, second is the patient's context */
    public static $contexts_to_grid = [
        /* Grids used before 2018-01-01 */
        1  => [
            1 => '01',
        ],
        2  => [
            1 => '02',
        ],
        /* Grids used after 2018-01-01 */
        10 => [
            1 => '13',
            2 => '13',
            3 => '13',
            4 => '13',
        ],
        11 => [
            1 => '13',
            2 => '13',
            3 => '13',
            4 => '13',
        ],
        12 => [
            1 => '15',
            2 => '15',
            3 => '15',
            4 => '15',
        ],
        13 => [
            1 => '15',
            2 => '15',
            3 => '15',
            4 => '15',
        ],
        14 => [
            1 => '03',
            2 => '03',
            3 => '03',
            4 => '03',
        ],
        15 => [
            1 => '03',
            2 => '03',
            3 => '03',
            4 => '03',
        ],
        16 => [
            1 => '03',
            2 => '03',
            3 => '03',
            4 => '03',
        ],
        17 => [
            1 => '03',
            2 => '03',
            3 => '03',
            4 => '03',
        ],
        18 => [
            1 => '07',
            2 => '07',
            3 => '07',
            4 => '07',
        ],
        19 => [
            1 => '11',
            2 => '11',
            3 => '11',
            4 => '11',
        ],
        20 => [
            1 => '09',
            2 => '09',
            3 => '09',
            4 => '09',
        ],
        21 => [
            1 => '16',
            2 => '16',
            3 => '16',
            4 => '16',
        ],
        22 => [
            1 => '05',
            2 => '05',
            3 => '05',
            4 => '05',
        ],
        23 => [
            1 => '07',
            2 => '07',
            3 => '07',
            4 => '07',
        ],
        24 => [
            1 => '11',
            2 => '11',
            3 => '11',
            4 => '11',
        ],
        25 => [
            1 => '09',
            2 => '09',
            3 => '09',
            4 => '09',
        ],
        26 => [
            1 => '16',
            2 => '16',
            3 => '16',
            4 => '16',
        ],
        27 => [
            1 => '05',
            2 => '05',
            3 => '05',
            4 => '05',
        ],
        28 => [
            1 => '07',
            2 => '07',
            3 => '07',
            4 => '07',
        ],
        29 => [
            1 => '11',
            2 => '11',
            3 => '11',
            4 => '11',
        ],
        30 => [
            1 => '09',
            2 => '09',
            3 => '09',
            4 => '09',
        ],
        31 => [
            1 => '16',
            2 => '16',
            3 => '16',
            4 => '16',
        ],
        32 => [
            1 => '03',
            2 => '03',
            3 => '03',
            4 => '03',
        ],
        33 => [
            1 => '07',
            2 => '07',
            3 => '07',
            4 => '07',
        ],
        34 => [
            1 => '11',
            2 => '11',
            3 => '11',
            4 => '11',
        ],
        35 => [
            1 => '09',
            2 => '09',
            3 => '09',
            4 => '09',
        ],
        36 => [
            1 => '16',
            2 => '16',
            3 => '16',
            4 => '16',
        ],
        37 => [
            1 => '04',
            2 => '04',
            3 => '04',
            4 => '04',
        ],
        38 => [
            1 => '08',
            2 => '08',
            3 => '08',
            4 => '08',
        ],
        39 => [
            1 => '12',
            2 => '12',
            3 => '12',
            4 => '12',
        ],
        40 => [
            1 => '10',
            2 => '10',
            3 => '10',
            4 => '10',
        ],
        41 => [
            1 => '14',
            2 => '14',
            3 => '14',
            4 => '14',
        ],
        42 => [
            1 => '04',
            2 => '04',
            3 => '04',
            4 => '04',
        ],
        43 => [
            1 => '08',
            2 => '08',
            3 => '08',
            4 => '08',
        ],
        44 => [
            1 => '12',
            2 => '12',
            3 => '12',
            4 => '12',
        ],
        45 => [
            1 => '10',
            2 => '10',
            3 => '10',
            4 => '10',
        ],
        46 => [
            1 => '14',
            2 => '14',
            3 => '14',
            4 => '14',
        ],
        47 => [
            1 => '06',
            2 => '06',
            3 => '06',
            4 => '06',
        ],
        48 => [
            1 => '08',
            2 => '08',
            3 => '08',
            4 => '08',
        ],
        49 => [
            1 => '12',
            2 => '12',
            3 => '12',
            4 => '12',
        ],
        50 => [
            1 => '10',
            2 => '10',
            3 => '10',
            4 => '10',
        ],
        51 => [
            1 => '14',
            2 => '14',
            3 => '14',
            4 => '14',
        ],
    ];

    /**
     * @param CMediusers $practitioner The practitioner
     * @param CPatient   $patient      The patient
     * @param string     $date         The date
     *
     * @return string
     */
    public static function getPriceGrid(CMediusers $practitioner, CPatient $patient, string $date = null): string
    {
        $date = CMbDT::date($date);

        /* Surcharge de la date dans le mode test de Pyxvital */
        if (CModule::getActive('oxPyxvital') && CAppUI::gconf('pyxVital General mode') == 'test') {
            $date = CMbDT::date();

            if (CAppUI::gconf('pyxVital General date_ccam')) {
                $date = CMbDT::date(CAppUI::gconf('pyxVital General date_ccam'));
            }
        }

        $patient_context      = self::getContextPatient($patient, $date);
        $practitioner_context = self::getPratictionerContext($practitioner, $date);

        $grid = null;
        if (
            array_key_exists($practitioner_context, self::$contexts_to_grid)
            && array_key_exists($patient_context, self::$contexts_to_grid[$practitioner_context])
        ) {
            $grid = self::$contexts_to_grid[$practitioner_context][$patient_context];
        } else {
            /* If no grid is found, we used the grid 14, which is for the other specialities,
             for the practitioners with no convention with the CPAM */
            $grid = '14';
        }

        return $grid;
    }

    /**
     * Return the patient's context from the patient object and the date
     *
     * @param CPatient $patient The patient
     * @param string   $date    The date
     *
     * @return integer
     */
    protected static function getContextPatient(CPatient $patient, string $date = null): int
    {
        $date = CMbDT::date($date);

        if ($date < '2018-01-01') {
            $context = 1;
        } elseif ($patient->c2s) {
            $context = 2;
        } elseif ($patient->acs) {
            $context = 4;
        } else {
            $context = 3;
        }

        return $context;
    }

    /**
     * Return the practitioner's context from the mediuser object and the date
     *
     * @param CMediusers $practitioner The practitioner
     * @param string     $date         The date
     *
     * @return integer
     */
    protected static function getPratictionerContext(CMediusers $practitioner, string $date = null): int
    {
        $date = CMbDT::date($date);

        if ($date < '2018-01-01') {
            if (
                $practitioner->secteur == '1'
                || ($practitioner->secteur == '2' && $practitioner->pratique_tarifaire != 'none')
            ) {
                $context = 1;
            } else {
                $context = 2;
            }
        } elseif ($practitioner->ccam_context) {
            $context = $practitioner->ccam_context;
        } else {
            /* Chirurgien ou Gynéco obstétriciens */
            if (
                in_array(
                    $practitioner->spec_cpam_id,
                    [4, 7, 10, 11, 15, 16, 18, 41, 43, 44, 45, 46, 47, 48, 49, 70, 77, 79]
                )
            ) {
                $context = self::getContextsSurgeons($practitioner);
            } elseif ($practitioner->spec_cpam_id == 2 || $practitioner->spec_cpam_id == 20) {
                /* Anesthésistes */
                $context = self::getContextAnesthesists($practitioner);
            } elseif ($practitioner->spec_cpam_id == 12) {
                /* Pédiatres */
                $context = self::getContextPediatrists($practitioner);
            } elseif (in_array($practitioner->spec_cpam_id, [1, 22, 23])) {
                /* Généralistes */
                $context = self::getContextGeneralists($practitioner);
            } elseif (in_array($practitioner->spec_cpam_id, [19, 36, 53, 54])) {
                /* Chirurgiens dentistes */
                $context = self::getContextDentists($practitioner);
            } elseif ($practitioner->spec_cpam_id == 21) {
                /* Sages femmes */
                $context = self::getContextMidwives($practitioner);
            } else {
                /* Autres spécialités */
                $context = self::getContextOtherSpecialities($practitioner);
            }
        }

        return $context;
    }

    /**
     * Return the context for the surgery specialities
     *
     * @param CMediusers $practitioner The practitioner
     *
     * @return int
     */
    protected static function getContextsSurgeons(CMediusers $practitioner): int
    {
        switch ($practitioner->secteur) {
            case '1':
                switch ($practitioner->pratique_tarifaire) {
                    case 'optam':
                        $context = 17;
                        break;
                    case 'optamco':
                        $context = 14;
                        break;
                    default:
                        $context = 32;
                }
                break;
            case '1dp':
                switch ($practitioner->pratique_tarifaire) {
                    case 'optam':
                        $context = 22;
                        break;
                    case 'optamco':
                        $context = 15;
                        break;
                    default:
                        $context = 37;
                }
                break;
            case '2':
                switch ($practitioner->pratique_tarifaire) {
                    case 'optam':
                        $context = 27;
                        break;
                    case 'optamco':
                        $context = 16;
                        break;
                    default:
                        $context = 42;
                }
                break;
            default:
                $context = 47;
        }

        return $context;
    }

    /**
     * Return the context for the anesthesists specialities
     *
     * @param CMediusers $practitioner The practitioner
     *
     * @return int
     */
    protected static function getContextAnesthesists(CMediusers $practitioner): int
    {
        switch ($practitioner->secteur) {
            case '1':
                if ($practitioner->pratique_tarifaire == 'none' || !$practitioner->pratique_tarifaire) {
                    $context = 33;
                } else {
                    $context = 18;
                }
                break;
            case '1dp':
                if ($practitioner->pratique_tarifaire == 'none' || !$practitioner->pratique_tarifaire) {
                    $context = 38;
                } else {
                    $context = 23;
                }
                break;
            case '2':
                if ($practitioner->pratique_tarifaire == 'none' || !$practitioner->pratique_tarifaire) {
                    $context = 43;
                } else {
                    $context = 28;
                }
                break;
            default:
                $context = 48;
        }

        return $context;
    }

    /**
     * Return the context for the pediatrists specialities
     *
     * @param CMediusers $practitioner The practitioner
     *
     * @return int
     */
    protected static function getContextPediatrists(CMediusers $practitioner): int
    {
        switch ($practitioner->secteur) {
            case '1':
                if ($practitioner->pratique_tarifaire == 'none' || !$practitioner->pratique_tarifaire) {
                    $context = 34;
                } else {
                    $context = 19;
                }
                break;
            case '1dp':
                if ($practitioner->pratique_tarifaire == 'none' || !$practitioner->pratique_tarifaire) {
                    $context = 39;
                } else {
                    $context = 24;
                }
                break;
            case '2':
                if ($practitioner->pratique_tarifaire == 'none' || !$practitioner->pratique_tarifaire) {
                    $context = 44;
                } else {
                    $context = 29;
                }
                break;
            default:
                $context = 49;
        }

        return $context;
    }

    /**
     * Return the context for the generalists specialities
     *
     * @param CMediusers $practitioner The practitioner
     *
     * @return int
     */
    protected static function getContextGeneralists(CMediusers $practitioner): int
    {
        switch ($practitioner->secteur) {
            case '1':
                if ($practitioner->pratique_tarifaire == 'none' || !$practitioner->pratique_tarifaire) {
                    $context = 35;
                } else {
                    $context = 20;
                }
                break;
            case '1dp':
                if ($practitioner->pratique_tarifaire == 'none' || !$practitioner->pratique_tarifaire) {
                    $context = 40;
                } else {
                    $context = 25;
                }
                break;
            case '2':
                if ($practitioner->pratique_tarifaire == 'none' || !$practitioner->pratique_tarifaire) {
                    $context = 45;
                } else {
                    $context = 30;
                }
                break;
            default:
                $context = 50;
        }

        return $context;
    }

    /**
     * Return the context for the dentists specialities
     *
     * @param CMediusers $practitioner The practitioner
     *
     * @return int
     */
    protected static function getContextDentists(CMediusers $practitioner): int
    {
        if (!$practitioner->secteur || $practitioner->secteur == 'nc') {
            $context = 11;
        } else {
            $context = 10;
        }

        return $context;
    }

    /**
     * Return the context for the midwives specialities
     *
     * @param CMediusers $practitioner The practitioner
     *
     * @return int
     */
    protected static function getContextMidwives(CMediusers $practitioner): int
    {
        if (!$practitioner->secteur || $practitioner->secteur == 'nc') {
            $context = 13;
        } else {
            $context = 12;
        }

        return $context;
    }

    /**
     * Return the context for the other specialities
     *
     * @param CMediusers $practitioner The practitioner
     *
     * @return int
     */
    protected static function getContextOtherSpecialities(CMediusers $practitioner): int
    {
        switch ($practitioner->secteur) {
            case '1':
                if ($practitioner->pratique_tarifaire == 'none' || !$practitioner->pratique_tarifaire) {
                    $context = 36;
                } else {
                    $context = 21;
                }
                break;
            case '1dp':
                if ($practitioner->pratique_tarifaire == 'none' || !$practitioner->pratique_tarifaire) {
                    $context = 41;
                } else {
                    $context = 26;
                }
                break;
            case '2':
                if ($practitioner->pratique_tarifaire == 'none' || !$practitioner->pratique_tarifaire) {
                    $context = 46;
                } else {
                    $context = 31;
                }
                break;
            default:
                $context = 51;
        }

        return $context;
    }

    /**
     * Return the code of the DOM, depending on the postal code of the given user
     *
     * @param CMediusers $user The user
     *
     * @return int
     */
    public static function getDOMCode(CMediusers $user): int
    {
        $dom         = 0;
        $postal_code = CGroups::loadCurrent()->cp;
        if ($user && $user->_id) {
            $user->loadRefFunction();

            if ($user->_ref_function->cp) {
                $postal_code = $user->_ref_function->cp;
            }
        }

        $postal_code = intval(ceil($postal_code / 100));
        if (in_array($postal_code, [971, 972, 973, 974])) {
            $dom = $postal_code % 10;
        }

        return $dom;
    }
}
