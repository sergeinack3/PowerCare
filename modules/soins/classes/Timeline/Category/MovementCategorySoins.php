<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins\Timeline\Category;

use Ox\Core\CMbObject;
use Ox\Mediboard\Brancardage\CBrancardage;
use Ox\Mediboard\Soins\Timeline\AlsoStayInCategory;
use Ox\Mediboard\System\Timeline\ITimelineCategory;
use Ox\Mediboard\System\Timeline\TimelineCategory;

/**
 * Class MovementCategorySoins
 */
class MovementCategorySoins extends TimelineCategory implements ITimelineCategory
{
    use AlsoStayInCategory;

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        if ($this->selectedPractitioner(null)) {
            /** @var CBrancardage[] $stretchers */
            $stretchers = $this->stay->loadBackRefs('sejour_brancard');
            CMbObject::massLoadFwdRef($stretchers, 'origine_id');

            $stretcher_item = CMbObject::massLoadBackRefs($stretchers, 'brancardage_items');
            $staff = CMbObject::massLoadFwdRef($stretcher_item, 'pec_user_id');
            $users = CMbObject::massLoadFwdRef($staff, 'user_id');

            CMbObject::massLoadFwdRef($users, 'function_id');
            CMbObject::massLoadFwdRef($stretcher_item, 'transport_id');
            CMbObject::massLoadFwdRef($stretcher_item, 'destination_id');

            foreach ($stretchers as $_stretcher) {
                $_stretcher->loadRefItems();
                $_stretcher->loadRefOrigine();

                foreach ($_stretcher->_ref_items as $_item) {
                    $_item->_ref_brancardage = $_stretcher;

                    $_item->loadRefTransport();
                    $_item->loadRefOrigine();
                    $_item->updateFormFields();
                    $_item->loadRefPersonnel();
                    $_item->loadRefDestination();

                    if ($_item->_ref_origine) {
                        $_item->_ref_origine->updateFormFields();
                    }
                    if ($_item->_ref_destination) {
                        $_item->_ref_destination->updateFormFields();
                    }

                    if ($_item->_ref_personnel->_id) {
                        $_item->_ref_personnel->loadRefUser()->loadRefFunction();
                    }

                    $datetime = null;
                    foreach (['arrivee', 'depart', 'prise_en_charge', 'demande_brancard', 'patient_pret'] as $_field) {
                        if ($_item->$_field) {
                            $datetime = $_item->$_field;
                            break;
                        }
                    }

                    if ($datetime) {
                        $dates = $this->makeListDates($datetime);
                        $this->appendTimeline($dates[0], $dates[1], $dates[2], 'movements', $_item);
                    }
                }
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
