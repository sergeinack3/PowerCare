{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm('import_files_by_regex');
    var regex = form.elements.regex;
    var from_meta_count = $('from_meta_count');
    var to_meta_count = $('to_meta_count');

    from_meta_count.addSpinner({min: 1});
    to_meta_count.addSpinner({min: 1});


    document.on('click', 'ul#regex_fields li.tag', function (e) {
      var element = e.element();
      var text = $V(regex);

      var field = element.get('field');
      var selector = element.get('selector');

      switch (field) {
        case 'separator':
          $V(regex, text + '(' + selector + ')');
          break;

        case 'wildcard':
          var from = $V(from_meta_count);
          var to = $V(to_meta_count);
          $V(regex, text + '(' + selector + '{' + from + ',' + to + '})');
          break;

        default:
          $V(regex, text + '(?P<' + field + '>' + selector + ')');
      }
    });
  });

  submitImportForm = function (form) {
    var url = new Url('dPfiles', 'ajax_preview_file_matching');
    url.addFormData(form);

    url.requestUpdate('preview_files', {method: 'post', getParameters: {m: 'dPfiles', a: 'ajax_preview_file_matching'}});

    if ($V(form.elements.import) == '1') {
      $V(form.elements.import, '');
    }

    return false;
  };

  importFile = function (elt) {
    var form = getForm('import_files_by_regex');
    var filename = elt.get('file');

    if (filename) {
      $V(form.elements.file, filename);
    }

    submitImportForm(form);

    $V(form.elements.file, '');
  };

  viewMetrics = function (form) {
    var url = new Url('dPfiles', 'ajax_view_file_matching_metrics');
    url.addParam('regex', $V(form.elements.regex));
    url.requestModal(700, null, {method: 'post', showReload: false, getParameters: {m: 'dPfiles', a: 'ajax_view_file_matching_metrics'}});
  }
</script>

<form name="EditConfig" method="post" onsubmit="return onSubmitFormAjax(this, document.location.reload);">
  {{mb_configure module=$m}}
  
  <table class="main form">
    <col style="width: 20%;" />

    {{mb_include module=system template=inc_config_str var=import_dir size=50}}

    <tr>
      <th class="narrow">{{tr}}User{{/tr}}</th>
      <td colspan="3">
        <select name="dPfiles[import_mediuser_id]" onchange="if (this.value) { this.form.onsubmit(); }">
          <option value="" disabled selected>&mdash; {{tr}}CMediusers.select{{/tr}}</option>

          {{foreach from=$users item=_user}}
            <option value="{{$_user->_id}}" {{if $conf.dPfiles.import_mediuser_id == $_user->_id}} selected{{/if}}>
              {{$_user}} &ndash; {{$_user->_ref_function}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        <button type="submit" class="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

{{if !$conf.dPfiles.import_dir}}
  <div class="small-error">
    {{tr}}common-error-You have to specify a directory.{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{if !$conf.dPfiles.import_mediuser_id}}
  <div class="small-error">
    {{tr}}common-error-You have to specify a mediuser.{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

<hr />

<ul class="tags" id="regex_fields" style="text-align: center;">
  <li class="tag tag_selector" data-field="wildcard" data-selector=".">Métacaractère</li>
  <input type="text" id="from_meta_count" value="1" size="2" />
  <input type="text" id="to_meta_count" value="1" size="2" />
  <li class="tag tag_selector" data-field="separator" data-selector="_">Séparateur</li>
  <li style="display: inline-block;">&bull;</li>
  <li class="tag tag_selector tag_selector_IPP" data-field="IPP" data-selector="[0-9]+">IPP</li>
  <li class="tag tag_selector tag_selector_lastname" data-field="lastname" data-selector="[A-Z ,._'-]+">Nom patient</li>
  <li class="tag tag_selector tag_selector_firstname" data-field="firstname" data-selector="[A-z ,._'-]+">Prénom patient</li>
  <li class="tag tag_selector tag_selector_birthdate" data-field="birthdate" data-selector="\d{4}\d{2}\d{2}">Date de naissance
    patient
  </li>
  {{*<li class="tag tag_selector tag_selector_NDA" data-field="NDA" data-selector="[0-9]+">NDA</li>*}}
  <li class="tag tag_selector tag_selector_sejour" data-field="sejour_start" data-selector="\d{4}\d{2}\d{2}">Début séjour</li>
  <li class="tag tag_selector tag_selector_sejour" data-field="sejour_end" data-selector="\d{4}\d{2}\d{2}">Fin séjour</li>
</ul>

<hr />

<form name="import_files_by_regex" method="post" onsubmit="return submitImportForm(this);">
  <input type="hidden" name="import" value="" />
  <input type="hidden" name="file" value="" />

  <table class="main form">
    <col class="narrow" />

    <tr>
      <th>Expression rationnelle</th>

      <td>
        <textarea name="regex" rows="3">{{if $regex}}{{$regex}}{{/if}}</textarea>
      </td>
    </tr>

    <tr>
      <th>Expression de date</th>
      <td>
        <textarea name="regex_date" rows="3">{{if $regex_date}}{{$regex_date}}{{else}}(?P<year>\d{4})(?P<month>\d{2})(?P<day>\d{2}){{/if}}</textarea>
      </td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        <button type="button" class="erase notext" onclick="$V(this.form.elements.regex, '');">
          {{tr}}common-action-Empty{{/tr}}
        </button>

        <button type="submit" class="search">{{tr}}common-action-Preview{{/tr}}</button>

        <label>
          {{tr}}common-noun-Start{{/tr}} : <input type="text" name="start" value="0" size="3" />
        </label>

        <label>
          {{tr}}common-Step{{/tr}} : <input type="text" name="step" value="50" size="3" />
        </label>

        <button type="submit" class="tick" id="import_btn" onclick="$V(this.form.elements.import, '1');">
          {{tr}}common-action-Import{{/tr}}
        </button>

        <label>
          {{tr}}common-Auto{{/tr}} : <input type="checkbox" name="continue" value="1" checked />
        </label>

        <button type="button" class="stats" onclick="viewMetrics(this.form);">
          {{tr}}common-Metric|pl{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>

<div id="preview_files"></div>


