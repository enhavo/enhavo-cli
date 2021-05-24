<?php

namespace Enhavo\Component\Cli\Subroutine;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\SubroutineInterface;
use Symfony\Component\Console\Command\Command;

class SelfUpdate extends AbstractSubroutine implements SubroutineInterface
{
    public function __invoke(): int
    {
        $url = "https://github.com/enhavo/enhavo-cli/releases/latest/download/enhavo.phar";
        $localFilename = realpath($_SERVER['argv'][0]) ?: $_SERVER['argv'][0];

        if (is_writable($localFilename)) {
            $this->output->writeln('Download file ...');
            file_put_contents($localFilename, file_get_contents($url));
            chmod($localFilename, 755);
            $this->output->writeln('Updated!');
        } else {
            $this->output->writeln('File is not writeable. Can\'t update!');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
