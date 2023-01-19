{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


<script>
  exportStat = function (form) {
    $V(form.elements.export, 'csv');
    $V(form.elements.suppressHeaders, 1);

    form.submit();

    $V(form.elements.export, '');
    $V(form.elements.suppressHeaders, '');
  };

  checkExclusiveFilters = function (element) {
    var value = $V(element);
    var classname = '';
    $w(element.className).each(function (_class) {
      if (_class.search('exclusive') > -1) {
        classname = _class;
      }
    });

    if (!value) {
      if (element.name == 'prat_id') {
        $V(element.form.elements.prat_id_view, '');
      }

      return;
    }

    $$('form[name="' + element.form.name + '"] .' + classname).each(function (elt) {
      if (elt.name != element.name) {
        if (elt.multiple) {
          var opts = elt.options;
          for (var i = 0; i < opts.length; i++) {
            opts[i].selected = false;
          }
          opts[0].selected = true;
        }
        $V(elt, '', true);
      }
    });

    if (element.multiple) {
      addMultipleElement(element);
    }
  };

  changePrefListUsers = function (element) {
    var bNewValue = $V(element);
    var oForm = getForm("editPrefUserAutocompleteEdit");

    if (bNewValue) {
      $V(oForm.elements['pref[useEditAutocompleteUsers]'], 1);
      $$(".changePrefListUsers").each(function (_element) {
        $V(_element, 1, false);
      });
    }
    else {
      $V(oForm.elements['pref[useEditAutocompleteUsers]'], 0);
      $$(".changePrefListUsers").each(function (_element) {
        $V(_element, 0, false);
      });
    }

    return onSubmitFormAjax(oForm);
  };

  addMultipleElement = function (element) {
    var values = [];
    var opts = element.options;
    for (var i = 0; i < opts.length; i++) {
      if (opts[i].selected) {
        values.push(opts[i].value);
      }
    }
    var ids = $$('form[name="' + element.form.name + '"] input[name="' + element.name + 's"]')[0];
    $V(ids, values);
  };

  Main.add(function () {
    var form = getForm('filterANAP');
    token_prat = new TokenField(form.prat_ids);
    var url = new Url("mediusers", "ajax_users_autocomplete");
    url.addParam("praticiens", '1');
    url.addParam("input_field", 'prat_id_view');
    url.autoComplete(form.elements.prat_id_view, null, {
      minChars:           0,
      method:             "get",
      select:             "view",
      dropdown:           true,
      afterUpdateElement: function (field, selected) {
        $V(form.elements.prat_id_view, '');
        var id = selected.getAttribute("id").split("-")[2];
        $V(form.elements.prat_id, id, true);
        token_prat.add(id);
        var button = '<button class="remove" type="button" onclick="token_prat.remove(\'' + id + '\');this.remove();">' + selected.down('.view').innerHTML + '</button>';
        $('filterANAP_prat_id_token').insert({before: button});
      }
    });
  });
</script>

<form name="filterANAP" action="?" method="get" target="_blank" onsubmit="return onSubmitFormAjax(this, null, 'resultANAPStats');">
  <input type="hidden" name="m" value="stats" />
  <input type="hidden" name="a" value="ajax_get_anap_stat" />
  <input type="hidden" name="export" value="" />
  <input type="hidden" name="suppressHeaders" value="" />
  <input type="hidden" name="salle_ids" value="" onchange="if($V(this)) {$V(this.form.bloc_ids, '');}" />
  <input type="hidden" name="bloc_ids" value="" onchange="if($V(this)) {$V(this.form.salle_ids, '');}" />
  <input type="hidden" name="prat_ids" value="" />
  <input type="hidden" name="discipline_ids" value="" />

  <table class="form">
    <tr>
      <th>
        {{mb_label object=$stats field=date_min}}
      </th>
      <td>
        {{mb_field object=$stats field=date_min canNull=false register=true form='filterANAP'}}
      </td>
      <th rowspan="2">
        {{mb_label object=$stats field=prat_id}}</th>
      <td rowspan="2" class="text">
        <input type="text" name="prat_id_view" class="autocomplete" style="width: 15em;"
               placeholder="&mdash; {{tr}}CMediusers.select{{/tr}}" />
        {{mb_field object=$stats field=prat_id hidden=true class='exclusive-filter_1' onchange='checkExclusiveFilters(this);'}}

        <input name="_limit_search_prat" class="changePrefListUsers" type="checkbox" onchange="changePrefListUsers(this);"
               {{if $app->user_prefs.useEditAutocompleteUsers}}checked{{/if}} title="Limiter la recherche des praticiens" />
        <div><span id="filterANAP_prat_id_token"></span></div>
      </td>

      <th rowspan="2">
        {{mb_label object=$stats field=discipline_id}}
      </th>
      <td rowspan="2">
        <select name="discipline_id" class="exclusive-filter_1" onchange="checkExclusiveFilters(this);" size="4" multiple>
          <option value="" selected>&mdash; {{tr}}CDiscipline.select{{/tr}}</option>

          {{foreach from=$disciplines item=_discipline}}
            <option value="{{$_discipline->_id}}">{{$_discipline}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$stats field=date_max}}
      </th>
      <td>
        {{mb_field object=$stats field=date_max canNull=false register=true form='filterANAP'}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$stats field=plages_to_display}}
      </th>
      <td>
        {{mb_field object=$stats field=plages_to_display}}
      </td>
      <th rowspan="3">{{mb_label object=$stats field=salle_id}}</th>
      <td rowspan="3">
        <select name="salle_id" style="width: 15em;" class="exclusive-filter_2" onchange="checkExclusiveFilters(this);" size="4"
                multiple>
          <option value="" selected>&mdash; {{tr}}CSalle.select{{/tr}}</option>

          {{foreach from=$blocs item=_bloc}}
            <optgroup label="{{$_bloc->nom}}">
              {{foreach from=$_bloc->_ref_salles item=_salle}}
                <option value="{{$_salle->_id}}">{{$_salle->nom}}</option>
                {{foreachelse}}
                <option value="" disabled>{{tr}}CSalle.none{{/tr}}</option>
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      </td>
      <th rowspan="3">{{mb_label object=$stats field=bloc_id}}</th>
      <td rowspan="3">
        <select name="bloc_id" style="width: 15em;" class="exclusive-filter_2" onchange="checkExclusiveFilters(this);" size="4"
                multiple>
          <option value="" selected>&mdash; {{tr}}CBlocOperatoire.select{{/tr}}</option>

          {{foreach from=$blocs item=_bloc}}
            <option value="{{$_bloc->_id}}">{{$_bloc}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$stats field=grouping}}
      </th>
      <td>
        {{mb_field object=$stats field=grouping emptyLabel="Select"}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$stats field=operations_to_display}}
      </th>
      <td>
        {{mb_field object=$stats field=operations_to_display}}
      </td>
    </tr>
    <tr>
      <td colspan="6" class="button">
        <button id="anap-submit" type="button" class="search" onclick="this.form.onsubmit();">
          {{tr}}Search{{/tr}}
        </button>
        <button type="button" class="download" onclick="exportStat(this.form);">
          {{tr}}common-action-Export{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>

<form name="editPrefUserAutocompleteEdit" method="post">
  <input type="hidden" name="m" value="admin" />
  <input type="hidden" name="dosql" value="do_preference_aed" />
  <input type="hidden" name="user_id" value="{{$app->user_id}}" />
  <input type="hidden" name="pref[useEditAutocompleteUsers]" value="{{$app->user_prefs.useEditAutocompleteUsers}}" />
</form>

<div id="resultANAPStats"></div>