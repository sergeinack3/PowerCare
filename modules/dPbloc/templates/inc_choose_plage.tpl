{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  {{foreach from=$plages item=_plage}}
    <tr>
      <td class="narrow">
        <button type="button" class="tick notext singleclick"
                onclick="
                  {{if $salle_id}}
                    Control.Modal.close();
                    EditPlanning.edit('{{$_plage->_id}}', '{{$_plage->date}}');
                  {{else}}
                    MultiSalle.changePlage('{{$_plage->_id}}', '{{$operation_id}}');
                  {{/if}}
                  "></button>
      </td>
      <td>
        {{$_plage->_ref_salle}}
        <div class="compact">
          {{mb_value object=$_plage field=debut}} - {{mb_value object=$_plage field=fin}}
        </div>
      </td>
    </tr>
  {{/foreach}}
</table>