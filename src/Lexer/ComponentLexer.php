<?php

namespace Havit\TwigComponents\Lexer;

use Twig_Source;

class ComponentLexer extends \Twig_Lexer
{

    /**
     * @throws \Twig_Error_Syntax
     */
    public function tokenize(Twig_Source $source): \Twig_TokenStream
    {
        $preparsed = $this->preparse($source->getCode());

        return parent::tokenize(
            new Twig_Source(
                $preparsed,
                $source->getName(),
                $source->getPath()
            )
        );
    }

    protected function preparse(string $value): string
    {
        return (new ComponentTagCompiler($value))->compile();
    }
}
