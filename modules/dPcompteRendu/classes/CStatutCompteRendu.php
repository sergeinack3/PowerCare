<?php

/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Mediusers\CMediusers;

class CStatutCompteRendu extends CMbObject
{
    /** @var int */
    public $statut_compte_rendu_id;
    /** @var int */
    public $compte_rendu_id;
    /** @var string */
    public $statut;
    /** @var string */
    public $commentaire;
    /** @var string */
    public $datetime;
    /** @var int */
    public $user_id;

    /** @var CMediusers */
    public $_ref_utilisateur;

    /** @var string */
    public $_delai_attente_correction;
    /**
     * @inheritdoc
     */

    public function getSpec()
    {
        $spec = parent::getSpec();
        $spec->table = 'statut_compte_rendu';
        $spec->key   = 'statut_compte_rendu_id';
        return $spec;
    }
    /**
     * @inheritdoc
     */

    public function getProps()
    {
        $props                    = parent::getProps();
        $props["compte_rendu_id"] = "ref notNull class|CCompteRendu cascade back|statut_compte_rendu";
        $props["statut"]          = "enum list|brouillon|attente_validation_praticien|attente_correction_secretariat|a_envoyer|envoye default|brouillon";
        $props["commentaire"]     = "text fieldset|default";
        $props["datetime"]        = "dateTime notNull";
        $props["user_id"]         = "ref notNull class|CMediusers back|creator_statut";
        return $props;
    }

    /**
     * @throws Exception
     */
    public function loadRefUtilisateur(): CStoredObject
    {
        return $this->_ref_utilisateur = $this->loadFwdRef("user_id", true);
    }

    public function getDelaiAttenteCorrection(): string
    {
        $time = CMbDT::duration($this->datetime, CMbDT::dateTime());
        if ($time['day'] > 0) {
            $trad = $time['day'] == 1 ? CAppUI::tr('day') : CAppUI::tr('day|pl');
            return $this->_delai_attente_correction = $time['day'] . " " . $trad;
        } elseif ($time['hour'] > 0) {
            $trad = $time['hour'] == 1 ? CAppUI::tr('hour') : CAppUI::tr('hour|pl');
            return $this->_delai_attente_correction = $time['hour'] . " " . $trad;
        } else {
            $trad = $time['minute'] == 1 ? CAppUI::tr('minute') : CAppUI::tr('minute|pl');
            return $this->_delai_attente_correction = $time['minute'] . " " . $trad;
        }
    }
}
