{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    $("list_modeles_{{$owner}}").makeIntuitiveCheck("line", "export_modele");
  });
</script>

{{if $can->admin}}
  <button class="download me-secondary me-margin-2"
          {{if $modeles|@count}}
    onclick="Modele.exportXML('{{$owners.$owner|escape:"javascript"}}', '{{$filtre->object_class}}', $('list_modeles_{{$owner}}').select('input.export_modele:checked').pluck('value'))"
          {{/if}}>
      {{tr}}Export-XML{{/tr}}
  </button>
    {{assign var=owner_export value=$owners.$owner}}
  <button onclick="Modele.importXML('{{$owner_export->_guid}}');" class="upload me-secondary me-margin-2">{{tr}}Import-XML{{/tr}}</button>
  <button type="button" class="download" onclick="Modele.exportCSV();">{{tr}}Export-CSV{{/tr}}</button>
  <button onclick="Modele.removeSelection('list_modeles_{{$owner}}')" class="trash me-tertiary">{{tr}}CCompteRendu-Delete selection{{/tr}}</button>
{{/if}}

<table class="tbl me-no-align me-no-box-shadow" id="list_modeles_{{$owner}}">
  <tr>
    <th class="narrow">
      <input type="checkbox" onclick="this.up('table').select('input.export_modele').invoke('writeAttribute', 'checked', this.checked);" />
    </th>
    <th style="width: 30%">{{mb_colonne class=CCompteRendu field=nom  order_col=$order_col order_way=$order_way function=sortBy}}</th>
    <th class="narrow">
      {{me_form_field field_class="me-form-icon search" label="Search"}}
        <input type="text" style="width: 10em" class="search" onkeyup="Modele.filter(this)" />
      {{/me_form_field}}
    </th>
    <th>{{mb_colonne class=CCompteRendu field=object_class     order_col=$order_col order_way=$order_way function=sortBy}}</th>
    <th>{{mb_colonne class=CCompteRendu field=file_category_id order_col=$order_col order_way=$order_way function=sortBy}}</th>
    <th>{{mb_colonne class=CCompteRendu field=type             order_col=$order_col order_way=$order_way function=sortBy}}</th>
    {{if "dmp"|module_active}}<th>{{tr}}CFile-type_doc_dmp{{/tr}}</th>{{/if}}
    <th class="narrow">{{mb_colonne class=CContentHTML field=_image_status order_col=$order_col order_way=$order_way function=sortBy}}</th>
    <th class="narrow">{{mb_colonne class=CCompteRendu field=_date_last_use order_col=$order_col order_way=$order_way function=sortBy}}</th>
    <th class="narrow" colspan="2">
      {{mb_colonne class=CCompteRendu field=_count_utilisation order_col=$order_col order_way=$order_way function=sortBy}}
    </th>
    <th class="narrow" colspan="2"></th>
  </tr>

  {{foreach from=$modeles item=_modele}}
    {{mb_include module=compteRendu template=inc_line_modele}}
  {{foreachelse}}
    <tr>
      <td colspan="10" class="empty">{{tr}}CCompteRendu.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>


