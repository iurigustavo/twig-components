<?php

namespace Havit\TwigComponents\Extension;

use Havit\TwigComponents\TokenParser\ComponentTokenParser;
use Havit\TwigComponents\TokenParser\SlotTokenParser;
use Twig_Extension;

class ComponentExtension extends Twig_Extension
{
    /** @var \Twig_Environment */
    private $enviroment;

    public function __construct($enviroment)
    {
        $this->enviroment  = $enviroment;
    }

    public function getTokenParsers(): array
    {
        return [
            new ComponentTokenParser($this->enviroment),
            new SlotTokenParser(),
        ];
    }
}
