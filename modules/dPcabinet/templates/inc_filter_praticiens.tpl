{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <td class="text">
      {{foreach from=$prats item=_prat}}
        <label>
          <input type="checkbox" name="prats_ids[{{$_prat->_id}}]" value="{{$_prat->_id}}" class="prats" checked />
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_prat}}
        </label>
      {{/foreach}}
    </td>
  </tr>
</table>