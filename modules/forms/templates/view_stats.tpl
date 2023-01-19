{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create("forms-stats", true, {
      afterChange: function(container) {
        switch (container.id) {
          case "tab-exclass":
            var paramForm = getForm('exclass-filter');

            if (!paramForm.get('loaded')) {
              paramForm.param_min_count.addSpinner({min: 0});
              paramForm.set('loaded');
            }

            paramForm.onsubmit();
            break;

          case "tab-exobject":
            var form = getForm('exobject-filter');

            if (!form.get('loaded')) {
              form.elements.ex_object_limit_threshold.addSpinner({min: 0, max: 100});
              Calendar.regField(form.elements.ex_object_date_min);
              Calendar.regField(form.elements.ex_object_date_max);
              form.set('loaded');
            }

            form.onsubmit();
            break;

          case "tab-filling-exobject":
            var form = getForm('filling-exobject-filter');

            if (!form.get('loaded')) {
              form.elements.ex_object_filling_min_threshold.addSpinner({min: 0, max: 100});
              form.elements.ex_object_filling_max_threshold.addSpinner({min: 0, max: 100});
              Calendar.regField(form.elements.ex_object_filling_date_min);
              Calendar.regField(form.elements.ex_object_filling_date_max);
              form.set('loaded');
            }

            form.onsubmit();
            break;
        }
      }
    });
  });
</script>

<ul class="control_tabs" id="forms-stats">
  <li><a href="#tab-exclass">Paramétrage</a></li>
  <li><a href="#tab-exobject">Saisie</a></li>
  <li><a href="#tab-filling-exobject">Remplissage</a></li>
</ul>

<div id="tab-exclass" style="display: none;">
  {{mb_include module=forms template=vw_ex_class_stats}}
</div>

<div id="tab-exobject" style="display: none;">
  {{mb_include module=forms template=vw_ex_object_stats}}
</div>

<div id="tab-filling-exobject" style="display: none;">
  {{mb_include module=forms template=vw_ex_object_filling_stats}}
</div>