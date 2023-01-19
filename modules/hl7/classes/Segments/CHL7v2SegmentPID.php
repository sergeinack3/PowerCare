<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CGroupDomain;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\CHL7v2TableEntry;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPaysInsee;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2SegmentPID
 * PID - Represents an HL7 PID message segment (Patient Identification)
 */
class CHL7v2SegmentPID extends CHL7v2Segment
{
    /** @var string */
    public $name = "PID";

    /** @var null */
    public $set_id;


    /** @var CPatient */
    public $patient;


    /** @var CSejour */
    public $sejour;

    /**
     * Build PID segement
     *
     * @param CHEvent $event Event
     * @param string  $name  Segment name
     *
     * @return null
     * @throws CHL7v2Exception
     */
    function build(CHEvent $event, $name = null)
    {
        parent::build($event);

        $message  = $event->message;
        $receiver = $event->_receiver;
        $group    = $receiver->loadRefGroup();

        $patient = $this->patient;
        $sejour  = $this->sejour;

        $mother       = null;
        $sejour_maman = null;
        $naissance    = null;
        if ($sejour && $sejour->_id) {
            if ($sejour->_sejour_maman_id) {
                $sejour_maman = new CSejour();
                $sejour_maman->load($sejour->_sejour_maman_id);
                $sejour_maman->loadNDA($group->_id);

                $sejour_maman->loadRefPatient()->loadIPP($group->_id);
                $mother = $sejour_maman->_ref_patient;
            } else {
                $naissance                   = new CNaissance();
                $naissance->sejour_enfant_id = $sejour->_id;
                $naissance->loadMatchingObject();

                $sejour_maman = $naissance->loadRefSejourMaman();
                $sejour_maman->loadNDA($group->_id);

                $sejour_maman->loadRefPatient()->loadIPP($group->_id);
                $mother = $sejour_maman->_ref_patient;
            }
        } elseif ($patient->_naissance && $patient->_sejour_maman_id) {
            $sejour_maman = new CSejour();
            $sejour_maman->load($patient->_sejour_maman_id);
            $sejour_maman->loadNDA($group->_id);

            $sejour_maman->loadRefPatient()->loadIPP($group->_id);
            $mother = $sejour_maman->_ref_patient;
        }

        $data = [];
        // PID-1: Set ID - PID (SI) (optional)
        $data[] = $this->set_id;

        // PID-2: Patient ID (CX) (optional)
        $data[] = null;

        // PID-3: Patient Identifier List (CX) (repeating)
        $data[] = $this->getPersonIdentifiers($patient, $group, $receiver);

        // PID-4: Alternate Patient ID - PID (CX) (optional repeating)
        $data[] = null;

        // PID-5: Patient Name (XPN) (repeating)
        $data[] = $this->getXPN($patient, $receiver);

        // PID-6: Mother's Maiden Name (XPN) (optional repeating)
        if ($patient->nom_jeune_fille && ($receiver->_configs["build_PID_6"] == "nom_naissance")) {
            $anonyme                 = is_numeric($patient->nom);
            $mode_identito_vigilance = $receiver->_configs["mode_identito_vigilance"];
            $prenom                  = CPatient::applyModeIdentitoVigilance(
                $patient->prenom,
                true,
                $mode_identito_vigilance,
                $anonyme
            );
            $nom_jf                  = CPatient::applyModeIdentitoVigilance(
                $patient->nom_jeune_fille,
                true,
                $mode_identito_vigilance,
                $anonyme
            );

            $data[] = [
                [
                    $nom_jf,
                    $prenom,
                    null,
                    null,
                    null,
                    null,
                    null,
                    "L",
                ],
            ];
        } else {
            // Dans le cas d'une naissance on va mettre l'identité de la mère
            $data[] = $mother ? $this->getXPN($mother, $receiver) : null;
        }

        // PID-7: Date/Time of Birth (TS) (optional)
        if ($patient->_naissance_id) {
            $data[] = $naissance->date_time;
        } else {
            if ($patient->naissance) {
                $data[] = CMbDT::isLunarDate($patient->naissance) ? null : $patient->naissance;
            } else {
                $data[] = null;
            }
        }

        // PID-8: Administrative Sex (IS) (optional)
        // Table - 0001
        // F - Female
        // M - Male
        // O - Other
        // U - Unknown
        // A - Ambiguous
        // N - Not applicable
        $sexe   = CHL7v2TableEntry::mapTo("1", $patient->sexe);
        $data[] = $sexe ?: "U";

        // PID-9: Patient Alias (XPN) (optional repeating)
        $data[] = null;

        // PID-10: Race (CE) (optional repeating)
        $data[] = null;

        // PID-11: Patient Address (XAD) (optional repeating)
        $address = [];
        if ($patient->adresse || $patient->ville || $patient->cp) {
            $linesAdress = explode("\n", $patient->adresse, 2);
            $address[]   = $this->addAdress(
                $event,
                CValue::read($linesAdress, 0),
                CValue::read($linesAdress, 1),
                $patient->ville,
                $patient->cp,
                $patient->pays_insee,
                "H"
            );
        }
        if ($receiver->_configs["build_PID_11"] == "simple") {
            $address = [reset($address)];
        } elseif (
            $patient->lieu_naissance || $patient->cp_naissance || $patient->pays_naissance_insee
            || $patient->commune_naissance_insee
        ) {
            $address[] = $this->addAdress(
                $event,
                null,
                null,
                $patient->lieu_naissance,
                $patient->cp_naissance,
                $patient->pays_naissance_insee,
                "BDL",
                $patient->commune_naissance_insee ?: $patient->pays_naissance_insee
            );
        }

        $data[] = $address;

        // PID-12: County Code (IS) (optional)
        $data[] = null;

        // PID-13: Phone Number - Home (XTN) (optional repeating)
        // Table - 0201
        // ASN - Answering Service Number
        // BPN - Beeper Number
        // EMR - Emergency Number
        // NET - Network (email) Address
        // ORN - Other Residence Number
        // PRN - Primary Residence Number
        // VHN - Vacation Home Number
        // WPN - Work Number

        // Table - 0202
        // BP       - Beeper
        // CP       - Cellular Phone
        // FX       - Fax
        // Internet - Internet Address: Use Only If Telecommunication Use Code Is NET
        // MD       - Modem
        // PH       - Telephone
        // TDD      - Telecommunications Device for the Deaf
        // TTY      - Teletypewriter
        $phones = [];
        if ($patient->tel) {
            $phones[] = $this->getXTN($receiver, $patient->tel, "PRN", "PH");
        }
        if ($patient->tel2) {
            // Pour le portable on met soit PRN ou ORN
            $phones[] = $this->getXTN($receiver, $patient->tel2, $receiver->_configs["build_cellular_phone"], "CP");
        }
        if ($patient->tel_autre) {
            $phones[] = $this->getXTN(
                $receiver,
                $patient->tel_autre,
                $receiver->_configs["build_other_residence_number"],
                "PH"
            );
        }
        if ($patient->tel_autre_mobile) {
            $phones[] = $this->getXTN(
                $receiver,
                $patient->tel_autre_mobile,
                $receiver->_configs["build_other_residence_number"],
                "CP"
            );
        }
        if ($patient->tel_pro) {
            $phones[] = $this->getXTN(
                $receiver,
                $patient->tel_pro,
                'WPN',
                "CP"
            );
        }
        if ($patient->email) {
            $phones[] = [
                null,
                // Table - 0201
                "NET",
                // Table - 0202
                "Internet",
                $patient->email,
            ];
        }
        if ($receiver->_configs["build_PID_13"] === "simple") {
            $phones = [reset($phones)];
        }
        $data[] = $phones;

        // PID-14: Phone Number - Business (XTN) (optional repeating)
        $data[] = null;

        // PID-15: Primary Language (CE) (optional)
        $data[] = null;

        // PID-16: Marital Status (CE) (table 0002)(optional)
        $data[] = $patient->situation_famille;

        // PID-17: Religion (CE) (optional)
        $data[] = null;

        // PID-18: Patient Account Number (CX) (optional)
        if ($sejour && $sejour->_id && ($receiver->_configs["build_NDA"] == "PID_18")) {
            switch ($build_PID_18 = $receiver->_configs["build_PID_18"]) {
                case 'normal':
                case 'simple':
                case 'sejour_id':
                    if (!$sejour->_NDA) {
                        $sejour->loadNDA($group->_id);
                    }

                    $NDA = $sejour->_NDA;
                    // Dans le cas d'AppFine, on veut l'identifiant interne quoi qu'il arrive
                    if ((($build_PID_18 == "sejour_id") && !$sejour->_NDA)
                        || (CModule::getActive("appFineClient") && CAppFineClient::loadIdex(
                                $sejour
                            )->_id && $receiver->_configs["send_evenement_to_mbdmp"])
                    ) {
                        $data[] = [
                            [
                                $sejour->_id,
                                null,
                                null,
                                // PID-3-4 Autorité d'affectation
                                $this->getAssigningAuthority("mediboard", null, null, null, $sejour->group_id),
                                "RI",
                            ],
                        ];

                        break;
                    }

                    if (!$sejour->_NDA && !CValue::read($receiver->_configs, "send_not_master_NDA")) {
                        $NDA = "===NDA_MISSING===";
                    }

                    if ($build_PID_18 == "simple") {
                        $data[] = $NDA;
                    } else {
                        // Même traitement que pour l'IPP
                        switch ($receiver->_configs["build_PID_3_4"]) {
                            case 'actor':
                                $assigning_authority = $this->getAssigningAuthority("actor", null, $receiver);
                                break;

                            case 'domain':
                                // Master domain
                                $group_domain               = new CGroupDomain();
                                $group_domain->group_id     = $group->_id;
                                $group_domain->master       = 1;
                                $group_domain->object_class = "CSejour";
                                $group_domain->loadMatchingObject();

                                $domain = $group_domain->loadRefDomain();

                                $assigning_authority = $this->getAssigningAuthority("domain", null, null, $domain);
                                break;

                            default:
                                $assigning_authority = $this->getAssigningAuthority("FINESS", $group->finess);
                                break;
                        }

                        $data[] = $NDA ? [
                            [
                                $NDA,
                                null,
                                null,
                                // PID-3-4 Autorité d'affectation
                                $assigning_authority,
                                "AN",
                            ],
                        ] : null;
                    }
                    break;

                default:
                    $data[] = null;
            }
        } else {
            $data[] = null;
        }

        // PID-19: SSN Number - Patient (ST) (forbidden)
        switch ($receiver->_configs["build_PID_19"]) {
            case 'matricule':
                $data[] = $patient->matricule;
                break;

            default:
                $data[] = null;
                break;
        }

        // PID-20: Driver's License Number - Patient (DLN) (optional)
        $data[] = null;

        // PID-21: Mother's Identifier (CX) (optional repeating)
        // Même traitement que pour l'IPP
        switch ($receiver->_configs["build_PID_3_4"]) {
            case 'actor':
                $assigning_authority = $this->getAssigningAuthority("actor", null, $receiver);
                break;

            default:
                $assigning_authority = $this->getAssigningAuthority("FINESS", $group->finess);
                break;
        }

        if ($mother && $sejour_maman) {
            $identifiers = [];
            if ($mother->_IPP) {
                $identifiers[] = [
                    $mother->_IPP,
                    null,
                    null,
                    // PID-3-4 Autorité d'affectation
                    $assigning_authority,
                    "PI",
                ];
            }
            if ($sejour_maman->_NDA) {
                $identifiers[] = [
                    $sejour_maman->_NDA,
                    null,
                    null,
                    // PID-3-4 Autorité d'affectation
                    $assigning_authority,
                    "AN",
                ];
            }

            $data[] = $identifiers;
        } else {
            $data[] = null;
        }

        // PID-22: Ethnic Group (CE) (optional repeating)
        $data[] = null;

        // PID-23: Birth Place (ST) (optional)
        $data[] = null;

        // PID-24: Multiple Birth Indicator (ID) (optional)
        $data[] = null;

        // PID-25: Birth Order (NM) (optional)
        $data[] = $patient->rang_naissance;

        // PID-26: Citizenship (CE) (optional repeating)
        $data[] = null;

        // PID-27: Veterans Military Status (CE) (optional)
        $data[] = null;

        // PID-28: Nationality (CE) (optional)
        $data[] = null;

        // PID-29: Patient Death Date and Time (TS) (optional)
        $data[] = ($patient->deces) ? $patient->deces : null;

        // PID-30: Patient Death Indicator (ID) (optional)
        $data[] = ($patient->deces) ? "Y" : "N";

        // PID-31: Identity Unknown Indicator (ID) (optional)
        switch ($receiver->_configs["build_PID_31"]) {
            case 'avs':
                $data[] = $patient->avs;
                break;

            default:
                $data[] = null;
                break;
        }

        // PID-32: Identity Reliability Code (IS) (optional repeating)
        // Table   - 0445
        // VIDE    - Identité non encore qualifiée
        // PROV    - Provisoire
        // VALI    - Validé
        // DOUB    - Doublon ou esclave
        // DESA    - Désactivé
        // DPOT    - Doublon potentiel
        // DOUA    - Doublon avéré
        // COLP    - Collision potentielle
        // COLV    - Collision validée
        // FILI    - Filiation
        // CACH    - Cachée
        // ANOM    - Anonyme
        // IDVER   - Identité vérifiée par le patient
        // RECD    - Reçue d'un autre domaine
        // IDRA    - Identité rapprochée dans un autre domaine
        // USUR    - Usurpation
        // HOMD    - Homonyme detecté
        // HOMA    - Homonyme avéré
        // SAppFine - Responsable de son compte sur AppFine

        // On doit obligatoirement mettre PROV / VAlI

        $status = 'PROV';
        if ($patient->status === 'VALI' || $patient->status === 'QUAL') {
            $status = 'VALI';
        }

        $data[] = array_filter(
            [
                $status,
                ($patient->status !== 'PROV' && $patient->status !== 'VALI' &&
                    $patient->status !== 'QUAL' && $patient->status !== 'RECUP') ? $patient->status : null,
                $patient->vip ? "CACH" : null,
                isset($patient->_self_appFine) ? "SAppFine" : null,
                isset($patient->_force_duplicate) ? "DOUA" : null,
            ]
        );

        // PID-33: Last Update Date/Time (TS) (optional)
        $data[] = $event->last_log->date;

        // PID-34: Last Update Facility (HD) (optional)
        $data[] = null;

        // PID-35: Species Code (CE) (optional)
        $data[] = null;

        // PID-36: Breed Code (CE) (optional)
        $data[] = null;

        // PID-37: Strain (ST) (optional)
        $data[] = null;

        // PID-38: Production Class Code (CE) (optional)
        $data[] = null;

        // PID-39: Tribal Citizenship (CWE) (optional repeating)
        $data[] = null;

        $this->fill($data);
    }

    /**
     * Add adress
     *
     * @param CHEvent     $event
     * @param string|null $street_address
     * @param string|null $other_designation
     * @param string|null $city
     * @param string|null $cp
     * @param string|null $country
     * @param string|null $adress_type
     * @param string|null $county
     * @param string|null $effective_date
     * @param string|null $expiration_date
     *
     * @return array
     */
    private function addAdress(
        CHEvent $event,
        string $street_address = null,
        string $other_designation = null,
        string $city = null,
        string $cp = null,
        string $country = null,
        string $adress_type = null,
        string $county = null,
        string $effective_date = null,
        string $expiration_date = null
    ): array {
        $message  = $event->message;
        $receiver = $event->_receiver;

        return [
            // Street Address - Voie de l'adresse
            $street_address,
            // Other Designation - Compléments d'adresse
            str_replace("\n", $message->componentSeparator, $other_designation ?? ''),
            // City - Commune
            ($receiver->_configs["build_fields_format"] == "uppercase") ? CMbString::upper($city) : $city,
            // State or Province - Etat ou province
            null,
            // Zip or Postal Code - Code postal ou code zip
            $cp,
            // Country - Pays
            // Pays INSEE, récupération de l'alpha 3
            CPaysInsee::getAlpha3($country),
            // Address Type - Type d'adresse
            // Table - 0190
            // B   - Firm/Business
            // BA  - Bad address
            // BDL - Birth delivery location (address where birth occurred)
            // BR  - Residence at birth (home address at time of birth)
            // C   - Current Or Temporary
            // F   - Country Of Origin
            // H   - Home
            // L   - Legal Address
            // M   - Mailing
            // N   - Birth (nee) (birth address, not otherwise specified)
            // O   - Office
            // P   - Permanent
            // RH  - Registry home
            $adress_type,
            // Other Geographic Designation
            null,
            // County/Parish Code - Code Officiel Géographique (COG) de la commune
            $county,
            // Census Tract
            null,
            // Address Representation Code
            null,
            // Address Validity Range
            null,
            // Effective Date - Date de début de validité
            $effective_date,
            // Expiration Date - Date de fin de validité
            $expiration_date,
        ];
    }

    /**
     * Fill other identifiers
     *
     * @param array         &$identifiers Identifiers
     * @param CPatient       $patient     Person
     * @param CInteropActor  $actor       Interop actor
     *
     * @return null
     */
    function fillOtherIdentifiers(&$identifiers, CPatient $patient, CInteropActor $actor = null)
    {
        if (CValue::read($actor->_configs, "send_own_identifier")) {
            $identifiers[] = [
                $patient->_id,
                null,
                null,
                // PID-3-4 Autorité d'affectation
                $this->getAssigningAuthority("mediboard", null, null, null, $actor->group_id),
                "RI",
            ];
        }

        if (!CValue::read($actor->_configs, "send_self_identifier")) {
            return;
        }

        if (!$idex_actor = $actor->getIdex($patient)->id400) {
            return;
        }

        $identifiers[] = [
            $idex_actor,
            null,
            null,
            // PID-3-4 Autorité d'affectation
            $this->getAssigningAuthority("actor", null, $actor),
        ];
    }
}
