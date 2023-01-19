{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=mediusers script=CMediusers}}

<script type="text/javascript">
Main.add(function(){
  var form = getForm("filter-ex_object");
  Calendar.regField(form.date_min);
  Calendar.regField(form.date_max);
});

selectConcept = function(field) {
  var id = field.value;
  var url = new Url("forms","ajax_concept_value_choser");
  url.addParam("concept_id", id);
  url.requestUpdate("concept-value-chose");
};

filterExObjects = function(form){
  $V(form.concept_search, Object.toJSON(getForm('filter-concept-value').serialize(true)));
  
  return Url.update(form, 'list-ex_object-counts');
};
</script>

<table class="main form">
  <tr>
    <th class="title">Critères</th>
    <th class="title">Recherche par valeur</th>
  </tr>
  <tr>
    <td class="narrow">
      <form name="filter-ex_object" method="get" onsubmit="return filterExObjects(this)">
        <input type="hidden" name="m" value="forms" />
        <input type="hidden" name="a" value="ajax_list_ex_object_counts" />
        <input type="hidden" name="search_mode" value="1" />
        <input type="hidden" name="concept_search" value="" />
        {{if $reference && $reference->_id}}
          <input type="hidden" name="reference_class" value="{{$reference->_class}}" />
          <input type="hidden" name="reference_id" value="{{$reference->_id}}" />
        {{/if}}

        <table class="main form me-no-box-shadow">
          <tr>
            <th class="narrow">Date min</th>
            <td class="narrow"><input type="hidden" name="date_min" value="{{$date_min}}" class="date" /></td>
          </tr>
          <tr>
            <th>Date max</th>
            <td><input type="hidden" name="date_max" value="{{$date_max}}" class="date" /></td>
          </tr>

          {{if !$reference || !$reference->_id}}
          <tr>
            <th class="narrow">{{tr}}CGroups{{/tr}}</th>
            <td>
              <select name="group_id">
                {{foreach from=$groups item=_group}}
                  <option value="{{$_group->_id}}" {{if $_group->_id == $g}} selected="selected" {{/if}}>{{$_group}}</option>
                {{/foreach}}
              </select>
            </td>
          </tr>
          {{/if}}

          <tr>
            <th>{{tr}}CExObject-owner_id{{/tr}}</th>
            <td>
              <script>
                Main.add(CMediusers.standardAutocomplete.curry('filter-ex_object', 'owner_id', '_view'));
              </script>
              <input type="hidden" name="owner_id" value="" />
              <input type="text" name="_view" class="autocomplete" placeholder="&mdash; {{tr}}All{{/tr}}" value="" />
              <button type="button" class="cancel notext" onclick="$V(this.form.owner_id, ''); $V(this.form._view, '');"></button>
            </td>
          </tr>

          <tr>
            <td></td>
            <td><button type="submit" class="search">Filtrer</button></td>
          </tr>
        </table>
      </form>
    </td>
    <td>
      <form name="filter-concept-value" method="post">
        <table class="main layout">
          <tr>
            <td class="narrow">
              {{mb_label object=$field field=concept_id}}
              {{mb_field object=$field field=concept_id form="filter-concept-value" autocomplete="true,1,50,true,true" onchange="selectConcept(this)"}}
              <button class="cancel notext" type="button" onclick="$V(this.form.concept_id, ''); $V(this.form.concept_id_autocomplete_view, '')"></button>
              <div id="concept-value-chose"></div>
            </td>
            <td id="concept-value"></td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
  <tr>
    <td id="list-ex_object-counts" style="width: 20%;"></td>
    <td id="list-ex_object">&nbsp;</td>
  </tr>
</table>
