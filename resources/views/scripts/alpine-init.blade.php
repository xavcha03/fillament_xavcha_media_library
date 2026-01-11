<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('mediaPickerUnified', (config) => ({
            open: false,
            activeTab: config.showLibrary ? 'library' : 'upload',
            selected: config.selected || [],
            selectedFiles: config.selectedFiles || {},
            multiple: config.multiple || false,
            acceptedTypes: config.acceptedTypes || [],
            collection: config.collection || 'default',
            maxFiles: config.maxFiles,
            minFiles: config.minFiles || 0,
            showUpload: config.showUpload !== false,
            showLibrary: config.showLibrary !== false,
            conversion: config.conversion || null,
            baseUrl: config.baseUrl || '',
            statePath: config.statePath || '',
            hasPendingUploads: false, // Fichiers sélectionnés mais pas encore uploadés
            
            getMediaUrl(mediaId) {
                // mediaId est un ID, on doit récupérer l'UUID depuis selectedFiles
                const file = this.selectedFiles[mediaId];
                if (file) {
                    if (this.conversion && file.conversions?.[this.conversion]) {
                        return file.conversions[this.conversion];
                    }
                    return file.url || this.baseUrl + file.uuid;
                }
                // Fallback: utiliser l'ID directement (ne devrait pas arriver)
                return this.baseUrl + mediaId;
            },
            
            isImage(mediaId) {
                const file = this.selectedFiles[mediaId];
                if (!file) return false;
                
                // Vérifier par extension du nom de fichier
                if (file.file_name) {
                    const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg', '.bmp', '.ico'];
                    const fileName = file.file_name.toLowerCase();
                    return imageExtensions.some(ext => fileName.endsWith(ext));
                }
                
                // Vérifier par extension de l'URL
                if (file.url) {
                    const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg', '.bmp', '.ico'];
                    const url = file.url.toLowerCase();
                    return imageExtensions.some(ext => url.includes(ext));
                }
                
                return false;
            },
            
            init() {
                // S'assurer que les IDs dans selected sont des entiers pour correspondre aux clés de selectedFiles
                this.selected = this.selected.map(id => parseInt(id));
                
                // S'assurer que les clés de selectedFiles sont des entiers
                const normalizedFiles = {};
                Object.keys(this.selectedFiles).forEach(key => {
                    normalizedFiles[parseInt(key)] = this.selectedFiles[key];
                });
                this.selectedFiles = normalizedFiles;
                
                // Watcher pour mettre à jour automatiquement le champ caché quand selected change
                this.$watch('selected', (newValue, oldValue) => {
                    if (JSON.stringify(newValue) !== JSON.stringify(oldValue)) {
                        this.$nextTick(() => {
                            this.updateForm();
                        });
                    }
                }, { deep: true });
                
                // Watcher pour selectedFiles pour forcer le rafraîchissement de l'affichage
                this.$watch('selectedFiles', () => {
                    // Forcer le rafraîchissement de l'affichage quand selectedFiles change
                    this.$nextTick(() => {
                        // L'affichage se mettra à jour automatiquement via Alpine.js
                    });
                }, { deep: true });
                
                // Écouter les événements globaux de sélection depuis le composant Livewire
                window.addEventListener('media-library-picker-select', (e) => {
                    // Filtrer par statePath pour isoler les instances
                    if (e.detail.statePath && e.detail.statePath !== this.statePath) {
                        return; // Ignorer les événements qui ne sont pas pour cette instance
                    }
                    
                    // Mettre à jour selectedFiles avec les infos du fichier sélectionné
                    if (e.detail.mediaId && e.detail.mediaUuid) {
                        const mediaId = parseInt(e.detail.mediaId);
                        this.selectedFiles[mediaId] = {
                            id: mediaId,
                            uuid: e.detail.mediaUuid,
                            file_name: e.detail.mediaFileName || '',
                            url: e.detail.mediaUrl || this.baseUrl + e.detail.mediaUuid,
                            conversions: e.detail.conversions || {}
                        };
                        
                        // Pour le mode single, mettre à jour immédiatement la sélection
                        if (!this.multiple) {
                            this.selected = [mediaId];
                            // Fermer le modal
                            this.open = false;
                            // Mettre à jour le formulaire immédiatement
                            this.$nextTick(() => {
                                this.updateForm();
                            });
                        } else {
                            // Pour le mode multiple, utiliser toggleMedia
                            this.toggleMedia(mediaId);
                        }
                    }
                });

                // Écouter les événements d'upload
                window.addEventListener('media-library-picker-uploaded', (e) => {
                    // Filtrer par statePath pour isoler les instances
                    if (e.detail.statePath && e.detail.statePath !== this.statePath) {
                        return; // Ignorer les événements qui ne sont pas pour cette instance
                    }
                    
                    if (e.detail.mediaId && e.detail.mediaUuid) {
                        const mediaId = parseInt(e.detail.mediaId);
                        // Mettre à jour selectedFiles avec les infos du fichier uploadé
                        this.selectedFiles[mediaId] = {
                            id: mediaId,
                            uuid: e.detail.mediaUuid,
                            file_name: e.detail.mediaFileName || '',
                            url: e.detail.mediaUrl || this.baseUrl + e.detail.mediaUuid,
                            conversions: {}
                        };
                        // Ajouter à la sélection
                        if (this.multiple) {
                            if (!this.selected.includes(mediaId)) {
                                this.selected.push(mediaId);
                            }
                            // Retourner à l'onglet Bibliothèque après l'upload
                            if (this.showLibrary) {
                                this.activeTab = 'library';
                            }
                        } else {
                            this.selected = [mediaId];
                            // Fermer le modal si sélection unique
                            this.open = false;
                        }
                        // Mettre à jour le formulaire
                        this.updateForm();
                        // Réinitialiser l'état des uploads en attente
                        this.hasPendingUploads = false;
                    }
                });
                
                // Vérifier périodiquement s'il y a des fichiers en attente d'upload
                const checkPendingUploads = () => {
                    // Chercher le composant Livewire d'upload dans le modal
                    const uploadTab = this.$el.querySelector('[x-show*=\"upload\"]');
                    if (uploadTab) {
                        const wireElement = uploadTab.querySelector('[wire\\:id]');
                        if (wireElement) {
                            const wireId = wireElement.getAttribute('wire:id');
                            if (wireId && window.Livewire) {
                                const component = window.Livewire.find(wireId);
                                if (component && component.get) {
                                    const files = component.get('uploadedFiles');
                                    this.hasPendingUploads = Array.isArray(files) && files.length > 0;
                                    return;
                                }
                            }
                        }
                    }
                    this.hasPendingUploads = false;
                };
                
                // Vérifier toutes les 300ms
                setInterval(checkPendingUploads, 300);
                // Vérifier immédiatement
                this.$nextTick(checkPendingUploads);
            },
            
            toggleMedia(mediaId) {
                mediaId = parseInt(mediaId);
                
                // Vérifier la limite maxFiles
                if (this.maxFiles && this.selected.length >= this.maxFiles && !this.selected.includes(mediaId)) {
                    return;
                }
                
                if (this.multiple) {
                    const index = this.selected.indexOf(mediaId);
                    if (index > -1) {
                        this.selected.splice(index, 1);
                    } else {
                        this.selected.push(mediaId);
                    }
                } else {
                    this.selected = [mediaId];
                    this.open = false;
                }
                this.updateForm();
            },
            
            removeMedia(mediaId) {
                mediaId = parseInt(mediaId);
                const index = this.selected.indexOf(mediaId);
                if (index > -1) {
                    this.selected.splice(index, 1);
                    // Mettre à jour immédiatement
                    this.updateForm();
                }
            },
            
            confirmSelection() {
                // Vérifier minFiles
                if (this.selected.length < this.minFiles) {
                    alert(`Vous devez sélectionner au moins ${this.minFiles} fichier(s).`);
                    return;
                }
                
                this.updateForm();
                this.open = false;
            },
            
            updateForm() {
                const hiddenInput = this.$refs.hiddenInput || this.$el.querySelector('input[type="hidden"][wire\\:model], input[type="hidden"][wire\\:model\\.live], input[type="hidden"][wire\\:model\\.defer]');
                if (!hiddenInput) {
                    console.warn('Hidden input not found');
                    return;
                }
                
                // Calculer la nouvelle valeur
                let value;
                if (this.multiple) {
                    value = this.selected.length > 0 ? JSON.stringify(this.selected) : '[]';
                } else {
                    value = this.selected.length > 0 ? this.selected[0].toString() : '';
                }
                
                // Mettre à jour la valeur de l'input
                const oldValue = hiddenInput.value;
                hiddenInput.value = value;
                
                // Si la valeur n'a pas changé, ne rien faire
                if (oldValue === value) {
                    return;
                }
                
                // Trouver tous les composants Livewire possibles
                const wireElements = this.$el.querySelectorAll('[wire\\:id]');
                let livewireComponent = null;
                
                // Chercher le composant Livewire le plus proche (formulaire Filament)
                for (let element of wireElements) {
                    const wireId = element.getAttribute('wire:id');
                    if (wireId && window.Livewire) {
                        const component = window.Livewire.find(wireId);
                        if (component) {
                            livewireComponent = component;
                            break;
                        }
                    }
                }
                
                // Si on ne trouve pas, chercher dans le parent
                if (!livewireComponent) {
                    const parentWire = this.$el.closest('[wire\\:id]');
                    if (parentWire) {
                        const wireId = parentWire.getAttribute('wire:id');
                        if (wireId && window.Livewire) {
                            livewireComponent = window.Livewire.find(wireId);
                        }
                    }
                }
                
                // Méthode 1: Utiliser $wire si disponible (Alpine.js + Livewire v3)
                if (this.$wire && this.statePath) {
                    try {
                        this.$wire.set(this.statePath, value);
                        // Forcer la mise à jour pour le mode single
                        if (!this.multiple) {
                            this.$wire.$commit();
                        }
                    } catch (e) {
                        console.warn('Erreur $wire.set:', e);
                    }
                }
                
                // Méthode 2: Utiliser Livewire directement avec le statePath
                if (livewireComponent && this.statePath) {
                    try {
                        livewireComponent.set(this.statePath, value);
                        // Forcer la mise à jour
                        if (!this.multiple) {
                            livewireComponent.$commit();
                        }
                    } catch (e) {
                        console.warn('Erreur Livewire.set avec statePath:', e);
                    }
                }
                
                // Méthode 3: Utiliser le nom du wire:model directement
                const wireModelAttr = hiddenInput.getAttribute('wire:model') || hiddenInput.getAttribute('wire:model.live') || hiddenInput.getAttribute('wire:model.defer');
                if (livewireComponent && wireModelAttr) {
                    try {
                        livewireComponent.set(wireModelAttr, value);
                        // Forcer la mise à jour
                        if (!this.multiple) {
                            livewireComponent.$commit();
                        }
                    } catch (e) {
                        console.warn('Erreur Livewire.set avec wire:model:', e);
                    }
                }
                
                // Méthode 4: Déclencher les événements DOM (pour wire:model)
                // Utiliser un InputEvent au lieu d'un Event simple
                const inputEvent = new InputEvent('input', {
                    bubbles: true,
                    cancelable: true,
                    data: value,
                    inputType: 'insertText'
                });
                hiddenInput.dispatchEvent(inputEvent);
                
                const changeEvent = new Event('change', {
                    bubbles: true,
                    cancelable: true
                });
                hiddenInput.dispatchEvent(changeEvent);
                
                // Méthode 5: Déclencher un événement Livewire personnalisé
                if (livewireComponent) {
                    try {
                        livewireComponent.call('$set', wireModelAttr || this.statePath, value);
                    } catch (e) {
                        // Ignorer si la méthode n'existe pas
                    }
                }
            }
        }));
    });
</script>


