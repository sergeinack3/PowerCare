<?php

/**
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Mediboard\Forms\Tests\Fixtures;

use Ox\Tests\Fixtures\GroupFixturesInterface;

class FormReportDataFixtures extends AbstractFormsFixtures implements GroupFixturesInterface
{
    public const REF_EX_CLASS_REPORT_DATA = "ref_ex_class_report_data";
    public const REF_EX_CLASS_FIELD_NUM   = "ref_ex_class_field_num";
    public const REPORTED_CLASS           = "CSejour";

    /**
     * @inheritDoc
     */
    public function load(): void
    {
        /** Simple form generation **/
        $this->createFormWithDataReport();
    }

    /**
     * Form creation with data report
     */
    private function createFormWithDataReport(): void
    {
        /** CONCEPTS **/
        // nouveau concept de type nombre entier
        $ex_concept_num = $this->generateExConcept("num");

        /** FORM **/
        // nouveau formulaire
        $ex_class = $this->generateExClass(self::REF_EX_CLASS_REPORT_DATA);

        /** FORM FIELDS **/
        // nouveau champ pour le groupe général du formulaire avec un concept de type nombre entier
        $ex_class_field_num = $this->generateExClassField(
            $this->getFirstClassGroup($ex_class),
            $ex_concept_num,
            self::REF_EX_CLASS_FIELD_NUM,
            self::REPORTED_CLASS
        );
        $this->generateExClassFieldTranslation($ex_class_field_num, "Champs nombre entier");
    }

    /**
     * @inheritDoc
     */
    public function purge()
    {
        $this->purgeEx();
        parent::purge();
    }

    public static function getGroup(): array
    {
        return ['forms_fixtures', 150];
    }
}
