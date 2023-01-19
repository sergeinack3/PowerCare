{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!--Formulaire d'ajout/édition d'une cible de favoris-->

{{mb_script module="search" script="Thesaurus" ajax=true}}
{{mb_script module=cim10 script=CIM ajax=true}}
<script>
  Main.add(function () {
    getAutocompleteCim();
    getAutocompleteCcam();
    getAutocompleteAtc();
    Control.Tabs.create('tabs-codes', true);
  });


  function getAutocompleteCim() {
    var form = getForm("cibleTarget");
    var element_input = form.elements.keywords_code;
    CIM.autocomplete(element_input, null, {
      updateElement: function (selected) {
        var form = getForm("cibleTarget");
        var _code = selected.down("span.code").getText();
        var _name = selected.down("div").getText();
        $V(form.elements.search_thesaurus_entry_target_id, "");
        $V(form.elements.object_id, _code);
        $V(form.elements.object_class, "CCodeCIM10");
        $V(form.elements.del, '0');
        Thesaurus.name_code = _code + " " + _name;
        if (!$("CCodeCIM10-" + _code)) {
          form.onsubmit();
        }
      }
    });
  }

  function getAutocompleteCcam() {
    var form = getForm("cibleTarget");
    var element_input = form.elements._codes_ccam;
    var url = new Url("dPccam", "autocompleteCcamCodes");
    url.addParam("_codes_ccam", $V(element_input));
    url.autoComplete(element_input, null, {
      minChars:      2,
      method:        "post",
      dropdown:      true,
      width:         "130%",
      updateElement: function (selected) {
        var _code = selected.down("strong").getText();
        var _name = selected.down("small").getText();
        $V(form.elements.search_thesaurus_entry_target_id, "");
        $V(form.elements.object_id, _code);
        $V(form.elements.object_class, "CCodeCCAM");
        $V(form.elements.del, '0');
        Thesaurus.name_code = _code + " " + _name;
        if (!$("CCodeCCAM-" + _code)) {
          form.onsubmit();
        }
      }
    });
  }

  function getAutocompleteAtc() {
    var form = getForm("cibleTarget");
    var element_input = form.elements.keywords_atc;
    var url = new Url("dPmedicament", "ajax_atc_autocomplete");
    url.addParam("keywords_atc", $V(element_input));
    url.autoComplete(element_input, null, {
      minChars:      1,
      method:        "post",
      dropdown:      true,
      width:         "130%",
      updateElement: function (selected) {
        var _code = selected.down("span").getText();
        var _name = selected.down("div").getText();
        $V(form.elements.search_thesaurus_entry_target_id, "");
        $V(form.elements.object_id, _code);
        $V(form.elements.object_class, "CMedicamentClasseATC");
        $V(form.elements.del, '0');
        Thesaurus.name_code = _code + " " + _name;
        if (!$("CClasseATC-" + _code)) {
          form.onsubmit();
        }
      }
    });
  }
</script>

<form method="post" name="cibleTarget" class="watched prepared" onsubmit="return onSubmitFormAjax(this);">
  <input type="hidden" name="object_class" />
  <input type="hidden" name="object_id" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="search_thesaurus_entry_id" value="{{$thesaurus_entry_id}}" />
  <input type="hidden" name="callback" value="Thesaurus.addTargetCallback" />
  {{mb_key   object=$target}}
  {{mb_class object=$target}}
  <table class="layout">
    <tr>
      <td>
        <ul id="tabs-codes" class="control_tabs">
          <li><a href="#tab-CIM">Codes Cim10</a></li>
          <li><a href="#tab-CCAM">Codes CCAM</a></li>
          <li><a href="#tab-ATC">Classes ATC</a></li>
        </ul>
      </td>
    </tr>
    <tr>
      <td>

        <!-- Codes CIM -->

        <div id="tab-CIM" style="display: none">
          <table class="layout">
            <tr>
              <td style="vertical-align: top;">
                <label><input type="search" id="keywords_code" name="keywords_code" value="" class="autocomplete" /></label>
                <label><input type="hidden" name="_CCodeCIM10" /></label>
              </td>
              <td>
                <ul id="CCodeCIM10_tags" class="tags">
                  {{foreach from=$thesaurus_entry->_cim_targets item=_target}}
                    <li class="tag me-tag" title="{{$_target->_ref_target->libelle}}" style="background-color: #CCFFCC; cursor:auto">
                      {{$_target->_ref_target->code}}  {{$_target->_ref_target->libelle}}
                      <button type="submit" class="delete"
                              onclick="$V(this.form.elements.search_thesaurus_entry_target_id, '{{$_target->_id}}');$V(this.form.elements.del,'1');  this.form.onsubmit() ; this.up('li').next('br').remove(); this.up('li').remove();"
                              style="display: inline-block !important;"></button>
                    </li>
                    <br />
                  {{/foreach}}
                </ul>
              </td>
            </tr>
          </table>
        </div>

        <!-- Actes CCAM -->

        <div id="tab-CCAM" style="display: none">
          <table class="layout">
            <tr>
              <td style="vertical-align: top;">
                <label><input type="search" id="_codes_ccam" name="_codes_ccam" value="" class="autocomplete" /></label>
                <label><input type="hidden" name="_CCodeCCAM" /></label>
              </td>
              <td>
                <ul id="CCodeCCAM_tags" class="tags">
                  {{foreach from=$thesaurus_entry->_ccam_targets item=_target}}
                    <li class="tag me-tag" title="{{$_target->_ref_target->libelle_long}}"
                        style="background-color: rgba(153, 204, 255, 0.6); cursor:auto">
                      {{$_target->_ref_target->code}}  {{$_target->_ref_target->libelle_court}}
                      <button type="submit" class="delete"
                              onclick="$V(this.form.elements.search_thesaurus_entry_target_id, '{{$_target->_id}}');$V(this.form.elements.del,'1');  this.form.onsubmit() ; this.up('li').next('br').remove(); this.up('li').remove();"
                              style="display: inline-block !important;"></button>
                    </li>
                    <br />
                  {{/foreach}}
                </ul>
              </td>
            </tr>
          </table>
        </div>

        <!-- Classes ATC -->
        <div id="tab-ATC" style="display:none;">
          <table class="layout">
            <tr>
              <td style="vertical-align: top;">
                <label><input type="search" id="keywords_atc" name="keywords_atc" value="" class="autocomplete" /></label>
                <label><input type="hidden" name="_ClasseATC" /></label>
              </td>
              <td>
                <ul id="CMedicamentClasseATC_tags" class="tags">
                  {{foreach from=$thesaurus_entry->_atc_targets item=_target}}
                    <li class="tag me-tag" title="{{$_target->_libelle}}" style="background-color: rgba(240, 255, 163, 0.60); cursor:auto">
                      {{$_target->object_id}}  {{$_target->_libelle}}
                      <button type="submit" class="delete"
                              onclick="$V(this.form.elements.search_thesaurus_entry_target_id, '{{$_target->_id}}');$V(this.form.elements.del,'1');  this.form.onsubmit() ; this.up('li').next('br').remove(); this.up('li').remove();"
                              style="display: inline-block !important;"></button>
                    </li>
                    <br />
                  {{/foreach}}
                </ul>
              </td>
            </tr>
          </table>
        </div>

      </td>
    </tr>
  </table>
</form>
