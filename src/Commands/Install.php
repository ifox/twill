<?php

namespace A17\Twill\Commands;

use A17\Twill\Commands\Traits\HandlesPresets;
use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;

class Install extends Command
{
    use HandlesPresets;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twill:install {preset? : Optional, the preset to install} {--fromBuild}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Twill into your Laravel application';

    public function __construct(public Filesystem $files, public DatabaseManager $db)
    {
        parent::__construct();
    }

    /**
     * Executes the console command.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle(): void
    {
        //check the database connection before installing
        try {
            $this->db->connection()->getPdo();
        } catch (\Exception $exception) {
            $this->components->error(
                'Could not connect to the database, please check your configuration:' . "\n" . $exception->getMessage()
            );

            return;
        }

        if (filled($preset = $this->argument('preset'))) {
            if ($this->presetExists($preset)) {
                if (
                    $this->confirm(
                        'Are you sure to install this preset? This can overwrite your models, config and routes.'
                    )
                ) {
                    $this->installPreset($preset);
                } else {
                    $this->components->warn('Cancelled.');
                }
            } else {
                $this->components->error("Could not find preset: $preset");
            }
        } else {
            $installSelections = $this->resolveInstallSelections();

            $this->copyBlockPreviewFile();
            $this->addRoutesFile();
            $this->call('migrate');
            $this->publishConfig();
            $this->publishAssets();
            $this->createSuperAdmin();
            $this->displayInstallSummary($installSelections);
        }
    }

    private function installPreset(string $preset): void
    {
        $this->installPresetFiles($preset);

        $this->call('migrate');
        $this->publishAssets();
        $this->createSuperAdmin();
        $this->components->info('Finished installing preset!');
        $this->displayPresetSummary($preset);
    }

    /**
     * Creates the default `twill.php` route configuration file.
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function addRoutesFile(): void
    {
        $routesPath = base_path('routes');

        if (! $this->files->exists($routesPath)) {
            $this->files->makeDirectory($routesPath, 0755, true);
        }

        if (! $this->files->exists($routesPath . '/twill.php')) {
            $stub = $this->files->get(__DIR__ . '/stubs/admin.stub');
            $this->files->put($routesPath . '/twill.php', $stub);
        }
    }

    private function copyBlockPreviewFile(): void
    {
        $layoutsDirectory = base_path('resources/views/site/layouts');

        if (! $this->files->exists($layoutsDirectory)) {
            $this->files->makeDirectory($layoutsDirectory, 0755, true);
        }

        if (! $this->files->exists($layoutsDirectory . '/block.blade.php')) {
            $stub = $this->files->get(__DIR__ . '/stubs/block.blade.php');
            $this->files->put($layoutsDirectory . '/block.blade.php', $stub);
        }
    }

    /**
     * Calls the command responsible for creation of the default superadmin user.
     */
    private function createSuperAdmin(): void
    {
        if (! $this->option('no-interaction')) {
            $this->call('twill:superadmin');
        }
    }

    /**
     * Publishes the package configuration files.
     */
    private function publishConfig(): void
    {
        $this->call('vendor:publish', [
            '--provider' => \A17\Twill\TwillServiceProvider::class,
            '--tag' => 'config',
        ]);
    }

    /**
     * Publishes the package frontend assets.
     */
    private function publishAssets(): void
    {
        if ($this->option('fromBuild')) {
            // If this is from a build, we copy from dist to public.
            $this->files->copyDirectory(__DIR__ . '/../../dist/', public_path());
        } else {
            $this->call('vendor:publish', [
                '--provider' => \A17\Twill\TwillServiceProvider::class,
                '--tag' => 'assets',
            ]);
        }
    }

    private function resolveInstallSelections(): array
    {
        if ($this->option('no-interaction')) {
            return [
                'storage' => 'local',
                'imageService' => 'glide',
                'features' => [],
            ];
        }

        $this->components->info('Twill 4 installation wizard');

        return [
            'storage' => $this->choice(
                'Storage backend',
                ['local', 's3', 'azure'],
                'local'
            ),
            'imageService' => $this->choice(
                'Image service',
                ['glide', 'imgix', 'twicpics'],
                'glide'
            ),
            'features' => $this->choice(
                'Optional features',
                ['2fa', 'oauth', 'permissions-management', 'dashboard-analytics'],
                default: null,
                attempts: null,
                multiple: true
            ),
        ];
    }

    private function displayInstallSummary(array $installSelections): void
    {
        $envLines = $this->buildEnvSnippet($installSelections);
        $composerPackages = $this->composerPackagesForSelections($installSelections);

        $this->components->info('Twill installed successfully.');

        $this->table(
            ['Installed item', 'Selection'],
            [
                ['Storage backend', strtoupper($installSelections['storage'])],
                ['Image service', ucfirst($installSelections['imageService'])],
                ['Optional features', empty($installSelections['features']) ? 'None' : implode(', ', $installSelections['features'])],
                ['Documentation', 'https://twillcms.com/docs'],
            ]
        );

        $this->components->info('Ready-to-paste .env snippet:');
        $this->line(implode(PHP_EOL, $envLines));

        if (! empty($composerPackages)) {
            $this->components->warn('Install the optional packages required by your selections:');
            $this->line('composer require ' . implode(' ', $composerPackages));
        }
    }

    private function displayPresetSummary(string $preset): void
    {
        $this->table(
            ['Installed item', 'Selection'],
            [
                ['Preset', $preset],
                ['Documentation', 'https://twillcms.com/docs'],
            ]
        );
    }

    private function buildEnvSnippet(array $installSelections): array
    {
        $lines = [
            'MEDIA_LIBRARY_ENDPOINT_TYPE=' . $installSelections['storage'],
            'FILE_LIBRARY_ENDPOINT_TYPE=' . $installSelections['storage'],
        ];

        if ($installSelections['storage'] === 's3') {
            $lines = [
                ...$lines,
                'S3_KEY=',
                'S3_SECRET=',
                'S3_BUCKET=',
                'S3_REGION=',
            ];
        }

        if ($installSelections['storage'] === 'azure') {
            $lines = [
                ...$lines,
                'AZURE_ACCOUNT_NAME=',
                'AZURE_ACCOUNT_KEY=',
                'AZURE_CONTAINER=public',
            ];
        }

        $imageServiceMap = [
            'glide' => 'A17\Twill\Services\MediaLibrary\Glide',
            'imgix' => 'A17\Twill\Services\MediaLibrary\Imgix',
            'twicpics' => 'A17\Twill\Services\MediaLibrary\TwicPics',
        ];

        $lines[] = 'MEDIA_LIBRARY_IMAGE_SERVICE="' . $imageServiceMap[$installSelections['imageService']] . '"';

        if ($installSelections['imageService'] === 'imgix') {
            $lines[] = 'IMGIX_SOURCE_HOST=';
        }

        if ($installSelections['imageService'] === 'twicpics') {
            $lines[] = 'TWICPICS_DOMAIN=';
            $lines[] = 'TWICPICS_PATH=';
        }

        return $lines;
    }

    private function composerPackagesForSelections(array $installSelections): array
    {
        $packages = [];

        if ($installSelections['storage'] === 's3') {
            $packages[] = 'league/flysystem-aws-s3-v3';
        }

        if ($installSelections['storage'] === 'azure') {
            $packages[] = 'matthewbdaly/laravel-azure-storage';
        }

        if ($installSelections['imageService'] === 'imgix') {
            $packages[] = 'imgix/imgix-php';
        }

        if (in_array('oauth', $installSelections['features'], true)) {
            $packages[] = 'laravel/socialite';
        }

        if (in_array('dashboard-analytics', $installSelections['features'], true)) {
            $packages[] = 'spatie/laravel-analytics';
        }

        return collect($packages)->unique()->sort()->values()->all();
    }
}
