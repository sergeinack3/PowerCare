{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main form">
  <tr>
    <td>
      <fieldset>
        <legend>Thumbnail- Sans rotation</legend>
        {{foreach from=$profiles item=_profile}}
          {{thumbnail profile=$_profile document=$file rotate=0 page=1 default_size=1}}
        {{/foreach}}
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>Thumbnail- Rotation de 90°</legend>
        {{foreach from=$profiles item=_profile}}
          {{thumbnail profile=$_profile document=$file rotate=90 page=1 default_size=1}}
        {{/foreach}}
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>Thumbnail- Rotation de 180°</legend>
        {{foreach from=$profiles item=_profile}}
          {{thumbnail profile=$_profile document=$file rotate=180 page=1 default_size=1 default_size=1}}
        {{/foreach}}
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>Thumbnail- Rotation de 270°</legend>
        {{foreach from=$profiles item=_profile}}
          {{thumbnail profile=$_profile document=$file rotate=270 page=1 default_size=1}}
        {{/foreach}}
      </fieldset>
    </td>
  </tr>
</table>