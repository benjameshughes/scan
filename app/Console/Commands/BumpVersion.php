<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\select;

class BumpVersion extends Command
{
    /**
     * The available version types.
     */
    protected array $types = ['patch', 'minor', 'major'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'version:bump {type?} {--rollback=} {--fix=} {--dry}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage application versioning (bump, rollback, fix)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Handle rollback
        if ($this->option('rollback')) {
            return $this->handleRollback($this->option('rollback'));
        }

        // Handle fix workflow
        if ($this->option('fix')) {
            return $this->handleFix($this->option('fix'));
        }

        // Handle version bump
        return $this->handleBump();
    }

    /**
     * Handle version bumping.
     */
    private function handleBump(): int
    {
        $type = $this->getType();
        $currentVersion = $this->getCurrentVersion();

        $this->info("Current version: $currentVersion");

        $newVersion = $this->getNewVersion($currentVersion, $type);

        if ($this->option('dry')) {
            $this->info('New version (dry-run): '.$newVersion);

            return 0;
        }

        $this->updateReadMeVersion($newVersion, $currentVersion);

        $this->updateComposerVersion($newVersion);

        $this->info("Updated version: $newVersion");

        // Commit and tag the new version
        $this->commitAndTag($newVersion);

        return 0;
    }

    /**
     * Handle rollback to a previous version.
     */
    private function handleRollback(string $version): int
    {
        $this->info("Rolling back to version: $version");

        // Reset master to the specified tag
        exec("git checkout master && git reset --hard $version");

        // Push the reset branch to GitHub
        exec('git push origin master --force');

        $this->info("Rollback complete. Master is now at version: $version");

        return 0;
    }

    /**
     * Handle fix workflow for a broken version.
     */
    private function handleFix(string $version): int
    {
        $this->info("Creating a fix branch for version: $version");

        // Create a new branch for the fix
        $branchName = "fix/$version";
        exec("git checkout -b $branchName");

        $this->info("Branch created: $branchName");
        $this->info('You can now work on fixing the issue in this branch.');

        return 0;
    }

    /**
     * Get the type of version bump.
     */
    private function getType(): string
    {
        $type = $this->argument('type');

        if (! $type || ! in_array($type, $this->types)) {
            $this->error('Invalid type.');
            $this->info('Valid types are: '.implode(', ', $this->types));
            $type = select(
                label: 'Select a type',
                options: $this->types,
                default: 'patch',
                hint: 'Choose the type to bump the version number.'
            );
        }

        return $type;
    }

    /**
     * Get the current version from composer.json.
     */
    private function getCurrentVersion(): string
    {
        $composerPath = base_path('composer.json');
        $composerJson = json_decode(file_get_contents($composerPath), true);

        if (! isset($composerJson['version'])) {
            $this->error('No version found. Please check your composer.json.');
            exit(1);
        }

        return $composerJson['version'];
    }

    /**
     * Calculate the new version number based on the type of bump.
     */
    private function getNewVersion(string $currentVersion, string $type): string
    {
        [$major, $minor, $patch] = explode('.', $currentVersion);

        switch ($type) {
            case 'patch':
                $patch++;
                break;
            case 'minor':
                $minor++;
                $patch = 0;
                break;
            case 'major':
                $major++;
                $minor = 0;
                $patch = 0;
                break;
        }

        return "$major.$minor.$patch";
    }

    /**
     * Update composer.json with the new version number.
     */
    private function updateComposerVersion(string $newVersion): void
    {
        $composerPath = base_path('composer.json');
        $composerJson = json_decode(file_get_contents($composerPath), true);

        $composerJson['version'] = $newVersion;
        file_put_contents($composerPath, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Update readme.md version number
     */
    private function updateReadMeVersion(string $newVersion, string $currentVersion): void
    {
        $readMePath = base_path('README.md');
        $readMeContent = file_get_contents($readMePath);
        $updatedContent = str_replace($currentVersion, $newVersion, $readMeContent);

        file_put_contents($readMePath, $updatedContent);
    }

    /**
     * Commit and tag the new version.
     */
    private function commitAndTag(string $newVersion): void
    {
        exec('git add .');
        exec("git commit -m 'Bump version to $newVersion'");
        exec("git tag -a $newVersion -m 'Release version $newVersion'");
        exec('git push origin master');
        exec('git push origin --tags');

        $this->info("Version $newVersion committed and tagged.");
    }
}
