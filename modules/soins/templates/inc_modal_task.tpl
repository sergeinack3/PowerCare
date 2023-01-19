{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=readonly value=null}}
{{if $task->_id && $task->author_id != $user}}
  {{assign var=readonly value=true}}
{{/if}}
<!-- Modale de creation / modification d'une activite -->
<form name="addTask" action="?" method="post"
      onsubmit="{{if $task_element}}
      return onSubmitFormAjax(this, function(){ Soins.refreshTask('{{$task->prescription_line_element_id}}'); Control.Modal.close();});
      {{else}}
      return submitActivite(this);
      {{/if}}">
  {{mb_class object=$task}}
  {{mb_key   object=$task}}
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="sejour_id" value="{{$sejour_id}}" />
  <input type="hidden" name="realise" value="{{$task->realise}}" />
  <input type="hidden" name="prescription_line_element_id" value="{{$task->prescription_line_element_id}}" />
  <input type="hidden" name="date" value="{{$task->date}}" />
  <input type="hidden" name="author_id" value="{{$task->author_id}}" />
        
  <table class="form">
    <tr>
      <th class="title {{if $task->_id}}modify{{else}}me-th-new{{/if}}">
        {{if $task->_id}}
          {{tr}}CSejourTask-title-modify{{/tr}}
        {{else}}
          {{tr}}CSejourTask-title-create{{/tr}}
        {{/if}}
      </th>
    </tr>
    <tr>
      <td colspan="4">
        <fieldset style="float: left; width: 45%;">
           <legend>
            {{mb_title object=$task field="description"}}
          </legend>
          {{mb_field object=$task field="description" form="addTask" aidesaisie="height: '100px'" readonly=$readonly}}
        </fieldset>
        <fieldset style="float: right; width: 45%">
           <legend>
             {{mb_title object=$task field="resultat"}}
           </legend>
          {{mb_field object=$task field="resultat" form="addTask" aidesaisie="height: '100px'"}}
        </fieldset>
      </td>
    </tr>
    <tr>  
      <td colspan="4">  
        <div style="text-align: center">
          <button class="submit">{{tr}}Save{{/tr}}</button>
          {{if $task->realise}}
            <button class="cancel" onclick="$V(this.form.realise, '0');">Annuler la réalisation</button>
          {{else}}
            <button class="tick" onclick="$V(this.form.realise, '1');">Réalisée</button>
          {{/if}}
          {{if $task->_id}}
          <button type="button" class="trash" {{if $readonly}}disabled{{/if}}
                  onclick="confirmDeletion(this.form, {
                      ajax: true,
                      objName:'{{$task->_view|smarty:nodefaults|JSAttribute}}'
                    },
                    function() {
                      Soins.refreshTask('{{$task->prescription_line_element_id}}');
                      Soins.updateTasks('{{$task->sejour_id}}');
                      Control.Modal.close();
                    });">{{tr}}Delete{{/tr}}</button>
          {{/if}}
        </div>
      </td>
    </tr>
    {{if $task->_id && $task->_ref_consult->_id}}
      {{assign var=consult value=$task->_ref_consult}}
      <tr>
        <th colspan="4" class="title">
          Consultation associée
        </th>
      </tr>
      <tr>
        <td colspan="4">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$consult->_guid}}')">
            {{$consult}}
          </span>
        </td>
      </tr>
    {{/if}}
  </table>
</form>