<?php

namespace Xavier\MediaLibraryPro\Commands;

use Illuminate\Console\Command;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Xavier\MediaLibraryPro\Services\ImageOptimizationService;
use Illuminate\Support\Facades\DB;

class OptimizeImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media-library-pro:optimize-images 
                            {--force : Forcer l\'optimisation même si déjà optimisée}
                            {--limit= : Limiter le nombre d\'images à traiter}
                            {--chunk=100 : Nombre d\'images à traiter par batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimise toutes les images existantes dans la bibliothèque média';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Démarrage de l\'optimisation des images...');
        $this->newLine();

        $optimizationService = app(ImageOptimizationService::class);

        // Vérifier si l'optimisation est activée
        if (!config('media-library-pro.optimization.enabled', false)) {
            $this->warn('⚠️  L\'optimisation d\'images est désactivée dans la configuration.');
            $this->info('   Activez-la dans config/media-library-pro.php avec "optimization.enabled => true"');
            return Command::FAILURE;
        }

        // Compter les images
        $query = MediaFile::where('mime_type', 'like', 'image/%');
        
        if (!$this->option('force')) {
            // Optionnel : filtrer les images déjà optimisées (si vous ajoutez un champ)
            // $query->whereNull('optimized_at');
        }

        $totalImages = $query->count();

        if ($totalImages === 0) {
            $this->info('✅ Aucune image à optimiser.');
            return Command::SUCCESS;
        }

        $this->info("📊 {$totalImages} image(s) trouvée(s)");
        $this->newLine();

        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $chunkSize = (int) $this->option('chunk');
        $processed = 0;
        $optimized = 0;
        $failed = 0;
        $totalSavedBytes = 0;

        $bar = $this->output->createProgressBar($limit ?? $totalImages);
        $bar->start();

        $shouldStop = false;
        $query->chunkById($chunkSize, function ($mediaFiles) use (
            $optimizationService,
            &$processed,
            &$optimized,
            &$failed,
            &$totalSavedBytes,
            $bar,
            $limit,
            &$shouldStop
        ) {
            foreach ($mediaFiles as $mediaFile) {
                if ($limit && $processed >= $limit) {
                    $shouldStop = true;
                    return false; // Arrêter le chunk
                }

                try {
                    $originalSize = $mediaFile->size;
                    $success = $optimizationService->optimizeMediaFile($mediaFile);
                    
                    if ($success) {
                        $mediaFile->refresh();
                        $newSize = $mediaFile->size;
                        $savedBytes = $originalSize - $newSize;
                        $totalSavedBytes += $savedBytes;
                        $optimized++;
                    } else {
                        $failed++;
                    }
                } catch (\Exception $e) {
                    $this->newLine();
                    $this->error("❌ Erreur pour {$mediaFile->file_name}: {$e->getMessage()}");
                    $failed++;
                }

                $processed++;
                $bar->advance();
                
                if ($shouldStop) {
                    return false; // Arrêter le chunk
                }
            }
            
            return !$shouldStop; // Continuer si on n'a pas atteint la limite
        });

        $bar->finish();
        $this->newLine(2);

        // Afficher les résultats
        $this->info('📈 Résultats :');
        $this->table(
            ['Statut', 'Nombre'],
            [
                ['✅ Optimisées', $optimized],
                ['❌ Échecs', $failed],
                ['📦 Total traitées', $processed],
            ]
        );

        if ($totalSavedBytes > 0) {
            $savedMB = round($totalSavedBytes / 1024 / 1024, 2);
            $this->info("💾 Espace économisé : {$savedMB} MB");
        }

        $this->newLine();
        $this->info('✅ Optimisation terminée !');

        return Command::SUCCESS;
    }
}
