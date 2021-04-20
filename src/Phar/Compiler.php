<?php

namespace Enhavo\Component\Cli\Phar;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * Class Compiler
 *
 * Copied and modified from https://github.com/weaverryan/docs-builder/blob/main/src/Phar/Compiler.php
 *
 * @package Enhavo\Component\Cli\Phar
 */
class Compiler
{
    /** @var string */
    private $version;

    /** @var \DateTime */
    private $versionDate;

    public function compile($pharFile = 'enhavo.phar')
    {
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $process = new Process(['git', 'log', '--pretty="%H"', '-n1', 'HEAD'], __DIR__);
        if (0 != $process->run()) {
            throw new \RuntimeException('Can\'t run git log.');
        }
        $this->version = trim($process->getOutput());

        $process = new Process(['git', 'log', '-n1', '--pretty=%ci', 'HEAD'], __DIR__);
        if (0 != $process->run()) {
            throw new \RuntimeException('Can\'t run git log.');
        }
        $date = new \DateTime(trim($process->getOutput()));
        $date->setTimezone(new \DateTimeZone('UTC'));
        $this->versionDate = $date->format('Y-m-d H:i:s');

        $process = new Process(['git', 'describe', '--tags', 'HEAD']);
        if (0 == $process->run()) {
            $this->version = trim($process->getOutput());
        }

        $phar = new \Phar($pharFile, 0, $pharFile);
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->exclude(['Release', 'Phar'])
            ->in(__DIR__.'/..')
        ;
        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->exclude('Tests')
            ->in(__DIR__.'/../../vendor/')
        ;

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }


        $this->addConsoleBin($phar);

        // Stubs
        $phar->setStub($this->getStub());

        $phar->stopBuffering();

        // disabled for interoperability with systems without gzip ext
        // $phar->compressFiles(\Phar::GZ);

        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../LICENSE'), false);

        unset($phar);
    }

    private function addFile($phar, $file, $strip = true)
    {
        $path = strtr(str_replace(\dirname(\dirname(__DIR__)).\DIRECTORY_SEPARATOR, '', $file->getRealPath()), '\\', '/');

        $content = file_get_contents($file);
        if ($strip) {
            $content = $this->stripWhitespace($content);
        }

        $phar->addFromString($path, $content);
    }

    private function addConsoleBin($phar)
    {
        $content = file_get_contents(__DIR__.'/../../bin/console');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/console', $content);
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     *
     * @param string $source A PHP string
     *
     * @return string The PHP string with the whitespace removed
     */
    private function stripWhitespace($source)
    {
        if (!\function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (\is_string($token)) {
                $output .= $token;
            } elseif (\in_array($token[0], [T_COMMENT, T_DOC_COMMENT])) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }

    private function getStub()
    {
        $stub = <<<'EOF'
#!/usr/bin/env php
<?php
/*
 * This file is part of the enhavo cli.
 *
 * For the full copyright and license information, please view
 * the license that is located at the bottom of this file.
 */

Phar::mapPhar('enhavo.phar');

EOF;

        return $stub.<<<'EOF'
require 'phar://enhavo.phar/bin/console';

__HALT_COMPILER();
EOF;
    }
}
