{{*
* @package Mediboard\Sante400
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=sante400 script=massReplace}}
{{mb_script module=system script=class_indexer}}

<script>
  Main.add(function () {
    const form = getForm('form');
    // Class selector autocomplete with full param false
    ClassIndexer.autocomplete(form.autocomplete_input, form.object_class, {profile: 'full'});
    form.onsubmit();
  });
</script>

<form name="form" class="main form me-no-box-shadow">
  <table class="form">
    <tr>
      <th class="section">
        <h1>{{tr}}mod-dPsante400-find_and_replace_label-desc{{/tr}}</h1>
      </th>
    </tr>
    <tr>
        {{me_form_field nb_cells=2 mb_class=CIdSante400 mb_field=object_class}}
      <input type="text" name="autocomplete_input" size="40">
      <input type="hidden" id="object_class"/>
      {{/me_form_field}}
    </tr>
    <tr>
        {{me_form_field nb_cells=2 mb_class=CIdSante400 mb_field=tag}}
      <input type="text" size="20" id="tag"/>
      {{/me_form_field}}
    </tr>
    <tr>
        {{me_form_field nb_cells=2 label="mod-dPsante400-values"}}
      <textarea name="values" rows="5" id="values"></textarea>
      {{/me_form_field}}
    </tr>
    <tr>
        {{me_form_field nb_cells=2}}
      <div>{{tr}}mod-dPsante400-values_desc{{/tr}}</div>
      {{/me_form_field}}
    </tr>
    <tr>
      <td>
          {{me_form_field}}
        <button type="button" class="submit search" id="count_button" onclick="MassReplace.count()">
            {{tr}}Count{{/tr}}
        </button>
        {{/me_form_field}}
      </td>
    </tr>
    <tr>
        {{me_form_field nb_cells=2 label="mod-dPsante400-new_tag"}}
      <input type="text" size="20" id="new_tag" onkeyup="MassReplace.manageEditButtonVisibility()"/>
      {{/me_form_field}}
    </tr>
    <tr>
      <td>
          {{me_form_field}}
        <button type="button" class="submit" id="edit_button" onclick="MassReplace.edit()" disabled>
            {{tr}}mod-dPsante400-edit_tags{{/tr}}
        </button>
        {{/me_form_field}}
      </td>
    </tr>
  </table>
</form>
<div id="loader" class="ajax-loading me-margin-bottom-4" style="width: 100%; display: none;"></div>
<div id="count" class="me-margin-top-4">
</div>
<div id="success" class="me-margin-top-4">
</div>
<div id="error" class="me-margin-top-4">
</div>
<div id="error_tag" class="small-warning me-margin-top-4" style="display: none">
    {{tr}}mod-dPsante400-all_field_must_be_valued{{/tr}}
</div>

