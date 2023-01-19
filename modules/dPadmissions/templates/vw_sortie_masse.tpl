{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  seeSejourMasse = function() {
    var form = getForm('formDateMasse');
    var url = new Url('admissions', 'vw_sortie_masse');
    url.addFormData(form);
    url.addParam('see_sejour_masse', 1);
    url.requestUpdate('see_sejour_masse');
  };

  selectAllSejours = function(valeur){
    $('tbl_sejours_masse').select('input[type=checkbox]').each(function(e){
      if (e.name.indexOf('box-') >= 0 && !e.disabled) {
        $V(e, valeur);
      }
    });
  };

  showCheckSejours = function() {
    var checked   = 0;
    var count     = 0;
    $('tbl_sejours_masse').select('input,checkbox').each(function(e){
      if (e.name.indexOf('box-') >= 0) {
        count++;
        if ($V(e)) { checked ++; }
      }
    });

    var check_all = $('tbl_sejours_masse').down('input[name=check_all]');
    var valide_sejours = $('btt_valide_sejours');
    valide_sejours.disabled  = "disabled";
    check_all.checked = '';
    check_all.style.opacity = '1';

    if (checked) {
      valide_sejours.disabled = '';
      check_all.checked = '1';
      if (checked < count) {
        check_all.style.opacity = '0.5';
      }
    }
  };

  Main.add(function(){
    seeSejourMasse(getForm('formDateMasse'));
  });
</script>

<div>
  <form name="formDateMasse" method="get" action="?">
    <table class="form">
      <tr>
        <th class="title" colspan="2">{{tr}}mod-dPadmissions-tab-vw_sortie_masse{{/tr}}</th>
      </tr>
      <tr>
        {{me_form_field nb_cells=2 mb_class=CSejour mb_field=entree_reelle}}
          {{mb_field object=$filter field="_date_entree" form="formDateMasse" register=true canNull="false"}}
        {{/me_form_field}}
      </tr>
      <tr>
        <td colspan="2" class="button">
          <button type="button" onclick="seeSejourMasse();" class="search me-primary">{{tr}}Filter{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>

<div id="see_sejour_masse"></div>