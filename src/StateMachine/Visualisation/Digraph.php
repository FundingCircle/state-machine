<?php

namespace StateMachine\Visualisation;

use Alom\Graphviz\Digraph as BaseDigraph;

class Digraph extends BaseDigraph
{
    public function render($indent = 0, $spaces = self::DEFAULT_INDENT)
    {
        $margin = str_repeat($spaces, $indent);
        $result = $margin.$this->getHeader($this->id).' { graph [splines=curved, size="10"]'."\n";
        foreach ($this->instructions as $instruction) {
            $result .= $instruction->render($indent + 1, $spaces);
        }
        $result .= $margin.'}'."\n";

        return $result;
    }
}
