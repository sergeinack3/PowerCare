{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  Une ligne ne sera prise en compte que si au moins une de ses cases est cochée.<br />
  Les permissions sont vérifiées dans l'ordre de la liste (de haut en bas).
</div>

<div id="exclass-permission-editor">
  {{mb_include module=forms template=inc_edit_ex_class_permissions_table list=$all_types}}
</div>

<div style="text-align: center">
  <form name="edit-perms-{{$ex_class->_guid}}" method="post" onsubmit="return ExClass.savePermissions(this, 'exclass-permission-editor');">
    <input type="hidden" name="m" value="system" />
    <input type="hidden" name="dosql" value="do_ex_class_aed" />
    {{mb_key object=$ex_class}}
    {{mb_field object=$ex_class field=permissions hidden=true}}

    <button class="submit">
      {{tr}}Save{{/tr}}
    </button>
  </form>
</div>