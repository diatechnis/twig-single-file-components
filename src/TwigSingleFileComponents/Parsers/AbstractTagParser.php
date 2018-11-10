<?php

namespace TwigSingleFileComponents\Parsers;

abstract class AbstractTagParser extends \Twig\TokenParser\AbstractTokenParser
{
    /** @var array */
    private $store = [];

    public function parse(\Twig_Token $token)
    {
        $open_tag = "<{$this->getHtmlTagName()}>";
        $close_tag = "</{$this->getHtmlTagName()}>";
        $parser = $this->parser;
        $stream = $parser->getStream();
        
        $sort = null;
        $contents = null;
        $file = 'default';

        $token = $stream->getCurrent();
        if (\Twig\Token::STRING_TYPE === $token->getType()) {
            $file = $token->getValue();
            $stream->next();
            $token = $stream->getCurrent();
        }

        if (\Twig\Token::NUMBER_TYPE === $token->getType()) {
            $sort = $token->getValue();
            $stream->next();
            $token = $stream->getCurrent();
        }

        if (\Twig\Token::BLOCK_END_TYPE === $token->getType()) {
            $stream->next();
            $token = $stream->next();
        }

        $contents = $token->getValue();

        $contents = str_replace([$open_tag, $close_tag], '', $contents);

        if (null !== trim($contents)) {
            if (empty($this->store[$file])) {
                $this->store[$file] = [];
            }

            if (null !== $sort) {
                while(! empty($this->store[$file][$sort])) {
                    $sort++;
                }
            } else {
                $sort = \count($this->store[$file]);
            }
            
            $this->store[$file][$sort] = trim($contents);
        }

        $stream->expect(\Twig\Token::BLOCK_START_TYPE);
        $stream->expect(\Twig\Token::NAME_TYPE);
        $stream->expect(\Twig\Token::BLOCK_END_TYPE);

        return new \Twig\Node\TextNode('', [$stream->getCurrent()->getLine()]);
    }

    abstract public function getTag();

    abstract public function getHtmlTagName(): string;

    public function getStore(): array
    {
        return $this->store;
    }
}
