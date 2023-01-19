{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Edit-{{$appel->_guid}}" method="post" onsubmit="return Appel.submit(this);">
  <input type="hidden" name="m" value="{{$m}}" />
  {{mb_key    object=$appel}}
  {{mb_class  object=$appel}}
  <input type="hidden" name="del" value="0"/>
  <input type="hidden" name="callback" value=""/>

  {{mb_field object=$appel field=sejour_id hidden=true}}
  {{mb_field object=$appel field=user_id hidden=true}}
  {{mb_field object=$appel field=type hidden=true}}
  {{mb_field object=$appel field=etat hidden=true}}

  <table class="main form">
    {{if !$appel_id && $sejour->_ref_appels_by_type.$type|@count}}
      <tr>
        <th colspan="2" class="title">{{tr}}CAppelSejour-Call list {{$type}}{{/tr}}</th>
      </tr>
      {{foreach from=$sejour->_ref_appels_by_type.$type item=_appel}}
        <tr>
          <th>{{mb_value object=$_appel field=etat}}</th>
          <td>
            {{if $app->user_id == $_appel->user_id && $_appel->datetime|date_format:$conf.date === $dnow|date_format:$conf.date}}
              <button type="button" class="edit notext" title="{{tr}}Modify{{/tr}}" onclick="Appel.edit('{{$_appel->_id}}', '{{$type}}', '{{$sejour->_id}}');"></button>
            {{/if}}

            <strong>[{{mb_value object=$_appel field=datetime}}]</strong>
            <span class="compact">{{$_appel->commentaire}} &nbsp;</span>
            <span style="float: right;" class="me-float-none">
            réalisé par {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_appel->_ref_user}} &nbsp;
              {{mb_include module=system template=inc_object_history object=$_appel}}
            </span>
            <br />

            {{if 'forms'|module_active}}
              {{foreach from=$_appel->_ref_forms item=_form name=list_forms}}
                {{assign var=ex_class value=$_form->_ref_ex_class}}
                <button type="button" class="search" title="{{tr}}CExClass-action-Show form{{/tr}}"
                        onclick="ExObject.display('{{$_form->ex_object_id}}', '{{$_form->ex_class_id}}', '{{$_appel->_guid}}');">
                  {{$ex_class->name}} ({{$_form->datetime_create|date_format:$conf.datetime}})
                </button>

                {{if !$smarty.foreach.list_forms.last}}
                  <br />
                {{/if}}
              {{/foreach}}
            {{/if}}
          </td>
        </tr>
      {{/foreach}}
    {{/if}}
     {{if $appel->_id}}
       <th class="title modify" colspan="2">
         {{mb_include module=system template=inc_object_notes     object=$appel}}
         {{mb_include module=system template=inc_object_idsante400 object=$appel}}
         {{mb_include module=system template=inc_object_history   object=$appel}}
         {{tr}}{{$appel->_class}}-title-modify-{{$type}}{{/tr}}
       </th>
     {{/if}}
     <tr>
       <th class="title me-th-new" colspan="2">
         {{assign var=patient value=$sejour->_ref_patient}}
         <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
          {{$patient->_view}} - {{mb_value object=$patient field=_age}} ({{mb_value object=$patient field=naissance}})
         </span>
         <br/>
       </th>
     </tr>
     <tr>
       <th class="section" colspan="2">
         {{tr}}{{$appel->_class}}-title-{{$type}}{{/tr}}
       </th>
     </tr>
     <tr>
       <th>{{tr}}CSejour-contexte{{/tr}}</th>
       <td>
         <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">
          {{$sejour->_view}}
         </span>
       </td>
     </tr>
    {{if $operation->_id}}
      <tr>
        <th></th>
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$operation->_guid}}')"
            {{if $operation->annulee == 1}}style="background-color: #f88;"{{/if}}>
            {{tr var1=$operation->_datetime|date_format:$conf.datetime}}COperation-Intervention of %s{{/tr}}

            {{if $operation->annulee == 1}}
              <span class="category cancelled">
              &mdash; {{tr}}COperation-annulee{{/tr}}
            </span>
            {{/if}}

          </span>
        </td>
      </tr>
    {{/if}}
     <tr>
       <th>
         {{mb_label object=$sejour field=entree}}
       </th>
       <td>
         {{mb_field object=$sejour field=entree}}
       </td>
     </tr>
     <tr>
       <th>
         {{mb_label object=$sejour field=sortie}}
       </th>
       <td>
         {{mb_field object=$sejour field=sortie}}
       </td>
     </tr>
     <tr>
       <th>{{mb_label object=$patient field=tel}}</th>
       <td>{{mb_value object=$patient field=tel}}</td>
     </tr>
     <tr>
       <th>{{mb_label object=$patient field=tel2}}</th>
       <td>{{mb_value object=$patient field=tel2}}</td>
     </tr>
     {{assign var=appel_guid value=$appel->_guid}}
     <tr>
       <th>{{mb_label object=$appel field=datetime}}</th>
       <td>{{mb_field object=$appel field=datetime form="Edit-$appel_guid" canNull="true" register=true}}</td>
     </tr>
     <tr>
       <th>{{mb_label object=$appel field=commentaire}}</th>
       <td>{{mb_field object=$appel field=commentaire form="Edit-$appel_guid" aidesaisie="validateOnBlur: 0"}}</td>
     </tr>
     <tr>
       <td class="button" colspan="2">
         {{if $appel->_id}}
           <button class="tick" type="button" onclick="Appel.changeEtat(this.form, 'realise');">{{tr}}CAppelSejour.etat.realise{{/tr}}</button>
           <button class="cancel" type="button" onclick="Appel.changeEtat(this.form, 'echec');">{{tr}}CAppelSejour.etat.echec{{/tr}}</button>
           <button class="cancel" type="button" onclick="Appel.submit(this.form);">{{tr}}Close{{/tr}}</button>
           <button class="trash" type="button" onclick="$V(this.form.del, 1);Appel.onDeletion(this.form);">{{tr}}Delete{{/tr}}</button>

         {{else}}
           <input type="hidden" name="_open_form" value="0" />
           <button class="tick" type="button" onclick="Appel.changeEtat(this.form, 'realise');">{{tr}}CAppelSejour.etat.realise{{/tr}}</button>
           <button class="cancel" type="button" onclick="Appel.changeEtat(this.form, 'echec');">{{tr}}CAppelSejour.etat.echec{{/tr}}</button>
           <button class="cancel" type="button" onclick=" Appel.modal.close();">{{tr}}Cancel{{/tr}}</button>

           {{if 'forms'|module_active}}
             <br />

             <button type="button" class="tick" onclick="$V(this.form.elements._open_form, 1);
                     Appel.changeEtat(this.form, 'realise', function() {Control.Modal.close(); });">
               {{tr}}CAppelSejour-action-Mark as realised and open a new form{{/tr}}
             </button>

             <button type="button" class="cancel" onclick="$V(this.form.elements.callback, 'Appel.edit');
                     Appel.changeEtat(this.form, 'echec', function() { Control.Modal.close(); });">
               {{tr}}CAppelSejour-action-Mark as failed and open a new form{{/tr}}
             </button>
           {{/if}}
         {{/if}}
       </td>
     </tr>
  </table>
</form>

{{if 'forms'|module_active && $appel->_id}}
  <fieldset>
    <legend>{{tr}}CExClass|pl{{/tr}}</legend>

    {{unique_id var=unique_id_appel_forms}}

    {{*appel de la veille et appel du lendemain*}}
    {{assign var=event_form_names value="appel_j_plus_1_auto|appel_j_moins_1_auto|appel"}}

    <script>
      Main.add(function() {
        ExObject.loadExObjects("{{$appel->_class}}", "{{$appel->_id}}", "{{$unique_id_appel_forms}}", 0.5, null, {event_names : '{{$event_form_names}}'});
      });
    </script>

    <div id="{{$unique_id_appel_forms}}"></div>
  </fieldset>
{{/if}}