# Translation Implementation Plan

## Overview
Convert hardcoded Spanish text in blade view files to use Laravel translation keys with __() helper function.

## Files to Update

### Translation Files
- [ ] Update `resources/lang/es.json` with new Spanish translation keys
- [ ] Update `resources/lang/en.json` with corresponding English translations

### Blade View Files
- [ ] `resources/views/menu_album.blade.php` - Replace hardcoded Spanish text with translation keys
- [ ] `resources/views/like.blade.php` - Replace hardcoded Spanish text with translation keys
- [ ] `resources/views/recientes.blade.php` - Replace hardcoded Spanish text with translation keys
- [ ] `resources/views/musica/subir.blade.php` - Replace hardcoded Spanish text with translation keys

## Translation Keys to Add

### Menu Album (menu_album.blade.php)
- albums.title: "Álbumes"
- albums.artist: "Artista"
- albums.no_albums: "No hay álbumes disponibles"
- albums.page: "Página"
- albums.of: "de"

### Likes (like.blade.php)
- likes.title: "Me gusta"
- likes.subtitle: "canciones guardadas para volver siempre."
- likes.search_placeholder: "Buscar canción o artista..."
- likes.clear_search: "Limpiar búsqueda"
- likes.no_likes: "No tienes canciones en Me gusta"
- likes.add_some: "¡Agrega algunas canciones que te gusten!"
- likes.remove_like: "¿Estás seguro de que quieres quitar esta canción de Me gusta?"
- likes.remove: "Quitar"
- likes.cancel: "Cancelar"

### Recent (recientes.blade.php)
- recent.title: "Recientes"
- recent.subtitle: "Revisa las canciones que has escuchado recientemente."
- recent.clear_history: "Limpiar Historial"
- recent.search_placeholder: "Buscar canción..."
- recent.clear_search: "Limpiar búsqueda"
- recent.select_all: "Seleccionar"
- recent.cancel_selection: "Cancelar selección"
- recent.delete: "Eliminar"
- recent.no_recent: "Aún no se ha reproducido ninguna canción"
- recent.start_listening: "¡Empieza a escuchar música para ver tu historial! 🎵"
- recent.add_to_queue: "Agregar a cola"
- recent.play: "Reproducir"
- recent.confirm_clear: "¿Estás seguro de que quieres limpiar todo el historial de canciones reproducidas? Esta acción no se puede deshacer."
- recent.clear: "Limpiar"
- recent.cancel: "Cancelar"

### Upload (musica/subir.blade.php)
- upload.title: "Subir Música"
- upload.subtitle: "Comparte tu talento con el mundo. Sube canciones individuales o álbumes completos."
- upload.song: "Subir Canción"
- upload.album: "Crear Álbum"
- upload.search_placeholder: "Buscar..."
- upload.clear_search: "Limpiar búsqueda"
- upload.song_title: "Título de la canción"
- upload.artist: "Artista"
- upload.album_name: "Nombre del álbum"
- upload.cover: "Portada"
- upload.audio_file: "Archivo de audio"
- upload.upload: "Subir"
- upload.cancel: "Cancelar"
- upload.create_album: "Crear Álbum"

## Implementation Steps
1. Update translation files with new keys
2. Update each blade view file to use __() helper
3. Test translations in both Spanish and English locales
4. Verify all user-facing text is properly translated

## Status
- [x] Plan created
- [ ] Translation keys added to es.json
- [ ] Translation keys added to en.json
- [ ] menu_album.blade.php updated
- [ ] like.blade.php updated
- [ ] recientes.blade.php updated
- [ ] musica/subir.blade.php updated
- [ ] Testing completed
