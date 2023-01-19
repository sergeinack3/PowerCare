{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $prestation->_id}}
  <script type="text/javascript">
    Main.add(function () {
      Prestation.editItem('{{$item_id}}', '{{$prestation->_class}}', '{{$prestation->_id}}');
      Prestation.refreshItems('{{$prestation->_class}}', '{{$prestation->_id}}', '{{$item_id}}');
    });
  </script>
{{/if}}
<form name="edit_prestation" method="post" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="dPhospi" />
  {{if $prestation|instanceof:'Ox\Mediboard\Hospi\CPrestationJournaliere'}}
    <input type="hidden" name="dosql" value="do_prestation_journaliere_aed" />
  {{else}}
    <input type="hidden" name="dosql" value="do_prestation_ponctuelle_aed" />
  {{/if}}
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="callback" value="Prestation.afterEditPrestation" />
  {{mb_key object=$prestation}}
  {{mb_field object=$prestation field=group_id hidden=1}}
  
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$prestation}}
    <tr>
      <th>
        {{mb_label object=$prestation field=nom}}
      </th>
      <td>
        {{mb_field object=$prestation field=nom}}
      </td>
    </tr>
    {{if $prestation|instanceof:'Ox\Mediboard\Hospi\CPrestationJournaliere'}}
      <tr>
        <th>
          {{mb_label object=$prestation field=desire}}
        </th>
        <td>
          {{mb_field object=$prestation field=desire}}
        </td>
      </tr>
    {{else}}
      <tr>
        <th>
          {{mb_label object=$prestation field=show_admission}}
        </th>
        <td>
          {{mb_field object=$prestation field=show_admission}}
        </td>
      </tr>
    {{/if}}
    <tr>
      <th>
          {{mb_label object=$prestation field=actif}}
      </th>
      <td>
          {{mb_field object=$prestation field=actif}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$prestation field=type_hospi}}
      </th>
      <td>
        {{mb_field object=$prestation field=type_hospi}}
      </td>
    </tr>
    <tr>
      <th>
        {{tr}}CPrestationExpert-type_pec{{/tr}}
      </th>
      <td>
        <label>
          {{mb_field object=$prestation field=M typeEnum=checkbox}} M
        </label>
        <label>
          {{mb_field object=$prestation field=C typeEnum=checkbox}} C
        </label>
        <label>
          {{mb_field object=$prestation field=O typeEnum=checkbox}} O
        </label>
        <label>
          {{mb_field object=$prestation field=SSR typeEnum=checkbox}} SSR
        </label>
      </td>
    </tr>
    {{if $prestation|instanceof:'Ox\Mediboard\Hospi\CPrestationPonctuelle'}}
      <tr>
        <th>
          {{mb_label object=$prestation field=forfait}}
        </th>
        <td>
          {{mb_field object=$prestation field=forfait}}
        </td>
      </tr>
    {{/if}}
    <tr>
      <td colspan="2" class="button">
        <button type="button" class="save" onclick="this.form.onsubmit()">
          {{tr}}{{if $prestation->_id}}Save{{else}}Create{{/if}}{{/tr}}
        </button>
        {{if $prestation->_id}}
          <button type="button" class="cancel" onclick="confirmDeletion(this.form, {
            typeName: 'la prestation',
            objName:'{{$prestation->_view|smarty:nodefaults|JSAttribute}}',
            ajax: true})">{{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>

<hr class="me-no-display" />
<div id="edit_item"></div>
<hr class="me-no-display" />
<div id="list_items"></div>
