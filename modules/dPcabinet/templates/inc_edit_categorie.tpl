{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=icone_selector ajax=1}}
{{mb_script module=cabinet script=edit_consultation ajax=1}}
{{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf && $app->user_prefs.allow_appfine_sync}}
  {{mb_script module=dPpatients script=exercice_place ajax=1}}
{{/if}}

<script>
  Main.add(function(){
    var form = getForm('editFrm');

    // Lieu AppFine - Prise RDV
    {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf && $app->user_prefs.allow_appfine_sync && !$selCabinet->_id}}
        ExercicePlace.loadExericePlaceByPratForMotif('{{$categorie->_id}}', $V(form.praticien_id));
    {{/if}}
  });
</script>

<form name="editFrm" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: Control.Modal.close});">
  {{mb_key object=$categorie}}
  {{mb_class object=$categorie}}
  <input type="hidden" name="function_id" value="{{$selCabinet->_id}}" />
  <input type="hidden" name="praticien_id" value="{{$selPrat->_id}}" />
  <input type="hidden" name="del" value="0" />
  <table class="form">
    <tr>
      {{if $categorie->_id}}
        <th class="title modify" colspan="2">
          {{mb_include module=system template=inc_object_idsante400 object=$categorie}}
          {{mb_include module=system template=inc_object_history object=$categorie}}

          {{tr}}CConsultationCategorie-_changing{{/tr}} &lsquo;{{$categorie->nom_categorie|spancate:35}}&rsquo;
        </th>
      {{else}}
        <th class="title me-th-new" colspan="2">
          {{tr}}CConsultationCategorie-_creating{{/tr}}
        </th>
      {{/if}}
    </tr>
    <tr>
      <th>{{mb_label object=$categorie field="nom_categorie"}}</th>
      <td >{{mb_field object=$categorie field="nom_categorie"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$categorie field="nom_icone"}}</th>
      <td>
        {{if $categorie->_id}}
          {{mb_include module=cabinet template=inc_icone_categorie_consult
          categorie=$categorie
          id="iconeBackground"
          onclick="IconeSelector.init()"
          }}
        {{else}}
          <img style="cursor:pointer" id="iconeBackground" src="images/icons/search.png" onclick="IconeSelector.init()"/>
        {{/if}}
        <input type="hidden" name="nom_icone" value="{{$categorie->nom_icone}}"  class="notNull" />
        <script>
          IconeSelector.init = function(){
            this.sForm = "editFrm";
            this.sView = "nom_icone";
            this.pop();
          }
        </script>
      </td>
    </tr>
    <tr>
      <th>
        {{if $selCabinet->_id}}
          {{mb_label object=$categorie field="function_id"}}
        {{else}}
          {{mb_label object=$categorie field="praticien_id"}}
        {{/if}}
      </th>
      <td>
        {{if $selCabinet->_id}}
          {{$selCabinet->_view}}
        {{else}}
          {{$selPrat->_view}}
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$categorie field="duree"}}</th>
      <td id="vw_categorie_td_choix_duree">
        {{foreach from=1|range:15 item=i}}
          <label>
            <input type="radio" value="{{$i}}" name="duree" {{if $categorie->duree == $i}}checked{{/if}}>x{{$i}}
          </label>
        {{/foreach}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$categorie field="commentaire"}}</th>
      <td id="vw_categorie_td_commentaires">{{mb_field object=$categorie field="commentaire" form="editFrm"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$categorie field="seance"}}</th>
      <td>{{mb_field object=$categorie field="seance"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$categorie field="max_seances"}}</th>
      <td>{{mb_field object=$categorie field="max_seances"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$categorie field="anticipation"}}</th>
      <td>{{mb_field object=$categorie field="anticipation"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$categorie field="couleur"}}</th>
      <td>{{mb_field object=$categorie field="couleur" form=editFrm register=true}}</td>
    </tr>
    {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf && $app->user_prefs.allow_appfine_sync}}
      <tr>
        <th colspan="2" class="title">{{tr}}AppFine{{/tr}}</th>
      </tr>
      {{if $selCabinet->_id}}
          <tr>
              <td colspan="2">
                  <div class="small-warning">{{tr}}AppFineClient-msg-Impossible to synchro this category{{/tr}}</div>
              </td>
          </tr>
      {{else}}
          <tr>
              <th>{{mb_label object=$categorie field="sync_appfine" typeEnum="checkbox"}}</th>
              {{if count($praticiens) > 0}}
                  <td>
                      <div class="small-info">
                          <p>{{tr}}appFineClient-msg-Consultation cat used by plage|pl{{/tr}}</p>
                          <ul>
                              {{foreach from=$praticiens item=_praticien}}
                                  <li>{{$_praticien->_view}}</li>
                              {{/foreach}}
                          </ul>
                      </div>
                  </td>
              {{else}}
                  <td>{{mb_field object=$categorie field="sync_appfine" typeEnum="checkbox"}}</td>
              {{/if}}
          </tr>
          <tr>
              <th>{{mb_label object=$categorie field="authorize_booking_new_patient" typeEnum="checkbox"}}</th>
              <td>{{mb_field object=$categorie field="authorize_booking_new_patient" typeEnum="checkbox"}}</td>
          </tr>
          {{if "teleconsultation"|module_active && $allow_teleconsultation}}
              <tr>
                  <th>{{mb_label object=$categorie field="eligible_teleconsultation" typeEnum="checkbox"}}</th>
                  <td>{{mb_field object=$categorie field="eligible_teleconsultation" typeEnum="checkbox"}}</td>
              </tr>
          {{/if}}
          <tr>
              <th>{{mb_label object=$categorie field="exercice_place_id"}}</th>
              <td id="exercice_places"></td>
          </tr>
      {{/if}}
    {{/if}}
    <tr>
      <td class="button" colspan="2">
        {{if $categorie->_id}}
          <button id="vw_categorie_button_modif_categorie" class="modify" type="submit">{{tr}}Validate{{/tr}}</button>
          <button id="vw_categorie_button_trash_categorie" class="trash" type="button" onclick="confirmDeletion(
              this.form, {
                ajax: true,
                objName: '{{$categorie->nom_categorie|smarty:nodefaults|JSAttribute}}'
              },
              Control.Modal.close
            )">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button id="vw_categorie_button_create_categorie" type="button" class="submit" name="btnFuseAction"
                  onclick="Consultation.checkSessionGroupExist(this.form);">
            {{tr}}Create{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
