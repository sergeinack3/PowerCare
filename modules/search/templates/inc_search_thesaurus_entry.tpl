{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!--Vue de la liste des favoris dans l'onglet Gestion des favoris.-->

<script>
  Main.add(function () {
    Control.Tabs.create("tabs-owner", true);
  });
</script>
<button type="button" class="favoris" style="margin:5px;"
        onclick="Thesaurus.addeditThesaurusEntryManual(null, null, {{$user->_id}}, null,null, null, function(){})">
  {{tr}}mod-search-thesaurus-add{{/tr}}
</button>

<ul id="tabs-owner" class="control_tabs">
  <li>
    <a href="#favoris_user" class="empty">
      <img src="images/icons/user.png"><br>
      <small>{{tr}}mod-search-thesaurus-user{{/tr}}</small>
      <br />
      {{$user}}
      <small>({{$thesaurus_user|@count}})</small>
    </a>
  </li>
  <li>
    <a href="#favoris_function" class="empty">
      <img src="images/icons/user-function.png"><br>
      <small>{{tr}}mod-search-thesaurus-function{{/tr}}</small>
      <br />
      {{$user->_ref_function}}
      <small>({{$thesaurus_function|@count}})</small>
    </a>
  </li>
  <li>
    <a href="#favoris_group" class="empty">
      <img src="images/icons/group.png"><br>
      <small>{{tr}}mod-search-thesaurus-group{{/tr}}</small>
      <br />
      {{$user->_ref_function->_ref_group}}
      <small>({{$thesaurus_group|@count}})</small>
    </a>
  </li>
</ul>


<div id="favoris_user" style="display: none;" class="me-no-align">
  {{mb_include template=inc_search_thesaurus_entry_detail thesaurus=$thesaurus_user entry=$entry}}
</div>
<div id="favoris_function" style="display: none;" class="me-no-align">
  {{mb_include template=inc_search_thesaurus_entry_detail thesaurus=$thesaurus_function entry=$entry}}
</div>
<div id="favoris_group" style="display: none;" class="me-no-align">
  {{mb_include template=inc_search_thesaurus_entry_detail thesaurus=$thesaurus_group entry=$entry}}
</div>
