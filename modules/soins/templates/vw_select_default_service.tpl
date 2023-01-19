{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="select_default_service" style="display: none;">
  <table class="form me-no-box-shadow">
    <tr>
      <td style="text-align: center;">
        <select name="default_service_id">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$services item=_service}}
            <option value="{{$_service->_id}}" {{if $default_service_id == $_service->_id}}selected{{/if}}>{{$_service}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <td class="button">
        <button type="button" class="submit" onclick="savePref(this.form);">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</div>