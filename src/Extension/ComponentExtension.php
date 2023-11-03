<?php

namespace Havit\TwigComponents\Extension;

use Havit\TwigComponents\TokenParser\ComponentTokenParser;
use Havit\TwigComponents\TokenParser\SlotTokenParser;

class ComponentExtension extends \Twig\Extension\AbstractExtension
{
    /** @var \Twig\Environment */
    private $enviroment;

    public function __construct($enviroment)
    {
        $this->enviroment = $enviroment;
    }

    public function getTokenParsers(): array
    {
        return [
            new ComponentTokenParser($this->enviroment),
            new SlotTokenParser(),
        ];
    }
}
