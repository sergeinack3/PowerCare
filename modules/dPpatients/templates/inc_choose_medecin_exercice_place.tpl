{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=submit_on_change value=1}}
{{mb_default var=field_select_name value=$field}}

{{if !$medecin->_ref_medecin_exercice_places || ($medecin->_ref_medecin_exercice_places|@count <= 1)}}
    {{mb_return}}
{{/if}}

<select name="{{$field_select_name}}" {{if $submit_on_change}}onchange="return onSubmitFormAjax(this.form);"{{/if}}>
    {{foreach from=$medecin->_ref_medecin_exercice_places item=_medecin_exercice_place}}
      {{if $_medecin_exercice_place->_ref_exercice_place->_id || ($_medecin_exercice_place->_id === $object->$field)}}
        <option value="{{$_medecin_exercice_place->_id}}"
                {{if $_medecin_exercice_place->_id === $object->$field}}selected{{/if}}>
          {{$_medecin_exercice_place->_ref_exercice_place->_view}}
            &mdash; {{$_medecin_exercice_place->_ref_exercice_place->_address}}
        </option>
      {{/if}}
    {{/foreach}}
</select>
