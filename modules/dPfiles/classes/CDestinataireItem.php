<?php

/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CMedecinExercicePlace;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\MedecinFieldService;

/**
 * Description
 */
class CDestinataireItem extends CStoredObject
{
    /** @var int */
    public $destinataire_item_id;

    /** @var string */
    public $dest_class;

    /** @var int */
    public $dest_id;

    /** @var int */
    public $medecin_exercice_place_id;

    /** @var string */
    public $tag;

    /** @var int */
    public $docitem_id;

    /** @var string */
    public $docitem_class;

    // References
    /** @var CDocumentItem */
    public $_ref_docitem;

    /** @var  CMbObject */
    public $_ref_destinataire;

    /** @var CLinkDestDispatch[] */
    public $_ref_dispatches;

    // Form fields
    /** @var string */
    public $_nom;

    /** @var string */
    public $_adresse;

    /** @var string */
    public $_cp;

    /** @var string */
    public $_ville;

    /** @var string */
    public $_pays;

    /** @var string */
    public $_email;

    /** @var string */
    public $_email_apicrypt;

    /** @var string */
    public $_email_mssante;

    /** @var string */
    public $_tag;

    /** @var string */
    public $_docitem_guid;

    /** @var string */
    public $_destinataire_guid;

    /** @var CMedecinExercicePlace */
    public $_ref_medecin_exercice_place;

    /** @var string[] */
    public static $tags = [
        'assurance',
        'assure',
        'autre',
        'correspondant',
        'employeur',
        'ìnconnu',
        'other_prat',
        'patient',
        'praticien',
        'prevenir',
        'traitant',
    ];

    /**
     * @inheritDoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "destinataire_item";
        $spec->key   = "destinataire_item_id";

        return $spec;
    }

    /**
     * @inheritDoc
     */
    public function getProps()
    {
        $props                  = parent::getProps();
        $props["dest_class"]    = "enum list|CCorrespondantPatient|CMedecin|CPatient notNull";
        $props["dest_id"]       = "ref notNull class|CMbObject meta|dest_class back|dest_items";
        $props['medecin_exercice_place_id'] = 'ref class|CMedecinExercicePlace back|dest_items';
        $props["tag"]           = "enum list|assurance|assure|autre|correspondant|employeur|ìnconnu|"
            . "other_prat|patient|praticien|prevenir|traitant";
        $props["docitem_class"] = "enum list|CCompteRendu|CFile notNull";
        $props["docitem_id"]    = "ref notNull class|CMbObject meta|docitem_class back|destinataires cascade";

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();

        $this->_docitem_guid      = "$this->docitem_class-$this->docitem_id";
        $this->_destinataire_guid = "$this->dest_class-$this->dest_id";
    }

    /**
     * Set the receiver and the tag according to the receiver's class
     *
     * @param CMbObject $receiver The receiver
     * @param string    $tag      An optional tag (if not given, the tag will be set according to the class of the
     *                            receiver)
     *
     * @return void
     */
    public function setReceiver(CMbObject $receiver, string $tag = null): void
    {
        $this->dest_class = $receiver->_class;
        $this->dest_id    = $receiver->_id;

        if ($tag) {
            $this->tag = $tag;
        } else {
            switch ($receiver->_class) {
                case 'CMedecin':
                    $this->tag = 'other_prat';
                    break;
                case 'CPatient':
                    $this->tag = 'patient';
                    break;
                case 'CCorrespondantPatient':
                    if (in_array($receiver->relation, self::$tags)) {
                        $this->tag = $receiver->relation;
                    } else {
                        $this->tag = 'autre';
                    }
                    break;
                default:
                    $this->tag = 'autre';
            }
        }
    }

    /**
     * Charge le destinataire
     *
     * @return CCorrespondantPatient|CMedecin|CPatient
     */
    public function loadRefDestinataire(): CMbObject
    {
        return $this->_ref_destinataire = $this->loadFwdRef("dest_id", true);
    }

    /**
     * Charge l'item documentaire
     *
     * @return CCompteRendu|CFile
     */
    public function loadRefDocItem(): CDocumentItem
    {
        return $this->_ref_docitem = $this->loadFwdRef("docitem_id", true);
    }

    /**
     * Charge les infos (adresse, nom) en fonction du destinataire
     */
    public function loadInfos(): void
    {
        $destinataire = $this->loadRefDestinataire();

        switch (get_class($destinataire)) {
            case CCorrespondantPatient::class:
            case CMedecin::class:
                $medecin_service = null;

                if (get_class($destinataire) === CMedecin::class) {
                    $medecin_service = new MedecinFieldService(
                        $destinataire,
                        $this->loadRefMedecinExercicePlace()
                    );
                }

                $this->_nom     = "$destinataire->prenom $destinataire->nom";
                $this->_adresse = $medecin_service ? $medecin_service->getAdresse() : $destinataire->adresse;
                $this->_cp      = $medecin_service ? $medecin_service->getCP() : $destinataire->cp;
                $this->_ville   = $medecin_service ? $medecin_service->getVille() : $destinataire->ville;
                $this->_email   = $destinataire->email;
                if ($this->_class == "CMedecin") {
                    $this->_email_apicrypt = $destinataire->email_apicrypt;
                }

                switch ($this->tag) {
                    case "prevenir":
                        $this->_tag = "Personne à prévenir";
                        break;
                    default:
                }

                if ($destinataire instanceof CMedecin) {
                    $this->_email_apicrypt = $destinataire->email_apicrypt;
                    $this->_email_mssante  = $destinataire->mssante_address;
                    $this->_tag            = $this->tag == "traitant" ? "Médecin traitant" : "Médecin correspondant";
                }
                break;
            case CPatient::class:
                if ($this->tag == "assure") {
                    $this->_nom     = "$destinataire->assure_prenom $destinataire->assure_nom";
                    $this->_adresse = $destinataire->assure_adresse;
                    $this->_cp      = $destinataire->assure_cp;
                    $this->_ville   = $destinataire->assure_ville;
                    $this->_pays    = $destinataire->assure_pays;
                    $this->_tag     = "Assuré";
                } else {
                    $this->_nom     = "$destinataire->prenom $destinataire->nom";
                    $this->_adresse = $destinataire->adresse;
                    $this->_cp      = $destinataire->cp;
                    $this->_ville   = $destinataire->ville;
                    $this->_email   = $destinataire->email;
                    $this->_pays    = $destinataire->pays;
                    $this->_tag     = "Patient";
                }
                break;

            default:
        }
    }

    /**
     * Charge les envois précédents pour ce destinataire
     */
    public function loadRefsDispatches(): void
    {
        $this->_ref_dispatches = $this->loadBackRefs(
            "links_dispatches",
            "link_dest_dispatch.link_dest_dispatch_id DESC"
        );

        if (count($this->_ref_dispatches)) {
            /** @var CLinkDestDispatch $_dispatch */
            foreach ($this->_ref_dispatches as $_dispatch) {
                $_dispatch->loadRefDispatch();
            }
        }
    }

    public function loadRefMedecinExercicePlace(): CMedecinExercicePlace
    {
        return $this->_ref_medecin_exercice_place = $this->loadFwdRef('medecin_exercice_place_id', true);
    }
}
