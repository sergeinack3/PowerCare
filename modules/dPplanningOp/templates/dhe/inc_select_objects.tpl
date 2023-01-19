{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="selectObjects_{{$method}}" method="get" action="?" onsubmit="return false;">
  <table class="tbl">
    <tr>
      <td colspan="2">
        <div class="small-warning">
          Veuillez sélectionner les objets à {{tr}}{{$method}}{{/tr}}.
          <br>
          Attention, {{tr}}{{$method}}{{/tr}} le séjour {{tr}}{{$method}}{{/tr}}a également les interventions et consultations liées à celui-ci.
        </div>
      </td>
    </tr>
    <tr>
      <td class="narrow">
        <input type="checkbox" id="{{$method}}-select_sejour" name="select_sejour" data-guid="{{$sejour->_guid}}" onclick="DHE.checkAllObjects('{{$method}}', this);"{{if $action == 'edit_sejour'}} checked{{/if}}>
      </td>
      <td onclick="this.up().down('input').checked = !this.up().down('input').checked;">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">{{$sejour}}</span>
      </td>
    </tr>
    {{foreach from=$sejour->_ref_operations item=_operation}}
      <tr>
        <td class="narrow">
          <input type="checkbox" name="objects" class="select_object" data-guid="{{$_operation->_guid}}"{{if $action == 'edit_sejour' || ($action == 'edit_operation' && $operation->_id == $_operation->_id)}} checked{{/if}}>
        </td>
        <td onclick="this.up().down('input').checked = !this.up().down('input').checked;">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}');">{{$_operation}}</span>
        </td>
      </tr>
    {{/foreach}}
    {{foreach from=$sejour->_ref_consultations item=_consultation}}
      <tr>
        <td class="narrow">
          <input type="checkbox" name="objects" class="select_object" data-guid="{{$_consultation->_guid}}"{{if $action == 'edit_sejour' || ($action == 'edit_consultation' && $consult->_id == $_consultation->_id)}} checked{{/if}}>
        </td>
        <td onclick="this.up().down('input').checked = !this.up().down('input').checked;">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_consultation->_guid}}');">{{$_consultation}}</span>
        </td>
      </tr>
    {{/foreach}}
    <tr>
      <td class="button" colspan="2">
        {{if $method == 'delete'}}
          <button type="button" class="trash" onclick="Control.Modal.close(); DHE.submit('delete');">
            Supprimer les objets sélectionnés
          </button>
        {{elseif $method == 'cancel'}}
          <button type="button" class="cancel" onclick="Control.Modal.close(); DHE.submit('cancel');">
            Annuler les objets sélectionnés
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>