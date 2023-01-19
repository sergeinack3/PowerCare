{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=appFine value=false}}
{{mb_default var=file_name value="form"}}

<table class="main tbl">
  <tr>
    <th>Veuillez choisir un �v�nement pour avoir un aper�u</th>
  </tr>
  {{foreach from=$ex_class->_ref_events item=_event}}
  <tr>
    <td>
      <button class="search" onclick="
              {{if !$appFine}}ExObject.preview('{{$_event->ex_class_id}}', '{{$_event->host_class}}-0');
              {{else}}appFineClient.previewForm('{{$_event->ex_class_id}}', '{{$file_name}}');{{/if}}">
        {{$_event}}
      </button>
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty">Il faut param�trer au moins un �v�nement</td>
  </tr>
  {{/foreach}}
</table>
