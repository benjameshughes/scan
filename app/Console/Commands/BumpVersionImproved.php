<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

class BumpVersionImproved extends Command
{
    /**
     * The available version types.
     */
    protected array $types = ['patch', 'minor', 'major'];

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'version:bump-improved {type?} {--rollback=} {--downgrade=} {--fix=} {--dry} {--claude} {--force}';

    /**
     * The console command description.
     */
    protected $description = 'Safely manage application versioning with rollback protection';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            // Pre-flight safety checks
            if (!$this->preflightChecks()) {
                return 1;
            }

            // Handle different operations
            if ($this->option('rollback')) {
                return $this->handleRollback($this->option('rollback'));
            }

            if ($this->option('downgrade')) {
                return $this->handleDowngrade($this->option('downgrade'));
            }

            if ($this->option('fix')) {
                return $this->handleFix($this->option('fix'));
            }

            // Handle version bump
            return $this->handleBump();

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Perform pre-flight safety checks.
     */
    private function preflightChecks(): bool
    {
        // Check if we're in a git repository
        if (!$this->isGitRepository()) {
            $this->error('Not in a git repository');
            return false;
        }

        // Check working directory is clean (unless forced)
        if (!$this->option('force') && !$this->isWorkingDirectoryClean()) {
            $this->error('Working directory is not clean. Commit or stash changes first, or use --force');
            $this->showGitStatus();
            return false;
        }

        // Check if we're on master/main branch
        $currentBranch = trim($this->execSafe('git branch --show-current'));
        if (!in_array($currentBranch, ['master', 'main']) && !$this->option('force')) {
            $this->error("Not on master/main branch (currently on: $currentBranch). Use --force to override");
            return false;
        }

        return true;
    }

    /**
     * Handle version bumping with safety measures.
     */
    private function handleBump(): int
    {
        $type = $this->getType();
        $currentVersion = $this->getCurrentVersion();

        $this->info("Current version: $currentVersion");

        $newVersion = $this->getNewVersion($currentVersion, $type);

        if ($this->option('dry')) {
            $this->info('New version (dry-run): ' . $newVersion);
            return 0;
        }

        // Create backup before proceeding
        $backupBranch = "backup/pre-version-$newVersion-" . date('Y-m-d-H-i-s');
        $this->createBackupBranch($backupBranch);

        // Confirm the operation
        if (!confirm("Bump version from $currentVersion to $newVersion?", true)) {
            $this->info('Version bump cancelled');
            return 0;
        }

        // Update files
        $this->updateReadMeVersion($newVersion, $currentVersion);
        $this->updateComposerVersion($newVersion);

        $this->info("Updated version: $newVersion");

        // Commit and tag with improved message
        $this->commitAndTag($newVersion, $backupBranch);

        return 0;
    }

    /**
     * Handle safe rollback to a previous version.
     */
    private function handleRollback(string $version): int
    {
        // Verify the tag exists
        if (!$this->tagExists($version)) {
            $this->error("Tag $version does not exist");
            $this->showAvailableTags();
            return 1;
        }

        // Create backup before rollback
        $backupBranch = "backup/pre-rollback-" . date('Y-m-d-H-i-s');
        $this->createBackupBranch($backupBranch);

        if (!confirm("Rollback to version $version? This will reset the current branch.", false)) {
            $this->info('Rollback cancelled');
            return 0;
        }

        $this->info("Rolling back to version: $version");

        // Safe rollback without force push
        $this->execSafe("git reset --hard $version");
        
        // Only push if user confirms
        if (confirm('Push rollback to remote? (This will require force push)', false)) {
            $this->execSafe('git push origin HEAD --force-with-lease');
        }

        $this->info("Rollback complete. Backup created at: $backupBranch");
        return 0;
    }

    /**
     * Handle version downgrade (new feature).
     */
    private function handleDowngrade(string $targetVersion): int
    {
        $currentVersion = $this->getCurrentVersion();
        
        if (!$this->isValidVersionFormat($targetVersion)) {
            $this->error('Invalid version format. Use semantic versioning (e.g., 1.2.3)');
            return 1;
        }

        if (version_compare($targetVersion, $currentVersion, '>=')) {
            $this->error('Target version must be lower than current version');
            return 1;
        }

        $this->info("Downgrading from $currentVersion to $targetVersion");

        if ($this->option('dry')) {
            $this->info('Downgrade version (dry-run): ' . $targetVersion);
            return 0;
        }

        // Create backup
        $backupBranch = "backup/pre-downgrade-" . date('Y-m-d-H-i-s');
        $this->createBackupBranch($backupBranch);

        if (!confirm("Downgrade version from $currentVersion to $targetVersion?", false)) {
            $this->info('Downgrade cancelled');
            return 0;
        }

        // Update files
        $this->updateReadMeVersion($targetVersion, $currentVersion);
        $this->updateComposerVersion($targetVersion);

        // Commit the downgrade
        $this->commitAndTag($targetVersion, $backupBranch, 'downgrade');

        return 0;
    }

    /**
     * Commit and tag with improved messaging and Claude integration.
     */
    private function commitAndTag(string $newVersion, string $backupBranch, string $operation = 'bump'): void
    {
        $this->execSafe('git add composer.json README.md');

        $commitMessage = $this->generateCommitMessage($newVersion, $operation);
        
        $this->execSafe("git commit -m " . escapeshellarg($commitMessage));
        $this->execSafe("git tag -a $newVersion -m " . escapeshellarg("Release version $newVersion"));

        if (confirm('Push to remote?', true)) {
            $this->execSafe('git push origin HEAD');
            $this->execSafe('git push origin --tags');
        }

        $this->info("Version $newVersion committed and tagged.");
        $this->info("Backup branch created: $backupBranch");
    }

    /**
     * Generate commit message with optional Claude integration.
     */
    private function generateCommitMessage(string $version, string $operation): string
    {
        $baseMessage = ucfirst($operation) . " version to $version";

        if ($this->option('claude')) {
            $this->info('Generating enhanced commit message with Claude...');
            
            // Get recent changes
            $changes = $this->execSafe('git diff --cached --name-only');
            $changelog = text('Describe the changes in this version (optional):');
            
            $enhancedMessage = $this->generateClaudeCommitMessage($version, $operation, $changes, $changelog);
            
            if ($enhancedMessage) {
                return $enhancedMessage;
            }
        }

        return $baseMessage;
    }

    /**
     * Generate enhanced commit message using Claude-style formatting.
     */
    private function generateClaudeCommitMessage(string $version, string $operation, string $changes, ?string $changelog): string
    {
        $message = ucfirst($operation) . " version to $version\n\n";
        
        if ($changelog) {
            $message .= "## Changes\n$changelog\n\n";
        }
        
        if ($changes) {
            $message .= "## Files Modified\n";
            $files = explode("\n", trim($changes));
            foreach ($files as $file) {
                $message .= "- $file\n";
            }
            $message .= "\n";
        }
        
        $message .= "🤖 Generated with [Claude Code](https://claude.ai/code)\n\n";
        $message .= "Co-Authored-By: Claude <noreply@anthropic.com>";
        
        return $message;
    }

    /**
     * Create a backup branch for safety.
     */
    private function createBackupBranch(string $branchName): void
    {
        $this->execSafe("git branch $branchName");
        $this->info("Backup branch created: $branchName");
    }

    /**
     * Check if tag exists.
     */
    private function tagExists(string $tag): bool
    {
        $result = Process::run("git tag -l $tag");
        return !empty(trim($result->output()));
    }

    /**
     * Show available tags.
     */
    private function showAvailableTags(): void
    {
        $tags = $this->execSafe('git tag -l --sort=-version:refname');
        $this->info('Available tags:');
        $this->line($tags);
    }

    /**
     * Check if working directory is clean.
     */
    private function isWorkingDirectoryClean(): bool
    {
        $status = $this->execSafe('git status --porcelain');
        return empty(trim($status));
    }

    /**
     * Show git status.
     */
    private function showGitStatus(): void
    {
        $status = $this->execSafe('git status --short');
        $this->line($status);
    }

    /**
     * Check if we're in a git repository.
     */
    private function isGitRepository(): bool
    {
        $result = Process::run('git rev-parse --git-dir');
        return $result->successful();
    }

    /**
     * Validate version format.
     */
    private function isValidVersionFormat(string $version): bool
    {
        return preg_match('/^\d+\.\d+\.\d+$/', $version);
    }

    /**
     * Execute command safely with error handling.
     */
    private function execSafe(string $command): string
    {
        $result = Process::run($command);
        
        if (!$result->successful()) {
            throw new \Exception("Command failed: $command\nError: " . $result->errorOutput());
        }
        
        return $result->output();
    }

    /**
     * Get the type of version bump.
     */
    private function getType(): string
    {
        $type = $this->argument('type');

        if (!$type || !in_array($type, $this->types)) {
            if ($type) {
                $this->error('Invalid type.');
                $this->info('Valid types are: ' . implode(', ', $this->types));
            }
            
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
        
        if (!file_exists($composerPath)) {
            throw new \Exception('composer.json not found');
        }
        
        $composerJson = json_decode(file_get_contents($composerPath), true);

        if (!isset($composerJson['version'])) {
            throw new \Exception('No version found in composer.json');
        }

        return $composerJson['version'];
    }

    /**
     * Calculate the new version number based on the type of bump.
     */
    private function getNewVersion(string $currentVersion, string $type): string
    {
        if (!$this->isValidVersionFormat($currentVersion)) {
            throw new \Exception('Current version format is invalid');
        }

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
        
        $encoded = json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            throw new \Exception('Failed to encode composer.json');
        }
        
        file_put_contents($composerPath, $encoded);
    }

    /**
     * Update readme.md version number with better error handling.
     */
    private function updateReadMeVersion(string $newVersion, string $currentVersion): void
    {
        $readMePath = base_path('README.md');
        
        if (!file_exists($readMePath)) {
            $this->warn('README.md not found, skipping version update');
            return;
        }
        
        $readMeContent = file_get_contents($readMePath);
        $updatedContent = str_replace($currentVersion, $newVersion, $readMeContent);

        if ($readMeContent === $updatedContent) {
            $this->warn('No version string found in README.md to update');
        }

        file_put_contents($readMePath, $updatedContent);
    }
}