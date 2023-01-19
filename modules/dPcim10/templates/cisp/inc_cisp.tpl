{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    CISP.mode = '{{$mode}}';
    CISP.tabs = Control.Tabs.create("tabs_cisp", null, {
      afterChange: function () {
        $$('div.list_cim10 li').invoke('removeClassName', 'selected');
        if ($('apply_cisp')) {
          $('apply_cisp').writeAttribute('disabled', 'disabled');
        }
        if ($('copy_cisp')) {
          $('copy_cisp').writeAttribute('disabled', 'disabled');
        }
      }
    });

    CISP.displayCimCodes();
  });
</script>

<style>
  div.list ul.list {
    height: 600px;
  }

  div#list_cim10 ul.list {
    height: 600px;
  }

  ul.list {
    list-style-type: none;
    padding: 0;
    overflow-y: auto;
  }

  ul.list li {
    background-color: #fff;
    cursor: pointer;
    padding: 5px;
  }

  ul.list li:nth-of-type(odd),
  table.list tr:nth-of-type(odd) td
  {
    background-color: rgba(168, 183, 235, 0.10);
  }

  ul.list li:hover,
  table.list tr:hover td,
  table.list tr:nth-of-type(odd):hover td {
    background-color: rgba(168, 183, 235, 0.30);
  }

  ul.list li.selected,
  table.list tr.selected {
    background-color: rgba(165, 182, 235, 0.60);
  }

  ul li.selected:hover,
  table.list tr.selected:hover {
    background-color: rgba(167, 184, 235, 0.90);
  }
</style>

<table class="main">
  <tr>
    <td style="width: 170px;">
      <ul id="tabs_cisp" class="control_tabs_vertical">
        {{foreach from=$chapitres item=_chapitre}}
          <li>
            <a href="#chapitre_{{$_chapitre}}">{{$_chapitre}} ({{$_chapitre->description}})</a>
          </li>
        {{/foreach}}
      </ul>
    </td>
    <td>
      {{foreach from=$chapitres item=_chapitre}}
        <div id="chapitre_{{$_chapitre}}">
          {{if $_chapitre->note}}
            <div class="small-info text">
              {{$_chapitre->note}}
            </div>
          {{/if}}

          <table class="main" style="width: 100%">
            <tr>
              <td style="width: 200px;">
                <fieldset>
                  <legend>{{tr}}CCISP-List{{/tr}}</legend>

                  <div class="list">
                    <ul class="list list-cisp">
                      {{foreach from=$_chapitre->_ref_cisps item=_cisp}}

                        <li class="me-display-flex me-justify-content-space-between"
                          {{if !$ged}} onclick="CISP.showDetail(this, '{{$_cisp|@json|JSAttribute}}', '{{$_cisp->_codes_cim10|@json|JSAttribute}}', '{{$_chapitre}}');" {{/if}}>
                          <div>
                            {{$_cisp}}
                            <div class="compact">
                              {{$_cisp->code_cisp}}
                            </div>
                          </div>
                          {{if $ged}}
                            <button type="button" class="tick notext" data-code="{{$_cisp->code_cisp}}" data-libelle="{{$_cisp}}"
                                    title="{{tr}}Select{{/tr}}"
                                    onclick="CISP.selectCode(this)">
                              {{tr}}Select{{/tr}}
                            </button>
                          {{/if}}
                        </li>
                        {{if $_cisp->_indice >= 29 && $_cisp->_indice < 70}}
                          {{foreach from=$procedures item=_procedure}}
                            <li class="me-display-flex me-justify-content-space-between">
                              <div>
                                {{$_procedure->description}}
                                <div class="compact">
                                  {{$_procedure->_indice}}
                                </div>
                              </div>
                              {{if $ged}}
                                <button type="button" class="tick notext"  data-code="{{$_procedure->_indice}}" data-libelle="{{$_procedure->description}}"
                                        title="{{tr}}Select{{/tr}}"
                                        onclick="CISP.selectCode(this)">
                                  {{tr}}Select{{/tr}}
                                </button>
                              {{/if}}
                            </li>
                          {{/foreach}}
                        {{/if}}
                      {{/foreach}}
                    </ul>
                  </div>
                </fieldset>
              </td>
              {{if !$ged}}
                <td class="cim-codes">
                  <table class="width100">
                    <tr>
                      <td>
                        <fieldset>
                          <legend>{{tr}}CCISP-Detail{{/tr}}</legend>

                          <div id="cisp_detail_{{$_chapitre}}"></div>
                        </fieldset>
                      </td>
                    </tr>

                    <tr class="cim10-details" style="display: none">
                      <td>
                        <fieldset>
                          <legend>{{tr}}CCISP-List cim10{{/tr}}</legend>
                          <div id="list_cim10_{{$_chapitre}}" class="list list_cim10">
                            <ul class="list" style="height: auto">
                            </ul>
                          </div>
                        </fieldset>
                      </td>
                    </tr>
                  </table>
                </td>
              {{/if}}
            </tr>
          </table>
        </div>
      {{/foreach}}
    </td>
  </tr>
</table>

