<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbString;

/**
 * hprimsante error
 */
class CHPrimSanteError implements IShortNameAutoloadable {

  public $type_error;
  public $code_error;
  public $address;
  public $field;
  public $type;
  public $sous_type;
  public $exchange;
  public $comment;

  /**
   * Constructor
   *
   * @param CExchangeHprimSante $exchange   Exchange
   * @param String              $type_error Error type
   * @param String              $code_error Error code
   * @param String[]            $address    Error address
   * @param String              $field      Error field
   * @param String              $comment    Comment
   */
  function __construct($exchange, $type_error, $code_error, $address, $field, $comment = null) {
    $this->type_error = $type_error;
    $this->code_error = $code_error;
    $this->address    = $address;
    $this->field      = $field;
    $this->type       = $exchange->type;
    $this->sous_type  = $exchange->sous_type;
    $this->exchange   = $exchange;
    $this->comment    = CMbString::removeAllHTMLEntities($comment);
  }

  /**
   * Get the comment error
   *
   * @return string
   */
  function getCommentError() {
    $comment_error = CAppUI::tr("CHPrimSanteEvent-$this->sous_type-$this->code_error");
    if ($this->comment) {
      $comment_error .= " : $this->comment";
    }
    $comment_error = str_replace("\r", "", $comment_error);
    return $comment_error;
  }
}