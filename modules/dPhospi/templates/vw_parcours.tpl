{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=canCabinet value=$modules.dPcabinet->_can}}

<script>
  ViewFullPatient = {
    select: function (eLink) {
      // Select current row
      if (this.eCurrent) {
        Element.classNames(this.eCurrent).remove("selected");
      }
      this.eCurrent = eLink.parentNode.parentNode;
      Element.classNames(this.eCurrent).add("selected");
    }
  };

  effectHighlight = () => {
    var elts = document.getElementsByClassName("current");
    for (var i = 0; i < elts.length; i++) {
      new Effect.Highlight(elts[i]);
    }
  };

  editIntervention = (op_id) => {
    window.opener.location.href = "?m=planningOp&tab=vw_edit_planning&operation_id=" + op_id;
  };

  Main.add(function () {
    Control.Tabs.create('main_tab_group{{$sejour->_id}}');
    new PeriodicalExecuter(effectHighlight, 1);
  });
</script>

<ul id="main_tab_group{{$sejour->_id}}" class="control_tabs me-small">
  <li><a href="#parcour">Parcours</a></li>
  <li><a href="#mouvements">Mouvements</a></li>
</ul>

<div id="parcour" style="display: none;" class="me-color-black-high-emphasis">
  {{if $sejour->_ref_service->is_soins_continue || $sejour->_ref_last_affectation->_ref_service->is_soins_continue}}
    <div class="small-info">{{tr}}CService-msg.is_soins_continue{{/tr}}</div>
  {{/if}}
  <table id="diagramme" class="me-parcours">
    <tr>
      <th colspan=5>{{$sejour->_view}} <br /><br /></th>
    </tr>
    <tr>
      {{if ($diagramme.bloc.type) != "none"}}
        <td class="only done ray me-step" colspan=2> ADMIS <br /> Date : {{$diagramme.admission.entree.date|date_format:$conf.datetime}}</td>
      {{else}}
        <td class="only me-step" colspan=2> ADMIS <br /> Date : {{$diagramme.admission.entree.date|date_format:$conf.datetime}}</td>
      {{/if}}
      <td></td>
      {{if ($diagramme.bloc.type) != "none" && $diagramme.admission.sortie.reelle == "sortie_prevue"}}
        <td class="only expect ray me-step" colspan=2> SORTIE <br /> Date : {{$diagramme.admission.sortie.date|date_format:$conf.datetime}}
          <br /> Mode Sortie : {{$diagramme.admission.sortie.mode_sortie}}</td>
      {{elseif ($diagramme.admission.sortie.reelle) == "sortie_reelle"}}
        <td class="only current me-step" colspan=2> SORTIE <br /> Date : {{$diagramme.admission.sortie.date|date_format:$conf.datetime}} <br />
          Mode Sortie : {{$diagramme.admission.sortie.mode_sortie}}</td>
      {{else}}
        <td class="only me-step" colspan=2> SORTIE <br /> Date : {{$diagramme.admission.sortie.date|date_format:$conf.datetime}} <br /> Mode
          Sortie : {{$diagramme.admission.sortie.mode_sortie}}</td>
      {{/if}}
      <td></td>
    </tr>
    <tr>
      <td class="arrowdown me-no-bg" colspan=2></td>
      <td></td>
      <td class="arrowup me-no-bg" colspan=2></td>
      <td></td>
    </tr>
    <tr>
      {{if ($diagramme.admission.sortie.reelle) == "sortie_reelle"}}
        <td class="only done ray me-step" colspan=5> HOSPITALISÉ <br /> Chambre : {{$diagramme.hospitalise.chambre}}</td>
      {{else}}
        <td class="only current me-step" colspan=5> HOSPITALISÉ <br /> Chambre : {{$diagramme.hospitalise.chambre}}</td>
      {{/if}}
      <td>
        <fieldset>
          <legend>Liste des affectations</legend>
          {{foreach from=$affectations item=curr_aff}}
            <span class="me-block me-padding-2 {{if ($curr_aff->_id == $diagramme.hospitalise.affectation)}}listeCurrent{{/if}}">
            <br class="me-no-display" />
            <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_aff->_guid}}')">
              Affectations du {{$curr_aff->entree|date_format:$conf.date}}
              au {{$curr_aff->sortie|date_format:$conf.date}}
            </span>
          </span>
          {{/foreach}}
        </fieldset>
      </td>
    </tr>
    {{if $diagramme.bloc}}
      <tr>
        <td class="space"></td>
        <td class="arrowdown me-no-bg" colspan=1></td>
        <td></td>
        <td class="arrowup me-no-bg" colspan=1></td>
        <td class="space"></td>
        <td></td>
      </tr>
      <tr>
        {{if ($diagramme.bloc.sortieSalleReveil) != ""}}
          <td class="space"></td>
          <td class="only done ray me-step" colspan=3> AU BLOC <br />
            <span onmouseover="ObjectTooltip.createEx(this, 'COperation-{{$diagramme.bloc.id}}')">
              {{$diagramme.bloc.vue}}
          </span>
          </td>
          <td class="space"></td>
        {{elseif (($diagramme.bloc.salle != "") || ($diagramme.bloc.bloc != "")) && ($diagramme.bloc.sortieSalleReveil == "")}}
          <td class="space"></td>
          <td class="only current me-step" colspan=3> AU BLOC <br />
            <span onmouseover="ObjectTooltip.createEx(this, 'COperation-{{$diagramme.bloc.id}}')">
              {{$diagramme.bloc.vue}}
          </span>
          </td>
          <td class="space"></td>
        {{else}}
          <td class="space"></td>
          <td class="only expect ray me-step" colspan=3> AU BLOC <br />
            <span onmouseover="ObjectTooltip.createEx(this, 'COperation-{{$diagramme.bloc.id}}')">
            {{$diagramme.bloc.vue}}
          </span>
          </td>
          <td class="space"></td>
        {{/if}}
        <td colspan=2>
          <fieldset>
            <legend>Liste des interventions</legend>
            {{foreach from=$sejour->_ref_operations item=curr_op}}
              {{if ($diagramme.bloc.checkCurrent == "check" && $diagramme.bloc.idCurrent == $curr_op->_id)}}
                <span class="me-block me-padding-2 {{if ($curr_op->_id == $diagramme.bloc.id)}}listeCurrent{{/if}}">
              <br class="me-no-display" />
                {{if $canCabinet->edit}}
                  <a href="#" title="Modifier l'intervention" onclick="editIntervention({{$curr_op->_id}})">
                  {{me_img src="edit.png" icon="tick" class="me-success" alt="modifier" icon="edit" class="me-primary"}}
                </a>
                {{/if}}
                  {{me_img src="tick.png" icon="tick" class="me-success" alt="edit" title="CSejour-_etat"}}
              <a href="?m=hospi&dialog=1&a=vw_parcours&sejour_id={{$sejour->_id}}&operation_id={{$curr_op->_id}}"
                 onmouseover="ObjectTooltip.createEx(this, '{{$curr_op->_guid}}')">
                 Intervention du {{$curr_op->_datetime|date_format:$conf.datetime}}
              </a>
            </span>
              {{else}}
                <span class="me-block me-padding-2 {{if ($curr_op->_id == $diagramme.bloc.id)}}listeCurrent{{/if}}">
              <br class="me-no-display" />
                {{if $canCabinet->edit}}
                  <a href="#" title="Modifier l'intervention" onclick="editIntervention({{$curr_op->_id}})">
                  {{me_img src="edit.png" icon="edit" class="me-primary" alt_tr="common-action-Plan"}}
                </a>
                {{/if}}
                <a href="?m=hospi&dialog=1&a=vw_parcours&sejour_id={{$sejour->_id}}&operation_id={{$curr_op->_id}}"
                   onmouseover="ObjectTooltip.createEx(this, '{{$curr_op->_guid}}')">
                Intervention du {{$curr_op->_datetime|date_format:$conf.datetime}}
              </a>
            </span>
              {{/if}}
            {{/foreach}}
          </fieldset>
        </td>
      </tr>
      <tr>
        <td class="space"></td>
        <td class="arrowdown me-no-bg" colspan=1></td>
        <td></td>
        <td class="arrowup me-no-bg" colspan=1></td>
        <td class="space"></td>
        <td></td>
      </tr>
      {{if $diagramme.bloc.type == "current"}}
        <tr>
          {{if $diagramme.bloc.sortieSalle == ""}}
            <td class="space"></td>
            <td class="only current me-step"> EN SALLE <br /> Heure : {{$diagramme.bloc.salle|date_format:$conf.time}}</td>
          {{else}}
            <td class="space"></td>
            <td class="only done ray me-step"> EN SALLE <br /> Heure : {{$diagramme.bloc.salle|date_format:$conf.time}}</td>
          {{/if}}
          <td></td>
          {{if $diagramme.bloc.sortieSalleReveil == ""}}
            <td class="only expect ray me-step"> SORTIE SALLE DE RÉVEIL</td>
            <td class="space"></td>
          {{else}}
            <td class="only done ray me-step"> SORTIE SALLE DE RÉVEIL <br /> Heure
              : {{$diagramme.bloc.sortieSalleReveil|date_format:$conf.time}} </td>
            <td class="space"></td>
          {{/if}}
          <td></td>
        </tr>
        <tr>
          <td class="space"></td>
          <td class="arrowdown me-no-bg" colspan=1></td>
          <td></td>
          <td class="arrowup me-no-bg" colspan=1></td>
          <td class="space"></td>
          <td></td>
        </tr>
        <tr>
          {{if $diagramme.bloc.sortieSalle == ""}}
            <td class="space"></td>
            <td class="only expect ray me-step"> SORTIE DE SALLE</td>
          {{elseif $diagramme.bloc.salleReveil != ""}}
            <td class="space"></td>
            <td class="only done ray me-step"> SORTIE DE SALLE <br /> Heure : {{$diagramme.bloc.sortieSalle|date_format:$conf.time}} </td>
          {{else}}
            <td class="space"></td>
            <td class="only current me-step"> SORTIE DE SALLE <br /> Heure : {{$diagramme.bloc.sortieSalle|date_format:$conf.time}} </td>
          {{/if}}
          <td class="arrowright me-no-bg"></td>
          {{if $diagramme.bloc.salleReveil == ""}}
            <td class="only expect ray me-step"> EN SALLE DE RÉVEIL</td>
            <td class="space"></td>
          {{elseif ($diagramme.bloc.sortieSalleReveil) != ""}}
            <td class="only done ray me-step"> EN SALLE DE RÉVEIL <br /> Heure : {{$diagramme.bloc.salleReveil|date_format:$conf.time}} </td>
            <td class="space"></td>
          {{else}}
            <td class="only current me-step"> EN SALLE DE RÉVEIL <br /> Heure : {{$diagramme.bloc.salleReveil|date_format:$conf.time}} </td>
            <td class="space"></td>
          {{/if}}
          <td></td>
        </tr>
      {{elseif $diagramme.bloc.type == "done"}}
        <tr>
          <td class="space"></td>
          <td class="only done ray"> EN SALLE <br /> Heure : {{$diagramme.bloc.salle|date_format:$conf.time}}</td>
          <td></td>
          <td class="only done ray me-step"> SORTIE SALLE DE RÉVEIL <br /> Heure : {{$diagramme.bloc.sortieSalleReveil|date_format:$conf.time}}
          </td>
          <td class="space"></td>
          <td></td>
        </tr>
        <tr>
          <td class="space"></td>
          <td class="arrowdown me-no-bg" colspan=1></td>
          <td></td>
          <td class="arrowup me-no-bg" colspan=1></td>
          <td class="space"></td>
          <td></td>
        </tr>
        <tr>
          <td class="space"></td>
          <td class="only done ray me-step"> SORTIE DE SALLE <br /> Heure : {{$diagramme.bloc.sortieSalle|date_format:$conf.time}} </td>
          <td class="arrowright me-no-bg"></td>
          <td class="only done ray me-step"> EN SALLE DE RÉVEIL <br /> Heure : {{$diagramme.bloc.salleReveil|date_format:$conf.time}} </td>
          <td class="space"></td>
          <td></td>
        </tr>
      {{elseif $diagramme.bloc.type == "expect"}}
        <tr>
          <td class="space"></td>
          <td class="only expect ray me-step"><br /> EN SALLE<br /><br /></td>
          <td></td>
          <td class="only expect ray me-step"> SORTIE SALLE DE RÉVEIL</td>
          <td class="space"></td>
          <td></td>
        </tr>
        <tr>
          <td class="space"></td>
          <td class="arrowdown me-no-bg" colspan=1></td>
          <td></td>
          <td class="arrowup me-no-bg" colspan=1></td>
          <td class="space"></td>
          <td></td>
        </tr>
        <tr>
          <td class="space"></td>
          <td class="only expect ray me-step"><br />SORTIE DE SALLE <br /><br /></td>
          <td class="arrowright me-no-bg"></td>
          <td class="only expect ray me-step"> EN SALLE DE RÉVEIL</td>
          <td class="space"></td>
          <td></td>
        </tr>
      {{/if}}
    {{/if}}
  </table>
</div>

<div id="mouvements" style="display: none;">
  {{mb_include module=hospi template=inc_movements}}
</div>
