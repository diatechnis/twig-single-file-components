# Twig Single File Components
Create Vue-like single components with Twig

## Quickstart
```php
$twig = new \TwigSingleFileComponents\Environment($twig_loader);

$rendered = $twig->render($template, $data);
```

This will create an extended version of the Twig environment. On instantiation, the environment will create script, style and template parsers.

On render, the parsers will look for the proper tags (`{% script %}`, `{% style %}`, `{% template %}`), and store the contents of those tags. 
