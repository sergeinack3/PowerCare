<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement\Controllers\Legacy;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CView;
use Ox\Core\Kernel\Kernel;
use Ox\Mediboard\Developpement\CModuleBuilder;
use ReflectionClass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class DeveloppementController extends CLegacyController
{
    public function configure()
    {
        $this->checkPermAdmin();
        $this->renderSmarty('configure.tpl');
    }

    public function do_create_module()
    {
        $this->checkPermAdmin();

        $name_canonical     = CView::post("name_canonical", "str notNull pattern|[a-zA-Z0-9_]*");
        $name_short         = CView::post("name_short", "str notNull");
        $name_long          = CView::post("name_long", "str notNull");
        $license            = CView::post("license", "str notNull");
        $namespace_prefix   = CView::post("namespace_prefix", "str notNull");
        $namespace_category = CView::post("namespace_category", "str notNull");
        $namespace          = CView::post("namespace", "str notNull");
        $mod_category       = CView::post("mod_category", "str notNull");
        $mod_package        = CView::post("mod_package", "str notNull");
        $trigramme          = CView::post("trigramme", "str notNull");

        CView::checkin();

        $namespace = str_replace('\\\\', '\\', "{$namespace_prefix}\\{$namespace_category}\\" . ucfirst($namespace));

        $builder = new CModuleBuilder(
            $name_canonical,
            $namespace,
            $name_short,
            $name_long,
            $license,
            $trigramme,
            $mod_package,
            $mod_category,
            $namespace_category
        );
        $builder->build();

        CAppUI::js("getForm('create-module-form').reset()");
        CAppUI::setMsg("Module '$name_canonical' créé", UI_MSG_OK);
        echo CAppUI::getMsg();
        CApp::rip();
    }

    public function css_test(): void
    {
        $this->checkPermRead();

        $files          = [
            "style/mediboard_ext/standard.css",
        ];
        $button_classes = [];

        foreach ($files as $_file) {
            $css_files = file_get_contents($_file);
            $matches   = [];
            preg_match_all('/button\:not\(\[class\^=v\-\]\)\.([^\:]+)\:\:before/', $css_files, $matches);
            $button_classes = array_merge($button_classes, $matches[1]);
        }

        $button_classes = array_unique($button_classes);
        $button_classes = array_filter(
            $button_classes,
            function ($button_class) {
                return strpos($button_class, '.') === false;
            }
        );

        $values_to_remove = [
            "notext",
            "me-notext",
            "me-btn-small",
            "rtl",
            "me-noicon",
            "me-small",
            "me-color-care[style*=forestgreen]",
            "me-color-care[style*=firebrick]",
            "me-dark",
            "me-secondary",
            "delete",
        ];
        foreach ($values_to_remove as $value) {
            CMbArray::removeValue($value, $button_classes);
        }

        // Création du template
        $this->renderSmarty(
            'css_test.tpl',
            [
                "button_classes" => array_values($button_classes),
            ]
        );
    }

    /**
     * private function vw_kernel()
     * {
     * $this->checkPermRead();
     *
     * $this->renderSmarty(
     * 'vw_kernel',
     * [
     * "listeners" => $this->getDispatcherListeners(),
     * ]
     * );
     * }
     *
     * private function getDispatcherListeners(): array
     * {
     * $dispatcher = Kernel::getInstanceForLegacy()->getContainer()->get('event_dispatcher');
     *
     * $dispatch_listeners = $dispatcher->getListeners();
     * foreach ($dispatch_listeners as $_event_name => &$listeners) {
     * foreach ($listeners as &$_listener) {
     * $_listener[] = $dispatcher->getListenerPriority($_event_name, [$_listener[0], $_listener[1]]);
     * }
     * }
     *
     * return $this->sortListeners($dispatch_listeners);
     * }
     *
     * private function sortListeners(array $listeners): array
     * {
     * $results = [];
     *
     * $reflexion = new ReflectionClass(KernelEvents::class);
     * $consts    = $reflexion->getConstants();
     *
     * foreach ($consts as $event_name) {
     * if (is_string($event_name) && isset($listeners[$event_name])) {
     * $results[$event_name] = [];
     *
     * foreach ($listeners[$event_name] as [$listener, $action, $priority]) {
     * $results[$event_name][$priority] = [
     * 'priority' => $priority,
     * 'callable' => get_class($listener) . '::' . $action,
     * ];
     * }
     *
     * ksort($results[$event_name]);
     * $results[$event_name] = array_reverse($results[$event_name]);
     * }
     * }
     *
     * return $results;
     * }
     */
}
