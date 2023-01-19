{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  Main.add(function () {
    ExportPatients.updatePraticienCount()
  });
</script>

<select id="praticien_ids" multiple size="40" onclick="ExportPatients.updatePraticienCount()" style="width: 100%">
    {{foreach from=$praticiens item=_prat}}
      <option value="{{$_prat->_id}}" {{if in_array($_prat->_id, $array_praticien_id)}}selected{{/if}}
              onmouseover="ObjectTooltip.createEx(this, '{{$_prat->_guid}}')">
        #{{$_prat->_id|pad:5:0}} - {{$_prat}}
      </option>

    {{/foreach}}
</select>
<input type="text" id="praticien_ids_view" size="30" onfocus="this.select()"/>
<button class="up notext" onclick="$V('praticien_ids', $V('praticien_ids_view').split(/,/))"></button>
