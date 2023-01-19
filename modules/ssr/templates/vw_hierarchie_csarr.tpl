{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl" style="text-align: left;">
  <tr>
    <th colspan="2" class="title">{{tr}}CActiviteCsARR-hierarchie{{/tr}} {{$hierarchie->code}}</th>
  </tr>
  <tr>
    <td colspan="2" class="text">
      <strong>{{$hierarchie->libelle}}</strong>
      <hr />
    </td>
  </tr>

  <!-- Hiérarchies parentes -->
  <tr>
    <th colspan="2" class="section">{{tr}}CActiviteCsARR.back.parent_hierarchies{{/tr}}</th>
  </tr>

  {{foreach from=$hierarchie->_ref_parent_hierarchies item=_hierarchie}}
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

  <!-- Notes de hierarchies -->
  {{foreach from=$hierarchie->_ref_notes_hierarchies key=_type item=_notes_by_type}}
    <tr>
      <th colspan="2" class="section">{{tr}}CNoteHierarchieCsARR.typenote.{{$_type}}{{/tr}}</th>
    </tr>
    <tr>
      <td colspan="2" class="text">
        {{foreach from=$_notes_by_type item=_note}}
          <div style="padding-left: {{math equation="n-1" n=$_note->niveau}}em;">
            {{if $_note->code_exclu}}
              <button class="compact search" onclick="CsARR.viewActivite('{{$_note->code_exclu}}')">
                {{$_note->code_exclu}}
              </button>
              {{$_note->libelle|substr:0:-10}}
            {{elseif $_note->hierarchie_exclue}}
              <button class="compact search" style="width: 6em;" onclick="CsARR.viewHierarchie('{{$_note->hierarchie_exclue}}')">
                {{$_note->hierarchie_exclue}}
              </button>
              {{$_note->libelle}}
            {{elseif $_note->ordre == "1"}}
              <strong>{{$_note->libelle}}</strong>
            {{else}}
              &bull; {{$_note->libelle}}
            {{/if}}
          </div>
        {{/foreach}}
      </td>
    </tr>
  {{/foreach}}

  <!-- Hiérarchies filles -->
  {{if count($hierarchie->_ref_child_hierarchies)}}
    <tr>
      <th colspan="2" class="section">{{tr}}CActiviteCsARR.back.child_hierarchies{{/tr}}</th>
    </tr>
    {{foreach from=$hierarchie->_ref_child_hierarchies item=_hierarchie}}
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
  {{/if}}

  <!-- Activités -->
  {{if count($hierarchie->_ref_activites)}}
    <tr>
      <th colspan="2" class="section">{{tr}}CActiviteCsARR.back.activites{{/tr}}</th>
    </tr>
    {{foreach from=$hierarchie->_ref_activites item=_activite}}
    <tr>
      <td class="narrow">
        <button class="compact" style="width: 6em;" onclick="CsARR.viewActivite('{{$_activite->code}}')">
          {{$_activite->code}}
        </button>
      </td>
      <td class="text">
        {{$_activite->libelle}}
      </td>
    </tr>
    {{/foreach}}
  {{/if}}
</table>
