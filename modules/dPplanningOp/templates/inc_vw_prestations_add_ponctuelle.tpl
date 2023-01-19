{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<th class="text">
  {{if $editRights}}
    <input type="hidden" name="date" class="date" value="{{$today_ponctuelle}}"/>
    <input type="text" class="autocomplete" name="keywords"/>
    <div class="autocomplete" id="prestation_autocomplete" style="display: none; color: #000; text-align: left;"></div>
  {{/if}}
  <script>
    Main.add(function() {
      var form = getForm("edit_prestations");
      var url = new Url("hospi", "ajax_item_prestation_autocomplete");
      url.addParam("type_hospi", "{{$sejour->type}}");
      url.addParam("type_pec"  , "{{$sejour->type_pec}}");
      url.autoComplete(form.keywords, "prestation_autocomplete", {
        minChars: 3,
        method: "get",
        select: "view",
        dropdown: true,
        afterUpdateElement: function(field,selected) {
          var item_prestation_id = selected.get("id");
          var form_prestation = getForm("add_prestation_ponctuelle");
          $V(form_prestation.item_prestation_id, item_prestation_id);
          $V(form_prestation.date, $V(form.date));
          if (item_prestation_id) {
            form_prestation.onsubmit();
          }
        }
      });
      var dates = {
        limit: {
          start: "{{$sejour->entree}}",
          stop:  "{{$sejour->sortie}}"
        }
      };
      new Calendar.regField(form.date, dates);
    });
  </script>
</th>
