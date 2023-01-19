{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=planification}}

<script>
  Main.add(function () {
    GroupePatient.tabs_planning_groupe = Control.Tabs.create('tab-planning_groupe', true, {
      afterChange: function (container) {
        var cat_id = container.id.split('-');
        GroupePatient.categorie_groupe_patient_id = cat_id[1];
        GroupePatient.refreshPlanning(cat_id[1]);
      }
    });

    Planification.showWeek('{{$date}}', 'groupe_patient');
  });
</script>

<div id="week-changer"></div>

{{if $categories_groupe_patient && $categories_groupe_patient|@count > 0}}
  <table class="main">
    <tr>
      <td style="width: 10%;">
        <ul id="tab-planning_groupe" class="control_tabs_vertical me-align-auto">
            {{foreach from=$categories_groupe_patient item=_categorie_groupe}}
              <li>
                <a href="#planningGroupe-{{$_categorie_groupe->_id}}">
                    {{mb_include module=system template=inc_vw_mbobject object=$_categorie_groupe}}
                </a>
              </li>
            {{/foreach}}
        </ul>
      </td>
      <td>
        {{foreach from=$categories_groupe_patient item=_categorie_groupe}}
          <div id="planningGroupe-{{$_categorie_groupe->_id}}" style="display:none;" class="me-align-auto me-no-border me-padding-0"></div>
        {{/foreach}}
      </td>
    </tr>
  </table>
{{else}}
  <div class="small-warning">
    {{tr}}CPlageGroupePatient-msg-No group range has been created in the schedule{{/tr}}
  </div>
{{/if}}



