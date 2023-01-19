{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  checkObject = function(elt) {
    var form = getForm('updateOperation');

    {{foreach from=$fields key=_name item=_field}}
    $V(form.{{$_name}}, $V(elt));
    {{/foreach}}
  };

  linkOperation = function(form) {
    Control.Modal.close();
    return onSubmitFormAjax(form, {
      onComplete: function() {
        if (typeof loadIntervention === 'function') {
          loadIntervention();
        }
        document.location.reload();
      }
    });
  };

  {{if $auto_link}}
      Main.add(function() {
        getForm('updateOperation').onsubmit();
      });
  {{/if}}
</script>

<form name="updateOperation" target="?m={{$m}}" method="post" onsubmit="return linkOperation(this);">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="dosql" value="do_update_operation" />
  <input type="hidden" name="operation_id" value="{{$operation->_id}}" />
  <input type="hidden" name="consult_anesth_id" value="{{$consult_anesth->_id}}" />

  {{if !$auto_link}}
    <table class="tbl">
      <tr>
        <th class="title" colspan="5">Mise à jour des champs de l'intervention</th>
      </tr>
      <tr>
        <th class="narrow"></th>
        <th class="narrow">
          <input type="radio" name="object" value="COperation" onchange="checkObject(this);"/>
        </th>
        <th>{{tr}}COperation{{/tr}}</th>
        <th class="narrow">
          <input type="radio" name="object" value="CConsultAnesth" onchange="checkObject(this);"/>
        </th>
        <th>{{tr}}CConsultAnesth{{/tr}}</th>
      </tr>
      {{foreach from=$fields key=_name item=_field}}
        <tr>
          <th class="narrow">{{tr}}COperation-{{$_name}}{{/tr}}</th>
          <th class="narrow">
            <input type="radio" class="operation" name="{{$_name}}" value="COperation" {{if $_field.object == 'COperation'}}checked='checked'{{/if}}/>
          </th>
          <td class="{{$_field.status}}{{if $_name == 'rques'}} text{{/if}}">
            {{mb_value object=$operation field=$_name}}
          </td>
          <th class="narrow">
            <input type="radio" class="consult_anesth" name="{{$_name}}" value="CConsultAnesth" {{if $_field.object == 'CConsultAnesth'}}checked='checked'{{/if}}/>
          </th>
          <td class="{{$_field.status}}{{if $_name == 'rques'}} text{{/if}}">
            {{mb_value object=$consult_anesth field=$_name}}
          </td>
        </tr>
      {{/foreach}}
      <tr>
        <td colspan="5" class="button">
          <button type="submit" class="save" onclick="this.form.onsubmit();">Lier l'intervention</button>
          <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
        </td>
      </tr>
    </table>
  {{else}}
    {{foreach from=$fields key=_name item=_field}}
      <input type="hidden" name="{{$_name}}" value="{{$_field.object}}"/>
    {{/foreach}}
  {{/if}}
</form>