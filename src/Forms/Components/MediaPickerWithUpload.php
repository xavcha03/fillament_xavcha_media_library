<?php

namespace Xavier\MediaLibraryPro\Forms\Components;

use Closure;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaPickerWithUpload extends SpatieMediaLibraryFileUpload
{
    // Ne pas surcharger la vue pour éviter la récursion infinie
    // Le composant utilisera la vue du parent par défaut
    // On ajoutera le bouton via suffix() dans setUp()

    protected array | Closure $acceptedFileTypesForPicker = [];
    
    protected function setUp(): void
    {
        parent::setUp();
    }


    public function acceptedFileTypesForPicker(array | Closure $types): static
    {
        $this->acceptedFileTypesForPicker = $types;

        return $this;
    }

    public function getAcceptedFileTypesForPicker(): array
    {
        $types = $this->evaluate($this->acceptedFileTypesForPicker);
        
        // Si aucun type spécifié et que le composant a été configuré avec ->image(),
        // on retourne les types d'images par défaut
        if (empty($types)) {
            // Vérifier si le composant a été configuré pour accepter uniquement les images
            // En regardant les propriétés du composant parent
            try {
                // Si le composant a été configuré avec ->image(), on peut supposer qu'on veut des images
                // Sinon, on retourne un tableau vide pour accepter tous les types
                return [];
            } catch (\Exception $e) {
                return [];
            }
        }
        
        return is_array($types) ? $types : [];
    }

}
