<?php

namespace TwigSingleFileComponents\Parsers;

final class ScriptTagParser extends AbstractTagParser
{
    public function getTag()
    {
        return 'javascript';
    }
    public function getHtmlTagName(): string
    {
        return 'script';
    }
}
