{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=sante400 script=mouvements}}

<table class="main">
  <tr>
    <td style="width: 50%;">
      {{if $can->edit}}
        <a class="button new" href="?m={{$m}}&{{$actionType}}={{$action}}&dialog={{$dialog}}&mark_id=0">
          {{tr}}CTriggerMark-create{{/tr}}
        </a>
      {{/if}}
      {{mb_include module=sante400 template=inc_filter_marks}}
      {{mb_include module=sante400 template=inc_list_marks}}
    </td>
    {{if $can->edit}}
      <td style="width: 50%">
        {{mb_include module=sante400 template=inc_edit_mark}}
      </td>
    {{/if}}
  </tr>
</table>