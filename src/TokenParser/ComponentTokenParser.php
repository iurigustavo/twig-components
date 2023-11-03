<?php

namespace Havit\TwigComponents\TokenParser;

use Exception;
use Havit\TwigComponents\Node\ComponentNode;
use Havit\TwigComponents\View\AnonymousComponent;
use Havit\TwigComponents\View\Component;
use Twig\Environment;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\IncludeTokenParser;

final class ComponentTokenParser extends IncludeTokenParser
{
    /** @var Environment */
    private $environment;


    /**
     * ComponentTokenParser constructor.
     *
     * @param  Environment  $enviroment
     */
    public function __construct(Environment $enviroment)
    {
        $this->environment = $enviroment;
    }

    public function parse(Token $token): Node
    {
        [$variables, $name] = $this->parseArguments();

        $slot = $this->parser->subparse([$this, 'decideBlockEnd'], true);

        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new ComponentNode($this->getComponent($name), $slot, $variables, $token->getLine(), $this->environment);
    }

    protected function parseArguments()
    {
        $stream = $this->parser->getStream();

        $name      = null;
        $variables = null;

        if ($stream->nextIf(Token::PUNCTUATION_TYPE, ':')) {
            $name = $this->parseComponentName();
        }

        if ($stream->nextIf(/* Token::NAME_TYPE */ 5, 'with')) {
            $variables = $this->parser->getExpressionParser()->parseExpression();
        }

        $stream->expect(/* Token::BLOCK_END_TYPE */ 3);

        return [$variables, $name];
    }

    public function parseComponentName(): string
    {
        $stream = $this->parser->getStream();

        $path = [];

        if ($this->parser->getCurrentToken()->getType() != /** Token::NAME_TYPE */ 5) {
            throw new Exception('First token must be a name type');
        }

        $name = $this->getNameSection();

        if ($stream->nextIf(Token::PUNCTUATION_TYPE, ':')) {
            $path[] = '@' . $name;
            $name   = $this->getNameSection();
        }

        $path[] = $name;

        while ($stream->nextIf(9/** Token::PUNCTUATION_TYPE */, '.')) {
            $path[] = $this->getNameSection();
        }

        return implode('/', $path);
    }

    public function getNameSection(): string
    {
        $stream = $this->parser->getStream();

        $name = $stream->next()->getValue();

        while ($stream->nextIf(Token::OPERATOR_TYPE, '-')) {
            $token = $stream->nextIf(Token::NAME_TYPE);
            if (!is_null($token)) {
                $name .= '-' . $token->getValue();
            }
        }

        return $name;
    }

    public function getComponent(string $name): Component
    {
        $componentClass = AnonymousComponent::class;

        if ($namespace = $this->getComponentsNamespace()) {
            $guessComponentClass = $namespace . '\\' . implode('\\', array_map(function ($name) {
                    return ucwords($name);
                }, explode('\\', $name)));

            if (class_exists($guessComponentClass) && is_subclass_of($guessComponentClass, Component::class)) {
                $componentClass = $guessComponentClass;
            }
        } else {
            $name = str_replace('\\', '/', $name);
        }

        return $componentClass::make()->withName($name)->withEnvironment($this->environment);
    }

    private function getComponentsNamespace()
    {
        return $this->environment->getGlobals()['app']['twig.options']['components']['namespace'];
    }

    public function decideBlockEnd(Token $token): bool
    {
        return $token->test('endx');
    }

    public function getTag(): string
    {
        return 'x';
    }
}
