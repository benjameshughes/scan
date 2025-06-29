<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

beforeEach(function () {
    // Create temporary test files in storage/app/testing
    $testDir = storage_path('app/testing');
    if (! File::exists($testDir)) {
        File::makeDirectory($testDir, 0755, true);
    }

    $this->composerPath = $testDir.'/composer.json';
    $this->readmePath = $testDir.'/README.md';

    // Clean up any existing test files
    if (File::exists($this->composerPath)) {
        File::delete($this->composerPath);
    }
    if (File::exists($this->readmePath)) {
        File::delete($this->readmePath);
    }

    // Create test composer.json
    $this->testComposerContent = [
        'name' => 'test/project',
        'version' => '1.2.3',
        'description' => 'Test project',
    ];
    File::put($this->composerPath, json_encode($this->testComposerContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    // Create test README.md
    $this->testReadmeContent = "# Test Project\n\n**Version: 1.2.3**\n\nA test project.";
    File::put($this->readmePath, $this->testReadmeContent);

    // Mock Process facade
    Process::fake([
        'git rev-parse --git-dir' => Process::result('', 0),
        'git status --porcelain' => Process::result('', 0),
        'git branch --show-current' => Process::result('master', 0),
        'git tag -l *' => Process::result('1.0.0\n1.1.0\n1.2.0\n1.2.3', 0),
        'git branch *' => Process::result('', 0),
        'git add *' => Process::result('', 0),
        'git commit *' => Process::result('', 0),
        'git tag *' => Process::result('', 0),
        'git push *' => Process::result('', 0),
        'git reset *' => Process::result('', 0),
        'git diff --cached --name-only' => Process::result("composer.json\nREADME.md", 0),
    ]);
});

afterEach(function () {
    // Clean up test files
    if (File::exists($this->composerPath)) {
        File::delete($this->composerPath);
    }
    if (File::exists($this->readmePath)) {
        File::delete($this->readmePath);
    }

    // Clean up the test directory if empty
    $testDir = storage_path('app/testing');
    if (File::exists($testDir) && count(File::files($testDir)) === 0) {
        File::deleteDirectory($testDir);
    }
});

describe('BumpVersionImproved Command', function () {

    describe('Version Bumping', function () {

        it('can bump patch version', function () {
            $this->artisan('version:bump-improved', ['type' => 'patch', '--force' => true, '--dry' => true])
                ->expectsOutput('Current version: 1.2.3')
                ->expectsOutput('New version (dry-run): 1.2.4')
                ->assertExitCode(0);
        });

        it('can bump minor version', function () {
            $this->artisan('version:bump-improved', ['type' => 'minor', '--force' => true, '--dry' => true])
                ->expectsOutput('Current version: 1.2.3')
                ->expectsOutput('New version (dry-run): 1.3.0')
                ->assertExitCode(0);
        });

        it('can bump major version', function () {
            $this->artisan('version:bump-improved', ['type' => 'major', '--force' => true, '--dry' => true])
                ->expectsOutput('Current version: 1.2.3')
                ->expectsOutput('New version (dry-run): 2.0.0')
                ->assertExitCode(0);
        });

        it('updates composer.json when bumping version', function () {
            $this->artisan('version:bump-improved', ['type' => 'patch', '--force' => true])
                ->expectsConfirmation('Bump version from 1.2.3 to 1.2.4?', 'yes')
                ->expectsConfirmation('Push to remote?', 'no')
                ->assertExitCode(0);

            $updatedComposer = json_decode(File::get($this->composerPath), true);
            expect($updatedComposer['version'])->toBe('1.2.4');
        });

        it('updates README.md when bumping version', function () {
            $this->artisan('version:bump-improved', ['type' => 'patch', '--force' => true])
                ->expectsConfirmation('Bump version from 1.2.3 to 1.2.4?', 'yes')
                ->expectsConfirmation('Push to remote?', 'no')
                ->assertExitCode(0);

            $updatedReadme = File::get($this->readmePath);
            expect($updatedReadme)->toContain('**Version: 1.2.4**');
            expect($updatedReadme)->not->toContain('**Version: 1.2.3**');
        });

        it('creates backup branch when bumping version', function () {
            $this->artisan('version:bump-improved', ['type' => 'patch', '--force' => true])
                ->expectsConfirmation('Bump version from 1.2.3 to 1.2.4?', 'yes')
                ->expectsConfirmation('Push to remote?', 'no')
                ->expectsOutput('Backup branch created: backup/pre-version-1.2.4-'.date('Y-m-d'))
                ->assertExitCode(0);

            Process::assertRan('git branch backup/pre-version-1.2.4-'.date('Y-m-d').'*');
        });

        it('can cancel version bump', function () {
            $this->artisan('version:bump-improved', ['type' => 'patch', '--force' => true])
                ->expectsConfirmation('Bump version from 1.2.3 to 1.2.4?', 'no')
                ->expectsOutput('Version bump cancelled')
                ->assertExitCode(0);

            // Verify files weren't changed
            $composer = json_decode(File::get($this->composerPath), true);
            expect($composer['version'])->toBe('1.2.3');
        });

    });

    describe('Version Downgrade', function () {

        it('can downgrade version', function () {
            $this->artisan('version:bump-improved', ['--downgrade' => '1.1.0', '--force' => true, '--dry' => true])
                ->expectsOutput('Downgrading from 1.2.3 to 1.1.0')
                ->expectsOutput('Downgrade version (dry-run): 1.1.0')
                ->assertExitCode(0);
        });

        it('prevents downgrade to higher version', function () {
            $this->artisan('version:bump-improved', ['--downgrade' => '1.3.0', '--force' => true])
                ->expectsOutput('Target version must be lower than current version')
                ->assertExitCode(1);
        });

        it('validates downgrade version format', function () {
            $this->artisan('version:bump-improved', ['--downgrade' => 'invalid', '--force' => true])
                ->expectsOutput('Invalid version format. Use semantic versioning (e.g., 1.2.3)')
                ->assertExitCode(1);
        });

        it('updates files when downgrading', function () {
            $this->artisan('version:bump-improved', ['--downgrade' => '1.1.0', '--force' => true])
                ->expectsConfirmation('Downgrade version from 1.2.3 to 1.1.0?', 'yes')
                ->expectsConfirmation('Push to remote?', 'no')
                ->assertExitCode(0);

            $composer = json_decode(File::get($this->composerPath), true);
            expect($composer['version'])->toBe('1.1.0');

            $readme = File::get($this->readmePath);
            expect($readme)->toContain('**Version: 1.1.0**');
        });

    });

    describe('Rollback Functionality', function () {

        it('can rollback to existing tag', function () {
            Process::fake([
                'git rev-parse --git-dir' => Process::result('', 0),
                'git status --porcelain' => Process::result('', 0),
                'git branch --show-current' => Process::result('master', 0),
                'git tag -l 1.2.0' => Process::result('1.2.0', 0),
                'git branch *' => Process::result('', 0),
                'git reset --hard 1.2.0' => Process::result('', 0),
            ]);

            $this->artisan('version:bump-improved', ['--rollback' => '1.2.0', '--force' => true])
                ->expectsConfirmation('Rollback to version 1.2.0? This will reset the current branch.', 'yes')
                ->expectsConfirmation('Push rollback to remote? (This will require force push)', 'no')
                ->expectsOutput('Rolling back to version: 1.2.0')
                ->assertExitCode(0);

            Process::assertRan('git reset --hard 1.2.0');
        });

        it('prevents rollback to non-existent tag', function () {
            Process::fake([
                'git rev-parse --git-dir' => Process::result('', 0),
                'git status --porcelain' => Process::result('', 0),
                'git branch --show-current' => Process::result('master', 0),
                'git tag -l 1.9.9' => Process::result('', 0),
                'git tag -l --sort=-version:refname' => Process::result("1.2.3\n1.2.0\n1.1.0\n1.0.0", 0),
            ]);

            $this->artisan('version:bump-improved', ['--rollback' => '1.9.9', '--force' => true])
                ->expectsOutput('Tag 1.9.9 does not exist')
                ->expectsOutput('Available tags:')
                ->assertExitCode(1);
        });

        it('can cancel rollback', function () {
            Process::fake([
                'git rev-parse --git-dir' => Process::result('', 0),
                'git status --porcelain' => Process::result('', 0),
                'git branch --show-current' => Process::result('master', 0),
                'git tag -l 1.2.0' => Process::result('1.2.0', 0),
                'git branch *' => Process::result('', 0),
            ]);

            $this->artisan('version:bump-improved', ['--rollback' => '1.2.0', '--force' => true])
                ->expectsConfirmation('Rollback to version 1.2.0? This will reset the current branch.', 'no')
                ->expectsOutput('Rollback cancelled')
                ->assertExitCode(0);

            Process::assertDidntRun('git reset --hard 1.2.0');
        });

    });

    describe('Safety Checks', function () {

        it('fails when not in git repository', function () {
            Process::fake([
                'git rev-parse --git-dir' => Process::result('', 1),
            ]);

            $this->artisan('version:bump-improved', ['type' => 'patch'])
                ->expectsOutput('Not in a git repository')
                ->assertExitCode(1);
        });

        it('fails when working directory is dirty', function () {
            Process::fake([
                'git rev-parse --git-dir' => Process::result('', 0),
                'git status --porcelain' => Process::result('M some-file.txt', 0),
                'git status --short' => Process::result('M some-file.txt', 0),
            ]);

            $this->artisan('version:bump-improved', ['type' => 'patch'])
                ->expectsOutput('Working directory is not clean. Commit or stash changes first, or use --force')
                ->assertExitCode(1);
        });

        it('allows dirty working directory with force flag', function () {
            Process::fake([
                'git rev-parse --git-dir' => Process::result('', 0),
                'git status --porcelain' => Process::result('M some-file.txt', 0),
                'git branch --show-current' => Process::result('master', 0),
                'git branch *' => Process::result('', 0),
                'git add *' => Process::result('', 0),
                'git commit *' => Process::result('', 0),
                'git tag *' => Process::result('', 0),
            ]);

            $this->artisan('version:bump-improved', ['type' => 'patch', '--force' => true, '--dry' => true])
                ->expectsOutput('Current version: 1.2.3')
                ->expectsOutput('New version (dry-run): 1.2.4')
                ->assertExitCode(0);
        });

        it('fails when not on master branch', function () {
            Process::fake([
                'git rev-parse --git-dir' => Process::result('', 0),
                'git status --porcelain' => Process::result('', 0),
                'git branch --show-current' => Process::result('feature-branch', 0),
            ]);

            $this->artisan('version:bump-improved', ['type' => 'patch'])
                ->expectsOutput('Not on master/main branch (currently on: feature-branch). Use --force to override')
                ->assertExitCode(1);
        });

        it('allows non-master branch with force flag', function () {
            Process::fake([
                'git rev-parse --git-dir' => Process::result('', 0),
                'git status --porcelain' => Process::result('', 0),
                'git branch --show-current' => Process::result('feature-branch', 0),
                'git branch *' => Process::result('', 0),
                'git add *' => Process::result('', 0),
                'git commit *' => Process::result('', 0),
                'git tag *' => Process::result('', 0),
            ]);

            $this->artisan('version:bump-improved', ['type' => 'patch', '--force' => true, '--dry' => true])
                ->expectsOutput('Current version: 1.2.3')
                ->expectsOutput('New version (dry-run): 1.2.4')
                ->assertExitCode(0);
        });

    });

    describe('Error Handling', function () {

        it('handles missing composer.json', function () {
            File::delete($this->composerPath);

            $this->artisan('version:bump-improved', ['type' => 'patch', '--force' => true])
                ->expectsOutput('Error: composer.json not found')
                ->assertExitCode(1);
        });

        it('handles composer.json without version', function () {
            $composerWithoutVersion = ['name' => 'test/project'];
            File::put($this->composerPath, json_encode($composerWithoutVersion));

            $this->artisan('version:bump-improved', ['type' => 'patch', '--force' => true])
                ->expectsOutput('Error: No version found in composer.json')
                ->assertExitCode(1);
        });

        it('handles invalid version format in composer.json', function () {
            $composerInvalidVersion = ['name' => 'test/project', 'version' => 'invalid'];
            File::put($this->composerPath, json_encode($composerInvalidVersion));

            $this->artisan('version:bump-improved', ['type' => 'patch', '--force' => true])
                ->expectsOutput('Error: Current version format is invalid')
                ->assertExitCode(1);
        });

        it('handles missing README.md gracefully', function () {
            File::delete($this->readmePath);

            $this->artisan('version:bump-improved', ['type' => 'patch', '--force' => true])
                ->expectsConfirmation('Bump version from 1.2.3 to 1.2.4?', 'yes')
                ->expectsConfirmation('Push to remote?', 'no')
                ->expectsOutput('README.md not found, skipping version update')
                ->assertExitCode(0);
        });

        it('handles git command failures', function () {
            Process::fake([
                'git rev-parse --git-dir' => Process::result('', 0),
                'git status --porcelain' => Process::result('', 0),
                'git branch --show-current' => Process::result('master', 0),
                'git branch *' => Process::result('', 0),
                'git add *' => Process::result('Failed to add files', 1),
            ]);

            $this->artisan('version:bump-improved', ['type' => 'patch', '--force' => true])
                ->expectsConfirmation('Bump version from 1.2.3 to 1.2.4?', 'yes')
                ->expectsOutput('Error: Command failed: git add composer.json README.md')
                ->assertExitCode(1);
        });

    });

    describe('Fix Branch Creation', function () {

        it('can create fix branch', function () {
            Process::fake([
                'git rev-parse --git-dir' => Process::result('', 0),
                'git status --porcelain' => Process::result('', 0),
                'git branch --show-current' => Process::result('master', 0),
                'git checkout -b fix/1.2.3' => Process::result('', 0),
            ]);

            $this->artisan('version:bump-improved', ['--fix' => '1.2.3', '--force' => true])
                ->expectsOutput('Creating a fix branch for version: 1.2.3')
                ->expectsOutput('Branch created: fix/1.2.3')
                ->assertExitCode(0);

            Process::assertRan('git checkout -b fix/1.2.3');
        });

    });

    describe('Claude Integration', function () {

        it('can generate enhanced commit message with claude flag', function () {
            $this->artisan('version:bump-improved', ['type' => 'patch', '--force' => true, '--claude' => true])
                ->expectsConfirmation('Bump version from 1.2.3 to 1.2.4?', 'yes')
                ->expectsOutput('Generating enhanced commit message with Claude...')
                ->expectsQuestion('Describe the changes in this version (optional):', 'Bug fixes and improvements')
                ->expectsConfirmation('Push to remote?', 'no')
                ->assertExitCode(0);
        });

    });

    describe('Interactive Type Selection', function () {

        it('prompts for version type when not provided', function () {
            $this->artisan('version:bump-improved', ['--force' => true, '--dry' => true])
                ->expectsChoice('Select a type', 'patch', ['patch', 'minor', 'major'])
                ->expectsOutput('Current version: 1.2.3')
                ->expectsOutput('New version (dry-run): 1.2.4')
                ->assertExitCode(0);
        });

        it('prompts for version type when invalid type provided', function () {
            $this->artisan('version:bump-improved', ['type' => 'invalid', '--force' => true, '--dry' => true])
                ->expectsOutput('Invalid type.')
                ->expectsChoice('Select a type', 'minor', ['patch', 'minor', 'major'])
                ->expectsOutput('Current version: 1.2.3')
                ->expectsOutput('New version (dry-run): 1.3.0')
                ->assertExitCode(0);
        });

    });

});
