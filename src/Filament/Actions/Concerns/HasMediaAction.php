<?php

namespace Xavier\MediaLibraryPro\Filament\Actions\Concerns;

use Filament\Notifications\Notification;

trait HasMediaAction
{
    /**
     * Affiche une notification de succès
     */
    protected function sendSuccessNotification(string $message): void
    {
        Notification::make()
            ->title('Succès')
            ->body($message)
            ->success()
            ->send();
    }

    /**
     * Affiche une notification d'erreur
     */
    protected function sendErrorNotification(string $message): void
    {
        Notification::make()
            ->title('Erreur')
            ->body($message)
            ->danger()
            ->send();
    }

    /**
     * Affiche une notification d'avertissement
     */
    protected function sendWarningNotification(string $message): void
    {
        Notification::make()
            ->title('Avertissement')
            ->body($message)
            ->warning()
            ->send();
    }
}






