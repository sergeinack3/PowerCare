{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  #results li {
    font-size:1.3em;
    list-style: none;
    float:left;
    border:solid 1px black;
    padding:3px;
    margin:3px;
    border-radius: 3px;
    box-shadow: 2px 2px 5px grey;
  }

  #results li a:link, #results li a:visited {
    color:black;
  }
</style>

<script>
  Main.add(function() {
    refreshFN();
  });

  refreshFN = function() {
    var oform = getForm('filters');
    oform.onsubmit();
  };

  changePage = function(page) {
    var oform = getForm('filters');
    $V(oform.page, page);
    oform.onsubmit();
  };

  editFS = function(fs_id) {
    var url = new Url('system', 'ajax_edit_firstname');
    url.addParam('fs_id', fs_id);
    url.requestModal();
    url.modalObject.observe('afterClose', function() {
      refreshFN();
    });
  };
</script>

Filtre :
<form method="get" name="filters" onsubmit="return onSubmitFormAjax(this, null, 'results')">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="a" value="ajax_list_firstnames" />
  <input type="text" name="name" value="" placeholder="prénom"/>
  <input type="hidden" name="page" value="0"/>
  <label>
    Sexe :
    <select name="type" onchange="$V(this.form.page, 0);">
      <option value="">&mdash; Tous</option>
      <option value="m">Masculin</option>
      <option value="f">Féminin</option>
      <option value="u">Les 2</option>
    </select>
  </label>
  <button class="change notext" type="button" onclick="this.form.onsubmit();"></button>
</form>


<ul id="results">

</ul>