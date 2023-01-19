/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * JS function Codage CCAM
 */
CCodageCCAM = {
    changeCodageMode: function (element, codage_id) {
        var codageForm = getForm("formCodageRules_codage-" + codage_id);
        if ($V(element)) {
            $V(codageForm.association_mode, "user_choice");
        } else {
            $V(codageForm.association_mode, "auto");
        }
        codageForm.onsubmit();
    },

    onChangeDepassement: function (element, view, pref) {
        if (pref != '') {
            if ($V(element)) {
                $V(getForm('codageActeMotifDepassement-' + view).motif_depassement, pref);
            } else {
                $V(getForm('codageActeMotifDepassement-' + view).motif_depassement, '');
            }
        }

        CCodageCCAM.syncCodageField(element, view);
    },

    syncCodageField: function (element, view) {
        var acteForm = getForm('codageActe-' + view);
        var fieldName = element.name;
        var fieldValue = $V(element);
        $V(acteForm[fieldName], fieldValue);
        if ($V(acteForm.acte_id)) {
            acteForm.onsubmit();
        } else {
            CCodageCCAM.checkModificateurs(view, element);
        }
    },

    setFacturableAuto: function (input) {
        $V(input.form.elements['facturable_auto'], '0');
    },

    checkModificateurs: function (acte, input) {
        var exclusive_modifiers = ['F', 'P', 'S', 'U', 'O'];
        var checkboxes = $$('input[data-acte="' + acte + '"].modificateur');
        var nb_checked = 0;
        var exclusive_modifier = '';
        var exclusive_modifier_checked = false;
        var optam_modifiers = ['K', 'T'];
        var optam_modifier = '';
        var optam_modifier_checked = false;
        checkboxes.each(function (checkbox) {
            if (checkbox.checked) {
                nb_checked++;
                if (checkbox.get('double') == 2) {
                    nb_checked++;
                }
                if (exclusive_modifiers.indexOf(checkbox.get('code')) != -1) {
                    exclusive_modifier = checkbox.get('code');
                    exclusive_modifier_checked = true;
                } else if (optam_modifiers.indexOf(checkbox.get('code')) != -1) {
                    optam_modifier = checkbox.get('code');
                    optam_modifier_checked = true;
                }
            }
        });

        checkboxes.each(function (checkbox) {
            if (!checkbox.get('billed')) {
                if (exclusive_modifiers.indexOf(checkbox.get('code')) != -1 || optam_modifiers.indexOf(checkbox.get('code')) != -1) {
                    checkbox.disabled = (!checkbox.checked && nb_checked == 4) || checkbox.get('price') == '0' || checkbox.get('state') == 'forbidden'
                        || (exclusive_modifiers.indexOf(checkbox.get('code')) != -1 && !checkbox.checked && exclusive_modifier_checked)
                        || (optam_modifiers.indexOf(checkbox.get('code')) != -1 && !checkbox.checked && optam_modifier_checked);
                }
            }
        });

        if (input) {
            var container = input.up();
            if (input.checked && container.hasClassName('warning')) {
                container.removeClassName('warning');
                container.addClassName('error');
            } else if (!input.checked && container.hasClassName('error')) {
                container.removeClassName('error');
                container.addClassName('warning');
            }
        }
    },

    setRule: function (element, codage_id) {
        var codageForm = getForm("formCodageRules_codage-" + codage_id);
        $V(codageForm.association_mode, "user_choice", false);
        var inputs = document.getElementsByName("association_rule");
        for (var i = 0; i < inputs.length; i++) {
            inputs[i].disabled = false;
        }
        $V(codageForm.association_rule, $V(element), false);
        codageForm.onsubmit();
    },

    switchViewActivite: function (value, activite) {
        if (value) {
            $$('.activite-' + activite).each(function (oElement) {
                oElement.show()
            });
        } else {
            $$('.activite-' + activite).each(function (oElement) {
                oElement.hide()
            });
        }
    },

    editActe: function (acte_id, sejour_guid, oOptions) {
        var oDefaultOptions = {
            onClose: function () {
                PMSI.reloadActesCCAM(sejour_guid);
            }
        };
        Object.extend(oDefaultOptions, oOptions);
        var url = new Url("salleOp", "ajax_edit_acte_ccam");
        url.addParam("acte_id", acte_id);
        url.requestModal(null, null, oDefaultOptions);
        window.urlEditActe = url;
    },

    submitFunction: function (form) {
        new Url("dPccam", "searchCodeCcamHistory")
            .addFormData(form)
            .requestUpdate("result_keyword");

        return false;
    },

    remiseAZeroSelect: function (select) {
        var form = select.form;
        if (select.name == "chap1") {
            form.elements.result_chap2.value = "";
            form.elements.result_chap3.value = "";
            form.elements.result_chap4.value = "";
            form.elements.chap2.update();
            form.elements.chap3.update();
            form.elements.chap4.update();
        } else if (select.name == "chap2") {
            form.elements.result_chap3.value = "";
            form.elements.result_chap4.value = "";
            form.elements.chap3.update();
            form.elements.chap4.update();
        } else if (select.name == "chap3") {
            form.elements.result_chap4.value = "";
            form.elements.chap4.update();
        }
    },

    associeFonction: function (select) {
        var value = $V(select);
        var form = select.form;
        if (value == "Choisir le 1er niveau du chapitre") {
            form.elements.result_chap1.value = "";
        } else if (value == "Choisir le niveau suivant") {
            switch (select.name) {
                case 'chap2' :
                    form.elements.result_chap2.value = "";
                case 'chap3' :
                    form.elements.result_chap3.value = "";
                case 'chap4' :
                    form.elements.result_chap4.value = "";
            }
        } else {
            var form = select.form;
            $V(form.elements["result_" + select.name], value);
            var number_last_letter = parseInt(select.get("index")) + 1;
            var next_ID = select.name.substr(0, select.name.length - 1) + number_last_letter;
            var data = select.options[select.selectedIndex].get('code-pere');

            new Url("dPccam", "refreshChapters")
                .addParam("value_selected", value)
                .addParam("codePere", data)
                .requestUpdate(select.form.elements[next_ID]);
        }
    },

    cacheElements: function () {
        $("keywords").setAttribute("disabled", true);
        $("chap1").setAttribute("disabled", true);
        $("chap2").setAttribute("disabled", true);
        $("chap3").setAttribute("disabled", true);
        $("chap4").setAttribute("disabled", true);
    },

    refreshCodeFrom: function (code_ccam, form) {
        new Url("dPccam", "showCcamCode")
            .addParam("code_ccam", code_ccam)
            .addParam("date_version", $V(form.elements['date_version']))
            .addParam('situation_patient', $V(form.elements['situation_patient']))
            .addParam('speciality', $V(form.elements['speciality']))
            .addParam('contract', $V(form.elements['contract']))
            .addParam('sector', $V(form.elements['sector']))
            .requestUpdate("info_code");
        return false;
    },

    show_code: function (code_ccam, date_demandee) {
        new Url("dPccam", "showCcamCode")
            .addParam("code_ccam", code_ccam)
            .addParam("date_demandee", date_demandee)
            .requestModal(900, 800, {});
    },

    refreshModal: function (code_ccam) {
        Control.Modal.close();
        new Url("dPccam", "showCcamCode")
            .addParam("code_ccam", code_ccam)
            .requestModal(900, 800, {});
    },

    chooseDateDuplication: function (codage_guid, codable_guid) {
      new Url("ccam", "showChooseDate")
        .addParam('codage_id', codage_guid)
        .addParam("codable_guid", codable_guid)
        .requestModal(900, 600);
    },

    /**
     * Add new date
     * @param element_new_date
     */
    addDate:function (element_new_date) {
      if(element_new_date.disabled){
        return;
      }
      let new_date = element_new_date.value;
      let week_number = Date.fromDATE(new_date).getWeekNumber();
      let weeks_numbers = this.createTableau();

      //Si la semaine n'existe pas on  l'insert a la bonne place
      if (!(week_number.toString() in weeks_numbers)) {
        weeks_numbers[week_number]=[];
        weeks_numbers = Object.keys(weeks_numbers)
          .sort()
          .reduce((accumulator, key) => {
            accumulator[key] = weeks_numbers[key];
            return accumulator;
          }, {});
      }
      if(!weeks_numbers[week_number].includes(Date.fromDATE(new_date).toLocaleDate())){
        weeks_numbers[week_number].push(Date.fromDATE(new_date).toLocaleDate());
      }

      this.createTableauHTML(weeks_numbers);

    },
  /**
   * Delete date
   * @param date
   * @param semaine
   */
    deleteDate: function(date, semaine) {
      let weeks_numbers = this.createTableau();
      weeks_numbers[semaine].splice(weeks_numbers[semaine].indexOf(date), 1);
      if(!weeks_numbers[semaine].length){
        delete weeks_numbers[semaine];
      }
      this.createTableauHTML(weeks_numbers)
    },
  /**
   * Create tableau html with all the date selected
   * @param weeks_numbers
   */
    createTableauHTML: function (weeks_numbers) {
      let tr = $("dates_choosen");
      let row;
      let sorted_table = Object.keys(weeks_numbers);
      //Empty tab before rewrite it
      if($$('.date_add').length){
        $$('.date_add').forEach(line_to_remove => {
          line_to_remove.remove();
        });
        $("weeks_number").remove();
      }

      for (let semaine in weeks_numbers) {
        if(!$('weeks_number')){
          row = tr.insertRow();
          row.setAttribute("id", "weeks_number");
        }
        let cell = row.insertCell();
        cell.innerHTML = $T("Week")+" "+ semaine;
        cell.setAttribute("id", semaine);
        cell.setAttribute("class", "category me-font-weight-bold me-text-align-center ");
        let i = 0;
        for (var j = 0; j < weeks_numbers[semaine].length; j++) {
          // Create tr will all td neccessary
          if (!$('data_' + j)) {
            let row_for_date = tr.insertRow();
            row_for_date.setAttribute("id", "data_" + j);
            row_for_date.setAttribute("class", "date_add");
            for (let y = 0; y < sorted_table.length; y++) {
              let cell = row_for_date.insertCell();
              cell.setAttribute("id", "data_" + j + "_" + sorted_table[y]);
              cell.setAttribute("class", "data_choose " + sorted_table[y] + " me-text-align-center");
            }
          }
          let cell = $("data_" + j + "_" + semaine);
          let function_on_click = "CCodageCCAM.deleteDate('"+weeks_numbers[semaine][j].toString()+"',"+semaine+")"
          cell.innerHTML = weeks_numbers[semaine][j]+"<button type='button' class='trash notext me-tertiary' onclick="+function_on_click+"></button>";
        }
        i++;
      }
      if ($$('.date_add').length) {
        $("class_empty").hide();
      } else {
        $("class_empty").show();
      }
    },
  /**
   * Create javascript tab for the creation of the html tab
   * @returns object
   */
    createTableau: function () {
    let weeks_numbers = {};
    let weeks = $("weeks_number");
    /**
     * Gestion de la première ligne du tableau avec les semaines
     */
    //Recupere tous les données déjà ajouté
    if (weeks) {
      Array.from(weeks.children).forEach(function (element) {
        if (!(element.getAttribute("id") in weeks_numbers)) {
          weeks_numbers[element.getAttribute("id")] = [];
        }
        let element_date = document.getElementsByClassName(element.getAttribute("id"));
        Array.from(element_date).forEach(function (date_element) {
          if (date_element.textContent !== '' && !weeks_numbers[element.getAttribute('id').includes(date_element.textContent)]) {
            weeks_numbers[element.getAttribute('id')].push(date_element.textContent);
          }
        })
      });
    }
    return weeks_numbers;
  },
  /**
   * Disables items depending on whether the user wants to choose the dates for duplication of acts
   * @param element
   * @param className
   */
    updateDuplicate: function (element, className) {
      let form;
      if (className === "CCodageCCAM") {
        form = getForm('duplicateCodage');
      } else {
        form = getForm('duplicateNGAP');
      }
      $V(form.type_of_date, element);

      if (element === "one_date") {

        $('choose_date_duplicate').addClassName('opacity-30');
        $('section_duplicate_jusqu_au').removeClassName("opacity-30");
        document.getElementsByName("date_jusqu_au_da")[0].removeAttribute("disabled");
        $('calendar_duplicate_date').setAttribute("disabled", "disabled");
        this.createTableauHTML({})

      } else {

        $('section_duplicate_jusqu_au').addClassName('opacity-30');
        $('choose_date_duplicate').removeClassName("opacity-30");
        document.getElementsByName("date_jusqu_au_da")[0].setAttribute("disabled", "disabled");
        $('calendar_duplicate_date').removeAttribute("disabled");
      }
    },
  /**
   * Get all date selected and update the form to submit
   * @param className
   */
    submitDuplicationCodage: function (className) {
      let form;
      if (className === "CCodageCCAM") {
        form = getForm('duplicateCodage');
      } else {
        form = getForm('duplicateNGAP');
      }
      if (form.type_of_date.value !== "one_date") {
        let all_date = this.createTableau();
        all_date = Object.values(all_date).toString().split(",");
        for (let i = 0; i < all_date.length; i++) {
          all_date[i] = Date.fromLocaleDate(all_date[i]).toDATE()
        }
        $V(form.multiple_date, all_date);
      }
      form.onsubmit();
    }
};
