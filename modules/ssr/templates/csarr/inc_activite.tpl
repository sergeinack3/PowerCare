{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl" style="text-align: left;">
  <tr>
    <th colspan="2" class="title">{{tr}}CActiviteCsARR{{/tr}} {{$activite->code}}</th>
  </tr>
  <tr>
    <td colspan="2" class="text">
      <strong>{{$activite->libelle}}</strong>
      <!-- Référence -->
      {{assign var=reference value=$activite->_ref_reference}}
      <div style="padding-left: 2em;">
        {{mb_include module=system template=inc_field_view object=$reference prop=dedie}}
        {{mb_include module=system template=inc_field_view object=$reference prop=non_dedie}}
        {{mb_include module=system template=inc_field_view object=$reference prop=collectif}}
        {{mb_include module=system template=inc_field_view object=$reference prop=pluripro}}
        {{mb_include module=system template=inc_field_view object=$reference prop=appareillage}}
      </div>
    </td>
  </tr>

  <!-- Hiérarchies -->
  <tr>
    <th colspan="2" class="section">{{tr}}CActiviteCsARR.back.hierarchies{{/tr}}</th>
  </tr>

  {{foreach from=$activite->_ref_hierarchies item=_hierarchie}}
    <tr>
      <td class="narrow">
        <button class="compact" style="width: 6em;" onclick="CsARR.viewHierarchie('{{$_hierarchie->code}}')">
          {{$_hierarchie->code}}
        </button>
      </td>
      <td class="text">
        {{$_hierarchie->libelle}}
      </td>
    </tr>
  {{/foreach}}

  <!-- Notes d'activité -->
  {{foreach from=$activite->_ref_notes_activites key=_type item=_notes_by_type}}
    <tr>
      <th colspan="2" class="section">{{$_type}}</th>
    </tr>
    <tr>
      <td colspan="2" class="text">
        {{foreach from=$_notes_by_type item=_note}}
        <div style="padding-left: {{math equation="n-1" n=$_note->niveau}}em;">
          {{if $_note->code_exclu}}
          <button class="compact" style="width: 5em;" onclick="CsARR.viewActivite('{{$_note->code_exclu}}')">
            {{$_note->code_exclu}}
          </button>
          {{$_note->libelle|substr:0:-10}}
          {{else}}
          &bull; {{$_note->libelle}}
          {{/if}}
        </div>
        {{/foreach}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="2" class="empty">{{tr}}CNoteActiviteCsARR.none{{/tr}}</td>
    </tr>
  {{/foreach}}

  <!-- Modulateurs d'activité -->
  <tr>
    <th colspan="2" class="section">{{tr}}CActiviteCsARR.back.modulateurs{{/tr}}</th>
  </tr>

  <tr>
    <td colspan="2" class="text">
      {{foreach from=$activite->_ref_modulateurs item=_modulateur}}
        <div>
          <strong>{{$_modulateur->modulateur}}</strong> : {{$_modulateur->_libelle}}
        </div>
      {{foreachelse}}
        <div class="empty">{{tr}}CModulateurCsARR{{/tr}}</div>
      {{/foreach}}
    </td>
  </tr>

  <!-- Gestes complémentaires -->
  <tr>
    <th colspan="2" class="section">{{tr}}CActiviteCsARR.back.gestes_complementaires{{/tr}}</th>
  </tr>

  <tr>
    <td colspan="2" class="text">
      {{foreach from=$activite->_ref_activites_complementaires item=_activite}}
        <div>
          <button class="compact search">{{$_activite->code}}</button>
          {{$_activite->libelle}}
        </div>
      {{foreachelse}}
        <div class="empty">{{tr}}CActiviteCsARR.back.gestes_complementaires.none{{/tr}}</div>
      {{/foreach}}
    </td>
  </tr>
</table>
