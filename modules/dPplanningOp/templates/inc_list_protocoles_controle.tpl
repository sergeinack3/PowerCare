{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination total=$count current=$page change_page="refreshListProtocoles" step=$step}}

<table class="tbl">
  <tr>
    <th>
      {{mb_label class=CProtocole field=libelle}}
    </th>
    <th class="narrow">
      {{mb_label class=CProtocole field=temp_operation}}
    </th>
    <th class="narrow">
      Temps médian
    </th>
    <th class="narrow">
      <button type="button" class="change notext singleclick" onclick="updateDurees('{{$page}}');">{{tr}}CProtocole-_update_durees{{/tr}}</button>
    </th>
  </tr>
  {{foreach from=$protocoles_by_type item=protocoles key=type}}
    {{foreach from=$protocoles item=_protocole name="protocole_by_type"}}
      {{if $smarty.foreach.protocole_by_type.first}}
        <tr>
          <th class="section" colspan="4">
            {{if $type == "function"}}
              {{$_protocole->_ref_function->_view}}
            {{elseif $type == "libelle"}}
              {{$libelle}}
            {{else}}
              {{$_protocole->_ref_chir->_view}}
            {{/if}}
          </th>
        </tr>
      {{/if}}
      <tr>
        <td class="text">
          <strong>
            {{if $_protocole->libelle}}
            {{mb_value object=$_protocole field=libelle}}
            {{else}}
            {{tr}}CProtocole-No label{{/tr}}
            {{/if}}
          </strong>
          <br />
          {{if $_protocole->duree_hospi}}
            {{$_protocole->duree_hospi}} nuits en
          {{/if}}

          {{mb_value object=$_protocole field=type}}
          {{if $_protocole->chir_id}}
          - Dr {{$_protocole->_ref_chir}}
          {{else}}
          - {{$_protocole->_ref_function}}
          {{/if}}
        </td>
        <td style="text-align: center;">
          {{mb_value object=$_protocole field=temp_operation}}
        </td>
        <td style="text-align: center;"
            class="{{if $_protocole->_temps_median}}
              {{if $_protocole->_temps_median == $_protocole->temp_operation}}
              median_ok
              {{else}}
              median_nonok
              {{/if}}
            {{/if}}">
          {{mb_value object=$_protocole field=_temps_median}}
        </td>
        <td class="narrow">
          {{if $_protocole->_temps_median && $_protocole->_temps_median != $_protocole->temp_operation}}
            <button type="button" class="change notext update_duree"
                    onclick="updateDuree('{{$_protocole->_id}}', '{{$_protocole->_temps_median}}', '{{$page}}')">{{tr}}CProtocole-_update_duree{{/tr}}</button>
          {{/if}}
        </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td class="empty" colspan="3">{{tr}}CProtocole.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="3">{{tr}}CProtocole.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>