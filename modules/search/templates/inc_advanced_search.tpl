{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=pat_selector ajax=$ajax}}
{{mb_script module=search script=Search ajax=$ajax}}
{{mb_script module=search script=AdvancedSearch ajax=$ajax}}
{{mb_script module=cim10 script=CIM ajax=$ajax}}

<script>
    Main.add(() => {
        const form = getForm("advanced_search");

        AdvancedSearch.init(form);

        new Url("dPmedicament", "ajax_atc_autocomplete")
          .addParam("keywords_atc", $V(form.keywords_atc))
          .autoComplete(form.keywords_atc, null, {
              minChars:      1,
              method:        "post",
              dropdown:      true,
              updateElement: function (selected) {
                  const code = selected.down("span").getText();
                  const name = selected.down("div").getText();

                  const html = '<li class="tag me-tag" style="background-color: rgba(240, 255, 163, 0.60); cursor:auto">' +
                    code + ' / ' + name +
                    '<button type="button" class="notext delete" onclick="this.up(\'li\').remove()"></button>' +
                    '</li>';

                  $V(form.atc, code);
                  $('atc_list').innerHTML += html;
              }
          });

        new Url("dPccam", "autocompleteCcamCodes")
          .addParam("input_field", "keywords_ccam")
          .autoComplete(form.keywords_ccam, null, {
              minChars:      2,
              method:        "post",
              dropdown:      true,
              updateElement: function (selected) {
                  const code = selected.down("strong").getText();
                  let name = selected.down("small").getText();
                  if (name.length > 10) {
                      name = name.substr(0, 10) + '...'
                  }

                  const html = '<li class="tag me-tag" style="background-color: rgba(153, 204, 255, 0.6); cursor:auto">' +
                    code + ' ' + name +
                    '<button type="button" class="notext delete" onclick="this.up(\'li\').remove()"></button>' +
                    '</li>';

                  $V(form.ccam, code);
                  $('ccam_list').innerHTML += html;
              }
          });

        CIM.autocomplete(form.keywords_cim, null, {
            updateElement: function (selected) {
                const code = selected.down("span.code").getText();
                let name = selected.down("div").getText().trim();
                if (name.length > 10) {
                    name = name.substr(0, 10) + '...';
                }

                const html = '<li class="tag me-tag" style="background-color: #CCFFCC; cursor:auto">' +
                  code + ' ' + name +
                  '<button type="button" class="notext delete" onclick="this.up(\'li\').remove()"></button>' +
                  '</li>';

                $V(form.cim, code);
                $('cim_list').innerHTML += html;
            }
        });
    });
</script>

<form name="advanced_search">
    <table class="form">
        <tr>
            {{me_form_field nb_cells=2 label='AdvancedSearch-Contains words'}}
                <input type="text" name="contains_words" id="contains_words">
            {{/me_form_field}}

            <td>
                <div class="info">{{tr}}AdvancedSearch-Contains words-desc{{/tr}}</div>
            </td>
        </tr>

        <tr>
            {{me_form_field nb_cells=2 label='AdvancedSearch-Exact expression'}}
                <input type="text" name="exact" id="exact">
            {{/me_form_field}}

            <td>
                <div class="info">{{tr}}AdvancedSearch-Exact expression-desc{{/tr}}</div>
            </td>
        </tr>

        <tr>
            {{me_form_field nb_cells=2 label='AdvancedSearch-Contains one word'}}
                <input type="text" name="contains_word" id="contains_word">
            {{/me_form_field}}

            <td>
                <div class="info">{{tr}}AdvancedSearch-Contains one word-desc{{/tr}}</div>
            </td>
        </tr>

        <tr>
            {{me_form_field nb_cells=2 label='AdvancedSearch-Without a word'}}
                <input type="text" name="without_words" id="without_words">
            {{/me_form_field}}

            <td>
                <div class="info">{{tr}}AdvancedSearch-Without a word-desc{{/tr}}</div>
            </td>
        </tr>

        <tr>
            {{me_form_field nb_cells=2 label='AdvancedSearch-Types' layout=true}}
                <div style="display: block; width: 100%;">
                    <input type="checkbox" name="all" id="advanced_search_select_all_types">
                    <label for="select_all_types">{{tr}}All{{/tr}}</label>
                </div>
            {{foreach from=$types item=_type}}
                <div style="display: block; width: 100%;">
                    <input type="checkbox"
                           id="advanced_search_{{$_type}}"
                           class="types"
                           name="names_types[]"
                           value="{{$_type}}">
                    <label for="{{$_type}}">{{tr}}{{$_type}}{{/tr}}</label>
                    <br>
                </div>
            {{/foreach}}
            {{/me_form_field}}

            <td class="me-valign-top">
                <div class="info">{{tr}}AdvancedSearch-Types-desc{{/tr}}</div>
            </td>
        </tr>

        <tr>
            {{me_form_field nb_cells=2 label='AdvancedSearch-Date|pl' layout=true}}
                <input id="_min_date" type="hidden" name="_min_date">
                <b>&raquo;</b>
                <input id="_max_date" type="hidden" name="_max_date">
            {{/me_form_field}}

            <td>
                <div class="info">{{tr}}AdvancedSearch-Date|pl-desc{{/tr}}</div>
            </td>
        </tr>

        <tr>
            {{me_form_field nb_cells=2 label='AdvancedSearch-Patient' layout=true}}
                <input id="patient_id" type="hidden" name="patient_id">
                <input id="patient_autocomplete" type="text" name="patient_autocomplete">
            {{/me_form_field}}
        </tr>

        <tr>
            {{me_form_field nb_cells=2 label='AdvancedSearch-User' layout=true}}
                <input id="user_id" type="hidden" name="user_id">
                <input id="user_autocomplete" type="text" name="user_autocomplete">
            {{/me_form_field}}
        </tr>

        <tr>
            {{me_form_field nb_cells=2 label='AdvancedSearch-Cim' layout=true}}
                <input type="hidden" name="cim">
                <input type="text" id="keywords_cim" name="keywords_cim" class="autocomplete">
            {{/me_form_field}}

            <td>
                <ul id="cim_list" class="tags"></ul>
            </td>
        </tr>

        <tr>
            {{me_form_field nb_cells=2 label='AdvancedSearch-Ccam' layout=true}}
                <input type="hidden" name="ccam">
                <input type="text" id="keywords_ccam" name="keywords_ccam" class="autocomplete">
            {{/me_form_field}}

            <td>
                <ul id="ccam_list" class="tags"></ul>
            </td>
        </tr>

        <tr>
            {{me_form_field nb_cells=2 label='AdvancedSearch-Atc' layout=true}}
                <input type="hidden" name="atc">
                <input type="text" id="keywords_atc" name="keywords_atc" class="autocomplete">
            {{/me_form_field}}

            <td>
                <ul id="atc_list" class="tags"></ul>
            </td>
        </tr>

        <tr>
            <td colspan="3" class="me-text-align-center">
                <button type="button" class="search me-primary" onclick="Search.buildAdvancedSearchQuery(this.form);">
                    {{tr}}Search{{/tr}}
                </button>
            </td>
        </tr>
    </table>
</form>
