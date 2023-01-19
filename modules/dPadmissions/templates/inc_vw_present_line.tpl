{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var="patient" value=$_sejour->_ref_patient}}
{{assign var=number_op value=$_sejour->_ref_operations|@count}}
{{mb_script module=planningOp script=prestations ajax=1}}

<td colspan="2" class="text">
  {{if $canPlanningOp->read}}
    <div style="float: right;">
      {{mb_include module=hospi template=inc_button_send_prestations_sejour}}

      {{if "web100T"|module_active}}
        {{mb_include module=web100T template=inc_button_iframe}}
      {{/if}}

      {{if "softway"|module_active}}
        {{mb_include module=softway template=inc_button_synthese}}
      {{/if}}

      <a href="#showDocs" title="{{tr}}admissions-action-Display document and file|pl{{/tr}}" class="button me-tertiary"
         onclick="Admissions.showDocs('{{$_sejour->_id}}')">
        <i class="far fa-file" style="font-size: 1.4em; padding-left: 2px;" aria-hidden="true"></i>
      </a>

      {{if "dPadmissions General show_deficience"|gconf}}
        {{mb_include module=patients template=inc_vw_antecedents type=deficience callback=PatientsPresents.reloadPresent}}
      {{/if}}

      {{if $number_op == 1}}
        {{foreach from=$_sejour->_ref_operations item=_op}}
          <button class="print notext me-tertiary" title="{{tr}}admissions-action-Print the DHE from the intervention{{/tr}}"
                  onclick="Admissions.printDHE('operation_id', {{$_op->_id}}); return false;">
          </button>
        {{/foreach}}
      {{elseif $number_op > 1}}
        <button class="print notext me-tertiary" title="{{tr}}admissions-action-Print the DHE from the intervention{{/tr}}"
                onclick="Admissions.chooseDHE('{{$_sejour->_id}}');" class="button print">
          {{tr}}Print{{/tr}}</button>
      {{else}}
        <button class="print notext me-tertiary" title="{{tr}}admissions-action-Print the DHE of the stay{{/tr}}"
                onclick="Admissions.printDHE('sejour_id', {{$_sejour->_id}}); return false;">
        </button>
      {{/if}}

      <a class="action me-planif-icon me-button me-tertiary actionPat notext" title="Modifier le séjour" href="#editDHE"
         onclick="Sejour.editModal({{$_sejour->_id}}, 0, 0, PatientsPresents.reloadPresent); return false;">
        <img src="images/icons/planning.png" />
      </a>
      
      {{mb_include module=system template=inc_object_notes object=$_sejour}}
    </div>
  {{/if}}

  {{if $patient->_ref_IPP}}
    <form name="editIPP{{$patient->_id}}" method="post">
      <input type="hidden" class="notNull" name="id400" value="{{$patient->_ref_IPP->id400}}" />
      <input type="hidden" class="notNull" name="object_id" value="{{$patient->_id}}" />
      <input type="hidden" class="notNull" name="object_class" value="CPatient" />
    </form>
  {{/if}}

  {{if $_sejour->_ref_NDA}}
    <form name="editNumdos{{$_sejour->_id}}" method="post">
      <input type="hidden" class="notNull" name="id400" value="{{$_sejour->_ref_NDA->id400}}"/>
      <input type="hidden" class="notNull" name="object_id" value="{{$_sejour->_id}}" />
      <input type="hidden" class="notNull" name="object_class" value="CSejour" />
    </form>
  {{/if}}

  {{if "dPsante400"|module_active}}
    {{mb_include module=dPsante400 template=inc_manually_ipp_nda sejour=$_sejour patient=$patient callback=PatientsPresents.reloadPresent}}
  {{/if}}

  <input type="checkbox" name="print_doc" value="{{$_sejour->_id}}"/>
  {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$_sejour _show_numdoss_modal=1}}

  {{if "maternite"|module_active && $_sejour->_ref_first_affectation->_ref_parent_affectation->_id}}
    {{assign var=sejour_maman value=$_sejour->_ref_first_affectation->_ref_parent_affectation->_ref_sejour}}
    <img src="images/pictures/identity_baby.png" style="width: 16px; height: 16px;" onmouseover="ObjectTooltip.createEx(this, 'CPatient-{{$sejour_maman->patient_id}}');" />
  {{/if}}

  <span class="CPatient-view" onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
    {{$patient}}
  </span>

  {{mb_include module=patients template=inc_status_icon}}
  {{mb_include module=patients template=inc_icon_bmr_bhre}}
</td>

<td class="text">
  {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien classe="CMediusers-view"}}
</td>

<td>
  <span {{if $_sejour->entree|iso_date == $date}}style="color: #070;"{{/if}}
    onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
    {{if $_sejour->presence_confidentielle}}
      {{mb_include module=planningOp template=inc_badge_sejour_conf}}
    {{/if}}
    {{$_sejour->entree|date_format:$conf.datetime}}
  </span>
  <div style="position: relative;">
  <div class="sejour-bar" title="arrivée il y a {{$_sejour->_entree_relative}}j et départ prévu dans {{$_sejour->_sortie_relative}}j ">
    <div style="width: {{if $_sejour->_duree}}{{math equation='100*(-entree / (duree))' entree=$_sejour->_entree_relative duree=$_sejour->_duree format='%.2f'}}{{else}}100{{/if}}%;"></div>
  </div>
  </div>
</td>

<td>
  <span {{if $_sejour->sortie|iso_date == $date}}style="color: #070;"{{/if}}>
    {{$_sejour->sortie|date_format:$conf.datetime}}
  </span>

  {{if $_sejour->confirme}}
    {{me_img src="tick.png" title="CSejour-confirme" icon="tick" class="me-success"}}
  {{/if}}
</td>

<td class="text">
  {{if !($_sejour->type == 'exte') && !($_sejour->type == 'consult') && $_sejour->annule != 1}}
    {{if "dPadmissions presents see_prestation"|gconf}}
      {{mb_include template=inc_form_prestations sejour=$_sejour edit=$canAdmissions->edit}}
    {{/if}}
    {{mb_include module=hospi template=inc_placement_sejour sejour=$_sejour classe="CChambre-view"}}
  {{/if}}  
</td>

<td>
  {{if $canAdmissions->edit}}
    {{if !isset($reloadLine|smarty:nodefaults) || (isset($reloadLine|smarty:nodefaults) && $reloadLine)}}
        {{mb_include module=forms template=inc_widget_ex_class_register object=$_sejour event_name=preparation_entree cssStyle="display: inline-block;"}}
    {{/if}}

    <form name="editSaisFrm{{$_sejour->_id}}" action="?" method="post" class="prepared">
      <input type="hidden" name="m" value="planningOp" />
      <input type="hidden" name="dosql" value="do_sejour_aed" />
      <input type="hidden" name="patient_id" value="{{$_sejour->patient_id}}" />
      {{mb_field object=$_sejour field=type hidden=true}}
      {{mb_key object=$_sejour}}

      {{if !$_sejour->entree_preparee}}
        <input type="hidden" name="entree_preparee" value="1" />
        <input type="hidden" name="_entree_preparee_trigger" value="1" />
        <button class="tick" type="button" onclick="PatientsPresents.submitAdmission(this.form, 1);">
          {{tr}}CSejour-entree_preparee{{/tr}}
        </button>
      {{else}}
        <input type="hidden" name="entree_preparee" value="0" />
        <button class="cancel" type="button" onclick="PatientsPresents.submitAdmission(this.form, 1);">
          {{tr}}Cancel{{/tr}}
        </button>
      {{/if}}
    </form>
  {{/if}}
</td>
