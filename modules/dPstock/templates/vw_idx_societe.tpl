{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  refreshSocietesList = function () {
    new Url("stock", "httpreq_vw_societes_list")
      .addFormData("filterSociete")
      .requestUpdate("list-societe");
    return false;
  };

  changePageSociete = function (page) {
    $V(getForm("filterSociete").start, page);
  };

  afterEditSociete = function (societe_id) {
    editSociete(societe_id);
    refreshSocietesList();
  };

  editSociete = function (societe_id) {
    new Url("stock", "httpreq_vw_societe_form")
      .addParam("societe_id", societe_id)
      .requestUpdate("edit-societe");
  };

  Main.add(function () {
    refreshSocietesList();
    editSociete();
  });
</script>

<table class="main">
  <tr>
    <td class="halfPane">
      <form name="filterSociete" method="get" action="" onsubmit="return refreshSocietesList()">
        <input type="hidden" name="start" value="0" onchange="this.form.onsubmit()" />
        
        <input type="text" name="keywords" value="" onchange="$V(this.form.start, 0)" />
        <button type="submit" class="search notext">{{tr}}Filter{{/tr}}</button>
        
        <label>
          <input type="checkbox" name="suppliers" value="1" {{if $suppliers}}checked{{/if}}
                 onchange="$V(this.form.start, 0); this.form.onsubmit()" />
          Distributeurs
        </label>
        
        <label>
          <input type="checkbox" name="manufacturers" value="1" {{if $manufacturers}}checked{{/if}}
                 onchange="$V(this.form.start, 0); this.form.onsubmit()" />
          Fabricants
        </label>
        
        <label>
          <input type="checkbox" name="inactive" value="1" {{if $inactive}}checked{{/if}}
                 onchange="$V(this.form.start, 0); this.form.onsubmit()" />
          Sociétés inactives
        </label>
      </form>
    </td>
    <td id="edit-societe" class="halfPane" rowspan="2"></td>
  </tr>
  <tr>
    <td id="list-societe"></td>
  </tr>
</table>