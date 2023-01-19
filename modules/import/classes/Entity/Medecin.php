<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Entity;

use Ox\Core\Specification\SpecificationViolation;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Transformer\TransformerVisitorInterface;
use Ox\Import\Framework\Validator\ValidatorVisitorInterface;

/**
 * External medecin representation
 */
class Medecin extends AbstractEntity
{
    public const EXTERNAL_CLASS = 'MEDC';

    /** @var string */
    protected $nom;

    /** @var string */
    protected $prenom;

    /** @var string */
    protected $sexe;

    /** @var string */
    protected $titre;

    /** @var string */
    protected $email;

    /** @var string */
    protected $disciplines;

    /** @var string */
    protected $tel;

    /** @var string */
    protected $tel_autre;

    /** @var string */
    protected $adresse;

    /** @var string */
    protected $cp;

    /** @var string */
    protected $ville;

    /** @var string */
    protected $rpps;

    /** @var string */
    protected $adeli;

    /**
     * @inheritDoc
     */
    public function validate(ValidatorVisitorInterface $validator): ?SpecificationViolation
    {
        return $validator->validateMedecin($this);
    }

    /**
     * @inheritDoc
     */
    public function transform(
        TransformerVisitorInterface $transformer,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): ImportableInterface {
        return $transformer->transformMedecin($this, $reference_stash, $campaign);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultRefEntities(): array
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
    public function getPrenom(): ?string
    {
        return $this->prenom;
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
    public function getTitre(): ?string
    {
        return $this->titre;
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
    public function getDisciplines(): ?string
    {
        return $this->disciplines;
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
    public function getRpps(): ?string
    {
        return $this->rpps;
    }

    /**
     * @return string
     */
    public function getAdeli(): ?string
    {
        return $this->adeli;
    }
}
