<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
namespace Ox\Core\Twig;

use SqlFormatter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * This class contains the needed functions in order to do the query highlighting
 */
class OxExtension extends AbstractExtension
{
    /**
     * Define our functions
     *
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('ox_format_sql', [$this, 'formatQuery']),
        ];
    }


    public function formatQuery($sql, $highlightOnly = false)
    {

        return '<div class="SqlFormater">' . SqlFormatter::format(utf8_encode($sql)) . '</div>';
    }

    /**
     * Get the name of the extension
     *
     * @return string
     */
    public function getName()
    {
        return 'twig_extension';
    }
}
