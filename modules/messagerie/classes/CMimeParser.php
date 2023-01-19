<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Mail_mimeDecode;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbPath;
use stdClass;

/**
 * A simple MIME parser
 */
class CMimeParser implements IShortNameAutoloadable {

  /** @var CUserMail The User mail */
  protected $mail;

  /** @var string The raw MIME content of the mail */
  protected $content;

  /**
   * CMimeParser constructor.
   *
   * @param CUserMail $mail    The UserMail object
   * @param string    $content The raw MIME content of the mail
   */
  public function __construct($mail, $content) {
    $this->mail = $mail;
    $this->content = $content;
  }

  /**
   * Parse the raw MIME content of a mail and set the CUserMail
   *
   * @return CUserMail
   */
  public function parse() {
    $structure = (new Mail_mimeDecode(''))->decode(
      array(
        'include_bodies' => true,
        'decode_bodies'  => true,
        'decode_headers' => true,
        'input'          => $this->content
      )
    );

    $this->handlePart($structure, 0);

    return $this->mail;
  }

  /**
   * Handle the part
   *
   * @param stdClass $structure The part
   * @param string   $number    The number of the part
   *
   * @return void
   */
  protected function handlePart($structure, $number) {
    switch ($structure->ctype_primary) {
      case 'text':
        $this->handleText($structure);
        break;
      case 'multipart':
        $this->handleMultipart($structure, $number);
        break;
      default:
        if (property_exists($structure, 'disposition') && $structure->disposition == 'attachment') {
          $this->handleAttachement($structure, $number);
        }
    }
  }

  /**
   * Handle the parts of the primary type Text
   *
   * @param stdClass $structure The part
   *
   * @return void
   */
  protected function handleText($structure) {
    switch ($structure->ctype_secondary) {
      case 'html':
        $this->mail->_text_html = $structure->body;
        break;
      case 'plain':
      default:
        $this->mail->_text_plain = $structure->body;
    }
  }

  /**
   * Handle the parts of primary type multipart
   *
   * @param stdClass $structure The part
   * @param string   $number    The number of the part
   *
   * @return void
   */
  protected function handleMultipart($structure, $number) {
    foreach ($structure->parts as $_number => $_part) {
      if ($number != '0') {
        $_number = "$number." . ($_number + 1);
      }
      else {
        $_number = 1;
      }
      $this->handlePart($_part, $_number);
    }
  }

  /**
   * Handle the attachments
   *
   * @param stdClass $structure The part
   * @param string   $number    The number of the part
   *
   * @return void
   */
  protected function handleAttachement($structure, $number) {
    $attachment = new CMailAttachments();

    switch ($structure->ctype_primary) {
      case 'message':
        $attachment->type = 1;
        break;
      case 'application':
        $attachment->type = 3;
        break;
      case 'audio':
        $attachment->type = 4;
        break;
      case 'image':
        $attachment->type = 5;
        break;
      case 'video':
        $attachment->type = 6;
        break;
      case 'text':
      default:
        $attachment->type = 0;
    }

    $attachment->subtype = $structure->ctype_secondary;

    if (property_exists($structure, 'headers') && array_key_exists('content-transfer-encoding', $structure->headers)) {
      $attachment->encoding = $structure->headers['content-transfer-encoding'] == 'base64' ? 3 : null;
    }

    $attachment->disposition = 'attachment';

    if (property_exists($structure, 'd_parameters') && array_key_exists('filename', $structure->d_parameters)) {
      $attachment->name = $structure->d_parameters['filename'];
      $attachment->extension = CMbPath::getExtension($attachment->name);

      if (in_array($attachment->extension, array('hpm', 'hps', 'hpr'))) {
        $attachment->subtype = 'x-hprim';
        $attachment->type = 3;
      }
    }

    $attachment->part = $number;

    $attachment->bytes = strlen($structure->body);

    $attachment->_content = $structure->body;

    $this->mail->_attachments[] = $attachment;
  }
}
