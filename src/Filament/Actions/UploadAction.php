<?php

namespace Xavier\MediaLibraryPro\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Xavier\MediaLibraryPro\Filament\Actions\Concerns\HasMediaAction;
use Xavier\MediaLibraryPro\Services\MediaUploadService;

class UploadAction extends Action
{
    use HasMediaAction;

    public static function getDefaultName(): string
    {
        return 'uploadMedia';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Uploader des fichiers')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('primary')
            ->form([
                FileUpload::make('files')
                    ->label('Fichiers')
                    ->multiple()
                    ->required()
                    ->helperText('Sélectionnez un ou plusieurs fichiers à uploader')
            ])
            ->action(function (array $data) {
                try {
                    $uploadService = app(MediaUploadService::class);
                    $count = 0;

                    if (isset($data['files']) && is_array($data['files'])) {
                        foreach ($data['files'] as $file) {
                            if ($file instanceof \Illuminate\Http\UploadedFile) {
                                $uploadService->upload($file);
                                $count++;
                            }
                        }
                    }

                    $this->sendSuccessNotification("{$count} fichier(s) ont été uploadés avec succès");
                } catch (\Exception $e) {
                    $this->sendErrorNotification($e->getMessage());
                }
            })
            ->modalHeading('Uploader des fichiers')
            ->modalDescription('Sélectionnez les fichiers que vous souhaitez uploader dans la bibliothèque.');
    }

    /**
     * Configure l'action via une méthode statique
     */
    public static function configureUsing(\Closure $callback): void
    {
        static::configure($callback);
    }
}





