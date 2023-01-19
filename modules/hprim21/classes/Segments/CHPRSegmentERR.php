<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21\Segments;

use Ox\Core\CMbString;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Error;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hprim21\CHPrim21Acknowledgment;

/**
 * Class CHPRSegmentERR 
 * ERR - Represents an HPR ERR message segment (Error)
 */

class CHPRSegmentERR extends CHL7v2Segment {
  public $name = "ERR";
  
  /**
   * @var CHPrim21Acknowledgment
   */
  public $acknowledgment;
  
  /**
   * @var CHL7v2Error
   */
  public $error;

  /**
   * @inheritdoc
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);
    
    $error          = $this->error;
    $acknowledgment = $this->acknowledgment;
    $exchange_hpr   = $event->_exchange_hpr;

    $data = array();
    
    if ($error instanceof CHL7v2Error) {
      // ERR-1: Segment Row
      $data[] = $acknowledgment->_row;
      
      // ERR-2: Filename
      $data[] = $exchange_hpr->nom_fichier;
      
      // ERR-3: Date / Time of receipt
      $data[] = $exchange_hpr->date_production;
      
      // ERR-4: Severity
      $data[] = null;
      
      // ERR-5: Line number
      $data[] = null;
  
      // ERR-6: Error Location
      $data[] = null;
      
      // ERR-7: Field Position
      $data[] = null;
      
      // ERR-8: Error value
      $data[] = null;
      
      // ERR-9: Error type
      $data[] = null;
      
      // ERR-10: Original Text
      $data[] = null;
    }
    else {
      // ERR-1
      $data[] = $acknowledgment->_row;
      
      // ERR-2
      $data[] = $exchange_hpr->nom_fichier;
      
      // ERR-3
      $data[] = $exchange_hpr->date_production;
      
      // ERR-4
      $data[] = $error[0];
      
      // ERR-5
      $data[] = null;
      
      // ERR-6
      $data[] = array( 
        array(
          $error[2][0],
          $error[2][1],
          $error[2][2]
        )
      );
      
      // ERR-7
      $data[] = null;
      
      // ERR-8
      $data[] = $error[4];
      
      // ERR-9
      $data[] = $error[5];
      
      // ERR-10
      $data[] = CMbString::removeAllHTMLEntities($error[6]);
    }
    
    
    $this->fill($data);
  }
}
