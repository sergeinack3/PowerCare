<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Transformer;

use Exception;
use Ox\Core\CMbDT;
use Ox\Import\Framework\Entity\ActeCCAM;
use Ox\Import\Framework\Entity\ActeNGAP;
use Ox\Import\Framework\Entity\Affectation;
use Ox\Import\Framework\Entity\Antecedent;
use Ox\Import\Framework\Entity\CImportCampaign;
use Ox\Import\Framework\Entity\Constante;
use Ox\Import\Framework\Entity\Consultation;
use Ox\Import\Framework\Entity\ConsultationAnesth;
use Ox\Import\Framework\Entity\Correspondant;
use Ox\Import\Framework\Entity\DossierMedical;
use Ox\Import\Framework\Entity\EvenementPatient;
use Ox\Import\Framework\Entity\ExternalReference;
use Ox\Import\Framework\Entity\ExternalReferenceStash;
use Ox\Import\Framework\Entity\File;
use Ox\Import\Framework\Entity\Injection;
use Ox\Import\Framework\Entity\Medecin;
use Ox\Import\Framework\Entity\Operation;
use Ox\Import\Framework\Entity\Patient;
use Ox\Import\Framework\Entity\PlageConsult;
use Ox\Import\Framework\Entity\Sejour;
use Ox\Import\Framework\Entity\Traitement;
use Ox\Import\Framework\Entity\User;
use Ox\Import\Framework\Entity\Vaccination;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CPrestation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\ObservationResult\CObservationAbnormalFlag;
use Ox\Mediboard\ObservationResult\CObservationIdentifier;
use Ox\Mediboard\ObservationResult\CObservationResponsibleObserver;
use Ox\Mediboard\ObservationResult\CObservationResult;
use Ox\Mediboard\ObservationResult\CObservationResultExamen;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\ObservationResult\CObservationResultValue;
use Ox\Mediboard\ObservationResult\CObservationValueUnit;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationAbnormalFlag;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationExam;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationFile;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationIdentifier;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationPatient;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationResponsible;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationResult;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationResultSet;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationResultValue;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationValueUnit;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CCorrespondant;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CTraitement;
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\SalleOp\CActeCCAM;

class DefaultTransformer extends AbstractTransformer
{
    /** @var array */
    private static $object_cache = [];

    /**
     * @inheritDoc
     */
    public function transformUser(
        User $external_user,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CUser {
        $user = new CUser();

        $user->user_username   = $external_user->getUsername();
        $user->user_first_name = $external_user->getFirstName();
        $user->user_last_name  = $external_user->getLastName();
        $user->user_sexe       = $external_user->getGender();
        $user->user_birthday   = $this->formatDateTimeToStrDate($external_user->getBirthday());
        $user->user_email      = $external_user->getEmail();
        $user->user_phone      = $external_user->getPhone();
        $user->user_mobile     = $external_user->getMobile();
        $user->user_address1   = $external_user->getAddress();
        $user->user_zip        = $external_user->getZip();
        $user->user_city       = $external_user->getCity();
        $user->user_country    = $external_user->getCountry();

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function transformPatient(
        Patient $external_patient,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CPatient {
        /** @var CPatient $patient */
        $patient = $external_patient->getMbObject() ?? new CPatient();

        $patient->nom                    = $external_patient->getNom() ?? null;
        $patient->prenom                 = $external_patient->getPrenom() ?? null;
        $patient->deces                  = $this->formatDateTimeToStr($external_patient->getDeces()) ?? null;
        $patient->naissance              = $this->formatDateTimeToStrDate($external_patient->getNaissance()) ?? null;
        $patient->cp_naissance           = $external_patient->getCpNaissance() ?? null;
        $patient->lieu_naissance         = $external_patient->getLieuNaissance() ?? null;
        $patient->nom_jeune_fille        = $external_patient->getNomJeuneFille() ?? null;
        $patient->profession             = $external_patient->getProfession() ?? null;
        $patient->email                  = $external_patient->getEmail() ?? null;
        $patient->tel                    = $this->sanitizeTel($external_patient->getTel()) ?? null;
        $patient->tel2                   = $this->sanitizeTel($external_patient->getTel2()) ?? null;
        $patient->tel_autre              = $this->sanitizeTel($external_patient->getTelAutre()) ?? null;
        $patient->adresse                = $external_patient->getAdresse() ?? null;
        $patient->cp                     = $external_patient->getCp() ?? null;
        $patient->ville                  = $external_patient->getVille() ?? null;
        $patient->pays                   = $external_patient->getPays() ?? null;
        $patient->matricule              = $external_patient->getMatricule() ?? null;
        $patient->sexe                   = $external_patient->getSexe() ?? null;
        $patient->civilite               = $external_patient->getCivilite() ?? null;
        $patient->situation_famille      = $external_patient->getSituationFamille() ?? null;
        $patient->activite_pro           = $external_patient->getActivitePro() ?? null;
        $patient->rques                  = $external_patient->getRques() ?? null;
        $patient->ald                    = $external_patient->getAld() ?? null;
        $patient->_IPP                   = $external_patient->getIpp() ?? null;
        $patient->assure_nom             = $external_patient->getNomAssure() ?? null;
        $patient->assure_prenom          = $external_patient->getPrenomAssure() ?? null;
        $patient->assure_nom_jeune_fille = $external_patient->getNomNaissanceAssure() ?? null;
        $patient->assure_sexe            = $external_patient->getSexeAssure() ?? null;
        $patient->assure_civilite        = $external_patient->getCiviliteAssure() ?? null;
        $patient->assure_naissance       =
            $this->formatDateTimeToStrDate(
                $external_patient->getNaissanceAssure()
            ) ?? null;
        $patient->assure_adresse         = $external_patient->getAdresseAssure() ?? null;
        $patient->assure_ville           = $external_patient->getVilleAssure() ?? null;
        $patient->assure_cp              = $external_patient->getCpAssure() ?? null;
        $patient->assure_pays            = $external_patient->getPaysAssure() ?? null;
        $patient->assure_tel             = $this->sanitizeTel($external_patient->getTelAssure()) ?? null;

        $patient->assure_matricule = $external_patient->getMatriculeAssure() ?? null;

        $patient->medecin_traitant =
            $reference_stash->getMbIdByExternalId('medecin', $external_patient->getMedecinTraitant());

        if ($patient->sexe === 'u') {
            $patient->guessSex();
            if ($patient->sexe === 'u') {
                $patient->sexe = null;
            }
        }

        return $patient;
    }

    /**
     * @inheritDoc
     */
    public function transformMedecin(
        Medecin $external_medecin,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CMedecin {
        $medecin = $external_medecin->getMbObject() ?? new CMedecin();

        $medecin->nom         = $external_medecin->getNom() ?? null;
        $medecin->prenom      = $external_medecin->getPrenom() ?? null;
        $medecin->titre       = $external_medecin->getTitre() ?? null;
        $medecin->email       = $external_medecin->getEmail() ?? null;
        $medecin->tel         = $this->sanitizeTel($external_medecin->getTel()) ?? null;
        $medecin->tel_autre   = $this->sanitizeTel($external_medecin->getTelAutre()) ?? null;
        $medecin->adresse     = $external_medecin->getAdresse() ?? null;
        $medecin->cp          = $external_medecin->getCp() ?? null;
        $medecin->ville       = $external_medecin->getVille() ?? null;
        $medecin->disciplines = $external_medecin->getDisciplines() ?? null;
        $medecin->sexe        = $external_medecin->getSexe() ?? null;
        $medecin->rpps        = $external_medecin->getRpps() ?? null;
        $medecin->adeli       = $external_medecin->getAdeli() ?? null;

        return $medecin;
    }

    /**
     * @inheritDoc
     */
    public function transformPlageConsult(
        PlageConsult $external_plage_consult,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CPlageconsult {
        $plage_consult = $external_plage_consult->getMbObject() ?? new CPlageconsult();

        // Never change the date of a CPlageconsult
        if (!$plage_consult->_id) {
            $plage_consult->date  = $this->formatDateTimeToStrDate($external_plage_consult->getDate()) ?? null;
            $plage_consult->debut = $this->formatDateTimeToStrTime($external_plage_consult->getDebut()) ?? null;
            $plage_consult->fin   = $this->formatDateTimeToStrTime($external_plage_consult->getFin()) ?? null;
        }

        $plage_consult->freq    = $this->formatDateTimeToStrTime($external_plage_consult->getFreq()) ?? null;
        $plage_consult->libelle = $external_plage_consult->getLibelle() ?? null;

        $plage_consult->chir_id = $reference_stash->getMbIdByExternalId(
            'utilisateur',
            $external_plage_consult->getChirId()
        );

        return $plage_consult;
    }

    /**
     * @inheritDoc
     */
    public function transformConsultation(
        Consultation $external_consultation,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CConsultation {
        $consultation = $external_consultation->getMbObject() ?? new CConsultation();

        $consultation->heure            = $this->formatDateTimeToStrTime($external_consultation->getHeure()) ?? null;
        $consultation->duree            = $external_consultation->getDuree() ?? null;
        $consultation->motif            = $external_consultation->getMotif() ?? null;
        $consultation->rques            = $external_consultation->getRques() ?? null;
        $consultation->examen           = $external_consultation->getExamen() ?? null;
        $consultation->traitement       = $external_consultation->getTraitement() ?? null;
        $consultation->histoire_maladie = $external_consultation->getHistoireMaladie() ?? null;
        $consultation->conclusion       = $external_consultation->getConclusion() ?? null;
        $consultation->resultats        = $external_consultation->getResultats() ?? null;
        $consultation->chrono           = $external_consultation->getChrono() ?? null;

        $plage_consult_type
            = ($external_consultation->getDefaultRefs()) ? 'plage_consultation' : 'plage_consultation_autre';

        $consultation->plageconsult_id =
            $reference_stash->getMbIdByExternalId($plage_consult_type, $external_consultation->getPlageconsultId());

        $consultation->patient_id =
            $reference_stash->getMbIdByExternalId('patient', $external_consultation->getPatientId());

        return $consultation;
    }

    /**
     * @inheritDoc
     */
    public function transformConsultationAnesth(
        ConsultationAnesth $external_consultation,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CConsultAnesth {
        // TODO: Implement transformConsultationAnesth() method.
        return new CConsultAnesth();
    }

    /**
     * @inheritDoc
     */
    public function transformSejour(
        Sejour $external_sejour,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CSejour {
        $sejour = $external_sejour->getMbObject() ?? new CSejour();

        $sejour->type          = $external_sejour->getType() ?? null;
        $sejour->libelle       = $external_sejour->getLibelle() ?? null;
        $sejour->entree_prevue = $this->formatDateTimeToStr($external_sejour->getEntreePrevue()) ?? null;
        $sejour->entree_reelle = $this->formatDateTimeToStr($external_sejour->getEntreeReelle()) ?? null;
        $sejour->sortie_prevue = $this->formatDateTimeToStr($external_sejour->getSortiePrevue()) ?? null;
        $sejour->sortie_reelle = $this->formatDateTimeToStr($external_sejour->getSortieReelle()) ?? null;

        $sejour->patient_id   = $reference_stash->getMbIdByExternalId('patient', $external_sejour->getPatientId());
        $sejour->praticien_id = $reference_stash->getMbIdByExternalId(
            'utilisateur',
            $external_sejour->getPraticienId()
        );
        $sejour->_NDA         = $external_sejour->getNda() ?? null;

        // prestation
        if ($prestation = $this->getPrestation($external_sejour->getPrestation())) {
            $sejour->prestation_id = $prestation->_id;
        }

        // mode de traitement
        if ($cpi = $this->getModeTraitement($external_sejour->getModeTraitement())) {
            $sejour->charge_id = $cpi->_id;
            $sejour->type      = $cpi->type;
            $sejour->type_pec  = $cpi->type_pec;
        } else {
            $sejour->type = "comp";
        }

        // mode entree
        if ($mes = $this->getModeEntree($external_sejour->getModeEntree())) {
            $sejour->mode_entree_id = $mes->_id;
            $sejour->mode_entree    = $mes->mode;
        }

        // mode sortie
        if ($mss = $this->getModeSortie($external_sejour->getModeSortie())) {
            $sejour->mode_sortie_id = $mss->_id;
            $sejour->mode_sortie    = $mss->mode;
        }

        // Todo: Try to remove
        $sejour->group_id = CGroups::loadCurrent()->_id;

        return $sejour;
    }

    /**
     * Get prestation object by name
     *
     * @param string|null $code
     *
     * @return CPrestation|null
     */
    protected function getPrestation(?string $code): ?CPrestation
    {
        if ($code === null) {
            return null;
        }

        if (!isset(static::$object_cache[CPrestation::class][$code])) {
            $presta           = new CPrestation();
            $presta->code     = $code;
            $presta->group_id = CGroups::loadCurrent()->_id;

            static::$object_cache[CPrestation::class][$code] = $presta->loadMatchingObjectEsc() ? $presta : null;
        }

        return static::$object_cache[CPrestation::class][$code];
    }

    /**
     * Get mode de traitement object by name
     *
     * @param string|null $code
     *
     * @return CChargePriceIndicator|null
     */
    protected function getModeTraitement(?string $code): ?CChargePriceIndicator
    {
        if ($code === null) {
            return null;
        }

        if (!isset(static::$object_cache[CChargePriceIndicator::class][$code])) {
            $cpi           = new CChargePriceIndicator();
            $cpi->code     = $code;
            $cpi->group_id = CGroups::loadCurrent()->_id;
            $cpi->actif    = 1;

            static::$object_cache[CChargePriceIndicator::class][$code] = $cpi->loadMatchingObjectEsc() ? $cpi : null;
        }

        return static::$object_cache[CChargePriceIndicator::class][$code];
    }

    /**
     * Get ode d'entrée object by name
     *
     * @param string|null $code
     *
     * @return CModeEntreeSejour|null
     */
    protected function getModeEntree(?string $code): ?CModeEntreeSejour
    {
        if ($code === null) {
            return null;
        }

        if (!isset(static::$object_cache[CModeEntreeSejour::class][$code])) {
            $mes           = new CModeEntreeSejour();
            $mes->code     = $code;
            $mes->group_id = CGroups::loadCurrent()->_id;
            $mes->actif    = 1;

            static::$object_cache[CModeEntreeSejour::class][$code] = $mes->loadMatchingObjectEsc() ? $mes : null;
        }

        return static::$object_cache[CModeEntreeSejour::class][$code];
    }

    /**
     * Get mode de sortie object by name
     *
     * @param string|null $code
     *
     * @return CModeSortieSejour|null
     */
    protected function getModeSortie(?string $code): ?CModeSortieSejour
    {
        if ($code === null) {
            return null;
        }

        if (!isset(static::$object_cache[CModeSortieSejour::class][$code])) {
            $mss           = new CModeSortieSejour();
            $mss->code     = $code;
            $mss->group_id = CGroups::loadCurrent()->_id;
            $mss->actif    = 1;

            static::$object_cache[CModeSortieSejour::class][$code] = $mss->loadMatchingObjectEsc() ? $mss : null;
        }

        return static::$object_cache[CModeSortieSejour::class][$code];
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function transformFile(
        File $external_file,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CFile {
        $file = $external_file->getMbObject() ?? new CFile();

        $file->file_date = $this->formatDateTimeToStr($external_file->getFileDate()) ?? null;
        $file->file_name = $external_file->getFileName() ?? null;
        $file->file_type = $external_file->getFileType() ?? null;

        if ($external_file->getFileContent()) {
            $file->setContent($external_file->getFileContent());
        } elseif ($external_file->getFilePath()) {
            $file->setCopyFrom($external_file->getFilePath());
        }

        if ($reference_stash && $external_file->getAuthorId()) {
            $file->author_id = $reference_stash->getMbIdByExternalId('utilisateur', $external_file->getAuthorId());
        }

        if (!$file->author_id) {
            $file->author_id = CMediusers::get()->_id;
        }

        if ($sejour_id = $this->getContextId($external_file->getSejourId(), 'sejour', $reference_stash)) {
            $file->object_class = 'CSejour';
            $file->object_id    = $sejour_id;
        } elseif (
            $consult_id = $this->getContextId(
                $external_file->getConsultationId(),
                $external_file->getDefaultRefs() ? 'consultation' : 'consultation_autre',
                $reference_stash
            )
        ) {
            $file->object_class = 'CConsultation';
            $file->object_id    = $consult_id;
        } elseif ($event_id = $this->getEvenementId($external_file, $reference_stash)) {
            $file->object_class = 'CEvenementPatient';
            $file->object_id    = $event_id;
        } elseif ($patient_id = $external_file->getPatientId()) {
            $file->object_class = 'CPatient';
            $file->object_id    = $reference_stash->getMbIdByExternalId('patient', $patient_id);
        }

        if ($cat_name = $external_file->getFileCatName()) {
            $file->file_category_id = $this->getCategorie($cat_name);
        }

        $file->fillFields();
        $file->updateFormFields();

        return $file;
    }

    protected function getCategorie(string $cat_name): ?int
    {
        $cat      = new CFilesCategory();
        $cat->nom = $cat_name;
        $cat->loadMatchingObjectEsc();

        return $cat->_id;
    }

    /**
     * @inheritDoc
     */
    public function transformAntecedent(
        Antecedent $external_atcd,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CAntecedent {
        $atcd = $external_atcd->getMbObject() ?? new CAntecedent();

        $patient_id = $reference_stash->getMbIdByExternalId('patient', $external_atcd->getPatientId());

        if ($patient_id) {
            $atcd->rques   = $external_atcd->getText() ?? null;
            $atcd->comment = $external_atcd->getComment() ?? null;
            $atcd->date    = ($atcd->_id) ? null : CMbDT::date();
            if ($date = $external_atcd->getDate()) {
                $atcd->date = $this->formatDateTimeToStrDate($date);
            }

            $atcd->type     = $external_atcd->getType() ?? null;
            $atcd->appareil = $external_atcd->getAppareil() ?? null;

            if ($user_id = $reference_stash->getMbIdByExternalId('utilisateur', $external_atcd->getOwnerId())) {
                $atcd->owner_id = $user_id;
            }

            $atcd->dossier_medical_id = CDossierMedical::dossierMedicalId($patient_id, 'CPatient');
        }

        return $atcd;
    }

    /**
     * @inheritDoc
     */
    public function transformTraitement(
        Traitement $external_trt,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CTraitement {
        $trt = $external_trt->getMbObject() ?? new CTraitement();

        $patient_id = $reference_stash->getMbIdByExternalId('patient', $external_trt->getPatientId());

        if ($patient_id) {
            $trt->traitement = $external_trt->getTraitement() ?? null;
            $trt->debut      = ($trt->_id) ? null : CMbDT::date();
            if ($debut = $external_trt->getDebut()) {
                $trt->debut = $this->formatDateTimeToStrDate($debut);
            }

            if ($fin = $external_trt->getFin()) {
                $trt->fin = $this->formatDateTimeToStrDate($fin);
            }

            if ($user_id = $reference_stash->getMbIdByExternalId('utilisateur', $external_trt->getOwnerId())) {
                $trt->owner_id = $user_id;
            }

            $trt->dossier_medical_id = CDossierMedical::dossierMedicalId($patient_id, 'CPatient');
        }

        return $trt;
    }

    /**
     * @inheritDoc
     */
    public function transformCorrespondant(
        Correspondant $external_correspondant,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CCorrespondant {
        $correspondant = $external_correspondant->getMbObject() ?? new CCorrespondant();

        $correspondant->patient_id =
            $reference_stash->getMbIdByExternalId('patient', $external_correspondant->getPatientId());
        $correspondant->medecin_id =
            $reference_stash->getMbIdByExternalId('medecin', $external_correspondant->getMedecinId());

        if ($correspondant->patient_id && $correspondant->medecin_id) {
            $correspondant->loadMatchingObjectEsc();
        }

        return $correspondant;
    }

    /**
     * @inheritDoc
     */
    public function transformEvenementPatient(
        EvenementPatient $external_patient_event,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CEvenementPatient {
        /** @var CEvenementPatient $patient_event */
        $patient_event = $external_patient_event->getMbObject() ?? new CEvenementPatient();

        $patient_event->dossier_medical_id = CDossierMedical::dossierMedicalId(
            $reference_stash->getMbIdByExternalId(
                ExternalReference::PATIENT,
                $external_patient_event->getPatientId()
            ),
            'CPatient'
        );
        $patient_event->praticien_id       = $reference_stash->getMbIdByExternalId(
            ExternalReference::UTILISATEUR,
            $external_patient_event->getPractitionerId()
        );
        $patient_event->date               = $this->formatDateTimeToStr(
            $external_patient_event->getDatetime()
        );
        $patient_event->libelle            = $external_patient_event->getLabel();
        $patient_event->type               = $external_patient_event->getType();
        $patient_event->description        = $external_patient_event->getDescription();

        return $patient_event;
    }

    /**
     * @inheritDoc
     */
    public function transformInjection(
        Injection $external_injection,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CInjection {
        $injection = $external_injection->getMbObject() ?? new CInjection();

        $injection->patient_id        =
            $reference_stash->getMbIdByExternalId('patient', $external_injection->getPatientId());
        $injection->practitioner_name = $external_injection->getPractitionerName() ?? null;
        $injection->injection_date    = $this->formatDateTimeToStr($external_injection->getInjectionDate()) ?? null;
        $injection->batch             = $external_injection->getBatch() ?? null;
        $injection->speciality        = $external_injection->getSpeciality() ?? null;
        $injection->remarques         = $external_injection->getRemarques() ?? null;
        $injection->cip_product       = $external_injection->getCipProduct() ?? null;

        if ($expiration_date = $external_injection->getExpirationDate()) {
            $injection->expiration_date = $this->formatDateTimeToStrDate($expiration_date);
        }

        $injection->recall_age   = $external_injection->getRecallAge() ?? null;
        $injection->_type_vaccin = $external_injection->getTypeVaccin() ?? "Autre";

        return $injection;
    }

    /**
     * @inheritDoc
     */
    public function transformActeCCAM(
        ActeCCAM $external_acte,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CActeCCAM {
        $acte_ccam = $external_acte->getMbObject() ?? new CActeCCAM();

        $acte_ccam->code_acte           = $external_acte->getCodeActe() ?? null;
        $acte_ccam->execution           = ($datetime = $external_acte->getDateExecution()) ? $this->formatDateTimeToStr(
            $datetime
        ) : null;
        $acte_ccam->code_activite       = $external_acte->getCodeActivite() ?? null;
        $acte_ccam->code_phase          = $external_acte->getCodePhase() ?? null;
        $acte_ccam->modificateurs       = $external_acte->getModificateurs() ?? null;
        $acte_ccam->montant_base        = $external_acte->getMontantBase() ?? null;
        $acte_ccam->montant_depassement = $external_acte->getMontantDepassement() ?? null;

        $acte_ccam->executant_id =
            $reference_stash->getMbIdByExternalId('utilisateur', $external_acte->getExecutantId());

        $acte_ccam->object_class = 'CConsultation';
        $acte_ccam->object_id    =
            $reference_stash->getMbIdByExternalId('consultation', $external_acte->getConsultationId());

        return $acte_ccam;
    }

    /**
     * @inheritDoc
     */
    public function transformActeNGAP(
        ActeNGAP $external_acte,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CActeNGAP {
        $acte_ngap = $external_acte->getMbObject() ?? new CActeNGAP();

        $acte_ngap->code                = $external_acte->getCodeActe() ?? null;
        $acte_ngap->execution           = ($datetime = $external_acte->getDateExecution()) ? $this->formatDateTimeToStr(
            $datetime
        ) : null;
        $acte_ngap->quantite            = $external_acte->getQuantite() ?? null;
        $acte_ngap->coefficient         = $external_acte->getCoefficient() ?? null;
        $acte_ngap->montant_base        = $external_acte->getMontantBase() ?? null;
        $acte_ngap->montant_depassement = $external_acte->getMontantDepassement() ?? null;
        $acte_ngap->numero_dent         = $external_acte->getNumeroDent() ?? null;

        $acte_ngap->executant_id =
            $reference_stash->getMbIdByExternalId('utilisateur', $external_acte->getExecutantId());

        $acte_ngap->object_class = 'CConsultation';
        $acte_ngap->object_id    =
            $reference_stash->getMbIdByExternalId('consultation', $external_acte->getConsultationId());

        return $acte_ngap;
    }

    /**
     * @inheritDoc
     */
    public function transformConstante(
        Constante $external_constante,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CConstantesMedicales {
        /** @var CConstantesMedicales $constante */
        $constante = $external_constante->getMbObject() ?? new CConstantesMedicales();

        $patient_id = $reference_stash->getMbIdByExternalId('patient', $external_constante->getPatientId());
        $user_id    = $reference_stash->getMbIdByExternalId('utilisateur', $external_constante->getUserId());

        $constante->datetime            = $this->formatDateTimeToStr($external_constante->getDatetime())
            ?? CMbDT::dateTime();
        $constante->patient_id          = $patient_id;
        $constante->user_id             = $user_id;
        $constante->poids               = $external_constante->getPoids();
        $constante->taille              = $external_constante->getTaille();
        $constante->pouls               = $external_constante->getPouls();
        $constante->temperature         = $external_constante->getTemperature();
        $constante->_ta_droit_systole   = $external_constante->getTaDroitSystole();
        $constante->_ta_droit_diastole  = $external_constante->getTaDroitDiastole();
        $constante->_ta_gauche_systole  = $external_constante->getTaGaucheSystole();
        $constante->_ta_gauche_diastole = $external_constante->getTaGaucheDiastole();
        $constante->pointure            = $external_constante->getPointure();

        return $constante;
    }

    /**
     * @inheritDoc
     */
    public function transformDossierMedical(
        DossierMedical $external_dossier,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CDossierMedical {
        $dossier = $external_dossier->getMbObject() ?? new CDossierMedical();

        $patient_id            = $reference_stash->getMbIdByExternalId('patient', $external_dossier->getPatientId());
        $dossier->object_class = 'CPatient';
        $dossier->object_id    = $patient_id;

        $dossier->groupe_sanguin = $external_dossier->getGroupSanguin();
        $dossier->rhesus         = $external_dossier->getRhesus();

        return $dossier;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function transformAffectation(
        Affectation $external_affectation,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CAffectation {
        $affectation = $external_affectation->getMbObject() ?? new CAffectation();

        $affectation->sejour_id = $reference_stash->getMbIdByExternalId(
            'sejour',
            $external_affectation->getSejourId()
        );
        $affectation->entree    = $this->formatDateTimeToStr($external_affectation->getEntree()) ?? null;
        $affectation->sortie    = $this->formatDateTimeToStr($external_affectation->getSortie()) ?? null;
        $affectation->rques     = $external_affectation->getRemarques() ?? null;
        $affectation->effectue  = $external_affectation->getEffectue() ?? null;

        // service
        if ($serv = $this->getService($external_affectation->getNomService())) {
            $affectation->service_id = $serv->_id;
        }

        // lit
        if ($lit = $this->getLit($external_affectation->getNomLit(), $serv->_id)) {
            $affectation->lit_id = $lit->_id;
        }

        // mode entree
        if ($me = $this->getModeEntree($external_affectation->getModeEntree())) {
            $affectation->mode_entree_id = $me->_id;
            $affectation->mode_entree    = $me->mode;
        }

        // mode sortie
        if ($ms = $this->getModeSortie($external_affectation->getModeSortie())) {
            $affectation->mode_sortie_id = $ms->_id;
            $affectation->mode_sortie    = $ms->code;
        }

        // code unite fonctionnelle
        if ($uf = $this->getUf($external_affectation->getCodeUf())) {
            if ($uf->type === 'soins') {
                $affectation->uf_soins_id = $uf->_id;
            } elseif ($uf->type === 'hebergement') {
                $affectation->uf_hebergement_id = $uf->_id;
            } elseif ($uf->type === 'medicale') {
                $affectation->uf_medicale_id = $uf->_id;
            }
        }

        return $affectation;
    }

    /**
     * Get service object by name
     *
     * @param string|null $nom
     *
     * @return CService|null
     */
    protected function getService(?string $nom): ?CService
    {
        if ($nom === null) {
            return null;
        }

        if (!isset(static::$object_cache[CService::class][$nom])) {
            $serv           = new CService();
            $serv->nom      = $nom;
            $serv->group_id = CGroups::loadCurrent()->_id;

            static::$object_cache[CService::class][$nom] = $serv->loadMatchingObjectEsc() ? $serv : null;
        }

        return static::$object_cache[CService::class][$nom];
    }

    /**
     * get lit object by name
     *
     * @param string|null $nom
     * @param int|null    $service_id
     *
     * @return CLit|null
     * @throws Exception
     */
    protected function getLit(?string $nom, ?int $service_id): ?CLit
    {
        if ($nom === null) {
            return null;
        }

        $code = $service_id . "-" . $nom;

        if (!isset(static::$object_cache[CLit::class][$code])) {
            $lit   = new CLit();
            $ljoin = [
                "chambre" => "chambre.chambre_id = lit.chambre_id",
            ];

            $ds = $lit->getDS();

            $where = [
                "lit.nom"            => $ds->prepare("= ?", $nom),
                "chambre.service_id" => $ds->prepare("= ?", $service_id),
            ];

            static::$object_cache[CLit::class][$code] = $lit->loadObject($where, null, null, $ljoin) ? $lit : null;
        }

        return static::$object_cache[CLit::class][$code];
    }

    /**
     * Get unite fonctionnelle object by name
     *
     * @param string|null $code
     *
     * @return CUniteFonctionnelle|false
     */
    protected function getUf(?string $code)
    {
        if ($code === null) {
            return null;
        }

        if (!isset(static::$object_cache[CUniteFonctionnelle::class][$code])) {
            $uf           = new CUniteFonctionnelle();
            $uf->code     = $code;
            $uf->group_id = CGroups::loadCurrent()->_id;

            static::$object_cache[CUniteFonctionnelle::class][$code] = $uf->loadMatchingObjectEsc() ? $uf : false;
        }

        return static::$object_cache[CUniteFonctionnelle::class][$code];
    }

    public function transformOperation(
        Operation $external_operation,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): COperation {
        $operation = $external_operation->getMbObject() ?? new COperation();

        $operation->sejour_id      = $reference_stash->getMbIdByExternalId(
            'sejour',
            $external_operation->getSejourId()
        );
        $operation->chir_id        = $reference_stash->getMbIdByExternalId(
            'utilisateur',
            $external_operation->getChirId()
        );
        $operation->cote           = $external_operation->getCote() ?? null;
        $operation->date           = CMbDT::date($external_operation->getDateTime()) ?? null;
        $operation->time_operation = CMbDT::time($external_operation->getDateTime()) ?? null;
        $operation->libelle        = $external_operation->getLibelle() ?? null;
        $operation->examen         = $external_operation->getExamen() ?? null;

        return $operation;
    }

    public function transformObservationResult(
        ObservationResult $external_observation_result,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CObservationResult {
        // Define in OxLaboTransformer
    }

    public function transformObservationIdentifier(
        ObservationIdentifier $external_observation_identifier,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CObservationIdentifier {
        // Define in OxLaboTransformer
    }

    public function transformObservationResultValue(
        ObservationResultValue $external_observation_result_value,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CObservationResultValue {
        // Define in OxLaboTransformer
    }

    public function transformObservationResultSet(
        ObservationResultSet $external_observation_result_set,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CObservationResultSet {
        // Define in OxLaboTransformer
    }

    public function transformObservationAbnormalFlag(
        ObservationAbnormalFlag $external_observation_flag,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CObservationAbnormalFlag {
        // Define in OxLaboTransformer
    }

    public function transformObservationValueUnit(
        ObservationValueUnit $external_observation_value_unit,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CObservationValueUnit {
        // Define in OxLaboTransformer
    }

    public function transformObservationFile(
        ObservationFile $observation_file,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CFile {
        // Define in OxLaboTransformer
    }

    public function transformObservationResponsible(
        ObservationResponsible $observation_responsible,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CObservationResponsibleObserver {
        // Define in OxLaboTransformer
    }

    public function transformObservationExam(
        ObservationExam $observation_responsible,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CObservationResultExamen {
        // Define in OxLaboTransformer
    }

    public function transformObservationPatient(
        ObservationPatient $observation_patient,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): CPatient {
        // Define in OxLaboTransformer
    }

    protected function getEvenementId(File $file, ExternalReferenceStash $reference_stash): ?string
    {
        return $reference_stash->getMbIdByExternalId(
            'evenement_patient',
            $file->getEvenementId()
        );
    }

    protected function getContextId(
        ?string $external_id,
        string $type,
        ExternalReferenceStash $reference_stash
    ): ?string {
        return $reference_stash->getMbIdByExternalId($type, $external_id);
    }
}
