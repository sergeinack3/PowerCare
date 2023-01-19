{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=lite value=false}}

<script>
  updatePraticienCount = function () {
    var list = $V($("praticien_ids"));

    $('praticien-count').update(list.length);

    var formPatients = getForm("{{$form_name}}");
    $V(formPatients["{{$form_select}}[]"], list);

    $V($("praticien_ids_view"), list.join(","));
  };

  Main.add(function () {
    updatePraticienCount();
  });
</script>

{{if !$lite}}
  <h2>{{tr}}CGroups{{/tr}} : {{$current_group}}</h2>
{{/if}}

Praticiens de l'établissement (<span id="praticien-count">0</span> sélectionnés)
<br />

<select id="praticien_ids" multiple size="40" onclick="updatePraticienCount()" class="me-vh75">
  {{foreach from=$praticiens item=_prat}}
    <option value="{{$_prat->_id}}" {{if in_array($_prat->_id,$praticien_id)}}selected{{/if}}
            onmouseover="ObjectTooltip.createEx(this, '{{$_prat->_guid}}')">
      #{{$_prat->_id|pad:5:0}} - {{$_prat}}
    </option>
  {{/foreach}}
</select>

<br />
<input type="text" id="praticien_ids_view" size="30" onfocus="this.select()" />
<button class="up notext" onclick="$V('praticien_ids', $V('praticien_ids_view').split(/,/)); updatePraticienCount();"></button>