<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps\Entity;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbString;
use Ox\Import\Rpps\Exception\CImportMedecinException;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSpecCPAM;
use Ox\Mediboard\Patients\CExercicePlace;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CMedecinExercicePlace;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Description
 */
class CPersonneExercice extends CAbstractExternalRppsObject
{
    public const TAG_RPPS_IDENTIFIANT_STRUCTURE = "rpps_identifiant_structure";

    /** @var int */
    public $personne_exercice_id;

    /** @var string */
    public $code_civilite_exercice;

    /** @var string */
    public $libelle_civilite_exercice;

    /** @var string */
    public $code_civilite;

    /** @var string */
    public $libelle_civilite;

    /** @var string */
    public $code_profession;

    /** @var string */
    public $libelle_profession;

    /** @var string */
    public $code_cat_pro;

    /** @var string */
    public $libelle_categorie_pro;

    /** @var string */
    public $code_type_savoir_faire;

    /** @var string */
    public $libelle_type_savoir_faire;

    /** @var string */
    public $code_savoir_faire;

    /** @var string */
    public $libelle_savoir_faire;

    /** @var string */
    public $code_mode_exercice;

    /** @var string */
    public $libelle_mode_exercice;

    /** @var string */
    public $siret_site;

    /** @var string */
    public $siren_site;

    /** @var string */
    public $finess_site;

    /** @var string */
    public $finess_etab_juridique;

    /** @var string */
    public $id_technique_structure;

    /** @var string */
    public $raison_sociale_site;

    /** @var string */
    public $enseigne_comm_site;

    /** @var string */
    public $comp_destinataire;

    /** @var string */
    public $comp_point_geo;

    /** @var string */
    public $num_voie;

    /** @var string */
    public $repetition_voie;

    /** @var string */
    public $code_type_voie;

    /** @var string */
    public $libelle_type_voie;

    /** @var string */
    public $libelle_voie;

    /** @var string */
    public $mention_distrib;

    /** @var string */
    public $cedex;

    /** @var string */
    public $cp;

    /** @var string */
    public $code_commune;

    /** @var string */
    public $libelle_commune;

    /** @var string */
    public $code_pays;

    /** @var string */
    public $libelle_pays;

    /** @var string */
    public $tel;

    /** @var string */
    public $tel2;

    /** @var string */
    public $fax;

    /** @var string */
    public $email;

    /** @var string */
    public $code_departement;

    /** @var string */
    public $libelle_departement;

    /** @var string */
    public $ancien_id_structure;

    /** @var string */
    public $autorite_enregistrement;

    /** @var string */
    public $code_secteur_activite;

    /** @var string */
    public $libelle_secteur_activite;

    /** @var string */
    public $code_section_tableau_pharma;

    /** @var string */
    public $libelle_section_tableau_pharma;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "personne_exercice";
        $spec->key   = 'personne_exercice_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['code_profession']                = 'str notNull';
        $props['libelle_profession']             = 'str notNull';
        $props['code_cat_pro']                   = 'str';
        $props['libelle_categorie_pro']          = 'str';
        $props['code_type_savoir_faire']         = 'str';
        $props['libelle_type_savoir_faire']      = 'str';
        $props['code_savoir_faire']              = 'str';
        $props['libelle_savoir_faire']           = 'str';
        $props['code_civilite_exercice']         = 'str';
        $props['libelle_civilite_exercice']      = 'str';
        $props['code_civilite']                  = 'str';
        $props['libelle_civilite']               = 'str';
        $props['code_mode_exercice']             = 'str';
        $props['libelle_mode_exercice']          = 'str';
        $props['siret_site']                     = 'str';
        $props['siren_site']                     = 'str';
        $props['finess_site']                    = 'str';
        $props['finess_etab_juridique']          = 'str';
        $props['id_technique_structure']         = 'str';
        $props['raison_sociale_site']            = 'str';
        $props['enseigne_comm_site']             = 'str';
        $props['comp_destinataire']              = 'str';
        $props['comp_point_geo']                 = 'str';
        $props['num_voie']                       = 'str';
        $props['repetition_voie']                = 'str';
        $props['code_type_voie']                 = 'str';
        $props['libelle_type_voie']              = 'str';
        $props['libelle_voie']                   = 'str';
        $props['mention_distrib']                = 'str';
        $props['cedex']                          = 'str';
        $props['cp']                             = 'str';
        $props['code_commune']                   = 'str';
        $props['libelle_commune']                = 'str';
        $props['code_pays']                      = 'str';
        $props['libelle_pays']                   = 'str';
        $props['tel']                            = 'str';
        $props['tel2']                           = 'str';
        $props['fax']                            = 'str';
        $props['email']                          = 'str';
        $props['code_departement']               = 'str';
        $props['libelle_departement']            = 'str';
        $props['ancien_id_structure']            = 'str';
        $props['autorite_enregistrement']        = 'str';
        $props['code_secteur_activite']          = 'str';
        $props['libelle_secteur_activite']       = 'str';
        $props['code_section_tableau_pharma']    = 'str';
        $props['libelle_section_tableau_pharma'] = 'str';

        return $props;
    }

    /**
     * @param CMedecin|null $medecin
     *
     * @return string|CMedecin
     * @throws Exception
     */
    public function synchronize(?CMedecin $medecin = null)
    {
        if (!$medecin || !$medecin->_id) {
            $medecin = new CMedecin();

            $ds = $medecin->getDS();

            $where = [
                'nom'    => ($this->nom) ? $ds->prepare('= ?', $this->nom) : 'IS NULL',
                'prenom' => ($this->prenom) ? $ds->prepare('= ?', $this->prenom) : 'IS NULL',
            ];

            if (CAppUI::isCabinet() || CAppUI::isGroup()) {
                $where['group_id']    = 'IS NULL';
                $where['function_id'] = 'IS NULL';
            }

            if ($this->type_identifiant == CAbstractExternalRppsObject::TYPE_IDENTIFIANT_RPPS) {
                $where['rpps'] = ($this->identifiant) ? $ds->prepare('= ?', $this->identifiant) : 'IS NULL';
            }

            $medecin->loadObject($where);
        }

        $medecin->nom    = ($this->nom) ? CMbString::upper($this->nom) : null;
        $medecin->prenom = ($this->prenom) ? CMbString::capitalize(CMbString::lower($this->prenom)) : null;
        if ($this->type_identifiant == CAbstractExternalRppsObject::TYPE_IDENTIFIANT_RPPS) {
            $medecin->rpps = $this->identifiant;
        }

        if ($medecin->_id && empty($medecin->actif)) {
            $medecin->actif = '1';
        }

        $code_civilite_exercice = ($this->code_civilite_exercice) ? CMbString::lower(
            $this->code_civilite_exercice
        ) : CMbString::lower($this->code_civilite);

        $code_civilite_exercice = $code_civilite_exercice === 'mlle' ? 'mme' : $code_civilite_exercice;

        $medecin->titre = $code_civilite_exercice;

        $this->addSex($medecin);
        $this->addSpecCPAM($medecin);

        $profession = $this->libelle_profession ? strtolower($this->libelle_profession) : null;
        if ($profession && in_array($profession, CMedecin::$types)) {
            $medecin->type = $profession;
        }

        $medecin->import_file_version = $this->version;

        return $medecin;
    }

    /**
     * Synchronize a mediuser with a external doctor (CPresonneExercice)
     *
     * @return CMediusers
     * @throws Exception
     */
    public function synchronizeMediuser(): CMediusers
    {
        $mediuser = new CMediusers();

        if ($this->type_identifiant != CAbstractExternalRppsObject::TYPE_IDENTIFIANT_RPPS) {
            return $mediuser;
        }

        $ds = $mediuser->getDS();

        $where = [
            'rpps' => $ds->prepare('= ?', $this->identifiant),
        ];

        if ($mediuser->loadObject($where)) {
            $idex_personne_exercice = CIdSante400::getMatch(
                $mediuser->_class,
                self::TAG_RPPS_IDENTIFIANT_STRUCTURE,
                null,
                $mediuser->_id
            );

            if ($idex_personne_exercice->_id) {
                $mediuser->_user_last_name  = CMbString::upper($this->nom);
                $mediuser->_user_first_name = CMbString::capitalize(CMbString::lower($this->prenom));
                $mediuser->rpps             = $this->identifiant;
                $mediuser->addSex($this);
                $mediuser->addSpecCPAM($this);
                $mediuser->addActivityType($this);
            }
        }

        return $mediuser;
    }

    /**
     * @param CMedecin            $medecin
     * @param CExercicePlace|null $place
     *
     * @return string|CMedecinExercicePlace
     * @throws CImportMedecinException
     * @throws Exception
     */
    public function synchronizeExercicePlace(CMedecin $medecin, ?CExercicePlace $place)
    {
        if (!$medecin->_id) {
            throw new CImportMedecinException('CMedecin must be a valide object');
        }

        $medecin_place = new CMedecinExercicePlace();
        $ds            = $medecin_place->getDS();

        $where = [
            'medecin_id'        => $ds->prepare('= ?', $medecin->_id),
            'exercice_place_id' => ($place && $place->_id) ? $ds->prepare('= ?', $place->_id) : 'IS NULL',
        ];

        $medecin_place->loadObject($where);

        $medecin_place->medecin_id        = $medecin->_id;
        $medecin_place->exercice_place_id = ($place && $place->_id) ? $place->_id : null;

        if ($this->type_identifiant == self::TYPE_IDENTIFIANT_ADELI) {
            $medecin_place->adeli = $this->identifiant;
        }

        $medecin_place->type          = ($this->code_profession) ? CMedecin::$types[$this->code_profession] : null;
        $medecin_place->categorie_pro = ($this->libelle_categorie_pro) ? CMbString::lower(
            $this->libelle_categorie_pro
        ) : null;
        $medecin_place->disciplines   = $this->code_savoir_faire
            ? $this->code_savoir_faire . ' : ' . $this->libelle_savoir_faire
            : null;
        $medecin_place->mode_exercice = $this->getModeExercice($this->code_mode_exercice);

        $medecin_place->rpps_file_version = $this->version;

        if ($msg = $medecin_place->store()) {
            return $msg;
        }

        if ($msg = $this->sanitizeMedecinAddresses($medecin)->store()) {
            return $msg;
        }

        return $medecin_place;
    }

    public function hashIdentifier(): string
    {
        if ($this->siret_site) {
            return md5(CExercicePlace::PREFIX_TYPE_SIRET . $this->siret_site);
        }

        if ($this->siren_site) {
            return md5(CExercicePlace::PREFIX_TYPE_SIREN . $this->siren_site);
        }

        if ($this->id_technique_structure) {
            return md5(CExercicePlace::PREFIX_TYPE_ID_TECHNIQUE . $this->id_technique_structure);
        }

        return md5($this->identifiant_national);
    }

    /**
     * @param CMedecin       $medecin
     * @param CExercicePlace $place
     *
     * @return CExercicePlace|string
     * @throws Exception
     */
    public function updateOrCreatePlace(CExercicePlace $place)
    {
        if (!$this->id_technique_structure) {
            return null;
        }

        $place->rpps_file_version = $this->version;
        $place->siret             = ($this->siret_site) ?: null;
        $place->siren             = ($this->siren_site) ?: null;
        $place->finess            = ($this->finess_site) ?: null;
        $place->finess_juridique  = ($this->finess_etab_juridique) ?: null;
        $place->id_technique      = $this->id_technique_structure;
        $place->raison_sociale    = ($this->raison_sociale_site) ?: null;
        $place->enseigne_comm     = ($this->enseigne_comm_site) ?: null;
        $place->comp_destinataire = ($this->comp_destinataire) ?: null;
        $place->comp_point_geo    = ($this->comp_point_geo) ?: null;
        $place->cp                = ($this->cp) ?: null;
        $place->commune           = ($this->libelle_commune) ?: null;
        $place->code_commune      = ($this->code_commune) ?: null;
        $place->pays              = ($this->libelle_pays) ?: null;
        $place->tel               = ($this->tel) ? $this->sanitizeTel($this->tel) : null;
        $place->tel2              = ($this->tel2) ? $this->sanitizeTel($this->tel2) : null;
        $place->fax               = ($this->fax) ? $this->sanitizeTel($this->fax) : null;
        $place->email             = ($this->email) ?: null;
        $place->departement       = ($this->libelle_departement) ?: null;

        if ($adresse = $this->buildAdresse()) {
            $place->adresse = $adresse;
        }

        if ($msg = $place->store()) {
            return $msg;
        }

        return $place;
    }

    /**
     * @return string
     */
    public function buildAdresse(): ?string
    {
        $adresse = [];
        if ($this->num_voie) {
            $adresse[] = $this->num_voie;
        }

        if ($this->repetition_voie) {
            $adresse[] = $this->repetition_voie;
        }

        if ($this->libelle_type_voie) {
            $adresse[] = $this->libelle_type_voie;
        }

        if ($this->libelle_voie) {
            $adresse[] = $this->libelle_voie;
        }

        if ($this->mention_distrib) {
            $adresse[] = $this->mention_distrib;
        }

        return implode(' ', $adresse) ?: null;
    }

    /**
     * @param CMedecin $medecin
     *
     * @return void
     */
    private function addSex(CMedecin $medecin): void
    {
        if (!$this->code_civilite) {
            return;
        }

        switch (CMbString::lower($this->code_civilite)) {
            case 'm':
                $medecin->sexe = 'm';
                break;
            case 'mme':
            case 'mlle':
                $medecin->sexe = 'f';
                break;
            default:
                $medecin->sexe = 'u';
        }
    }

    private function getModeExercice(?string $mode_exercice): ?string
    {
        if (!$mode_exercice) {
            return null;
        }

        switch (CMbString::lower($mode_exercice)) {
            case 'l':
                return 'liberal';
            case 's':
                return 'salarie';
            case 'b':
                return 'benevole';
            default:
                return null;
        }
    }

    /**
     * @param CMedecin $medecin
     *
     * @return void
     */
    private function addSpecCPAM(CMedecin $medecin): void
    {
        if (!$this->code_profession) {
            return;
        }

        $code = $this->code_savoir_faire != "" ? $this->code_savoir_faire : $this->code_profession;

        $specCPAM              = CSpecCPAM::getMatchingCPAMSpecOfRPPS($code);
        $medecin->spec_cpam_id = $specCPAM->_id ?? null;
    }

    /**
     * @throws Exception
     */
    public function addMSSanteAddress(
        CMedecinExercicePlace $medecin_exercice_place,
        CMedecin $medecin,
        array $addresses
    ): void {
        // tableau destiné à contenir les addresses non affectées à un exercice place
        $unstored_addresses = $addresses;

        // medecin exercice place contenant un exercice place fictif destiné à accueillir les emails mssanté
        $_medecin_exercice_place = $this->synchronizeExercicePlace($medecin, new CExercicePlace());

        /** @var CMssanteInfos $_address */
        foreach ($addresses as $_address) {
            // En premier lieu nous purifions les entrées dans la table, notamment pour supprimer les doublons
            $_address->sanitizeMedecinExercicePlaceMSSante($medecin_exercice_place);
            $_address->sanitizeMedecinExercicePlaceMSSante($_medecin_exercice_place);

            // On vient retirer les emails qui sont déjà affectés à un exercice place
            if ($_address->addressExistsInMedecinExercicePlace($medecin)) {
                CMbArray::removeValue($_address, $unstored_addresses);
                continue;
            }

            // Si l'adresse contient un id structure et qu'il n'a pas déjà été rattaché à un lieu d'exercice
            // alors on le rattache
            if (!empty($_address->id_structure) && $_address->id_structure === $this->finess_etab_juridique) {
                $medecin_exercice_place = $_address->synchronizeMssante($medecin_exercice_place);
            }
        }

        if (!empty($unstored_addresses)) {
            // On rattache les adresses mssanté au lieu d'exercice fictif
            foreach ($unstored_addresses as $_unstored_address) {
                $_unstored_address->synchronizeMssante($_medecin_exercice_place);
            }
        }

        $medecin_exercice_place->store();
        $_medecin_exercice_place->store();
    }

    /**
     * @param string $str
     *
     * @return string|null
     */
    private function sanitizeTel(string $str): ?string
    {
        $str = preg_replace('/\D+/', '', $str);

        if (strlen($str) !== 10) {
            return null;
        }

        return $str;
    }

    /**
     * @param CMedecin $medecin
     *
     * @return CMedecin
     */
    private function sanitizeMedecinAddresses(CMedecin $medecin): CMedecin
    {
        // On retire les anciens champs d'adresse du medecin car ils ne sont plus valide
        if ($medecin->adresse) {
            $medecin->adresse = '';
        }

        if ($medecin->cp) {
            $medecin->cp = '';
        }

        if ($medecin->ville) {
            $medecin->ville = '';
        }

        return $medecin;
    }
}
