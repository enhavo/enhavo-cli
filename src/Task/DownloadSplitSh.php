<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

class DownloadSplitSh extends AbstractSubroutine
{
    public function __invoke($overwrite = false)
    {
        $home = getenv("HOME");
        $path = sprintf("%s/.enhavo/bin/splitsh-lite", realpath($home));
        $fs = new Filesystem();
        if ($fs->exists($path)) {
            return;
        }

        while(true) {
            $question = new Question(sprintf('install splitsh to "%s"? [y/n]', $path), 'y');
            $option = $this->questionHelper->ask($this->input, $this->output, $question);

            if (strtolower($option) === 'n') {
                return;
            } elseif (strtolower($option) === 'y') {
                self::download($path, $overwrite);
                return;
            }
        }
    }

    public static function download($targetPath, $overwrite = false)
    {
        // find url
        if (preg_match('/linux/i', PHP_OS)) {
            $url = 'https://github.com/splitsh/lite/releases/download/v1.0.1/lite_linux_amd64.tar.gz';
        } elseif (preg_match('/darwin/i', PHP_OS)) {
            $url = 'https://github.com/splitsh/lite/releases/download/v1.0.1/lite_darwin_amd64.tar.gz';
        } else {
            throw new \Exception(sprintf('Splitsh doesn\'t support this os "%s"', PHP_OS));
        }

        // delete old file
        $fs = new Filesystem();
        if ($fs->exists($targetPath) && $overwrite) {
            $fs->remove($targetPath);
        }

        // download
        $tmpDownloadName = tempnam(sys_get_temp_dir(), 'splitsh_download');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $st = curl_exec($ch);
        $fd = fopen($tmpDownloadName, 'w');
        fwrite($fd, $st);
        fclose($fd);

        curl_close($ch);

        // decompress
        $tmpDecompressName = sprintf('%s.tar', tempnam(sys_get_temp_dir(), 'splitsh_decompress'));
        $bufferSize = 4096;

        $file = gzopen($tmpDownloadName, 'rb');
        $outFile = fopen($tmpDecompressName, 'wb');

        while (!gzeof($file)) {
            fwrite($outFile, gzread($file, $bufferSize));
        }

        fclose($outFile);
        gzclose($file);

        // unpack
        $phar = new \PharData($tmpDecompressName);
        $phar->extractTo(dirname($targetPath), './splitsh-lite'); // extract all files

        // permission
        $fs->chmod($targetPath, 0755);
    }
}
