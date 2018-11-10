<?php

namespace Tests;

use Twig\Loader\FilesystemLoader;
use TwigSingleFileComponents\Environment;

class CompileTest extends \PHPUnit\Framework\TestCase
{
    public function test_compile(): void
    {
        $results_dirs = [
            'styles'    => TEST_RESULTS_DIR . '/css/',
            'scripts'   => TEST_RESULTS_DIR . '/js/',
            'templates' => TEST_RESULTS_DIR . '/templates/',
        ];

        foreach ($results_dirs as $results_dir) {
            foreach (scandir($results_dir, null) as $file) {
                if (\in_array($file, ['.', '..'])) {
                    continue;
                }

                if (is_file($results_dir . $file)) {
                    unlink($results_dir . $file);
                }
            }
        }

        $twig = new Environment(
            new FilesystemLoader(TEST_TEMPLATES_DIR),
            ['debug' => true]
        );

        $this->compileStoresFromDirectory($twig, TEST_TEMPLATES_DIR, '');

        $stores = $twig->getStores();

        foreach ($stores as $store => $files) {
            switch ($store) {
                case 'styles':
                    $suffix = '.css';
                    break;
                case 'scripts':
                    $suffix = '.js';
                    break;
                default: // case 'templates'
                    $suffix = '.twig';
            }


            foreach ($files as $file_name => $sort) {
                ksort($sort);

                if (strpos($file_name, $suffix) === false) {
                    $file_name .= $suffix;
                }

                $contents = implode("\n", $sort);

                $this->assertTrue(
                    (bool) file_put_contents(
                        $results_dirs[$store] . $file_name,
                        $contents
                    )
                );
            }
        }
    }

    private function compileStoresFromDirectory(
        Environment $twig,
        $base_directory,
        $directory
    ): void {
        $base_directory = rtrim($base_directory, '/') . '/';

        if (! empty($directory)) {
            $directory = rtrim($directory, '/') . '/';
        }

        foreach (scandir($base_directory . $directory, null) as $item) {
            if ('.' === $item || '..' === $item) {
                continue;
            }

            if (is_dir($base_directory . $directory . $item)) {
                $this->compileStoresFromDirectory(
                    $twig,
                    $base_directory,
                    $directory . $item
                );
                continue;
            }

            $twig->compileStores($directory . $item);
        }
    }
}
