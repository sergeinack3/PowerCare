{{*
 * @package Mediboard\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    getForm('search-campaign-objects').onsubmit();
  });
</script>

<form name="search-campaign-objects" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result-campaign-objects')">
  <input type="hidden" name="m" value="import"/>
  <input type="hidden" name="a" value="ajax_vw_campaign_objects"/>

  <table class="main form">
    <tr>
      <th>{{tr}}CImportCampaign{{/tr}}</th>
      <td>
        <select name="campaign_id" onchange="this.form.onsubmit();">
          {{foreach from=$all_campaign item=_campaign}}
            <option value="{{$_campaign->_id}}" {{if $campaign->_id && $_campaign->_id === $campaign->_id}}selected{{/if}}>
              {{$_campaign}}
            </option>
          {{/foreach}}
        </select>
      </td>

      <th>Affichage</th>
      <td>
        <label>
          <input type="radio" name="show_errors" value="all" {{if $show_errors === 'all'}} checked{{/if}} onchange="this.form.onsubmit();"/>
          {{tr}}CImportEntity-label-All{{/tr}}
        </label>

        <label>
          <input type="radio" name="show_errors" value="valid" {{if $show_errors === 'valid'}} checked{{/if}} onchange="this.form.onsubmit();"/>
          {{tr}}CImportEntity-label-Only imported|pl{{/tr}}
        </label>

        <label>
          <input type="radio" name="show_errors" value="error" {{if $show_errors === 'error'}} checked{{/if}} onchange="this.form.onsubmit();"/>
          {{tr}}CImportEntity-label-Only in error|pl{{/tr}}
        </label>
      </td>
    </tr>
  </table>
</form>

<div id="result-campaign-objects"></div>
