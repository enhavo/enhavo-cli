<?php

namespace Enhavo\Component\Cli\Git;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class Git
{
    /** @var string */
    private $dir;

    /** @var string */
    private $url;

    /** @var Filesystem */
    private $fs;

    /**
     * Git constructor.
     * @param string $dir
     * @param string $url
     */
    public function __construct(string $dir, string $url)
    {
        $this->dir = $dir;
        $this->url = $url;
        $this->fs = new Filesystem;
    }

    private function execute(array $command)
    {
        $process = new Process($command);
        $process->start();
        return $process->getOutput();
    }

    public function cloneRepository()
    {
        return $this->execute(["git", "clone", $this->url, $this->dir]);
    }

    public function exists(): bool
    {
        return $this->fs->exists($this->dir.'/.git');
    }

    public function fetch()
    {
        return $this->execute(["git", "fetch", "--tags"]);
    }

    public function pull()
    {
        return $this->execute(["git", "pull", "origin", "master"]);
    }

    public function addRemote(string $name, string $url)
    {
        return $this->execute(["git", "remote", "add", $name, $url]);
    }

    public function hasRemote(string $name): bool
    {
        $result = $this->execute(["git", "remote", "show"]);
        $lines = explode("\n", $result);

        foreach ($lines as $line) {
            if($line === $name) {
                return true;
            }
        }
        return false;
    }

    public function pushBranch(string $name, string $prefix, string $branch = 'master', bool $force = false)
    {
        $splitShBin = $this->getSplitshBin();
        $this->execute(["git", "checkout", $branch]);
        $this->execute(["git", "pull", "origin", $branch]);

        $commit = $this->execute([$splitShBin, "--prefix", $prefix]);
        $commit = trim($commit);
        if ($commit) {
            $branchId = uniqid();
            $this->execute(["git", "checkout", $commit]);
            $this->execute(["git", "checkout", "-b", $branchId]);
            $this->execute(["git", "push", "--set-upstream", $force ? '--force' : '', $name, sprintf("%s:%s", $branchId, $branch)]);
            $this->execute(["git", "checkout", $branch]);
            $this->execute(["git", "branch", "-D", $branchId]);
        }
    }

    public function pushTag(string $name, string $prefix, string $tag)
    {
        $splitShBin = $this->getSplitShBin();
        $this->execute(["git", "checkout", $tag]);
        $branchId = uniqid();
        $this->execute(["git", "checkout", "-b", $branchId]);

        $commit = $this->execute([$splitShBin, "--prefix", $prefix]);
        $commit = trim($commit);
        if ($commit) {
            $this->execute(["git", "checkout", $commit]);
            $tagBranchId = uniqid();
            $this->execute(["git", "checkout", "-b", $tagBranchId]);

            $tempTag = sprintf('%s-%s', $tagBranchId, $tag);
            $pushTag = sprintf('%s:%s', $tempTag, $tag);

            $this->execute(["git", "tag", "-a", $tempTag, "-m", $tag]);
            $this->execute(["git", "push", $name, $pushTag]);

            $this->execute(["git", "checkout", "master"]);
            $this->execute(["git", "branch", "-D", $tagBranchId]);
            $this->execute(["git", "tag", "-d", $tempTag]);
        } else {
            // No commits found
            $this->execute(["git", "checkout", "master"]);
        }
        $this->execute(["git", "branch", "-D", $branchId]);
    }

    private function getSplitShBin()
    {
        $splitShBin = $this->execute(["command", "-v", "splitsh-lite"]);
        if(!trim($splitShBin)) {
            $splitShBin = realpath(__DIR__.'/../../bin/splitsh-lite');
        }
        return $splitShBin;
    }

    public function getBranches()
    {
        $data = [];
        $result = $this->execute(["git", "branch", "-r"]);
        $branches = explode("\n", $result);
        foreach ($branches as $branch) {
            $branch = trim($branch);
            preg_match('/origin\/([0-9a-zA-Z-_.]+)/', $branch, $matches);
            if (count($matches)) {
                $branchName = $matches[1];
                if ($branchName != 'HEAD') {
                    $data[] = $matches[1];
                }
            }
        }
        return $data;
    }

    public function getTags()
    {
        $data = [];
        $result = $this->execute(["git", "tag", "-l"]);
        $tags = explode("\n", $result);
        foreach($tags as $tag) {
            $tag = trim($tag);
            if($tag) {
                $data[] = $tag;
            }
        }
        return $data;
    }

    public function hasTag(string $remote, string $tag)
    {
        $result = $this->execute(["git", "ls-remote", "--tags", $remote]);
        $tags = explode("\n", $result);
        foreach ($tags as $foundTag) {
            $sequence = sprintf("refs/tags/%s", $tag);
            $matches = strpos($foundTag, $sequence);
            if ($matches !== false) {
                return true;
            }
        }
        return false;
    }

    public function release()
    {
        // implement solution https://stackoverflow.com/questions/17911466/how-to-push-tags-with-git-subtree
    }
}