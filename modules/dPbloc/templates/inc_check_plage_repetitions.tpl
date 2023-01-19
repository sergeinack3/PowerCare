{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  toggleDetails = function(id) {
    var details = $(id + '_details');
    var icon = $(id + '_toggle');
    if (details.visible()) {
      icon.removeClassName('fa-chevron-down');
      icon.addClassName('fa-chevron-right');
    }
    else {
      icon.removeClassName('fa-chevron-right');
      icon.addClassName('fa-chevron-down');
    }

    details.toggle();
  };
</script>

<table class="tbl">
  <tr>
    <th class="title" colspan="2">
      Bilan des vacations {{if $plage->_id}}modifiées{{else}}créées{{/if}}
    </th>
  </tr>
  <tr>
    <td>
      Nombre de semaines : {{$repeat}}
    </td>
    <td>
      Type de répétition : {{tr}}CPlageOp._type_repeat.{{$type_repeat}}{{/tr}}
    </td>
  </tr>
  <tr>
    <td class="halfPane {{if $success|@count == $repeat}}ok{{elseif $success|@count > 0}}warning{{else}}error{{/if}}"{{if $success|@count > 0}} style="cursor: pointer;" onclick="toggleDetails('success');"{{/if}}>
      {{if $success|@count > 0}}<i class="fa fa-chevron-right" id="success_toggle"></i>
      &nbsp;{{/if}}Nombre de plages {{if $plage->_id}}modifiées{{else}}créées{{/if}} : {{$success|@count}}
    </td>
    <td class="{{if $errors|@count > 0}}error{{else}}ok{{/if}}"{{if $errors|@count > 0}} style="cursor: pointer;" onclick="toggleDetails('errors');"{{/if}}>
      {{if $errors|@count > 0}}<i class="fa fa-chevron-right" id="errors_toggle"></i>
      &nbsp;{{/if}}Nombre de plages en erreur : {{$errors|@count}}
    </td>
  </tr>
  <tr>
    <td>
      <ul id="success_details" style="display: none;">
        {{foreach from=$success item=_plage}}
          <li{{if $_plage.guid}} onmouseover="ObjectTooltip.createEx(this, '{{$_plage.guid}}');"{{/if}}>
            {{$_plage.view}}
          </li>
        {{/foreach}}
      </ul>
    </td>
    <td>
      <ul id="errors_details" style="display: none;">
        {{foreach from=$errors item=_error}}
          <li{{if $_error.guid}} onmouseover="ObjectTooltip.createEx(this, '{{$_error.guid}}');"{{/if}}>
            {{$_error.view}} : {{$_error.text}}
          </li>
        {{/foreach}}
      </ul>
    </td>
  </tr>
  <tr>
    <td class="button" colspan="2">
      <button type="button" class="tick" onclick="onSubmitFormAjax(getForm('editFrm'), {onComplete: Control.Modal.close}); Control.Modal.close();">Appliquer</button>
    </td>
  </tr>
</table>