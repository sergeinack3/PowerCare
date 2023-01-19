{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=in_compte_rendu value=false}}
{{mb_default var=see_unit value=true}}
{{mb_default var=print value=false}}

<table class="main tbl print">
  <tbody>
    {{foreach from=$observation_grid item=_row key=_datetime name=_observation_grid}}
      <tr style="page-break-inside: avoid;">
        <th class="narrow">
          {{$_datetime|date_format:$conf.datetime}}

          {{if !$print && !$in_compte_rendu}}
            <br />
            {{assign var=_obr value=$observation_list.$_datetime}}
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_obr->_ref_first_log->_ref_user->_ref_mediuser}}
          {{/if}}
        </th>

        {{foreach from=$_row item=_cell}}
          <td style="text-align: center;">
            {{if $_cell}}
              {{if $_cell->file_id && !$print}}
                {{if !$in_compte_rendu}}
                  {{thumbnail style="width: 50px;" document_id=$_cell->file_id document_class=CFile profile=small}}
                {{else}}
                  {{thumbnail document=$_cell->_ref_file profile=small style="width:50px"}}
                {{/if}}
              {{elseif $_cell->label_id}}
                {{mb_value object=$_cell field=label_id}}
              {{else}}
                {{$_cell->_value}}
                {{if $_cell->_unit_id && $see_unit}}
                  {{$_cell->_ref_value_unit->label}}
                {{/if}}
              {{/if}}
            {{/if}}
          </td>
        {{/foreach}}
      </tr>
    {{/foreach}}
  </tbody>
  <thead>
  {{if !$in_compte_rendu}}
    <tr>
      <th class="title"
          colspan="{{math equation="x+1" x=$observation_labels|@count}}">{{tr}}CSupervisionGraph-type-{{$type}}{{/tr}}</th>
    </tr>
  {{/if}}
  <tr>
    <th style="text-align: center;">{{mb_title class=CObservationResultSet field=datetime}}</th>

    {{foreach from=$observation_labels item=_label}}
      <th class="text" style="text-align: center;">{{$_label}}</th>
    {{/foreach}}
  </tr>
  </thead>
</table>
