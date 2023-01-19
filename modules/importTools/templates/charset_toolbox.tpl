{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="me-margin-top-4">
  <table class="main layout">
    <tr>
      <td>
        <form name="input-form" method="post" action="?m=importTools&a=ajax_charset_finder" onsubmit="return Url.update(this, 'result-viewer')">
          <input type="hidden" name="m" value="importTools" />
          <input type="hidden" name="a" value="ajax_charset_finder" />
          <input type="hidden" name="compare" value="0" />
          <textarea name="string">{{$string}}</textarea>
          <select name="mode">
            <option value="raw" {{if $mode == "raw"}}selected{{/if}}>Brut</option>
            <option value="hex" {{if $mode == "hex"}}selected{{/if}}>Hex</option>
          </select>
          <button class="tick me-primary"   onclick="this.form.compare.value=0;">{{tr}}Process{{/tr}}</button>
          <button class="search" onclick="this.form.compare.value=1;">{{tr}}Compare{{/tr}}</button>
        </form>
      </td>
    </tr>
    <tr>
      <td id="result-viewer"></td>
    </tr>
  </table>
</div>