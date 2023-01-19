{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=soins      script=soins}}
{{mb_script module=cabinet    script=edit_consultation}}
{{mb_script module=planningOp script=sejour}}

<script>
  Main.add(function() {
    var form = getForm('filterSejoursReeducation');

    Calendar.regField(form.date_min);
    Calendar.regField(form.date_max);

    Soins.reloadSejoursReeducation();
  });
</script>

<form name="filterSejoursReeducation" method="get">
  <fieldset>
    <legend>
      {{tr}}CSejour-Filter reeducation{{/tr}}
    </legend>

    <table class="form">
      <tr>
        <th class="narrow">
          {{tr}}Date{{/tr}}
        </th>
        <td style="width: 33%;">
          <input type="hidden" name="date_min" value="{{$date_min}}" class="date notNull"
                 onchange="Soins.reloadSejoursReeducation();" />
          &gt;&gt;&gt;
          <input type="hidden" name="date_max" value="{{$date_max}}" class="date notNull"
                 onchange="Soins.reloadSejoursReeducation();" />
        </td>
        <td style="width: 33%">
          {{if "soins Sejour select_services_ids"|gconf}}
            <button type="button" class="search"
                    onclick="Soins.selectServices('reeduc', 0, 0);">
              {{tr}}Services{{/tr}}
            </button>
          {{else}}
            <select name="service_id" style="max-width: 145px;" onchange="Soins.reloadSejoursReeducation();">
              <option value="">&mdash; {{tr}}CService{{/tr}}</option>
              {{foreach from=$services item=curr_service}}
                <option value="{{$curr_service->_id}}" {{if $curr_service->_id == $service_id}}selected{{/if}}>{{$curr_service->nom}}</option>
              {{/foreach}}
            </select>
          {{/if}}
        </td>
        <td>
          <select name="praticien_id" onchange="Soins.reloadSejoursReeducation();" style="width: 145px;">
            <option value="">&mdash; Choix du praticien</option>
            {{mb_include module=mediusers template=inc_options_mediuser selected=$praticien_id list=$praticiens}}
          </select>
        </td>
      </tr>
      <tr>
        <td colspan="4" class="button">
          <button type="button" class="tick" onclick="Soins.reloadSejoursReeducation();">{{tr}}Filter{{/tr}}</button>
        </td>
      </tr>
    </table>
  </fieldset>
</form>

<form name="handleAlerte" method="post">
  {{mb_class object=$alerte}}
  {{mb_key   object=$alerte}}
  <input type="hidden" name="handled" value="1" />
</form>

<br />

<div id="sejours_area"></div>