<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Graph;

/**
 * A simple dependency graph.
 * Used for preventing circular dependencies.
 */
class DependencyGraph
{
    /** @var array */
    private $adjacency = [];

    /** @var array */
    private $cyclic_dependencies = [];

    /**
     * Add an edge to the graph.
     *
     * @param string $node
     * @param string $other_node
     */
    public function addEdge(string $node, string $other_node): void
    {
        if (!isset($this->adjacency[$node])) {
            $this->adjacency[$node] = [];
        }

        $this->adjacency[$node][] = $other_node;

        if (!isset($this->adjacency[$other_node])) {
            $this->adjacency[$other_node] = [];
        }
    }

    /**
     * @param string      $node            The node to check.
     * @param array<bool> $visited         The list of visited nodes.
     * @param array<bool> $recursion_stack The list of nodes being actually recursively checked (most of the work
     *                                     happens here).
     *
     * @return bool
     */
    private function isCyclicUntil(string $node, array &$visited, array &$recursion_stack): bool
    {
        if ($recursion_stack[$node]) {
            return true;
        }

        if ($visited[$node]) {
            return false;
        }

        $visited[$node]         = true;
        $recursion_stack[$node] = true;

        $children = $this->adjacency[$node];

        $is_cyclic = false;
        foreach ($children as $_child) {
            if ($this->isCyclicUntil($_child, $visited, $recursion_stack)) {
                // Not returning now allow us to compute all dependencies
                $is_cyclic = true;
                //return true;
            }
        }

        if ($is_cyclic) {
            return true;
        }

        $recursion_stack[$node] = false;

        return false;
    }

    /**
     * Tell whether the graph is cyclic.
     *
     * @return bool
     */
    public function isCyclic(): bool
    {
        $this->cyclic_dependencies = [];

        $nodes   = array_keys($this->adjacency);
        $visited = array_fill_keys($nodes, false);

        foreach ($nodes as $_node) {
            $_recursion_stack = array_fill_keys($nodes, false);

            if ($this->isCyclicUntil($_node, $visited, $_recursion_stack)) {
                $this->cyclic_dependencies[$_node] = $_recursion_stack;
            }
        }

        return (count($this->cyclic_dependencies) > 0);
    }

    /**
     * Perform a cyclic check and return the cyclic dependencies by node.
     *
     * @return array
     */
    public function getDependencies(): array
    {
        $this->isCyclic();

        $dependencies = [];
        foreach ($this->cyclic_dependencies as $_node => $_dependencies) {
            $dependencies[$_node] = [];

            foreach ($_dependencies as $_dependency => $_is_cyclic) {
                if (!$_is_cyclic) {
                    continue;
                }

                if ($_dependency === $_node) {
                    continue;
                }

                $dependencies[$_node][] = $_dependency;
            }
        }

        foreach ($dependencies as $_node => &$_dependencies) {
            // Node depends on itself
            if (count($_dependencies) === 0) {
                $_dependencies[] = $_node;
            }
        }

        return $dependencies;
    }
}
