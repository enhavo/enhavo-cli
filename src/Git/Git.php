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

    /** @var string|null */
    private $splitShBin;

    /**
     * Git constructor.
     * @param string $dir
     */
    public function __construct(string $dir)
    {
        $this->dir = $dir;
        $this->fs = new Filesystem;
    }

    private function execute(array $command, $exceptionOnFail = true)
    {
        $process = new Process($command, $this->dir);
        $process->run();

        if ($process->isSuccessful()) {
            return $process->getOutput();
        }

        $output = $process->getErrorOutput();
        if (empty($output)) {
            $output = $process->getOutput();
        }

        if ($exceptionOnFail) {
            throw new GitException($output);
        }
        return $output;
    }

    public function init($bare = false)
    {
        if (!$this->fs->exists($this->dir)) {
            $this->fs->mkdir($this->dir);
        }

        if ($bare) {
            $this->execute(["git", "init", "--bare"]);
        }

        $this->execute(["git", "init"]);
        return true;
    }

    public function getDir(): string
    {
        return $this->dir;
    }

    public function commit($message)
    {
        $this->execute(["git", "commit", "-m", $message]);
    }

    /**
     * @return array
     * @throws GitException
     */
    public function getCommits()
    {
        $output = $this->execute(["git", "--no-pager", "log", "--pretty=oneline", '--no-decorate']);
        $lines = explode("\n", $output);
        $commits = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $commits[] = new Commit(substr($line, 41), substr($line, 0, 40));
            }
        }
        return $commits;
    }

    /**
     * @return array
     * @throws GitException
     */
    public function getTags()
    {
        $output = $this->execute(["git", "tag", "-l"]);
        $lines = explode("\n", $output);
        $tags = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $tags[] = $line;
            }
        }
        return $tags;
    }

    public function tag($tag, $message = '')
    {
        $this->execute(["git", "tag", '-a', $tag, '-m', $message]);
        return;
    }

    public function checkout($branch, $createBranchIfNotExists = true)
    {
        if ($createBranchIfNotExists && !$this->hasBranch($branch)) {
            $this->execute(["git", "checkout", '-b', $branch]);
            return;
        }
        $this->execute(["git", "checkout", $branch]);
    }

    public function getBranches()
    {
        $output = $this->execute(["git", "branch", "--list", "--format=%(refname:short)"]);
        $lines = explode("\n", $output);
        $data = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $data[] = $line;
            }
        }
        return $data;
    }

    public function hasBranch($name)
    {
        return in_array($name, $this->getBranches());
    }

    public function add($path = null)
    {
        if ($path === null) {
            $this->execute(["git", "add", '--all']);
        } else {
            $this->execute(["git", "add", $path]);
        }
    }

    public function cloneFromUrl($url)
    {
        if (!$this->fs->exists($this->dir)) {
            $this->fs->mkdir($this->dir);
        }

        return $this->execute(["git", "clone", $url, '.']);
    }

    public function exists(): bool
    {
        return $this->fs->exists($this->dir.'/.git');
    }

    public function fetch($remote = null)
    {
        if ($remote !== null) {
            $this->execute(["git", "fetch", "--tags", $remote]);
            return;
        }

        $this->execute(["git", "fetch", "--tags", "--all"]);
    }

    public function pull($remote = 'origin', $branch = 'main')
    {
        return $this->execute(["git", "pull", $remote, $branch]);
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

    public function getCurrentBranch()
    {
        return trim($this->execute(["git", "branch", "--show-current"]));
    }

    public function pushSubtreeBranch(string $remote, string $prefix, string $branch = 'main', bool $force = false)
    {
        $currentBranch = $this->getCurrentBranch();

        $this->checkout($branch);

        $splitShBin = $this->getSplitshBin();
        $commit = $this->execute([$splitShBin, "--prefix", $prefix]);
        $commit = trim($commit);
        if ($commit) {
            $branchId = uniqid();
            $this->execute(["git", "checkout", $commit]);
            $this->execute(["git", "checkout", "-b", $branchId]);
            $this->execute(["git", "push", "--set-upstream", $force ? '--force' : '', $remote, sprintf("%s:%s", $branchId, $branch)]);
            $this->execute(["git", "checkout", $currentBranch]);
            $this->execute(["git", "branch", "-D", $branchId]);
        } else {
            $this->execute(["git", "checkout", $currentBranch]);
        }
    }

    public function pushSubtreeTag(string $remote, string $prefix, string $tag)
    {
        $currentBranch = $this->getCurrentBranch();
        $this->execute(["git", "checkout", $tag]);

        $branchId = uniqid();
        $this->execute(["git", "checkout", "-b", $branchId]);

        $splitShBin = $this->getSplitShBin();
        $commit = $this->execute([$splitShBin, "--prefix", $prefix]);
        $commit = trim($commit);
        if ($commit) {
            $this->execute(["git", "checkout", $commit]);
            $tagBranchId = uniqid();
            $this->execute(["git", "checkout", "-b", $tagBranchId]);

            $tempTag = sprintf('%s-%s', $tagBranchId, $tag);
            $pushTag = sprintf('%s:%s', $tempTag, $tag);

            $this->execute(["git", "tag", "-a", $tempTag, "-m", $tag]);
            $this->execute(["git", "push", $remote, $pushTag]);

            $this->execute(["git", "checkout", $currentBranch]);
            $this->execute(["git", "branch", "-D", $tagBranchId]);
            $this->execute(["git", "tag", "-d", $tempTag]);
        } else {
            // No commits found
            $this->execute(["git", "checkout", $currentBranch]);
        }
        $this->execute(["git", "branch", "-D", $branchId]);
    }

    public function addSubtree(string $remote, string $prefix, string $remoteBranch = 'main')
    {
        $this->execute(["git", "subtree", "add", "--prefix", $prefix, "--squash", sprintf('%s/%s', $remote, $remoteBranch)]);
    }

    public function push(string $remote, string $branch)
    {
        $this->execute(["git", "push", $remote, $branch]);
    }

    private function getSplitShBin()
    {
        if ($this->splitShBin) {
            return $this->splitShBin;
        }
        $splitShBin = $this->execute(["command", "-v", "splitsh-lite"]);
        if(!trim($splitShBin)) {
            throw new GitException('Can\t find command splitsh-lite');
        }
        return $splitShBin;
    }

    /**
     * @param string|null $splitShBin
     */
    public function setSplitShBin(?string $splitShBin): void
    {
        $this->splitShBin = $splitShBin;
    }

    public function getRemoteBranches()
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
