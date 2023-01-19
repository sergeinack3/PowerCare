{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="3">
      Liste des plages
    </th>
  </tr>
  {{if $plages|@count || $hors_plage|@count}}
    {{foreach from=$plages item=_plage}}
      <tr>
        <th colspan="3">
          Plage du {{mb_value object=$_plage field=date}}
          {{if $all_prats}}
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_plage->_ref_chir}}
          {{/if}}
        </th>
      </tr>
      {{foreach from=$_plage->_ref_operations item=_operation}}
        {{assign var=codes_ccam value=$_operation->codes_ccam}}
        <tr>
          <td class="narrow">
            {{assign var=patient value=$_operation->_ref_patient}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
              {{$patient}}
            </span>
          </td>
          <td class="text">
            <a href="#1" onclick="Operation.dossierBloc('{{$_operation->_id}}'); return false;">
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}')">
                {{$_operation}} ({{$_operation->_actes_non_cotes}} acte(s) non coté(s))
              </span>
            </a>
          </td>
          <td class="text">
            {{if $_operation->libelle}}
              {{$_operation->libelle}}
            {{else}}
              {{" ; "|implode:$_operation->_codes_ccam}}
            {{/if}}
          </td>
        </tr>
      {{/foreach}}
    {{/foreach}}
    {{if $hors_plage|@count}}
      <tr>
        <th>Hors plages</th>
      </tr>
      {{foreach from=$hors_plage item=_operation}}
        {{assign var=codes_ccam value=$_operation->codes_ccam}}
        <tr>
          <td class="narrow">
            {{assign var=patient value=$_operation->_ref_patient}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
              {{$patient}}
            </span>
          </td>
          <td class="text">
            <a href="#1" onclick="Operation.dossierBloc('{{$_operation->_id}}'); return false;">
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}')">
                {{$_operation}} ({{$_operation->_actes_non_cotes}} acte(s) non coté(s))
              </span>
            </a>
          </td>
          <td class="text">
            {{if $_operation->libelle}}
              {{$_operation->libelle}}
            {{else}}
              {{" ; "|implode:$_operation->_codes_ccam}}
            {{/if}}
          </td>
        </tr>
      {{/foreach}}
    {{/if}}
  {{else}}
    <tr>
      <td class="empty">{{tr}}COperation.none_non_cotee{{/tr}}</td>
    </tr>
  {{/if}}
</table>