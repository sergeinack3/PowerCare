{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">

Main.add(function(){
  prepareEmptyRows();
  toggleEmptyRows();
});

prepareEmptyRows = function(){
  var container = $("ex_class-tables");
  if (!container) return;
  
  var emptyBodies = container.select(".ex_class-table tbody.data-row").filter(function(tbody){ 
    var emptyRows = tbody.select('tr.field.empty').length;
    var allRows = tbody.select('tr.field').length;
    var empty = (emptyRows == allRows);
    
    if (!empty) {
      tbody.select('th.ex_group').each(function(th){
        th.addClassName("rowspan-changed");
        th.emptyRowSpan = allRows-emptyRows+1;
        th.origRowSpan = th.rowSpan;
      });
    }
    
    return empty;
  });
  
  emptyBodies.each(function(body){
    body.addClassName("empty");
    
    // Hide the tbody's header (previous tbody) if the tbody is empty
    var previous = body.previous("tbody");
    if (previous) {
      previous.addClassName("empty");
    }
  });
};

toggleEmptyRows = function(){
  var container = $("ex_class-tables");
  if (!container) return;
  
  var show = !container.hasClassName("hide-empty-rows");
  
  container.select(".ex_class-table .empty").invoke("setVisible", show);
  
  container.select(".ex_class-table th.rowspan-changed").each(function(th) {
    th.rowSpan = (show ? th.origRowSpan : th.emptyRowSpan);
  });
  
  container.toggleClassName("hide-empty-rows", show);
};
</script>

<div id="ex_class-tables" class="hide-empty-rows">
  {{if !$print}}
  <button class="change" onclick="toggleEmptyRows()">
    Afficher/cacher les valeurs vides
  </button>
  {{/if}}
  
  {{foreach from=$ex_objects item=_ex_objects key=_ex_class_id}}
    <div {{if !$ex_class_id && !$print}} style="display: none;" {{/if}} class="ex_class-table">
      {{mb_include module=forms template=inc_ex_objects_columns}}
    </div>
  {{foreachelse}}
    <div class="empty">{{tr}}CExClass.none{{/tr}}</div>
  {{/foreach}}
</div>