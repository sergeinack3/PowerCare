{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{unique_id var=unique_traitement}}

{{if $dossier_medical->_count_traitements_in_progress}}
  <span class="texticon texticon-traitement" style="box-shadow: none" onmouseover="ObjectTooltip.createDOM(this, 'traitements{{$sejour_id}}_{{$unique_traitement}}')">{{tr}}Traitements perso.{{/tr}}</span>

  <div id="traitements{{$sejour_id}}_{{$unique_traitement}}" style="text-align:left;  display: none;">
    <table class="tbl me-no-box-shadow">
      <tr>
        <th class="title">
            {{tr}}Traitements personnels{{/tr}} ({{$dossier_medical->_count_traitements_in_progress}})
        </th>
      </tr>
      {{foreach from=$dossier_medical->_traitements_in_progress key=type item=_traitements}}
        {{foreach from=$_traitements item=_traitement}}
          <tr>
            <td>
                {{if $type == "medicament"}}
                    {{$_traitement->_ucd_view}}
                {{else}}
                    {{$_traitement->_view}}
                {{/if}}
            </td>
          </tr>
        {{/foreach}}
      {{/foreach}}
    </table>
  </div>
{{/if}}
