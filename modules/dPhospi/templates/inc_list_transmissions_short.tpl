{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=all_content value=0}}
<table class="tbl">
  <tr>
    <th class="title" colspan="{{if $all_content}}5{{else}}4{{/if}}">
      {{if $transmission->cible_id}}
        {{$transmission->_ref_cible->_view|smarty:nodefaults|truncate:50:"...":false}}
      {{elseif $transmission->libelle_ATC}}
        {{$transmission->libelle_ATC}}
      {{else}}
        {{$transmission->_ref_object}}
      {{/if}}
    </th>
  </tr>
  <tr>
    {{if $all_content}}
      <th class="category text">
        {{tr}}CTransmissionMedicale-object_id{{/tr}}
      </th>
    {{/if}}
    <th class="category text">
      {{tr}}CTransmissionMedicale-user_id{{/tr}}
    </th>
    <th class="category text">
      {{tr}}CTransmissionMedicale-date{{/tr}}
    </th>
    <th class="category text">
      {{tr}}CTransmissionMedicale._heure{{/tr}}
    </th>
    <th class="category">
      {{tr}}CTransmissionMedicale-text{{/tr}}
    </th>
  </tr>
  <tbody>
    {{foreach from=$transmissions item=_transmission}}
      <tr>
        {{if $all_content}}
          <td>
            {{if $_transmission->_ref_object instanceof CAdministration}}
              Administration le {{$_transmission->_ref_object->dateTime|date_format:$conf.date}} à {{$_transmission->_ref_object->dateTime|date_format:$conf.time}}

            {{else}}
              {{$_transmission->_ref_object}}
            {{/if}}
          </td>
        {{/if}}
        <td>
          {{$_transmission->_ref_user}}
        </td>
        <td>
          {{mb_ditto name=date value=$_transmission->date|date_format:$conf.date}}
        </td>
        <td>
          {{$_transmission->date|date_format:$conf.time}}
        </td>
        <td class="text {{if $_transmission->type}}trans-{{$_transmission->type}}{{/if}} libelle_trans" {{if $_transmission->degre == "high"}} style="background-color: #faa" {{/if}}>
          {{if !$all_content}}
            <button class="add notext" type="button" data-text="{{$_transmission->text}}" onclick="completeTrans('{{$_transmission->type}}',this);" style="float: right;">{{tr}}Add{{/tr}}</button>
          {{/if}}
          {{mb_value object=$_transmission field=text}}
        </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="4" class="empty">
          {{tr}}CTransmissionMedicale.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
  </tbody>
</table>

