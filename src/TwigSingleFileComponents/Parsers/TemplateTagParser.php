<?php

namespace TwigSingleFileComponents\Parsers;

final class TemplateTagParser extends \Twig\TokenParser\AbstractTokenParser
{
    /** @var array */
    private $store = [];

    public function parse(\Twig_Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();
        $token = $stream->getCurrent();
        
        $sort = null;
        $contents = null;
        $file = 'default';

        // Look for template name: {% template 'this-part' %}
        if (\Twig\Token::STRING_TYPE === $token->getType()) {
            $file = $token->getValue();
            $stream->next();
            $token = $stream->getCurrent();
        }

        // Look for template sort: {% template 'the-next-part' 10 %}
        if (\Twig\Token::NUMBER_TYPE === $token->getType()) {
            $sort = $token->getValue();
            $stream->next();
            $token = $stream->getCurrent();
        }

        if (\Twig\Token::BLOCK_END_TYPE === $token->getType()) {
            $stream->next();
        }

        $source = $stream->getSourceContext()->getCode();

        if (strpos($source, $this->getTag()) !== false) {
            $end_tag = 'end' . $this->getTag();
            $template_start = strpos($source, $this->getTag());
            $source = substr($source, $template_start);

            $template_start = strpos($source, '%}') + 2;
            $template_end = strpos($source, $end_tag) - 3;
            $contents = trim(substr(
                $source,
                $template_start,
                $template_end - $template_start
            ));
        }

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

        /** @var \Twig\Node\Node|\Twig\Node\Node[] $contents */
        $this->parser->subparse([$this, 'decideTemplateEnd'], true);
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new \Twig\Node\TextNode(
            $contents,
            [$stream->getCurrent()->getLine()]
        );
    }

    public function decideTemplateEnd(\Twig\Token $token): bool
    {
        return $token->test('end' . $this->getTag());
    }

    public function getTag()
    {
        return 'template';
    }

    public function getStore(): array
    {
        return $this->store;
    }
}
