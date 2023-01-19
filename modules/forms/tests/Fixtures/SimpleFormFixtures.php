<?php

/**
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Mediboard\Forms\Tests\Fixtures;

use Ox\Tests\Fixtures\GroupFixturesInterface;

class SimpleFormFixtures extends AbstractFormsFixtures implements GroupFixturesInterface
{
    public const REF_EX_CLASS_SINGLE_FIELD = "ref_ex_class_single_field";
    public const REF_EX_CLASS_FIELD_STR    = "ref_ex_class_field_str";

    /**
     * @inheritDoc
     */
    public function load(): void
    {
        /** Simple form generation **/
        $this->createSimpleForm();
    }

    /**
     * Simple form creation
     */
    private function createSimpleForm(): void
    {
        /** CONCEPTS **/
        // nouveau concept de type texte court (str)
        $ex_concept_str = $this->generateExConcept("str");

        /** FORM **/
        // nouveau formulaire
        $ex_class = $this->generateExClass(self::REF_EX_CLASS_SINGLE_FIELD);

        /** FORM FIELDS **/
        // nouveau champ pour le groupe général du formulaire avec un concept de type texte court (str)
        $ex_class_field_str = $this->generateExClassField(
            $this->getFirstClassGroup($ex_class),
            $ex_concept_str,
            self::REF_EX_CLASS_FIELD_STR
        );
        $this->generateExClassFieldTranslation($ex_class_field_str, "Champs texte court");
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
        return ['forms_fixtures', 200];
    }
}
