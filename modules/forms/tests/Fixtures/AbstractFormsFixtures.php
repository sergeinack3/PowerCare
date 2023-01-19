<?php

/**
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Mediboard\Forms\Tests\Fixtures;

use Exception;
use Ox\Core\CAppUI;
use Ox\Erp\SourceCode\CFixturesReference;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassField;
use Ox\Mediboard\System\Forms\CExClassFieldGroup;
use Ox\Mediboard\System\Forms\CExClassFieldTranslation;
use Ox\Mediboard\System\Forms\CExConcept;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\GroupFixturesInterface;

abstract class AbstractFormsFixtures extends Fixtures implements GroupFixturesInterface
{
    protected function generateExConcept(string $prop): CExConcept
    {
        $ex_concept       = new CExConcept();
        $ex_concept->name = uniqid();
        $ex_concept->prop = $prop;
        $this->store($ex_concept);

        return $ex_concept;
    }

    protected function generateExClass(string $tag): CExClass
    {
        $ex_class       = new CExClass();
        $ex_class->name = uniqid();
        $this->store($ex_class, $tag);

        return $ex_class;
    }

    protected function generateExClassField(
        CExClassFieldGroup $first_class_group,
        CExConcept $ex_concept,
        string $tag,
        string $report_class = null
    ): CExClassField {
        $ex_class_field               = new CExClassField();
        $ex_class_field->ex_group_id  = $first_class_group->_id;
        $ex_class_field->concept_id   = $ex_concept->_id;
        $ex_class_field->disabled     = 0;
        $ex_class_field->report_class = $report_class;
        $this->store($ex_class_field, $tag);

        return $ex_class_field;
    }

    protected function getFirstClassGroup(CExClass $ex_class): CExClassFieldGroup
    {
        $ex_class_groups = $ex_class->loadRefsGroups();

        return reset($ex_class_groups);
    }

    protected function generateExClassFieldTranslation(CExClassField $ex_class_field, string $std): void
    {
        $ex_class_field_translation                    = new CExClassFieldTranslation();
        $ex_class_field_translation->ex_class_field_id = $ex_class_field->_id;
        $ex_class_field_translation->lang              = "fr";
        $ex_class_field_translation->std               = $std;
        $this->store($ex_class_field_translation);
    }

    /**
     * @throws Exception
     */
    protected function purgeEx(): void
    {
        $db_name   = CAppUI::conf('db std dbname');
        $ex_class  = new CExClass();
        $reference = new CFixturesReference();
        $ds        = $ex_class->getDS();
        $ids       = [];
        $where     = [];

        $where['object_class'] = $ds->prepareLike("CExClass");
        $references            = $reference->loadList($where);

        foreach ($references as $_ref) {
            $ids[] = $_ref->object_id;
        }

        $selectColumns = "'ex_object_" . implode("', 'ex_object_", $ids) . "'";
        $query         = "SELECT CONCAT('DROP TABLE ', GROUP_CONCAT(concat(table_schema,'.',TABLE_NAME) SEPARATOR ','), ';')
            FROM information_schema.`TABLES`
            WHERE table_schema = '{$db_name}'
            AND TABLE_NAME IN ({$selectColumns})
            GROUP BY table_schema;";

        if ($result = $ds->loadResult($query)) {
            $ds->exec($result);
        }
    }

    public static function getGroup(): array
    {
        return ['forms_fixtures'];
    }
}
