<?php

namespace TwigSingleFileComponents;

use TwigSingleFileComponents\Parsers\ScriptTagParser;
use TwigSingleFileComponents\Parsers\StyleTagParser;
use TwigSingleFileComponents\Parsers\TemplateTagParser;

class Environment extends \Twig\Environment
{
    /** @var \TwigSingleFileComponents\Parsers\AbstractTagParser */
    private $style_parser;
    /** @var \TwigSingleFileComponents\Parsers\AbstractTagParser */
    private $script_parser;
    /** @var \Twig\TokenParser\AbstractTokenParser */
    private $template_parser;

    private const CONTENT = 'content';
    private const SCRIPTS = 'scripts';
    private const STYLES = 'styles';
    private const TEMPLATES = 'templates';

    public function __construct(
        \Twig\Loader\LoaderInterface $loader,
        $options = array(),
        string $rerenderTemplate = null
    ) {
        $template = $rerenderTemplate ?? $this->getDefaultRerenderTemplate();

        $array_loader = new \Twig\Loader\ArrayLoader([
            'base_template' => $template
        ]);

        if ($loader instanceof \Twig\Loader\ChainLoader) {
            $loader->addLoader($array_loader);
        } else {
            $loader = new \Twig\Loader\ChainLoader([$loader, $array_loader]);
        }

        parent::__construct($loader, $options);

        $this->style_parser = new StyleTagParser();
        $this->script_parser = new ScriptTagParser();
        $this->template_parser = new TemplateTagParser();

        $this->addTokenParser($this->style_parser);
        $this->addTokenParser($this->script_parser);
        $this->addTokenParser($this->template_parser);
    }

    public function render($name, array $context = array()): string
    {
        $content = parent::render($name, $context);

        return parent::render('base_template', [
            self::CONTENT => $content,
            self::STYLES => $this->style_parser->getStore(),
            self::SCRIPTS => $this->script_parser->getStore(),
        ]);
    }

    public function compileStores($name, array $context = array()): array
    {
        parent::render($name, $context);

        return $this->getStores();
    }

    public function getStores(): array
    {
        return [
            self::STYLES => $this->style_parser->getStore(),
            self::SCRIPTS => $this->script_parser->getStore(),
            self::TEMPLATES => $this->template_parser->getStore(),
        ];
    }

    private function getDefaultRerenderTemplate(): string
    {
        return '<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Testing</title>
{% if styles %}{% for file, sort in styles %}<style type="text/css" data-file="{{ file }}">{% for style_block in sort %}
{{ style_block|raw }}
{% endfor %}</style>{% endfor %}{% endif %}</head>
<body>
{{ content|raw }}
{% if scripts %}<script>
"use strict";{% for file, sort in scripts %}{% for script_block in sort %}
{{ script_block|raw }}
{% endfor %}{% endfor %}</script>{% endif %}</body>
</html>';
    }
}
