<?php

namespace Enhavo\Component\Cli\Tests\Git;

use Enhavo\Component\Cli\Git\Git;
use Enhavo\Component\Cli\Task\DownloadSplitSh;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class GitTest extends TestCase
{
    const MAIN_REPO = __DIR__.'/../fixtures/git/main-repo';
    const SUBTREE_REPO = __DIR__.'/../fixtures/git/subtree';
    const SUBTREE_LOCAL_REPO = __DIR__.'/../fixtures/git/subtree-local';
    const ORIGIN_REPO = __DIR__.'/../fixtures/git/origin';
    const NO_REPO = __DIR__.'/../fixtures/git/no-repo';

    const SPLIT_SH_BIN = __DIR__.'/../fixtures/git/splitsh-lite';

    private function createRepo($path, $init = true, $bare = false)
    {
        $fs = new Filesystem();
        if ($fs->exists($path)) {
            $fs->remove($path);
        }

        $git = new Git($path);
        if ($init) {
            $git->init($bare);
        }

        if (!file_exists(self::SPLIT_SH_BIN)) {
            DownloadSplitSh::download(self::SPLIT_SH_BIN);
        }
        $git->setSplitShBin(realpath(self::SPLIT_SH_BIN));

        return $git;
    }

    private function createCommit(Git $git, $message, $file, $content = '', $branch = 'main')
    {
        $git->checkout($branch);

        $fs = new Filesystem();
        $fs->dumpFile(sprintf('%s/%s', $git->getDir(), $file), $content);

        $git->add();
        $git->commit($message);
    }

    public function testInit()
    {
        $this->createRepo(self::MAIN_REPO);
        $fs = new Filesystem();
        $this->assertTrue($fs->exists(sprintf('%s/.git', self::MAIN_REPO)));
    }

    public function testExists()
    {
        $git = $this->createRepo(self::MAIN_REPO);
        $this->assertTrue($git->exists());

        $git = new Git(self::NO_REPO);
        $this->assertFalse($git->exists());
    }

    public function testCommit()
    {
        $git = $this->createRepo(self::MAIN_REPO);
        $this->createCommit($git, 'init', 'test');

        $commits = $git->getCommits();

        $this->assertCount(1, $commits);
        $this->assertEquals('init', $commits[0]->getMessage());
    }

    public function testTags()
    {
        $git = $this->createRepo(self::MAIN_REPO);
        $this->createCommit($git, 'init', 'test');
        $this->createCommit($git, 'second', 'second');

        $git->tag('v0.1.0');
        $tags = $git->getTags();

        $this->assertCount(1, $tags);
        $this->assertEquals('v0.1.0', $tags[0]);
    }

    public function testPull()
    {
        $originGit = $this->createRepo(self::ORIGIN_REPO);
        $this->createCommit($originGit, 'init', 'test');

        $git = $this->createRepo(self::MAIN_REPO, false);
        $git->cloneFromUrl(sprintf('file://%s', self::ORIGIN_REPO));
        $this->assertCount(1, $git->getCommits());

        $this->createCommit($originGit, 'other', 'other');
        $git->pull();
        $this->assertCount(2, $git->getCommits());
    }

    public function testPushSubtreeBranch()
    {
        $this->createRepo(self::SUBTREE_REPO, true, true);

        $localGit = $this->createRepo(self::SUBTREE_LOCAL_REPO, false);
        $localGit->cloneFromUrl(sprintf('file://%s', self::SUBTREE_REPO));
        $this->createCommit($localGit, 'init', 'subtree');
        $localGit->push('origin', 'main');

        $mainGit = $this->createRepo(self::MAIN_REPO);
        $this->createCommit($mainGit, 'init', 'main');

        $mainGit->addRemote('subtree', sprintf('file://%s', self::SUBTREE_REPO));
        $mainGit->fetch('subtree');
        $mainGit->addSubtree('subtree', 'src/subtree');

        $this->createCommit($mainGit, 'add to subtree', 'src/subtree/test');

        $mainGit->pushSubtreeBranch('subtree', 'src/subtree', 'main', true);

        // history has changed so create from scratch
        $localGit = $this->createRepo(self::SUBTREE_LOCAL_REPO, false);
        $localGit->cloneFromUrl(sprintf('file://%s', self::SUBTREE_REPO));
        $localGit->checkout('main');
        $localGit->pull('origin', 'main');

        $this->assertCount(2, $localGit->getCommits());
        $this->assertEquals('add to subtree', $localGit->getCommits()[0]->getMessage());
    }

    public function testPushSubtreeTag()
    {
        $this->createRepo(self::SUBTREE_REPO, true, true);

        $localGit = $this->createRepo(self::SUBTREE_LOCAL_REPO, false);
        $localGit->cloneFromUrl(sprintf('file://%s', self::SUBTREE_REPO));
        $this->createCommit($localGit, 'init', 'subtree');
        $localGit->push('origin', 'main');

        $mainGit = $this->createRepo(self::MAIN_REPO);
        $this->createCommit($mainGit, 'init', 'main');

        $mainGit->addRemote('subtree', sprintf('file://%s', self::SUBTREE_REPO));
        $mainGit->fetch('subtree');
        $mainGit->addSubtree('subtree', 'src/subtree');

        $this->createCommit($mainGit, 'add to subtree', 'src/subtree/test');
        $mainGit->tag('v0.1.0');

        $mainGit->pushSubtreeTag('subtree', 'src/subtree', 'v0.1.0');

        // history has changed so create from scratch
        $localGit = $this->createRepo(self::SUBTREE_LOCAL_REPO, false);
        $localGit->cloneFromUrl(sprintf('file://%s', self::SUBTREE_REPO));
        $localGit->checkout('main');
        $localGit->fetch('origin');
        $localGit->pull('origin', 'main');

        $this->assertCount(1, $localGit->getTags());
        $this->assertEquals('v0.1.0', $localGit->getTags()[0]);
    }
}
