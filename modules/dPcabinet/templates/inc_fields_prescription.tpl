{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=with_form value=1}}
{{mb_default var=form value="editActe"}}

{{if $with_form}}
<form name="{{$form}}" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_class object=$acte}}
  {{mb_key   object=$acte}}
  <table class="form">
    <tr>
      <th class="title" colspan="2">
        {{tr}}CActeNGAP{{/tr}} {{$acte->code}}
      </th>
    </tr>
{{/if}}

    <tr>
      <th class="narrow">{{mb_label object=$acte field=prescription_id}}</th>
      <td>
        <select name="prescription_id">
          <option value="">&mdash; {{tr}}None|f{{/tr}}</option>
          {{foreach from=$prescriptions item=_prescription}}
            <option value="{{$_prescription->_id}}" {{if $acte->prescription_id === $_prescription->_id}}selected{{/if}}>
              {{$_prescription}} (le {{$_prescription->_ref_object->_date|date_format:$conf.date}})
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$acte field=motif}}</th>
      <td>{{mb_field object=$acte field=motif}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$acte field=motif_unique_cim}}</th>
      <td>
        <script>
          Main.add(function() {
            var form = getForm('{{$form}}');
            CIM.autocomplete(form.keywords_code, null, {
              limit_favoris: parseInt(Preferences.cim10_search_favoris),
              chir_id: $V(form.executant_id),
              afterUpdateElement: function(input) {
                $V(getForm('{{$form}}').motif_unique_cim, input.value);
              }
            });
          });
        </script>

        <input type="text" name="keywords_code" value="{{$acte->motif_unique_cim}}"
               class="autocomplete str code cim10" style="width: 12em" />

        {{mb_field object=$acte field=motif_unique_cim hidden=true}}
      </td>
    </tr>
    {{*
    <tr>
      <th>
        {{mb_label object=$acte field=other_executant_id}}
      </th>
      <td>
        {{mb_script module=patients script=medecin register=true}}

        <script>
          Main.add(function() {
            Medecin.set = function(id, view) {
              $V(this.form.other_executant_id, id);
              $V(this.form._view, view);
            };

            var form = getForm('{{$form}}');

            new Url('patients', 'httpreq_do_medecins_autocomplete')
              .autoComplete(form._view, form._view.id+'_autocomplete', {
                updateElement : function(element) {
                  $V(getForm('{{$form}}').other_executant_id, element.id.split('-')[1]);
                  $V(getForm('{{$form}}')._view, element.select(".view")[0].innerHTML.stripTags());
                }
              });
          });
        </script>

        <input type="text" name="_view" class="autocomplete" />
        <button type="button" class="search"
                onclick="Medecin.edit(this.form, $V(this.form._view));">{{tr}}Choose{{/tr}}</button>
        <button type="button" class="cancel notext"
                onclick="$V(this.form._view, ''); $V(this.form.other_executant_id, '');">{{tr}}Empty{{/tr}}</button>

        {{mb_field object=$acte field=other_executant_id hidden=true}}
      </td>
    </tr>
*}}
    <tr>
      <th>{{tr}}CFile|pl{{/tr}}</th>
      <td>
        {{mb_include module=system template=inc_inline_upload}}
        {{foreach from=$acte->_ref_files item=_file}}
          <div style="float: left; margin: 5px;">
            <a href="#" class="action"
               onclick="File.popup('{{$acte->_class}}','{{$acte->_id}}','{{$_file->_class}}','{{$_file->_id}}');"
               onmouseover="ObjectTooltip.createEx(this, '{{$_file->_guid}}', 'objectView')">
              {{thumbnail document=$_file profile=medium title=$_file->file_name style="height: 64px; width: 64px; border: solid 1px #888;"}}
            </a>
          </div>
        {{/foreach}}
      </td>
    </tr>

    {{if $with_form}}
    <tr>
      <td colspan="2" class="button">
        <button type="button" class="save" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>

  </table>
</form>
{{/if}}
