{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=edit_for_admin value="dPpatients CMedecin edit_for_admin"|gconf}}
{{assign var=function_distinct value=$conf.dPpatients.CPatient.function_distinct}}

<script>
  setClose = function (id, view, view_update) {
    Medecin.set(id, view, view_update);
    Control.Modal.close();
  };

  var formVisible = false;

  function showAddCorres() {
    if (!formVisible) {
      $('addCorres').show();
      getForm('editFrm').focusFirstElement();
      formVisible = true;
    } else {
      hideAddCorres();
    }
  }

  function hideAddCorres() {
    $('addCorres').hide();
    formVisible = false;
  }


  function onSubmitCorrespondant(form) {
    return onSubmitFormAjax(form, {
      onComplete: function () {
        hideAddCorres();
        var formFind = getForm('find_medecin');
        formFind.elements.medecin_nom.value = form.elements.nom.value;
        formFind.elements.medecin_prenom.value = form.elements.prenom.value;
        formFind.elements.medecin_cp.value = form.elements.cp.value;
        formFind.submit();
      }
    });
  }
</script>


{{if !$annuaire}}
<form name="fusion_medecin" action="?" method="get">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="a" value="object_merger" />
  <input type="hidden" name="objects_class" value="CMedecin" />
  <input type="hidden" name="readonly_class" value="true" />
  {{/if}}

  {{mb_include module=system template=inc_pagination current=$start_med step=$step_med total=$count_medecins change_page=refreshPageMedecin}}

  <table class="tbl">
    {{if $annuaire}}
      <tr>
        <th class="title" colspan="20">Annuaire interne</th>
      </tr>
    {{/if}}
    <tr>
      {{if $can->admin || !$edit_for_admin}}
        {{if !$annuaire}}
          <th class="narrow">
            <button type="button" onclick="Medecin.doMerge('fusion_medecin');" class="merge notext compact me-tertiary" title="{{tr}}Merge{{/tr}}">
              {{tr}}Merge{{/tr}}
            </button>
          </th>
          <th class="category narrow"></th>
          {{if $is_admin && $function_distinct}}
            {{if $function_distinct == 1}}
              <th>{{mb_title class=CMedecin field=function_id}}</th>
            {{else}}
              <th>{{mb_title class=CMedecin field=group_id}}</th>
            {{/if}}
          {{/if}}
        {{else}}
          <th>{{tr}}Import{{/tr}}</th>
        {{/if}}
      {{/if}}
      <th>{{mb_title class=CMedecin field=nom}}</th>
      <th class="narrow">{{mb_title class=CMedecin field=sexe}}</th>
      <th>{{mb_title class=CExercicePlace field=raison_sociale}}</th>
      <th>{{mb_title class=CMedecin field=adresse}}</th>
      <th>{{mb_title class=CMedecin field=type}}</th>
      <th>{{mb_title class=CMedecin field=disciplines}}</th>
      <th>{{mb_title class=CMedecin field=tel}}</th>
      <th>{{mb_title class=CMedecin field=fax}}</th>
      <th>{{mb_title class=CMedecin field=email}}</th>
      {{if $dialog && !$annuaire}}
        <th id="vw_medecins_th_select">{{tr}}Select{{/tr}}</th>
      {{/if}}
    </tr>
    {{foreach from=$medecins item=_medecin}}
      {{assign var=medecin_id value=$_medecin->_id}}
      <tr {{if !$_medecin->actif}}class="hatching"{{/if}}>
        {{mb_ternary var=href test=$dialog value="#choose" other="?m=$m&tab=vw_correspondants&medecin_id=$medecin_id"}}

        {{if !$annuaire}}
          {{if $can->admin || !$edit_for_admin}}
            <td style="text-align: center">
              <input type="checkbox" name="objects_id[]" value="{{$_medecin->_id}}" />
            </td>
            <td>
              <button type="button" class="edit notext me-tertiary"
                      onclick="Medecin.editMedecin('{{$_medecin->_id}}',refreshPageMedecin)">
              </button>
            </td>
          {{/if}}

          {{if $is_admin && $function_distinct}}
            <td>
            {{if $function_distinct == 1}}
              <span onmouseover="ObjectTooltip.createEx(this, 'CFunction-{{$_medecin->function_id}}')">
                {{mb_value object=$_medecin field=function_id}}
              </span>
            {{else}}
              <span onmouseover="ObjectTooltip.createEx(this, 'CGroups-{{$_medecin->group_id}}')">
                {{mb_value object=$_medecin field=group_id}}
              </span>
            {{/if}}
            </td>
          {{/if}}
        {{else}}
          <td class="button">
            <button type="button" class="import notext me-tertiary"
                    onclick="$V(getForm('find_medecin').annuaire, 0); Medecin.duplicate('{{$_medecin->_id}}', refreshPageMedecin)">
              {{tr}}Import{{/tr}}
            </button>
          </td>
        {{/if}}

        <!-- Nom et prénom -->
        <td class="text">
          {{if $_medecin->nom || $_medecin->prenom}}
           <p>{{$_medecin->nom}} {{$_medecin->prenom|strtolower|ucfirst}}</p>
          {{else}}
            <p><div class="empty">N/A</div></p>
          {{/if}}
        </td>

        <!-- Sexe -->
        <td style="text-align: center">
           {{if $_medecin->sexe == "f"}}
              <i class="fas fa-venus" style="color: deeppink;"></i>
           {{elseif $_medecin->sexe == "m"}}
              <i class="fas fa-mars" style="color: blue;"></i>
           {{else}}
            <i class="fas fa-genderless" style="color: grey;"></i>
           {{/if}}
        </td>

        <!-- Raison sociale -->
        <td class="text">
          {{foreach from=$_medecin->_ref_exercice_places item=_exercice_place}}
            {{if $_exercice_place->raison_sociale}}
               <p>{{mb_value object=$_exercice_place field=raison_sociale}}</p>
            {{else}}
              <p><div class="empty">N/A</div></p>
            {{/if}}
          {{foreachelse}}
            <p><div class="empty">N/A</div></p>
          {{/foreach}}
        </td>

        <!-- Adresse -->
        <td class="text">
          {{foreach from=$_medecin->_ref_exercice_places item=_exercice_place}}
            {{if $_exercice_place->adresse || $_exercice_place->cp || $_exercice_place->commune}}
              <p>
                {{mb_value object=$_exercice_place field=adresse}}
                {{mb_value object=$_exercice_place field=cp}}
                {{mb_value object=$_exercice_place field=commune}}
              </p>
            {{else}}
              <p><div class="empty">N/A</div></p>
            {{/if}}
          {{foreachelse}}
            {{if $_medecin->adresse || $_medecin->cp || $_medecin->ville}}
              <p>
                {{mb_value object=$_medecin field=adresse}}
                {{mb_value object=$_medecin field=cp}}
                {{mb_value object=$_medecin field=ville}}
              </p>
            {{else}}
              <p><div class="empty">N/A</div></p>
            {{/if}}
          {{/foreach}}
        </td>

        <!-- Type -->
        <td class="text">
          {{foreach from=$_medecin->_ref_medecin_exercice_places item=_medecin_exercice_place}}
            {{if $_medecin_exercice_place->type}}
               <p>{{mb_ditto name=type_$_medecin value=$_medecin_exercice_place->getFormattedValue('type')}}</p>
            {{else}}
              <p><div class="empty">N/A</div></p>
            {{/if}}
          {{foreachelse}}
            {{if $_medecin->type}}
              <p>{{mb_value object=$_medecin field=type}}</p>
            {{else}}
              <p><div class="empty">N/A</div></p>
            {{/if}}
          {{/foreach}}
        </td>

        <!-- Disciplines -->
        <td class="text">
          {{foreach from=$_medecin->_ref_medecin_exercice_places item=_medecin_exercice_place}}
            {{if $_medecin_exercice_place->disciplines}}
               <p>{{mb_ditto name=discipline_$_medecin value=$_medecin_exercice_place->disciplines}}</p>
            {{else}}
              <p><div class="empty">N/A</div></p>
            {{/if}}
          {{foreachelse}}
            {{if $_medecin->disciplines}}
              <p>{{mb_value object=$_medecin field=disciplines}}</p>
            {{else}}
              <p><div class="empty">N/A</div></p>
            {{/if}}
          {{/foreach}}
        </td>

        <!-- Tel -->
        <td class="text">
          {{foreach from=$_medecin->_ref_exercice_places item=_exercice_place}}
            {{if $_exercice_place->tel}}
               <p>{{mb_value object=$_exercice_place field=tel}}</p>
            {{else}}
              <p><div class="empty">N/A</div></p>
            {{/if}}
          {{foreachelse}}
            {{if $_medecin->tel}}
              <p>{{mb_value object=$_medecin field=tel}}</p>
            {{else}}
              <p><div class="empty">N/A</div></p>
            {{/if}}
          {{/foreach}}
        </td>

        <!-- Fax -->
        <td class="text">
          {{foreach from=$_medecin->_ref_exercice_places item=_exercice_place}}
            {{if $_exercice_place->fax}}
              <p>{{mb_value object=$_exercice_place field=fax}}</p>
            {{else}}
              <p><div class="empty">N/A</div></p>
            {{/if}}
          {{foreachelse}}
            {{if $_medecin->fax}}
              <p>{{mb_value object=$_medecin field=fax}}</p>
            {{else}}
              <p><div class="empty">N/A</div></p>
            {{/if}}
          {{/foreach}}
        </td>

        <!-- Email -->
        <td class="text">
          {{foreach from=$_medecin->_ref_exercice_places item=_exercice_place}}
            {{if $_exercice_place->email}}
              <p>{{mb_value object=$_exercice_place field=email}}</p>
            {{else}}
              <p><div class="empty">N/A</div></p>
            {{/if}}
          {{foreachelse}}
            {{if $_medecin->email}}
              <p>{{mb_value object=$_medecin field=email}}</p>
            {{elseif $_medecin->email_apicrypt}}
              <p>{{mb_value object=$_medecin field=email_apicrypt}}</p>
            {{else}}
              <p><div class="empty">N/A</div></p>
            {{/if}}
          {{/foreach}}
        </td>

        {{if $dialog && !$annuaire}}
          <td>
            <button type="button" class="tick me-secondary"
                    onclick="setClose({{$_medecin->_id}}, '{{$_medecin->_view|smarty:nodefaults|JSAttribute}}', '{{$view_update}}' )">
              {{tr}}Select{{/tr}}
            </button>
          </td>
        {{/if}}
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="20" class="empty">{{tr}}CMedecin.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>

  {{if !$annuaire}}
</form>
{{/if}}
