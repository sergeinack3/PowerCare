{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div>
  <table class="main form">
    {{foreach from=$profiles item=_profile}}
      <tr>
        <td>
          <fieldset>
            <legend>Thumbnail {{$_profile}} - Sans crop</legend>
            {{thumbnail profile=$_profile document=$file page=1 default_size=1}}
            {{thumbnail profile=$_profile document=$file page=2 default_size=1}}
            {{thumbnail profile=$_profile document=$file page=3 default_size=1}}
            {{thumbnail profile=$_profile document=$file page=4 default_size=1}}
            {{thumbnail profile=$_profile document=$file page=5 default_size=1}}
          </fieldset>
        </td>
      </tr>
    {{/foreach}}
    {{foreach from=$profiles item=_profile}}
      <tr>
        <td>
          <fieldset>
            <legend>Thumbnail {{$_profile}} - Avec crop</legend>
            {{thumbnail profile=$_profile document=$file page=1 crop=1 default_size=1}}
            {{thumbnail profile=$_profile document=$file page=2 crop=1 default_size=1}}
            {{thumbnail profile=$_profile document=$file page=3 crop=1 default_size=1}}
            {{thumbnail profile=$_profile document=$file page=4 crop=1 default_size=1}}
            {{thumbnail profile=$_profile document=$file page=5 crop=1 default_size=1}}
          </fieldset>
        </td>
      </tr>
    {{/foreach}}
  </table>
</div>