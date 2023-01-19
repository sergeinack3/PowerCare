<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins\Timeline\Category;

use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Soins\Timeline\AlsoStayInCategory;
use Ox\Mediboard\System\Timeline\ITimelineCategory;
use Ox\Mediboard\System\Timeline\TimelineCategory;

/**
 * Class PrescriptionBeginCategorySoins
 */
class PrescriptionBeginCategorySoins extends TimelineCategory implements ITimelineCategory
{
    use AlsoStayInCategory;

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        $prescription = $this->stay->loadRefPrescriptionSejour();

        if ($prescription->_id) {
            $prescription->loadRefsLinesElement();
            $prescription->loadRefsLinesElementByCat();
            $prescription->loadRefsLinesMed();
            $prescription->loadRefsPrescriptionLineMixes();
            CPrescription::massLoadAdministrations(
                $prescription,
                [CMbDT::date(null, $this->stay->entree), CMbDT::date(null, $this->stay->sortie)]
            );

            // CPrescriptionLineElement
            CMbObject::massLoadFwdRef($prescription->_ref_prescription_lines_element, 'element_prescription_id');
            foreach ($prescription->_ref_prescription_lines_element as $_line) {
                $_line->loadRefPraticien();
                $_line->_ref_praticien->loadRefFunction();
                $_line->loadRefElement();
                $_line->updateFormFields();

                if ($this->selectedPractitioner($_line->_ref_praticien)) {
                    $dates_list = $this->makeListDates($_line->_debut_reel);
                    $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'prescription_begin', $_line);

                    $this->addToInvolvedUser($_line->_ref_praticien);
                }
            }

            // CPrescriptionLineMedicament
            foreach ($prescription->_ref_prescription_lines as $_line) {
                $_line->loadRefPraticien();
                $_line->_ref_praticien->loadRefFunction();
                $_line->updateFormFields();

                if ($this->selectedPractitioner($_line->_ref_praticien)) {
                    $dates_list = $this->makeListDates($_line->_debut_reel);
                    $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'prescription_begin', $_line);

                    $this->addToInvolvedUser($_line->_ref_praticien);
                }
            }

            // CPrescriptionLineMix
            CMbObject::massLoadBackRefs($prescription->_ref_prescription_line_mixes, 'lines_mix', 'solvant');
            foreach ($prescription->_ref_prescription_line_mixes as $_line) {
                $_line->loadRefPraticien();
                $_line->_ref_praticien->loadRefFunction();
                $_line->loadRefsLines();
                $_line->updateFormFields();

                if ($this->selectedPractitioner($_line->_ref_praticien)) {
                    $dates_list = $this->makeListDates($_line->_debut_reel);
                    $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'prescription_begin', $_line);

                    $this->addToInvolvedUser($_line->_ref_praticien);
                }
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
