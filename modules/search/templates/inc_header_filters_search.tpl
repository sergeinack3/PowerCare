{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=display_date      value=true}}
{{mb_default var=display_patient   value=true}}
{{mb_default var=display_user      value=true}}
{{mb_default var=display_types     value=true}}
{{mb_default var=display_contextes value=false}}
{{mb_default var=query             value=true}}
{{mb_default var=expand            value=true}}
{{mb_default var=submit            value=false}}
{{mb_default var=contexte          value='classique'}}
{{mb_default var=tooltip_help      value=true}}

<script>
  Main.add(function () {
    var form = getForm('esSearch');
    var url = new Url('system', 'ajax_seek_autocomplete');
    url.addParam('object_class', 'CPatient');
    url.addParam('field', 'patient_id');
    url.addParam('view_field', '_seek_patient');
    url.addParam('input_field', '_seek_patient');
    url.autoComplete(form.elements._seek_patient, null, {
      minChars:           3,
      method:             'get',
      select:             'view',
      dropdown:           false,
      width:              '300px',
      afterUpdateElement: function (field, selected) {
        $V(field.form.patient_id, selected.getAttribute('id').split('-')[2]);
        $V(field.form._seek_patient, selected.down('.view').innerHTML);
      }
    });
  });

  function insertTag(guid, name) {
    var tag = $("CTag-" + guid);

    if (!tag) {
      var btn = DOM.button({
        "type":      "button",
        "className": "delete",
        "style":     "display: inline-block !important",
        "onclick":   "window.user_tag_token.remove($(this).up('li').get('tag_item_id')); this.up().remove();"
      });

      var li = DOM.li({
        "data-tag_item_id": guid,
        "id":               "CTag-" + guid,
        "className":        "tag"
      }, name, btn);

      $("user_tags").insert(li);
    }
  }
</script>

<style>
  #divAdvancedSearch {
    margin-top: 5px;
    margin-left: 2px;
  }

  .divFiltre {
    display: inline-block;
    vertical-align: top;
    min-width: 200px;
    max-width: 300px;
  }

  .divFiltre:not(:first-child) {
    margin-left: 10px;
  }

  .divFiltreTitle {
    border-radius: 5px;
    background: #DDDDDD;
    color: #6B6966;
    padding: 3px;
    font-size: 14px;
    text-align: center;
  }

  .divFiltreContenu {
    padding: 3px;
  }

  .divCloseReference {
    float: right;
    cursor: pointer
  }

  .divCloseReference > span {
    margin-right: 2px;
  }

  .divReference {
    border-radius: 5px;
    color: white;
    padding: 3px;
    display: inline-block;
    background: #0DC143;
  }
</style>

<div id="divAdvancedSearch" style="display: none;">
  <!-- DATES -->
  <div class="divFiltre">
    <div class="divFiltreTitle">{{tr}}mod-search-intervalle{{/tr}}</div>
    <div class="divFiltreContenu">
      <input type="hidden" class="date" id="_min_date" name="_min_date"
             onchange="$V(this.form.start, '0'); {{if $submit}}this.form.onsubmit();{{/if}}" />
      <b>&raquo;</b>
      <input type="hidden" class="date" id="_max_date" name="_max_date"
             onchange="$V(this.form.start, '0') ; {{if $submit}}this.form.onsubmit();{{/if}}" />
    </div>
  </div>

  <!-- PATIENT -->
    {{if $display_patient}}
      <div class="divFiltre">
        <div class="divFiltreTitle">{{tr}}mod-search-patient{{/tr}}</div>
        <div class="divFiltreContenu">
          <input type="text" name="_seek_patient" style="width: 13em;" placeholder="{{tr}}fast-search{{/tr}} patient"
                 value="" />
          <button type="button" onclick="$V(this.form._seek_patient, ''); $V(this.form.patient_id, '');" class="erase notext"
                  title="{{tr}}mod-search-vider{{/tr}}"></button>
          <input type="hidden" name="patient_id" value="" />
        </div>
      </div>
    {{/if}}

  <!-- TYPES -->
  <div class="divFiltre">
    <div class="divFiltreTitle">
      <input type="checkbox" name="searchAll" id="SearchAll" value="SearchAll"
             onclick="Search.checkAllCheckboxes(this, 'names_types[]')">
      <label for="SearchAll">{{tr}}mod-search-types{{/tr}}</label>
    </div>
    <div class="divFiltreContenu" style="column-count: 2;">
        {{foreach from=$types item=_type}}
            {{if $_type != "CPrescriptionLineMedicament" && $_type != "CPrescriptionLineMix" &&  $_type != "CPrescriptionLineElement"}}
              <input type="checkbox" name="names_types[]" id="{{$_type}}" value="{{$_type}}" />
              <label for="{{$_type}}">{{tr}}{{$_type}}{{/tr}}</label>
              <br />
            {{/if}}
        {{/foreach}}
        {{if array_intersect(array("CPrescriptionLineMedicament", "CPrescriptionLineMix","CPrescriptionLineElement"), $types)}}
          <input type="checkbox" name="names_types[]" id="CPrescriptionLineMedicament"
                 value="CPrescriptionLineMedicament" />
          <label for="precription">{{tr}}mod-search-prescription{{/tr}}</label>
          <br />
        {{/if}}
    </div>
  </div>

  <!-- Contexte -->
    {{if $display_contextes}}
      <div class="divFiltre">
        <div class="divFiltreTitle">
          <input type="checkbox" name="SearchAllContextes" id="SearchAllContextes" value="SearchAllContextes"
                 onclick="Search.checkAllCheckboxes(this, 'contextes[]')">
          <label for="SearchAllContextes">{{tr}}mod-search-contexte{{/tr}}</label>
        </div>
        <div class="divFiltreContenu" style="column-count: 2;">
            {{foreach from=$contextes item=_contexte}}
              <input type="checkbox" name="contextes[]" id="{{$_contexte}}" value="{{$_contexte}}" />
              <label for="{{$_contexte}}">{{tr}}CSearchThesaurusEntry.contextes.{{$_contexte}}{{/tr}}</label>
              <br />
            {{/foreach}}
        </div>
      </div>
    {{/if}}

  <!-- AUTHOR -->
    {{if $display_user}}
      <div class="divFiltre">
        <div class="divFiltreTitle">{{tr}}mod-search-intervenant{{/tr}}</div>
        <div class="divFiltreContenu">
          <table class="layout">
            <tr>
              <td>
                <input type="text" name="user_view" class="autocomplete" value=""
                       placeholder="{{tr}}mod-search-intervenant-choisir{{/tr}}" />
                <input type="hidden" name="user_id" />
                <button type="button" class="user notext" title="Mon compte"
                        onclick="window.user_tag_token.add('{{$app->user_id}}'); insertTag('{{$app->_ref_user->_guid}}', '{{$app->_ref_user}}')">
                </button>
                <button type="button" title="{{tr}}mod-search-effacer-champ{{/tr}}" class="erase notext" onclick="$V(this.form.elements.user_id, '');
                            $V(this.form.elements.user_view, ''); $$('li.tag').each(function(elt) { elt.remove(); });">
                </button>
              </td>
            </tr>
            <tr>
              <td>
                <ul id="user_tags" class="tags" style="float: none;"></ul>
              </td>
            </tr>
          </table>
        </div>
      </div>
    {{/if}}

  <!-- Pattern   -->
  <div class="divFiltre">
    <div class="divFiltreTitle">
        {{tr}}CSearchThesaurusEntry-Pattern{{/tr}}
        {{mb_include module=search template=inc_tooltip_help display=$tooltip_help}}
    </div>
    <div class="divFiltreContenu" style="display:none">
      <button type="button" title="{{tr}}CSearchThesaurusEntry-Pattern-title and{{/tr}}"
              onclick="Thesaurus.addPatternToEntry('add', this.form)">{{tr}}CSearchThesaurusEntry-Pattern and{{/tr}}</button>
      <button type="button" title="{{tr}}CSearchThesaurusEntry-Pattern-title or{{/tr}}"
              onclick="Thesaurus.addPatternToEntry('or', this.form)">{{tr}}CSearchThesaurusEntry-Pattern or{{/tr}}</button>
      <button type="button" title="{{tr}}CSearchThesaurusEntry-Pattern-title not{{/tr}}"
              onclick="Thesaurus.addPatternToEntry('not', this.form)">{{tr}}CSearchThesaurusEntry-Pattern not{{/tr}}</button>
      <br>
      <button type="button" title="{{tr}}CSearchThesaurusEntry-Pattern-title like{{/tr}}"
              onclick="Thesaurus.addPatternToEntry('like', this.form)">{{tr}}CSearchThesaurusEntry-Pattern like{{/tr}}</button>
      <button type="button" title="{{tr}}CSearchThesaurusEntry-Pattern-title obligation{{/tr}}"
              onclick="Thesaurus.addPatternToEntry('obligation', this.form)">{{tr}}CSearchThesaurusEntry-Pattern obligation{{/tr}}</button>
      <button type="button" title="{{tr}}CSearchThesaurusEntry-Pattern-title prohibition{{/tr}}"
              onclick="Thesaurus.addPatternToEntry('prohibition', this.form)">{{tr}}CSearchThesaurusEntry-Pattern prohibition{{/tr}}</button>
    </div>
  </div>

  <!-- Reference   -->
  <div class="divFiltre" id="divFiltreReference" style="display:none;">
    <div class="divFiltreTitle">
        {{tr}}CSearchThesaurusEntry-reference{{/tr}}
      <div class="divCloseReference" title="{{tr}}mod-search-effacer-champ{{/tr}}"
           onclick="Search.closeFiltreReference();">
        <i class="fas fa-times"></i>
      </div>
    </div>
    <div class="divFiltreContenu">
      <input type="hidden" id="reference" name="reference" value="" />
      <div id="divReference" class="divReference" value=""></div>
    </div>
  </div>


</div>


