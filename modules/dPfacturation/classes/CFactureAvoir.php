<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Permet d'editer des avoirs pour les factures cloturées
 */
class CFactureAvoir extends CMbObject {
  // DB Table key
  public $facture_avoir_id;
  
  // DB Fields
  public $object_id;
  public $object_class;
  public $date;
  public $montant;
  public $commentaire;

  // Object References
  /** @var  CFactureCabinet|CFactureEtablissement $_ref_object*/
  public $_ref_object;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'facture_avoir';
    $spec->key   = 'facture_avoir_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["object_id"]    = "ref notNull class|CStoredObject meta|object_class back|avoirs";
    $props["object_class"]  = "enum notNull list|CFactureCabinet|CFactureEtablissement default|CFactureCabinet";
    $props["date"]          = "dateTime";
    $props["montant"]       = "currency notNull min|0";
    $props["commentaire"]   = "text helped markdown";

    return $props;
  }
  
  /**
   * Chargement de l'objet facturable
   * 
   * @return CFactureEtablissement|CFactureCabinet
  **/
  function loadRefFacture() {
    return $this->_ref_object = $this->object_id ? $this->loadTargetObject() : new CFactureCabinet();
  }

  /**
   * @inheritDoc
   */
  function fillTemplate(&$template) {
    $this->loadRefFacture()->fillTemplate($template);
    $this->fillLimitedTemplate($template);
  }

  /**
   * @inheritDoc
   */
  function fillLimitedTemplate(&$template) {
    parent::fillLimitedTemplate($template);

    $avoir_section = CAppUI::tr("CFactureAvoir");
    $template->addDateProperty("$avoir_section - " . CAppUI::tr("CFactureAvoir-date") , $this->date);
    $template->addProperty("$avoir_section - " . CAppUI::tr("CFactureAvoir-montant")   , $this->montant);
    $template->addProperty("$avoir_section - " . CAppUI::tr("CFactureAvoir-commentaire")   , $this->commentaire);
  }

  /**
   * @param CStoredObject $object
   * @deprecated
   * @todo redefine meta raf
   * @return void
   */
  public function setObject(CStoredObject $object) {
    CMbMetaObjectPolyfill::setObject($this, $object);
  }

  /**
   * @param bool $cache
   * @deprecated
   * @todo redefine meta raf
   * @return bool|CStoredObject|CExObject|null
   * @throws Exception
   */
  public function loadTargetObject($cache = true) {
    return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
  }

  /**
   * @inheritDoc
   * @todo remove
   */
  function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->loadTargetObject();
  }
}
