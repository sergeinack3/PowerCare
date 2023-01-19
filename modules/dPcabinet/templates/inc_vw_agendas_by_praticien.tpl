{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if count($agendas_praticien)}}
  {{me_form_field nb_cells=4 mb_object=$plage mb_field="agenda_praticien_id"}}
    <select name="agenda_praticien_id" style="width: 15em;">
      <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
      {{foreach from=$agendas_praticien item=_agenda_praticien}}
        <option value="{{$_agenda_praticien->_id}}"
                {{if $plage->agenda_praticien_id === $_agenda_praticien->_id}}selected{{/if}}>{{$_agenda_praticien->_ref_lieu->label}}</option>
      {{/foreach}}
    </select>
  {{/me_form_field}}
{{else}}
  <input type="hidden" name="agenda_praticien_id" />
{{/if}}