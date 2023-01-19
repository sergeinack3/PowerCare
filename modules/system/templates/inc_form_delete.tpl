{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="del{{$object->_guid}}" action="" method="post">
  {{mb_class object=$object}}
  {{mb_key object=$object}}
  <input type="hidden" name="del" value="1" />

  <button class="cancel notext" type="button" onclick="confirmDeletion(this.form, {
	    ajax:1, 
      typeName:&quot;{{tr}}{{$object->_class}}.one{{/tr}}&quot;,
      objName:&quot;{{$object->_view|smarty:nodefaults|JSAttribute}}&quot;
    })">
  </button>
</form>