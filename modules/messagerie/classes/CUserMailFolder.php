<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\System\CSourcePOP;

/**
 * Description
 */
class CUserMailFolder extends CMbObject {
  /** @var integer Primary key */
  public $user_mail_folder_id;

  /** @var integer The account id (CSourcePOP) */
  public $account_id;

  /** @var string The folder name */
  public $name;

  /** @var string A description of the folder */
  public $description;

  /** @var integer The id of the parent folder */
  public $parent_id;

  /** @var string The type of folder (inbox, sent, draft, archived, etc...) */
  public $type;

  /** @var CUserMailFolder The parent folder */
  public $_ref_parent;

  /** @var CUserMailFolder[] The children folders */
  public $_ref_children;

  /** @var CUserMailFolder[] The ancestors folders */
  public $_ref_ancestors = array();

  /** @var integer The number of mails in the folder */
  public $_count_mails;

  /** @var CUserMail[] The mails in the folder */
  public $_ref_mails;

  /** @var array The list of folder types */
  public static $types = array('inbox', 'archived', 'favorites', 'sentbox', 'drafts');

  /**
   * Initialize the class specifications
   *
   * @return CMbObjectSpec
   */
  public function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "user_mail_folders";
    $spec->key    = "user_mail_folder_id";
    return $spec;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  public function getProps() {
    $props = parent::getProps();

    $props['account_id']  = 'ref class|CSourcePOP notNull cascade back|mail_folders';
    $props['name']        = 'str maxLength|50 notNull';
    $props['description'] = 'str';
    $props['parent_id']   = 'ref class|CUserMailFolder back|children';
    $props['type']        = 'enum list|inbox|archived|favorites|sentbox|drafts notNull';

    return $props;
  }

  /**
   * @inheritdoc
   */
  public function store() {
    $this->loadOldObject();

    /* Handle the cases when a parent is moved under one of its descendant */
    if ($this->_id && $this->fieldModified('parent_id')) {
      $new_parent_id = $this->parent_id;
      $old_parent_id = $this->_old->parent_id;
      if (!$old_parent_id) {
        $old_parent_id = '';
      }

      $this->loadDescendants();
      $switch_parent = false;
      if (array_key_exists($new_parent_id, $this->_ref_children)) {
        $switch_parent = true;
        $new_parent = $this->_ref_children[$new_parent_id];
      }
      else {
        $switch_parent = false;
        foreach ($this->_ref_children as $_child) {
          if (array_key_exists($new_parent_id, $_child->_ref_children)) {
            $switch_parent = true;
            $new_parent = $_child;
            break;
          }
        }
      }

      if ($switch_parent) {
        $new_parent->parent_id = $old_parent_id;
        if ($msg = $new_parent->store()) {
          return $msg;
        }
      }
    }

    /* If the type is modified, we also modify the types of the subfolders and of the mails */
    if ($this->_id && $this->fieldModified('type')) {
      $this->loadMails();

      switch ($this->type) {
        case 'archived':
          foreach ($this->_ref_mails as $_mail) {
            $_mail->archived = 1;

            if ($msg = $_mail->store()) {
              return $msg;
            }
          }
          break;
        case 'favorites':
          foreach ($this->_ref_mails as $_mail) {
            $_mail->favorite = 1;

            if ($msg = $_mail->store()) {
              return $msg;
            }
          }
          break;
        default:
          foreach ($this->_ref_mails as $_mail) {
            $_mail->favorite = 0;
            $_mail->archived = 0;

            if ($msg = $_mail->store()) {
              return $msg;
            }
          }
      }

      $this->loadDescendants();
      foreach ($this->_ref_children as $_child) {
        $_child->type = $this->type;

        if ($msg = $_child->store()) {
          return $msg;
        }
      }
    }

    return parent::store();
  }

  /**
   * @inheritdoc
   */
  public function delete() {
    $parent_id = $this->parent_id;
    if (!$parent_id) {
      $parent_id = '';
    }

    /* We set the parent folder of the folder's children to the current parent */
    $this->loadChildren();
    foreach ($this->_ref_children as $_child) {
      $_child->parent_id = $parent_id;
      if ($msg = $_child->store()) {
        return $msg;
      }
    }

    /* We set the parent folder of the folder's mails to the current parent */
    $this->loadMails();
    foreach ($this->_ref_mails as $_mail) {
      $_mail->folder_id = $parent_id;
      if ($msg = $_mail->store()) {
        return $msg;
      }
    }

    return parent::delete();
  }

  /**
   * Load the parent folder
   *
   * @param bool $cache Use of the object cache
   *
   * @return CUserMailFolder
   */
  public function loadParent($cache = true) {
    if (!$this->_ref_parent) {
      $this->_ref_parent = $this->loadFwdRef('parent_id', $cache);
    }

    return $this->_ref_parent;
  }

  /**
   * Load the children folders
   *
   * @return CUserMailFolder[]
   */
  public function loadChildren() {
    if (!$this->_ref_children) {
      $this->_ref_children = $this->loadBackRefs('children');
    }

    return $this->_ref_children;
  }

  /**
   * Load the children of the folder, and their descendants
   *
   * @return void
   */
  public function loadDescendants() {
    $children = $this->loadChildren();

    foreach ($children as $child) {
      $child->_ref_ancestors[$this->_id] = $this;
      $child->countMails();
      $child->loadDescendants();
    }
  }

  /**
   * Load all the ancestors of the folder
   *
   * @return CUserMailFolder[]
   */
  public function loadAncestors() {
    $this->loadParent();

    if ($this->_ref_parent->_id) {
      $this->_ref_ancestors[$this->_ref_parent->_id] = $this->_ref_parent;
      $this->_ref_ancestors = CMbArray::mergeKeys($this->_ref_ancestors, $this->_ref_parent->loadAncestors());
    }
    return $this->_ref_ancestors;
  }

  /**
   * Count the mails in the folder
   *
   * @param bool $subfolders If true, the mails in the subfolders will be counted as well
   *
   * @return int
   */
  public function countMails($subfolders = false) {
    $this->_count_mails = $this->countBackRefs('mails');

    if ($subfolders) {
      $this->loadDescendants();
      foreach ($this->_ref_children as $_child) {
        $this->_count_mails += $_child->countMails(true);
      }
    }

    return $this->_count_mails;
  }

  /**
   * Load the mails
   *
   * @param bool   $subfolders If true, the mails in the subfolders will be included as well
   * @param string $limit      The limit for loading the mails
   *
   * @return CUserMail[]
   */
  public function loadMails($subfolders = false, $limit = null) {
    if (!$this->_ref_mails) {
      $this->countMails($subfolders);
      $order = "date_inbox DESC";

      if (!$subfolders) {
        $this->_ref_mails   = $this->loadBackRefs('mails', $order, $limit);
      }
      else {
        $mail = new CUserMail();
        $where = array('folder_id' => CSQLDataSource::prepareIn($this->getDescendantsId()));
        $this->_ref_mails = $mail->loadList($where, $order, $limit);
      }
    }

    return $this->_ref_mails;
  }

  /**
   * Return an array containing the folder's id, and all it's descendant's ids
   *
   * @return array
   */
  public function getDescendantsId() {
    $this->loadDescendants();

    $folders = array($this->_id);
    foreach ($this->_ref_children as $_child) {
      $folders = array_merge($folders, $_child->getDescendantsId());
    }

    return $folders;
  }

  /**
   * Load the main folders of the given type
   *
   * @param CSourcePOP $account The account
   * @param string     $type    The type of folder
   *
   * @return CUserMailFolder[]
   */
  public static function loadFolders($account, $type = 'inbox') {
    if (!in_array($type, self::$types)) {
      return array();
    }

    $folder = new self;
    $where = array('account_id' => " = '$account->_id'", 'type' => " = '$type'", 'parent_id' => ' IS NULL');
    $folders = $folder->loadList($where, 'name');

    foreach ($folders as $folder) {
      $folder->countMails();
      $folder->loadDescendants();
    }

    return $folders;
  }
}
