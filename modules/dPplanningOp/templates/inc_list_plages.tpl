{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create("main_tab_group", true);
  });
</script>

{{if $listPlages|@count > 1}}
  <div class="small-warning">
    Plusieurs blocs sont disponibles, veillez à bien choisir le bloc souhaité
  </div>
{{elseif $listPlages|@count == 0}}
  <div class="small-info">
    Vous n'avez pas de plage ce mois-ci, vous pouvez contacter le responsable de bloc pour ajouter une vacation
  </div>

  {{mb_return}}
{{/if}}

<ul id="main_tab_group" class="control_tabs">
  {{foreach from=$listPlages key=_key_bloc item=_blocplage}}
    {{assign var=_bloc value=$blocs.$_key_bloc}}
    <li><a href="#bloc-{{$_bloc->_id}}">{{$_bloc->_view}} ({{$_blocplage|@count}})</a></li>
  {{/foreach}}
</ul>

{{foreach from=$listPlages key=_key_bloc item=_blocplage}}
  {{assign var=_bloc value=$blocs.$_key_bloc}}
  {{assign var=date_min value=$_bloc->_date_min}}
  <table class="tbl" id="bloc-{{$_bloc->_id}}" style="display: none;">
    <tr>
      <th class="category" colspan="4">
        Choisir une date
      </th>
    </tr>
    {{foreach from=$_blocplage item=_plage}}
      <tr>
        <td class="narrow">
          {{mb_include module=system template=inc_object_notes object=$_plage}}
        </td>
        <td class="narrow">
          {{if $_plage->spec_id}}
            <img src="images/icons/user-function.png" />
          {{else}}
            <img src="images/icons/user.png" />
          {{/if}}
        </td>
        <td>
          {{assign var=pct value=$_plage->_fill_rate}}
          {{if $pct < 100}}
            {{assign var=backgroundClass value="normal"}}
          {{elseif $pct == 100}}
            {{assign var=backgroundClass value="booked"}}
          {{else}}
            {{assign var=backgroundClass value="full"}}
          {{/if}}

          {{if $pct > 100}}
            {{assign var=pct value=100}}
          {{/if}}
          <label for="list_{{$_plage->_id}}"
            {{if $resp_bloc || $_plage->_verrouillee|@count == 0}}
              ondblclick="setClose('{{$_plage->date}}', '{{$_plage->salle_id}}', '{{$_plage->_id}}')"
              onclick="showProgramme({{$_plage->_id}}, DHEMultiple.actual_rank);
                var form = getForm('plageSelectorFrm' + DHEMultiple.actual_rank);
                $V(form._date, '{{$_plage->date}}');
                $V(form._salle_id, '{{$_plage->salle_id}}');
                $V(form._plage_id, '{{$_plage->_id}}');"
            {{else}}
              style="cursor: not-allowed;"
            {{/if}}>
            <div class="progressBar" style="width: 98%;{{if $_plage->spec_id}}height: 25px;{{/if}}">
              <div class="bar {{$backgroundClass}}" style="width: {{$pct}}%;"></div>
              <div class="text me-line-height-12" style="text-align: left">
                {{$_plage->date|date_format:"%a %d"}} -
                {{$_plage->debut|date_format:$conf.time}} -
                {{$_plage->fin|date_format:$conf.time}}
                &mdash; {{$_plage->_ref_salle->_view}}
                {{if $_plage->spec_id}}
                  <br />{{$_plage->_ref_spec->_view|truncate:50}}
                {{/if}}
              </div>
            </div>
          </label>
        </td>
        <td class="narrow">
          {{if $resp_bloc || $_plage->_verrouillee|@count == 0}}
            <input type="radio" name="list" id="list_{{$_plage->_id}}" value="{{$_plage->_id}}"
                   {{if !$multiple}}
                   ondblclick="setClose('{{$_plage->date}}', '{{$_plage->salle_id}}', '{{$_plage->_id}}')"
                   {{/if}}
                   onclick="showProgramme({{$_plage->_id}}, DHEMultiple.actual_rank);
                     var form = getForm('plageSelectorFrm' + DHEMultiple.actual_rank);
                     $V(form._date, '{{$_plage->date}}');
                     $V(form._salle_id, '{{$_plage->salle_id}}');
                     $V(form._plage_id, '{{$_plage->_id}}');"/>
          {{/if}}
          {{if $_plage->_verrouillee|@count > 0}}
            <i class="me-icon lock me-primary" onmouseover="ObjectTooltip.createDOM(this, 'verrou_{{$_plage->_guid}}')"></i>
            <div style="display: none;" id="verrou_{{$_plage->_guid}}">
              Impossible {{if $resp_bloc}}pour le personnel{{/if}} de planifier à cette date :
              <ul>
                {{foreach from=$_plage->_verrouillee item=_raison name=foreach_verrou}}
                  <li>
                    {{tr}}CPlageOp._verrouillee.{{$_raison}}{{/tr}}
                  </li>
                {{/foreach}}
              </ul>
            </div>
          {{/if}}
        </td>
      </tr>
    {{/foreach}}
  </table>
{{/foreach}}

<table class="tbl me-small">
  <tr>
    <th class="category" colspan="3">
      Légende
    </th>
  </tr>
  <tr>
    <td class="narrow button">
      <img src="images/icons/user.png" />
    </td>
    <td colspan="2">plage personnelle</td>
  </tr>
  <tr>
    <td class="button">
      <img src="images/icons/user-function.png" />
    </td>
    <td colspan="2">plage de spécialité</td>
  </tr>
  <tr>
    <td>
      <div class="progressBar">
        <div class="bar full"></div>
      </div>
    </td>
    <td colspan="2">plage pleine</td>
  </tr>
  <tr>
    <td>
      <div class="progressBar">
        <div class="bar booked"></div>
      </div>
    </td>
    <td colspan="2">plage presque pleine</td>
  </tr>
  <tr>
    <td>
      <div class="progressBar">
        <div class="bar normal" style="width: 60%;"></div>
      </div>
    </td>
    <td colspan="2">taux de remplissage</td>
  </tr>
  <tr>
    <td class="button">
      <div class="rank">1</div>
    </td>
    <td colspan="2">intervention validée par le bloc</td>
  </tr>
  <tr>
    <td class="button">
      <div class="rank desired" title="Pas encore validé par le bloc">2</div>
    </td>
    <td colspan="2">intervention ayant un ordre de passage souhaité</td>
  </tr>
</table>
