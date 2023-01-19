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
 * Class PrescriptionCategorySoins
 */
class PrescriptionCategorySoins extends TimelineCategory implements ITimelineCategory
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
                    foreach ($_line->_ref_administrations as $_administration) {
                        $_administration->loadRefAdministrateur();
                        $_administration->loadTargetObject();
                        $_administration->_ref_object->loadRefElement()->loadRefCategory();

                        $dates_list = $this->makeListDates($_administration->dateTime);
                        $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'administer', $_administration);
                    }

                    $this->addToInvolvedUser($_line->_ref_praticien);
                }
            }

            // CPrescriptionLineMedicament
            foreach ($prescription->_ref_prescription_lines as $_line) {
                $_line->loadRefPraticien();
                $_line->_ref_praticien->loadRefFunction();
                $_line->updateFormFields();

                if ($this->selectedPractitioner($_line->_ref_praticien)) {
                    foreach ($_line->_ref_administrations as $_administration) {
                        $_administration->loadRefAdministrateur();
                        $_administration->loadTargetObject();
                        $_administration->_ref_object->loadRefProduit();

                        $dates_list = $this->makeListDates($_administration->dateTime);
                        $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'administer', $_administration);
                    }

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
                    foreach ($_line->_ref_lines as $_line_item) {
                        $_line_item->loadRefsAdministrations();
                        foreach ($_line_item->_ref_administrations as $_administration) {
                            $_administration->loadRefAdministrateur();
                            $_administration->loadTargetObject();
                            $_administration->_ref_object->loadRefProduit();

                            $dates_list = $this->makeListDates($_administration->dateTime);
                            $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'administer', $_administration);
                        }
                    }

                    $this->addToInvolvedUser($_line->_ref_praticien);
                }
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
