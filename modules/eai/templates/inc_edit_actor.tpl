{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  InteropActor.actor_guid = '{{$actor->_guid}}';

  confirmPurge = function (button, view) {
    var oForm = button.form;
    if (confirm("ATTENTION : Vous êtes sur le point de purger cet acteur")) {
      oForm._purge.value = "1";
      confirmDeletion(oForm, {
        typeName:'l\'acteur',
        objName:view
      } );
    }
  }
</script>

{{if (($actor->_class != "CInteropActor") || ($actor->_class != "CInteropSender")) && $can->edit}}
  <form name="edit{{$actor->_guid}}" action="?m={{$m}}" method="post"
        onsubmit="return onSubmitFormAjax(this, { onComplete: Control.Modal.close })">
    {{mb_key object=$actor}}
    {{mb_class object=$actor}}
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="parent_class" value="{{$actor->_parent_class}}" />
    <input type="hidden" name="_purge" value="0" />
    <input type="hidden" name="callback" value="InteropActor.refreshActor" />
                  
    <table class="form">
      <tr>
        {{mb_include module=system template=inc_form_table_header object=$actor}}
      </tr>

      <tr>
        <th class="title" colspan="2"> {{tr}}{{$actor->_parent_class}}-settings{{/tr}} </th>
      </tr>

      <tr>
        <th>{{mb_label object=$actor field="nom"}}</th>
        <td>{{mb_field object=$actor field="nom"}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$actor field="libelle"}}</th>
        <td>{{mb_field object=$actor field="libelle"}}</td>
      </tr>
      
      <tr>
        <th>{{mb_label object=$actor field="group_id"}}</th>
        <td>{{mb_field object=$actor field="group_id" form="edit`$actor->_guid`" autocomplete="true,1,50,true,true"}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$actor field="actif"}}</th>
        <td>{{mb_field object=$actor field="actif"}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$actor field="role"}}</th>
        <td>{{mb_field object=$actor field="role" typeEnum="radio"}}</td>
      </tr>

      <tr>
        <th>{{mb_label object=$actor field="type"}}</th>
        <td>{{mb_field object=$actor field="type" typeEnum="select" emptyLabel="`$actor->_class`.type."}}</td>
      </tr>

      <tr>
        <th class="title" colspan="2"> {{tr}}{{$actor->_class}}-settings{{/tr}} </th>
      </tr>

      {{mb_include module=eai template="`$actor->_parent_class`_inc"}}
        
      <tr>
        <td class="button" colspan="2">
          {{if $actor->_id}}
            <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>
            <button type="button" class="trash"
                    onclick="confirmDeletion(this.form,
                      {typeName:'',objName:'{{$actor->_view|smarty:nodefaults|JSAttribute}}', ajax:true})">
              {{tr}}Delete{{/tr}}
            </button>

            {{if $can->admin}}
              <button type="button" class="cancel" onclick="confirmPurge(this, '{{$actor->_view|smarty:nodefaults|JSAttribute}}');">
                {{tr}}Purge{{/tr}}
              </button>
            {{/if}}
          {{else}}
             <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
          {{/if}}
        </td>
      </tr>  
    </table>
  </form>
{{/if}}
