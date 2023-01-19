{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{math equation="x+5" x='Ox\Mediboard\Pmsi\CRelancePMSI'|static:"docs"|@count assign=colspan}}

{{foreach from='Ox\Mediboard\Pmsi\CRelancePMSI'|static:"docs" item=doc}}
  {{if !"dPpmsi relances $doc"|gconf}}
    {{math equation=x-1 x=$colspan assign=colspan}}
  {{/if}}
{{/foreach}}

<script>
  Main.add(function() {
    Control.Tabs.setTabCount("relances", {{$relances|@count}});
  });
</script>

<table class="tbl">
  <tr>
    <th class="narrow"></th>
    <th>Patient</th>
    <th>Séjour</th>
    {{foreach from='Ox\Mediboard\Pmsi\CRelancePMSI'|static:"docs" item=doc}}
      {{if "dPpmsi relances $doc"|gconf}}
        <th style="width: 46px;" title="{{tr}}CRelancePMSI-{{$doc}}-desc{{/tr}}">{{tr}}CRelancePMSI-{{$doc}}-court{{/tr}}</th>
      {{/if}}
    {{/foreach}}
    <th>Comm. DIM</th>
    <th>Comm. Méd.</th>
  </tr>
  {{foreach from=$relances item=_relance}}
  <tr>
    {{assign var=patient value=$_relance->_ref_patient}}
    {{assign var=sejour value=$_relance->_ref_sejour}}
    <td>
      <button type="button" class="edit notext" title="{{tr}}Edit{{/tr}}"
              onclick="Relance.edit('{{$_relance->_id}}', null, updateRelances);"></button>
    </td>
    <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
        {{$patient}}
      </span>
    </td>
    <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">
        {{$sejour}}
      </span>
    </td>
    {{foreach from='Ox\Mediboard\Pmsi\CRelancePMSI'|static:"docs" item=doc}}
      {{if "dPpmsi relances $doc"|gconf}}
        <td style="text-align: center;">
          {{if $_relance->$doc}}
            <span {{if $doc == "autre"}}title="{{$_relance->description}}" style="cursor: pointer;"{{/if}}>
              X
            </span>
          {{/if}}
        </td>
      {{/if}}
    {{/foreach}}
    <td>
      {{$_relance->commentaire_dim|spancate:40:"...":true}}
    </td>
    <td>
      {{$_relance->commentaire_med|spancate:40:"...":true}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="{{$colspan}}" class="empty">
      {{tr}}CRelancePMSI.none{{/tr}}
    </td>
  </tr>
  {{/foreach}}
</table>