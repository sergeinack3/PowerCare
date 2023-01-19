{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">

  var Process = {
    running: false,
    step:    null,
    pass: {{$pass|json}},

    total: {
      medecins: 0,
      time:     0.0,
      updates:  0.0,
      errors:   0
    },

    doStep: function () {
      var form = getForm("importMedecinForm");

      this.step = $V(form.step);

      var url = new Url("patients", "import_medecin");
      url.addElement(form.step);
      url.addParam("mode", $V(form.mode));
      url.addParam("pass", this.pass);
      url.addParam("departement", $V(form.departement));
      url.addParam("mode_import", $V(form.mode_import));
      url.requestUpdate("process");
    },

    updateScrewed: function (medecins, time, updates, errors) {
      var tr = document.createElement("tr");
      var td;

      td = document.createElement("td");
      td.textContent = this.step;
      tr.appendChild(td);

      td = document.createElement("td");
      td.textContent = "XPAth Screwed, try again";
      tr.appendChild(td);

      $("results").appendChild(tr);
    },

    updateTotal: function (medecins, time, updates, errors) {
      this.total.medecins += medecins;
      this.total.time += time;
      this.total.updates += updates;
      this.total.errors += errors;

      var tr = document.createElement("tr");
      var td;
      td = document.createElement("td");
      td.textContent = this.step;
      tr.appendChild(td);
      td = document.createElement("td");
      td.textContent = medecins;
      tr.appendChild(td);
      td = document.createElement("td");
      td.textContent = time.toFixed(2);
      tr.appendChild(td);
      td = document.createElement("td");
      td.textContent = updates;
      tr.appendChild(td);
      td = document.createElement("td");
      td.textContent = errors;
      tr.appendChild(td);

      var node = {tr: {td: [this.step, medecins, time, updates, errors]}};

      $("results").appendChild(tr);

      $("total-medecins").innerHTML = this.total.medecins;
      $("total-time").innerHTML = this.total.time.toFixed(2);
      $("total-updates").innerHTML = this.total.updates;
      $("total-errors").innerHTML = this.total.errors;
    },

    endStep: function () {
      var form = getForm("import");
      var step = form.step;
      $V(step, parseInt($V(step)) + 1);
      if ($V(form.auto)) {
        Process.doStep.bind(Process).defer();
      }
    },

    nextDep: function () {
      var form = getForm("import");
      // Tester si on est sur le dernier département
      if (form.departement.selectedIndex == form.departement.length) {
        return;
      }
      $V(form.step, 0);
      form.departement.selectedIndex++;
      this.endStep();
    }
  };

  function importSF(form) {
    var url = new Url("patients", "import_sages_femmes");
    url.addParam("pass", Process.pass);
    url.addElement(form.departement);
    url.requestUpdate("resultSF", {
      onComplete: function () {
        if (form.auto.checked) {
          var select = form.departement;
          if ((select.length - 1) != select.selectedIndex) {
            select.selectedIndex += 1;
            importSF(form);
          }
        }
      },
      insertion:  function (element, content) {
        element.innerHTML += content;
      }
    });
  }

  function importKine(form) {
    var url = new Url("patients", "import_kines");
    url.addParam("pass", Process.pass);
    url.addElement(form.departement);
    url.requestUpdate("resultKine", {
      onComplete: function () {
        if (form.auto.checked) {
          var select = form.departement;
          if ((select.length - 1) != select.selectedIndex) {
            select.selectedIndex += 1;
            importKine(form);
          }
        }
      },
      insertion:  function (element, content) {
        element.innerHTML += content;
      }
    });
  }

  function installMouvMedecinPatient() {
    var url = new Url('patients', 'install_mouv_medecin_patient');
    url.requestUpdate('installMouvMedecinPatient');
  }

  /**
   * Disable all correspondents without import date
   */
  function disableCorrespondentsWithoutImportDate() {
    var date = $V("mass_disable_date"),
      date_format = DateFormat.format(new Date(date), "dd/MM/yyyy"),
      confirm_message = date ? $T('CMedecin_maintenance-confirm_with_date', date_format) :
        $T('CMedecin_maintenance-confirm_without_date');
    if (confirm(confirm_message)) {
      $("massDisable").addClassName('loading');
      new Url('patients', 'disableCorrespondentsWithoutImportDate')
        .addParam("date", date)
        .requestJSON(function (count) {
          $('results_mass_disable').innerHTML = $T("CMedecin count updated", count);
          $('massDisable').removeClassName('loading');
        });
    }
  }

  Main.add(function () {
    Control.Tabs.create("CMedecin-tab", true);
    Calendar.regField($("mass_disable_date"));
  })
</script>

<ul class="control_tabs small" id="CMedecin-tab">
  <li><a href="#CMedecin-install_trigger">Trigger medecin-patient</a></li>
  <li><a href="#CMedecin-tools">Outils</a></li>
</ul>

<div id="CMedecin-install_trigger" style="display: none;">
  <h2>Mouvement (trigger) medecin-patient</h2>

  <table class="tbl">
    <tr>
      <th class="narrow">{{tr}}Action{{/tr}}</th>
      <th>{{tr}}Status{{/tr}}</th>
    </tr>
    <tr>
      <td>
        <button type="button" class="tick" onclick="installMouvMedecinPatient()">Installer le trigger</button>
      </td>
      <td class="text" id="installMouvMedecinPatient"></td>
    </tr>
  </table>
</div>

<div id="CMedecin-tools" style="display: none;">

  <form name="cleanup-correspondant" method="post" onsubmit="return onSubmitFormAjax(this, {}, 'cleanup-correspondant-log')">
    <input type="hidden" name="m" value="patients" />
    <input type="hidden" name="dosql" value="do_cleanup_correspondant" />

    <table class="tbl">
      <tr>
        <th class="section">{{tr}}Action{{/tr}}</th>
        <th class="section">{{tr}}Status{{/tr}}</th>
      </tr>

      <tr>
        <td style="width: 20%">
          <table class="layout">
            <tr>
              <td>
                <h2 class="me-margin-left-0">{{tr}}CMedecin_maintenance-cleanup-correspondant-title{{/tr}}</h2>
                <div class="small-info">
                  {{tr}}CMedecin_maintenance-cleanup-correspondant-desc{{/tr}}
                </div>
                <label> {{tr}}CMedecin_maintenance-process duplicate{{/tr}} <input type="number" name="count_min" value="50" size="5" /> </label>
              </td>
            </tr>
            <tr>
              <td><label> {{tr}}CMedecin_maintenance-dry run{{/tr}} <input type="checkbox" name="dry_run" value="1" checked /> </label>
              </td>
            </tr>
            <tr>
              <td>
                <button type="submit" class="tick">{{tr}}Clean up{{/tr}}</button>
              </td>
            </tr>
          </table>
        </td>

        <td id="cleanup-correspondant-log"></td>
      </tr>
      <tr>
        <td>
          <h2 class="me-margin-left-0">{{tr}}CMedecin_maintenance-mass-disable-title{{/tr}}</h2>
          <div class="small-info">
            {{tr}}CMedecin_maintenance-Disable all correspondents without import date-desc{{/tr}}
          </div>
          <button type="button" id="massDisable" class="tick me-primary" onclick="disableCorrespondentsWithoutImportDate()">
            {{tr}}CMedecin_maintenance-Disable all correspondents without import date{{/tr}}
          </button>
          <input type="hidden" id="mass_disable_date" name="mass_disable_date" class="date" value="">
        </td>
        <td>
          <span id="results_mass_disable"></span>
        </td>
      </tr>
    </table>
  </form>
</div>
