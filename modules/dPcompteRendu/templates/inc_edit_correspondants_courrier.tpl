{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=correspondants value=$compte_rendu->_refs_correspondants_courrier_by_tag_guid}}

{{if $patient->_id}}
  {{mb_script module=patients script=correspondant ajax=true}}
  {{mb_script module=patients script=medecin       ajax=true}}
  {{mb_script module=patients script=salutation    ajax=true}}
{{/if}}

<script>
  Main.add(function () {
    var form = getForm("editFrm");
    var formCorres = getForm("addCorrespondant");
    var url = new Url("dPpatients", "httpreq_do_medecins_autocomplete");
    url.autoComplete(form._view, "correspondants_area", {
      minChars: 2,
      dropdown: true,
      callback: function(input, queryString){
        return queryString+"&all_departements="+(input.form.all_departements.checked ? 1 : 0);
      },
      afterUpdateElement: function(input, selected) {
        var medecin_id = selected.id.split("-")[1];
        $V(formCorres.object_id, medecin_id);
        $V(formCorres.compte_rendu_id, '{{$compte_rendu->_id}}');
        onSubmitFormAjax(formCorres, {onComplete: function() {
          if (confirm($T("CCorrespondantPatient-add_to_dossier"))) {
            var formAdd = getForm("addCorrespondantToDossier");
            $V(formAdd.patient_id, '{{$patient->_id}}');
            $V(formAdd.medecin_id, medecin_id);
            onSubmitFormAjax(formAdd, { onComplete: function() {
              openCorrespondants('{{$compte_rendu->_id}}', '{{$compte_rendu->_ref_object->_guid}}', 0, 1);
            } });
          }
          else {
            openCorrespondants('{{$compte_rendu->_id}}', '{{$compte_rendu->_ref_object->_guid}}', 0, 1);
          }
        } });
      }
    });
  });

  Medecin.set = function(medecin_id) {
    var formCorres = getForm("addCorrespondant");
    $V(formCorres.object_id, medecin_id);
    $V(formCorres.compte_rendu_id, '{{$compte_rendu->_id}}');
    onSubmitFormAjax(formCorres, {onComplete: function() {
      openCorrespondants('{{$compte_rendu->_id}}', '{{$compte_rendu->_ref_object->_guid}}', 0, 1);
    } });
  };

  updateMergeButton = function() {
    var button = $("merge_correspondants");
    button.writeAttribute("disabled", $("correspondants_courrier").select("input:checked").length ? null : true);
  }
</script>

<table class="tbl">
  {{foreach from=$destinataires key=_class item=_destinataires}}
    <tr>
      <th class="title" colspan="3">
        {{if $_class == "CPatient" && $compte_rendu->_id}}
          <button type="button" class="add notext" style="float: left;"
          onclick="Correspondant.edit(0, '{{$patient->_id}}', openCorrespondants.curry('{{$compte_rendu->_id}}', '{{$compte_rendu->_ref_object->_guid}}', 0, 1))"></button>
          {{tr}}CCorrespondantPatient{{/tr}}
        {{/if}}
        {{if $_class == "CMedecin" && $compte_rendu->_id}}
          <div style="float: left">
            <button type="button" class="add notext"
              onclick="Medecin.edit()"></button>
            <input type="text" name="_view" class="autocomplete"/>
            <label>
              <input type="checkbox" name="all_departements" /> Inclure tous les départements
            </label>
            <div id="correspondants_area"
              style="color: #000; text-align: left; width: 35px; float: left; font-weight: normal;" class="autocomplete"></div>
          </div>
        {{/if}}
        {{if $_class != "CPatient"}}
          </th>
        <tr>
          <th class="title" colspan="3">
        {{/if}}
        {{tr}}{{$_class}}{{/tr}}
      </th>
    </tr>
    {{foreach from=$_destinataires key=_index item=_destinataire}}
      {{assign var=object_guid value=$_destinataire->object_guid}}
      {{assign var=tag value=$_destinataire->tag}}
      {{if @isset($correspondants.$tag.$object_guid|smarty:nodefaults)}}
        {{assign var=correspondant value=$correspondants.$tag.$object_guid}}
      {{else}}
        {{assign var=correspondant value=$empty_corres}}
      {{/if}}
      <tr>
        <td class="narrow">
          <input type="checkbox" name="_dest_{{$_class}}_{{$_index}}" id="editFrm__dest_{{$_class}}_{{$_index}}"
            {{if $correspondant->_id}}checked{{/if}} onclick="updateMergeButton()"/>

          {{if $_class == 'CMedecin' && $compte_rendu->_id}}
            <button type="button" class="edit notext" onclick="Medecin.editMedecin('{{$_index}}', null, null, {{$compte_rendu->_id}});">{{tr}}Edit{{/tr}}</button>
          {{/if}}
        </td>
        <td>
          <label for="editFrm__dest_{{$_class}}_{{$_index}}">
            {{$_destinataire->nom}} ({{tr}}CDestinataire.tag.{{$tag}}{{/tr}})
          </label>

          {{if $_class === "CMedecin" && $compte_rendu->_id}}
            <div>
              {{mb_include module=patients template=inc_choose_medecin_exercice_place
                medecin=$_destinataire->_ref_medecin
                object=$_destinataire
                field=medecin_exercice_place_id
                field_select_name="_medecin_exercice_place[`$_index`]"
                submit_on_change=0}}
            </div>
          {{/if}}
        </td>
        <td>
          <input type="text" name="_count_{{$_class}}_{{$_index}}" id="editFrm__count_{{$_class}}_{{$_index}}"
            value="{{$correspondant->quantite}}" style="width: 3em;"/>
          <script>
            Main.add(function() {
              $('editFrm__count_{{$_class}}_{{$_index}}').addSpinner({min: 1});
            });
          </script>
        </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td class="empty" colspan="3">{{tr}}CMedecin.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>
<p style="text-align: center;">
  <button type="button" class="tick" onclick="saveAndMerge();" id="merge_correspondants"
    {{if !$correspondants|@count}}disabled{{/if}}>{{tr}}Merge{{/tr}}</button>

  <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
</p>
