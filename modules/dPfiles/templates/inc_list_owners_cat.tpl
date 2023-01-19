{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="layout">
  {{foreach from=$file_cat_default_list item=_file_cat_default}}
  <tr>
    <td class="text">
      <form name="delCatDefault{{$_file_cat_default->_id}}" method="post" onsubmit="return onSubmitFormAjax(this, refreshList.curry('{{$type}}'));">
        {{mb_class object=$_file_cat_default}}
        {{mb_key   object=$_file_cat_default}}
        <input type="hidden" name="del" value="1" />
        <button type="button" class="remove notext" onclick="this.form.onsubmit();"></button>
      </form>
      {{if $_file_cat_default->owner_class == "CMediusers"}}
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_file_cat_default->_ref_owner}}
      {{else}}
        {{mb_include module=mediusers template=inc_vw_function function=$_file_cat_default->_ref_owner}}
      {{/if}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty">
      {{if $type == "users"}}
        {{tr}}CMediusers.none{{/tr}}
      {{else}}
        {{tr}}CFunctions.none{{/tr}}
      {{/if}}
    </td>
  </tr>
  {{/foreach}}
</table>