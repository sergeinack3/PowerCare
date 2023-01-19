<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Mediboard\System\CContentTabular;

/**
 * Class CExchangeTabular
 * Echange Tabular
 */
class CExchangeTabular extends CExchangeDataFormat
{
    // DB Fields
    public $version;
    public $nom_fichier;
    public $identifiant_emetteur;

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props = parent::getProps();

        $props["version"]                 = "str";
        $props["nom_fichier"]             = "str";
        $props["identifiant_emetteur"]    = "str";
        $props["message_content_id"]      = "ref class|CContentTabular show|0 cascade back|messages_tabular";
        $props["acquittement_content_id"] = "ref class|CContentTabular show|0 cascade back|acquittements_tabular";

        $props["_message"]      = "str";
        $props["_acquittement"] = "str";

        return $props;
    }

    /**
     * @see parent::loadContent()
     */
    function loadContent()
    {
        // Cas d'un exchange HL7v2 qui a un contenu altéré
        if ($this instanceof CExchangeHL7v2 && $this->altered_content_id) {
            $this->_ref_message_content = $this->loadFwdRef("altered_content_id", true);
            $this->_ref_message_initial = $this->loadFwdRef("message_content_id", true);
            $this->_message             = $this->_ref_message_initial->content;
        } else {
            $this->_ref_message_content = $this->loadFwdRef("message_content_id", true);
            $this->_message             = $this->_ref_message_content->content;
        }

        $this->_ref_acquittement_content = $this->loadFwdRef("acquittement_content_id", true);
        $this->_acquittement             = $this->_ref_acquittement_content->content;
    }

    /**
     * @see parent::updatePlainFields()
     */
    function updatePlainFields()
    {
        parent::updatePlainFields();

        if ($this->_message !== null) {
            /** @var CContentTabular $content */
            $content          = $this->loadFwdRef("message_content_id", true);
            $content->content = $this->_message;
            if ($msg = $content->store()) {
                return;
            }
            if (!$this->message_content_id) {
                $this->message_content_id = $content->_id;
            }
        }

        if ($this->_acquittement !== null) {
            /** @var CContentTabular $content */
            $content          = $this->loadFwdRef("acquittement_content_id", true);
            $content->content = $this->_acquittement;
            if ($msg = $content->store()) {
                return;
            }
            if (!$this->acquittement_content_id) {
                $this->acquittement_content_id = $content->_id;
            }
        }
    }

    /**
     * @see parent::getMessage()
     */
    function getMessage()
    {
    }

    /**
     * @see parent::getACK()
     */
    function getACK()
    {
    }
}

