{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=list_mode value=$app->user_prefs.ant_trai_grid_list_mode}}

<script>
  function addAntecedent(event, rques, date, type, appareil, input, reaction_indesirable) {
    if (event && event.ctrlKey) {
      window.save_params = { 'input': input, 'type': type, 'appareil': appareil};
      var complete_atcd = $('complete_atcd');
      $V(complete_atcd.down("textarea"), rques);

      if (type == 'alle') {
        $('show_reaction').show();
      }
      else {
        $('show_reaction').hide();
      }

      Modal.open(complete_atcd);
      return;
    }
    if (window.opener) {
      var oForm = window.opener.getForm('editAntFrm');

      if (typeof (oForm) == "undefined") {
        oForm = getForm('editAntFrmGrid');
        oForm._patient_id.value = "{{$patient->_id}}";
      }

      if (oForm) {
        if ($V(oForm._patient_id) != "{{$patient->_id}}") {
          alert($T('closeModale-no_concern_this_patient'));
          input.checked = false;
          return;
        }
        oForm.rques.value = rques;
        if (reaction_indesirable && (reaction_indesirable != "undefined")) {
          oForm.reaction_indesirable.value = reaction_indesirable;
        }
        oForm.type.value = type;
        oForm.appareil.value = appareil;
        if (oForm.date) {
          oForm.date.value = date;
        }

        window.opener.onSubmitAnt(oForm);

        input.disabled = 'disabled';

        $(input).up('label').removeAttribute('onmouseover');

        $(input).up('td').setStyle({cursor: 'default', opacity: 0.3});
      }
    }
    window.focus();
  }

  selectAtcd = function(selector){
    if (!selector.value) {
      return;
    }

    $V(selector.form.type, selector.down(':selected').get('type'));
    $V(selector.form.appareil, selector.down(':selected').get('appareil'));
    $V(selector.form.rques, selector.value); selector.form.down('input[class=autocomplete]').value='';
    selector.form.type.value='';
    selector.form.rques.value = '';
    selector.value='';
  };

  completeAtcd = function() {
    var form = getForm("completeAtcdFom");
    addAntecedent(null, $V(form.rques), $V(form.date), window.save_params.type, window.save_params.appareil, window.save_params.input, $V(form.reaction_indesirable));
    emptyDate(form);
  };

  emptyDate = function(form) {
    form.select("input.date").each(function(input) {
      $V(input, "");
    });
  };

  var oFormAntFrmGrid;

  Main.add(function () {
    Control.Tabs.create('tab-antecedents', false);

    var oFormAnt = window.opener.document.editAntFrm;
    oFormAntFrmGrid = document.editAntFrmGrid;
    $V(oFormAntFrmGrid._patient_id,  oFormAnt._patient_id.value);
    if(oFormAnt._sejour_id){
      $V(oFormAntFrmGrid._sejour_id,  oFormAnt._sejour_id.value);
    }

    {{if !$list_mode}}
      $$(".droppable").each(function(li) {
        Droppables.add(li, {
          onDrop: function(from, to, event) {
            var parent = from.up("ul");
            if (parent != to.up("ul") || !to) {
              return;
            }
            // S'ils sont côte à côte, juste insérer le premier après le deuxième
            if (from.next("li") == to) {
              from = from.remove();
              to.insert({after: from});
              return;
            }
            if (from.previous("li") == to) {
              from = from.remove();
              to.insert({before: from});
              return;
            }

            // Sinon on sauvegarde la position et on insère
            // Cas du dernier élément
            var next = from.next("li");
            if (next) {
              from = from.remove();
              to.insert({before: from});
              to = to.remove();
              next.insert({before: to});
              return;
            }

            var previous = from.previous("li");
            if (previous) {
              from = from.remove();
              to.insert({after: from});
              to = to.remove();
              previous.insert({after: to});
              return;
            }
          },
          accept: 'draggable',
          hoverclass: "atcd_hover"
        });
      });

      $$(".draggable").each(function(li) {
        new Draggable(li, {
          onEnd: function() {
            var form = getForm("editPref");
            var pref_tabs = {};

            $("tab-antecedents").select("a").each(function(a) {

              var appareils = $(a.href.split("#")[1]).select("a").invoke("get", "appareil").join("|");
              var type = a.get("type");
              pref_tabs[type] = appareils;
            });

            $V(form.elements["pref[order_mode_grille]"], Object.toJSON(pref_tabs));
            onSubmitFormAjax(form);
          },
          revert: true });
        });
    {{/if}}

    Calendar.regProgressiveField($('date_atcd'));

    oFormAntFrmGrid._search.makeAutocomplete({width: "200px"});
  });
</script>

<form name="editPref" method="post">
  <input type="hidden" name="m" value="admin" />
  <input type="hidden" name="dosql" value="do_preference_aed" />
  <input type="hidden" name="user_id" value="{{$user_id}}" />
  <input type="hidden" name="pref[order_mode_grille]" value="{{$order_mode_grille|@json}}" />
</form>

<div id="complete_atcd" style="display: none; width: 500px; height: 180px;">
  <form name="completeAtcdFom" method="get">
    <table class="form">
      <tr>
        <th class="title" colspan="3">
          {{tr}}CAntecedent-complete{{/tr}}
        </th>
      </tr>
      <tr>
        <th style="height: 100%;" class="narrow">{{mb_label class=CAntecedent field=date}}</th>
        <td class="narrow">
          <input type="hidden" name="date" class="date" id="date_atcd" />
        </td>
        <td></td>
      </tr>
      <tr>
        <th>{{mb_label class=CAntecedent field=rques}}</th>
        <td colspan="2">
          <textarea name="rques"></textarea>
        </td>
      </tr>
      <tr id="show_reaction">
        <th>{{mb_label class=CAntecedent field=reaction_indesirable}}</th>
        <td colspan="2">{{mb_field class=CAntecedent field=reaction_indesirable}}</td>
      </tr>
      <tr>
        <td colspan="3" class="button">
          <button type="button" class="tick" onclick="Control.Modal.close(); completeAtcd();">
            {{tr}}Validate{{/tr}}
           </button>
          <button type="button" class="close" onclick="Control.Modal.close(); window.save_params.input.checked = ''; emptyDate(this.form);">{{tr}}Close{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>

<!-- Antécédents -->
{{assign var=numCols value=4}}
{{math equation="100/$numCols" assign=width format="%.1f"}}
<table id="antecedents" class="main" style="display: none;">
  <tr>
    <td colspan="3">
      <form name="editAntFrmGrid" method="post" onsubmit="if (!this.rques.value) { return false; } return window.opener.onSubmitAnt(this);">
        <input type="hidden" name="m" value="patients" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="dosql" value="do_antecedent_aed" />
        <input type="hidden" name="_patient_id" />
        <input type="hidden" name="_sejour_id" />
      
        <input type="hidden" name="_hidden_rques" />
        <input type="hidden" name="rques" onchange="this.form.onsubmit();"/>
       
        <input type="hidden" name="type" />
        <input type="hidden" name="appareil" />

        {{mb_label object=$antecedent field=_search}}
        <select name="_search" onchange="selectAtcd(this);">
          <option value="">{{tr}}CAntecedent-_search{{/tr}}</option>
          {{foreach from=$aides_autocomplete item=_aide key=name}}
            <option data-type="{{$_aide.type}}" data-appareil="{{$_aide.appareil}}" value="{{$_aide.text}}">{{$name}}</option>
          {{/foreach}}
        </select>
      </form>
    </td>  
  </tr>
  <tr>
    {{if !$list_mode}}
      <td style="vertical-align: top;" class="narrow">
        <ul id="tab-antecedents" class="control_tabs_vertical">
          {{foreach from=$antecedent->_count_rques_aides item=count key=type}}
            {{if $count}}
              <li class="draggable droppable">
                <a href="#antecedents_{{$type}}" style="white-space: nowrap;" data-type="{{$type}}">
                  {{tr}}CAntecedent.type.{{$type}}{{/tr}}
                  <small>({{$count}})</small>
                </a>
              </li>
            {{/if}}
          {{/foreach}}
        </ul>
      </td>
    {{/if}}
    <td>
      {{foreach from=$antecedent->_count_rques_aides item=count key=type}}
        {{if $count}}
          <table id="antecedents_{{$type}}" class="main me-no-box-shadow me-no-border" style="border: none; border-collapse: collapse; margin-bottom: 10px;">
            {{if $list_mode}}
              <tr>
                <th class="title">
                  {{tr}}CAntecedent.type.{{$type}}{{/tr}}<small>({{$count}})</small>
                </th>
              </tr>
              <tr>
                <td style="padding:0px;">
                  {{if $count}}
                    {{mb_include module=cabinet template=inc_grid_list_antecedents
                    title_class="category" table_display="table" appareil_count=true}}
                  {{/if}}
                </td>
              </tr>
            {{else}}
              <tr>
                <td class="narrow text me-padding-left-3 me-padding-right-3" style="background-color: transparent; border: none;">
                  <script>
                    Main.add(function() {
                      Control.Tabs.create('tab-{{$type}}', false);
                    });
                  </script>

                  <ul id="tab-{{$type}}" class="me-control-tabs-wraped control_tabs">
                    {{foreach from=$aides_antecedent.$type item=_aides key=appareil}}
                      <li class="draggable droppable">
                        <a href="#{{$type}}-{{$appareil}}"style="white-space: nowrap;" data-appareil="{{$appareil}}">
                          {{tr}}CAntecedent.appareil.{{$appareil}}{{/tr}}
                          <small>({{$antecedent->_count_rques_aides_appareil.$type.$appareil}})</small>
                        </a>
                      </li>
                    {{/foreach}}
                  </ul>
                </td>
              </tr>
              <tr>
                <td>
                  {{if $count}}
                    {{mb_include module=cabinet template=inc_grid_list_antecedents}}
                  {{/if}}
                </td>
              </tr>
            {{/if}}
          </table>
        {{/if}}
      {{/foreach}}
    </td>
  </tr>
</table>