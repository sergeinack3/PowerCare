<?php
/**
 * @package Mediboard\Smp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Smp;

use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CDomain;
use Ox\Interop\Eai\CEAIObjectHandler;
use Ox\Interop\Eai\CGroupDomain;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CSmpObjectHandler
 * SMP Object handler
 */

class CSmpObjectHandler extends CEAIObjectHandler {
  /** @var array $handled */
  static $handled = array ("CSejour", "CAffectation", "CNaissance");

  /**
   * @inheritdoc
   */
  static function isHandled(CStoredObject $object) {
      return !$object->_ignore_eai_handlers && in_array($object->_class, self::$handled);
  }

  /**
   * @inheritdoc
   */
  function onBeforeStore(CStoredObject $object) {
    if (!parent::onBeforeStore($object)) {
      return false;
    }

    // Si pas de tag séjour
    if (!CAppUI::conf("eai use_domain") && !CAppUI::conf("dPplanningOp CSejour tag_dossier")) {
      throw new CMbException("no_tag_defined");
    }

    $this->sendFormatAction("onBeforeStore", $object);

    return true;
  }

  /**
   * @inheritdoc
   */
  function onAfterStore(CStoredObject $object) {
    if (!parent::onAfterStore($object)) {
      return false;
    }

    // Si pas de tag séjour
    if (!CAppUI::conf("eai use_domain") && !CAppUI::conf("dPplanningOp CSejour tag_dossier")) {
      throw new CMbException("no_tag_defined");
    }

    $this->sendFormatAction("onAfterStore", $object);

    return true;
  }

  /**
   * @inheritdoc
   */
  function onBeforeMerge(CStoredObject $object) {
    if (!parent::onBeforeMerge($object)) {
      return false;
    }

    if (!$object instanceof CSejour) {
      return false;
    }
    
    $sejour = $object;

    if (!$sejour_elimine_id = array_key_first($sejour->_merging)) {
      return false;
    }

    $sejour_elimine = new CSejour();
    $sejour_elimine->load($sejour_elimine_id);
    if (!$sejour_elimine->_id) {
      return false;
    }

    $object->_fusion = array();

      if (CAppUI::conf("eai use_domain")) {
          $domains = CDomain::getAllDomains(CGroupDomain::DOMAIN_TYPE_SEJOUR);
          foreach ($domains as $_domain) {
              $sejour->_NDA = null;
              $idexSejour = CIdSante400::getMatchFor($sejour, $_domain->tag);
              $sejour1_nda = $idexSejour->id400;

              $sejour_elimine->_NDA = null;
              $idexSejourElimine = CIdSante400::getMatchFor($sejour_elimine, $_domain->tag);
              $sejour2_nda = $idexSejourElimine->id400;

              // Eviter de prendre des idex de tous les séjours en cas de problème
              if (!$sejour->_id || !$sejour_elimine->_id) {
                  continue;
              }

              $idexs = array_merge(
                  CIdSante400::getMatches($sejour->_class        , $_domain->tag, null, $sejour->_id),
                  CIdSante400::getMatches($sejour_elimine->_class, $_domain->tag, null, $sejour_elimine->_id)
              );

              $idexs_changed = array();
              if (count($idexs) > 1) {
                  foreach ($idexs as $_idex) {
                      // On continue pour ne pas mettre en trash le NDA du séjour que l'on garde
                      if ($_idex->id400 == $sejour1_nda) {
                          continue;
                      }

                      $_idex->tag = CAppUI::conf('dPplanningOp CSejour tag_dossier_trash') . $_domain->tag;
                      if (!$msg = $_idex->store()) {
                          if ($_idex->object_id == $sejour_elimine->_id) {
                              $idexs_changed[$_idex->_id] = $_domain->tag;
                          }
                      }
                  }
              }

              foreach ($_domain->loadRefsGroupDomains() as $_group_domain) {
                  $object->_fusion[$_group_domain->group_id] = array (
                      "sejourElimine" => $sejour_elimine,
                      "sejour1_nda"   => $sejour1_nda,
                      "sejour2_nda"   => $sejour2_nda,
                      "idexs_changed" => $idexs_changed
                  );
              }
          }
      }
      else {
          foreach (CGroups::loadGroups() as $_group) {
              $sender = CMbObject::loadFromGuid($object->_eai_sender_guid);

              if ($sender && $sender->group_id == $_group->_id) {
                  continue;
              }

              $sejour->_NDA = null;
              $sejour->loadNDA($_group->_id);
              $sejour1_nda = $sejour->_NDA;

              $sejour_elimine->_NDA = null;
              $sejour_elimine->loadNDA($_group->_id);
              $sejour2_nda = $sejour_elimine->_NDA;

              // Passage en trash des NDA des séjours
              $tag_NDA = CSejour::getTagNDA($_group->_id);
              if (!$tag_NDA) {
                  continue;
              }

              // Eviter de prendre des idex de tous les séjours en cas de problème
              if (!$sejour->_id || !$sejour_elimine->_id) {
                  continue;
              }

              $idexSejour = new CIdSante400();
              $idexSejour->tag          = $tag_NDA;
              $idexSejour->object_class = "CSejour";
              $idexSejour->object_id    = $sejour->_id;
              $idexsSejour = $idexSejour->loadMatchingList();

              $idexSejourElimine = new CIdSante400();
              $idexSejourElimine->tag          = $tag_NDA;
              $idexSejourElimine->object_class = "CSejour";
              $idexSejourElimine->object_id    = $sejour_elimine->_id;
              $idexsSejourElimine = $idexSejourElimine->loadMatchingList();

              /** @var CIdSante400[] $idexs */
              $idexs         = array_merge($idexsSejour, $idexsSejourElimine);
              $idexs_changed = array();
              if (count($idexs) > 1) {
                  foreach ($idexs as $_idex) {
                      // On continue pour ne pas mettre en trash le NDA du séjour que l'on garde
                      if ($_idex->id400 == $sejour1_nda) {
                          continue;
                      }

                      $old_tag = $_idex->tag;

                      $_idex->tag = CAppUI::conf('dPplanningOp CSejour tag_dossier_trash').$tag_NDA;
                      if (!$msg = $_idex->store()) {
                          if ($_idex->object_id == $sejour_elimine->_id) {
                              $idexs_changed[$_idex->_id] = $old_tag;
                          }
                      }
                  }
              }

              if (!$sejour1_nda && !$sejour2_nda) {
                  continue;
              }

              $object->_fusion[$_group->_id] = array (
                  "sejourElimine" => $sejour_elimine,
                  "sejour1_nda"   => $sejour1_nda,
                  "sejour2_nda"   => $sejour2_nda,
                  "idexs_changed" => $idexs_changed
              );
          }
      }

    $this->sendFormatAction("onBeforeMerge", $object);

    return true;
  }

  /**
   * @inheritdoc
   */
  function onAfterMerge(CStoredObject $object) {
    if (!parent::onAfterMerge($object)) {
      return false;
    }

    if (!$object instanceof CSejour) {
      return false;
    }

    $this->sendFormatAction("onAfterMerge", $object);

    return true;
  }

  /**
   * @inheritdoc
   */
  function onBeforeDelete(CStoredObject $object) {
    if (!parent::onBeforeDelete($object)) {
      return false;
    }
    
    $this->sendFormatAction("onBeforeDelete", $object);

    return true;
  }

  /**
   * @inheritdoc
   */
  function onAfterDelete(CStoredObject $object) {
    if (!parent::onAfterDelete($object)) {
      return false;
    }
    
    $this->sendFormatAction("onAfterDelete", $object);

    return true;
  }  
}
