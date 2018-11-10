<?php

namespace TwigSingleFileComponents\Parsers;

final class StyleTagParser extends AbstractTagParser
{
    public function getTag()
    {
        return 'style';
    }

    public function getHtmlTagName(): string
    {
        return $this->getTag();
    }
}
