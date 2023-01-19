{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  statusIntegrity = function() {
    new Url("files", "ajax_status_integrity")
      .addParam("cron_job_id", "{{$cron_job->_id}}")
      .requestUpdate("statut_integrity");
  };

  Main.add(function() {
    var form = getForm("frmIntegrity");
    form.limit.addSpinner();

    Calendar.regField(form.last_file_date);
  });
</script>

<table class="tbl">
  <tr>
    <th>
      Actions
    </th>
  </tr>
  <tr>
    <td>
      <form name="frmIntegrity" method="post" onsubmit="return onSubmitFormAjax(this, toolsIntegrity);">
        <input type="hidden" name="m" value="files" />
        <input type="hidden" name="dosql" value="do_cron_job_integrity" />
        {{mb_key   object=$cron_job}}

        {{mb_field object=$cron_job field=active hidden=true}}
        <input type="hidden" name="reset" />

        <button type="button" class="play"  onclick="$V(this.form.active, '1'); this.form.onsubmit();" {{if $cron_job->active}}disabled{{/if}}>Démarrer</button>

        <button type="button" class="pause" onclick="$V(this.form.active, '0'); this.form.onsubmit();" {{if !$cron_job->active}}disabled{{/if}}>Arrêter</button>

        <button type="button" class="cancel" onclick="$V(this.form.reset, '1'); this.form.onsubmit();">Réinitialiser</button>

        <label>
          Limite :
          <input type="text" name="limit" value="{{if $params}}{{$params.limit}}{{/if}}" size="4" onchange="this.form.onsubmit();" />
        </label>
        
        <label>
          Date limite des fichiers
          <div style="display: inline-block;">
            <input type="hidden" class="dateTime" name="last_file_date" value="{{if $params}}{{$params.last_file_date}}{{/if}}" onchange="this.form.onsubmit();"/>
          </div>
        </label>

        <button type="button" class="search" onclick="statusIntegrity();">Statut</button>
      </form>
    </td>
  </tr>
</table>

<div id="statut_integrity"></div>
