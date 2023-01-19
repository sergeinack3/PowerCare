<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Entity;

use DateTime;
use Ox\Core\Specification\SpecificationViolation;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Transformer\TransformerVisitorInterface;
use Ox\Import\Framework\Validator\ValidatorVisitorInterface;

/**
 * External patient representation
 */
class Patient extends AbstractEntity
{
    public const EXTERNAL_CLASS = 'PATI';

    /** @var string */
    protected $nom;

    /** @var string */
    protected $prenom;

    /** @var DateTime */
    protected $deces;

    /** @var DateTime */
    protected $naissance;

    /** @var string */
    protected $cp_naissance;

    /** @var string */
    protected $lieu_naissance;

    /** @var string */
    protected $nom_jeune_fille;

    /** @var string */
    protected $profession;

    /** @var string */
    protected $email;

    /** @var string */
    protected $tel;

    /** @var string */
    protected $tel2;

    /** @var string */
    protected $tel_autre;

    /** @var string */
    protected $adresse;

    /** @var string */
    protected $cp;

    /** @var string */
    protected $ville;

    /** @var string */
    protected $pays;

    /** @var string */
    protected $matricule;

    /** @var string */
    protected $sexe;

    /** @var string */
    protected $civilite;

    /** @var string|null */
    protected $situation_famille;

    /** @var string|null */
    protected $activite_pro;

    /** @var string */
    protected $rques;

    /** @var mixed */
    protected $medecin_traitant;

    /** @var bool */
    protected $ald;

    /** @var string */
    protected $ipp;

    /** @var string */
    protected $nom_assure;

    /** @var string */
    protected $prenom_assure;

    /** @var string */
    protected $nom_naissance_assure;

    /** @var string */
    protected $sexe_assure;

    /** @var string */
    protected $civilite_assure;

    /** @var DateTime */
    protected $naissance_assure;

    /** @var string */
    protected $adresse_assure;

    /** @var string */
    protected $ville_assure;

    /** @var string */
    protected $cp_assure;

    /** @var string */
    protected $pays_assure;

    /** @var string */
    protected $tel_assure;

    /** @var string */
    protected $matricule_assure;

    /**
     * @inheritDoc
     */
    public function validate(ValidatorVisitorInterface $validator): ?SpecificationViolation
    {
        return $validator->validatePatient($this);
    }

    /**
     * @inheritDoc
     */
    public function transform(
        TransformerVisitorInterface $transformer,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): ImportableInterface {
        return $transformer->transformPatient($this, $reference_stash, $campaign);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultRefEntities(): array
    {
        return [
            ExternalReference::getNotMandatoryFor(ExternalReference::MEDECIN, $this->medecin_traitant),
        ];
    }

    public function getCollections(): array
    {
        return [
            'antecedent'   => 'patient_id',
            'consultation' => 'patient_id',
        ];
    }

    /**
     * TODO Use this for DFSImport
     *
     * @return array
     */
    public function getCollectionsObjects(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getExternalClass()
    {
        return static::EXTERNAL_CLASS;
    }

    /**
     * @return string
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * @return string
     */
    public function getPrenom(): string
    {
        return $this->prenom;
    }

    /**
     * @return DateTime|null
     */
    public function getDeces(): ?DateTime
    {
        return $this->deces;
    }

    /**
     * @return DateTime
     */
    public function getNaissance(): ?DateTime
    {
        return $this->naissance;
    }

    /**
     * @return string|null
     */
    public function getCpNaissance(): ?string
    {
        return $this->cp_naissance;
    }

    /**
     * @return string|null
     */
    public function getLieuNaissance(): ?string
    {
        return $this->lieu_naissance;
    }

    /**
     * @return string
     */
    public function getNomJeuneFille(): ?string
    {
        return $this->nom_jeune_fille;
    }

    /**
     * @return string
     */
    public function getProfession(): ?string
    {
        return $this->profession;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getTel(): ?string
    {
        return $this->tel;
    }

    /**
     * @return string
     */
    public function getTel2(): ?string
    {
        return $this->tel2;
    }

    /**
     * @return string
     */
    public function getTelAutre(): ?string
    {
        return $this->tel_autre;
    }

    /**
     * @return string
     */
    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    /**
     * @return string
     */
    public function getCp(): ?string
    {
        return $this->cp;
    }

    /**
     * @return string
     */
    public function getVille(): ?string
    {
        return $this->ville;
    }

    /**
     * @return string
     */
    public function getPays(): ?string
    {
        return $this->pays;
    }

    /**
     * @return string
     */
    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    /**
     * @return string
     */
    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    /**
     * @return string
     */
    public function getCivilite(): ?string
    {
        return $this->civilite;
    }

    /**
     * @return mixed
     */
    public function getMedecinTraitant()
    {
        return $this->medecin_traitant;
    }

    /**
     * @return string|null
     */
    public function getSituationFamille(): ?string
    {
        return $this->situation_famille;
    }

    /**
     * @return string|null
     */
    public function getActivitePro(): ?string
    {
        return $this->activite_pro;
    }

    /**
     * @return string
     */
    public function getRques()
    {
        return $this->rques;
    }

    /**
     * @return null|bool
     */
    public function getAld(): ?bool
    {
        return $this->ald;
    }

    /**
     * @return null|string
     */
    public function getIpp(): ?string
    {
        return $this->ipp;
    }

    /**
     * @return null|string
     */
    public function getNomAssure(): ?string
    {
        return $this->nom_assure;
    }

    /**
     * @return null|string
     */
    public function getPrenomAssure(): ?string
    {
        return $this->prenom_assure;
    }

    /**
     * @return null|string
     */
    public function getNomNaissanceAssure(): ?string
    {
        return $this->nom_naissance_assure;
    }

    /**
     * @return null|string
     */
    public function getSexeAssure(): ?string
    {
        return $this->sexe_assure;
    }

    /**
     * @return null|string
     */
    public function getCiviliteAssure(): ?string
    {
        return $this->civilite_assure;
    }

    /**
     * @return null|DateTime
     */
    public function getNaissanceAssure(): ?DateTime
    {
        return $this->naissance_assure;
    }

    /**
     * @return null|string
     */
    public function getAdresseAssure(): ?string
    {
        return $this->adresse_assure;
    }

    /**
     * @return null|string
     */
    public function getVilleAssure(): ?string
    {
        return $this->ville_assure;
    }

    /**
     * @return null|string
     */
    public function getCpAssure(): ?string
    {
        return $this->cp_assure;
    }

    /**
     * @return null|string
     */
    public function getPaysAssure(): ?string
    {
        return $this->pays_assure;
    }

    /**
     * @return null|string
     */
    public function getTelAssure(): ?string
    {
        return $this->tel_assure;
    }

    /**
     * @return null|string
     */
    public function getMatriculeAssure(): ?string
    {
        return $this->matricule_assure;
    }
}
