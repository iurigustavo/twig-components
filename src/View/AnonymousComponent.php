<?php

namespace Havit\TwigComponents\View;

class AnonymousComponent extends Component
{
    /** @var array|mixed */
    private $attributes;

    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function template(): string
    {
        if (strpos($this->name, '@') === 0) {
            return $this->name.'.twig';
        }

        $componentPath = rtrim($this->getTemplatePath(), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR
            .$this->name;

        $componentPath .= '.twig';

        return $componentPath;
    }
}
