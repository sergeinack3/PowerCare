{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $ex_field->formula && "/[^A-z]/"|preg_match:$ex_field->formula && ($spec|instanceof:'Ox\Core\FieldSpecs\CDateSpec' || $spec|instanceof:'Ox\Core\FieldSpecs\CTimeSpec' || $spec|instanceof:'Ox\Core\FieldSpecs\CDateTimeSpec')}}
  {{unique_id var=checkbox_formula_uid}}
  {{assign var=field_name value=$ex_field->name}}

  <label title="Lier à la formule '{{$ex_field->_formula|JSAttribute}}'">
    <img src="style/mediboard_ext/images/buttons/formula.png" /><input class="date-toggle-formula"
     type="checkbox"
     data-toggle-formula-for="{{$field_name}}"
     id="cb-{{$checkbox_formula_uid}}"
     {{if !$ex_object->_id || $ex_object->$field_name == ""}} checked {{/if}}
    />
  </label>
{{/if}}
