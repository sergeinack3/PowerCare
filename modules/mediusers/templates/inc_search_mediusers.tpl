{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=mediusers script=CMediusers ajax=true}}

{{mb_include module=system template=inc_pagination total=$total_mediuser current=$page change_page='changePage'}}

<!--<form name="mediuser-adeli" action="" method="get"
      onsubmit="return CMediusers.doesMediuserExist($V($('adeli')));" style="float: right; clear: both;">
  <input type="search" id="adeli" name="adeli" required placeholder="{{tr}}CMediusers-adeli{{/tr}}" />
  <button type="submit" class="lookup notext">{{tr}}search_mediuser_by_adeli-button{{/tr}}</button>
</form>-->

{{mb_include template=vw_list_mediusers}}