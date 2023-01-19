<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Laboratoires
 */
class CLaboratoire extends CMbObject
{

    /** @var int */
    public $group_id;

    /** @var string */
    public $libelle;

    /** @var int */
    public $actif;

    /** @var string */
    public $adresse;

    /** @var string */
    public $cp;

    /** @var string */
    public $ville;

    /** @var string */
    public $tel;

    /** @var string */
    public $fax;

    /** @var string */
    public $mail;

    // Form fields
    public $_adresse;

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props             = parent::getProps();
        $props['group_id'] = 'ref class|CGroups notNull';
        $props['libelle']  = 'str seekable notNull';
        $props['actif']    = 'bool default|1';
        $props['adresse']  = 'text';
        $props['cp']       = 'str minLength|4 maxLength|10';
        $props["ville"]    = 'str maxLength|50';
        $props['tel']      = 'phone';
        $props['fax']      = 'phone';
        $props['mail']     = 'email';
        $props['_adresse'] = 'text';

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view    = $this->libelle;
        $this->_adresse = "{$this->adresse}\n{$this->cp} {$this->ville}";
    }

    /**
     * @inheritDoc
     */
    public function store()
    {
        if (!$this->_id) {
            $this->group_id = CGroups::loadCurrent()->_id;
        }

        return parent::store();
    }

    /**
     * @inheritDoc
     */
    public function fillLimitedTemplate(&$template)
    {
        $labo_section = CAppUI::tr('COperation-Operation') . ' - ' .
            CAppUI::tr((get_class($this) === CLaboratoireAnapath::class) ? 'COperation-anapath' : 'COperation-labo');

        $template->addProperty(
            "$labo_section - " . CAppUI::tr('CLaboratoireAnapath-adresse'),
            $this->_adresse
        );
        $template->addProperty(
            "$labo_section - " . CAppUI::tr('CLaboratoire-way'),
            $this->adresse
        );
        $template->addProperty(
            "$labo_section - " . CAppUI::tr('CLaboratoireAnapath-cp'),
            $this->getFormattedValue('cp')
        );
        $template->addProperty(
            "$labo_section - " . CAppUI::tr('CLaboratoireAnapath-ville'),
            $this->ville
        );
        $template->addProperty(
            "$labo_section - " . CAppUI::tr('CLaboratoireAnapath-adresse'),
            $this->_adresse
        );
        $template->addProperty(
            "$labo_section - " . CAppUI::tr('CLaboratoireAnapath-tel'),
            $this->getFormattedValue('tel')
        );
        $template->addProperty(
            "$labo_section - " . CAppUI::tr('CLaboratoireAnapath-fax'),
            $this->getFormattedValue('fax')
        );
    }
}
