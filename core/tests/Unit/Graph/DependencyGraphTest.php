<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Graph;

use Ox\Core\Graph\DependencyGraph;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class DependencyGraphTest extends OxUnitTestCase
{
    public function testGraphIsCyclic(): void
    {
        $nodes = [
            'a' => ['b'],
            'b' => ['c'],
            'c' => ['a'],
        ];

        $graph = new DependencyGraph();

        foreach ($nodes as $_node => $_dependencies) {
            foreach ($_dependencies as $_dependency) {
                $graph->addEdge($_node, $_dependency);
            }
        }

        $this->assertTrue($graph->isCyclic());
    }

    public function testGraphIsNotCyclic(): void
    {
        $nodes = [
            'a' => ['b'],
            'b' => ['c'],
            'c' => ['d'],
        ];

        $graph = new DependencyGraph();

        foreach ($nodes as $_node => $_dependencies) {
            foreach ($_dependencies as $_dependency) {
                $graph->addEdge($_node, $_dependency);
            }
        }

        $this->assertFalse($graph->isCyclic());
    }

    public function testGraphDependenciesAreDetermined(): void
    {
        $nodes = [
            'a' => ['b', 'c'],
            'b' => ['a'],
            'c' => ['d', 'a'],
            'd' => ['c'],
            'e' => ['e'],
            'f' => ['e'],
            'g' => ['h'],
        ];

        $graph = new DependencyGraph();

        foreach ($nodes as $_node => $_dependencies) {
            foreach ($_dependencies as $_dependency) {
                $graph->addEdge($_node, $_dependency);
            }
        }

        $this->assertEquals(
            [
                'a' => ['b', 'c', 'd'],
                'e' => ['e'],
            ],
            $graph->getDependencies()
        );
    }
}
