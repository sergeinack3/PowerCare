<?php

namespace Ox\Interop\Cda\Components\Entries\ANS\Transversaux;

use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\IHE\Transversaux\CDAEntryComment;
use Ox\Interop\Cda\Rim\CCDARIMAct;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Act;
use Ox\Mediboard\Loinc\CLoinc;

/**
 * Class CDAEntryFRCommentaire
 *
 * @package Ox\Interop\Cda\Components\Entries\ANS\Transversaux
 */
class CDAEntryFRCommentaire extends CDAEntryComment
{
    /** @var string */
    public const  TEMPLATE_ID = '1.2.250.1.213.1.1.3.32';

    /** @var string */
    private $text;

    /** @var array */
    private $authors;

    /**
     * CDAEntryFRSimpleObservation constructor.
     *
     * @param CCDAFactory $factory
     * @param array       $options
     */
    public function __construct(CCDAFactory $factory, string $text)
    {
        parent::__construct($factory);

        $this->text = $text;
    }

    /**
     * @param array $authors
     *
     * @return CDAEntryFRCommentaire
     */
    public function setAuthors(array $authors): CDAEntryFRCommentaire
    {
        $this->authors = $authors;

        return $this;
    }

    /**
     * @param CCDAPOCD_MT000040_Act $act
     *
     * @return void
     */
    protected function buildContent(CCDAClasseCda $act): void
    {
        $act->setClassCode('ACT');
        $act->setMoodCode('EVN');

        if ($this->authors) {
            // not implemented
        }
    }

    /**
     * @param CCDAPOCD_MT000040_Act $entry_content
     */
    protected function setCode(CCDARIMAct $entry_content): void
    {
        $entry_content->setCode(
            CCDADocTools::prepareCodeCE('48767-8', CLoinc::$oid_loinc, 'Commentaire', CLoinc::$name_loinc)
        );
    }

    protected function setStatusCode(CCDARIMAct $entry_content): void
    {
        CCDADocTools::setStatusCode($entry_content, 'completed');
    }

    protected function setText(CCDARIMAct $entry_content): void
    {
        CCDADocTools::setTextWithReference($entry_content, $this->text);
    }
}
