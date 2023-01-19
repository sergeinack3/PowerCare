<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

use Exception;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Liaison entre les factures et leur journal
 */
class CJournalLiaison extends CMbObject {

  // DB Table key
  public $journal_liaison_id;

  // DB Fields
  public $object_id;
  public $object_class;
  public $journal_id;

  public $_ref_object;

  // Object References
  /** @var  CFacture $_ref_facture*/
  public $_ref_facture;
  /** @var  CFacturable $_ref_facturable*/
  public $_ref_facturable;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'facture_link_journal';
    $spec->key   = 'journal_liaison_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs = parent::getProps();
    $specs["object_id"]    = "ref notNull class|CFacture meta|object_class back|journal_liaison";
    $specs["object_class"] = "enum notNull list|CFactureCabinet|CFactureEtablissement show|0 default|CFactureCabinet";
    $specs["journal_id"]   = "ref notNull class|CJournalBill autocomplete|nom back|journal_link";
    return $specs;
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