{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=field        value="box_id"}}
{{mb_default var=field_view   value="CRPU-box_id"}}
{{mb_default var=selected_id  value=""}}
{{mb_default var=ajaxSubmit   value=""}}
{{mb_default var=reservations value=""}}
{{mb_default var=width_select value="15em"}}
{{mb_default var=classes      value=""}}

<select name="{{$field}}" {{if $ajaxSubmit}}onchange="this.form.onsubmit();"{{/if}} style="width: {{$width_select}};" class="{{$classes}}">
  <option value="">&mdash; Choisir un {{tr}}{{$field_view}}{{/tr}}</option>
  {{foreach from=$services item=_service}}
    <optgroup label="{{$_service->_view}}" data-service_id="{{$_service->_id}}">
      {{foreach from=$_service->_ref_chambres item=_chambre}}
        {{foreach from=$_chambre->_ref_lits item=_lit}}
          {{assign var=lit_id value=$_lit->_id}}
          <option value="{{$_lit->_id}}" {{if $selected_id == $_lit->_id}}selected{{/if}}
            {{if $lit_id != $selected_id && $reservations && isset($reservations.$lit_id|smarty:nodefaults)}}disabled{{/if}}>
            {{$_lit}}
          </option>
        {{/foreach}}
        {{foreachelse}}
        <option value="">Aucune chambre disponible</option>
      {{/foreach}}
    </optgroup>
    {{foreachelse}}
    <option value="">Aucun service disponible</option>
  {{/foreach}}
</select>
