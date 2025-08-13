@php
  /**
   * Normaliza un enlace de Google Drive (url completa o id) a formato uc?export=download&id=...
   */
  function drive_direct_url($value){
      if (!$value) return null;
      // ¿viene ya como uc?export=download&id=...?
      if (str_contains($value, 'drive.google.com/uc?')) return $value;

      // /file/d/FILE_ID/view
      if (preg_match('~/d/([^/]+)~', $value, $m)) {
          return "https://drive.google.com/uc?export=download&id={$m[1]}";
      }
      // ?id=FILE_ID
      if (preg_match('~[?&]id=([^&]+)~', $value, $m)) {
          return "https://drive.google.com/uc?export=download&id={$m[1]}";
      }
      // si es un ID "pelado"
      if (!str_contains($value, '://') && !str_contains($value, 'drive.google.com')) {
          return "https://drive.google.com/uc?export=download&id={$value}";
      }
      // otro caso: devuélvelo tal cual
      return $value;
  }
@endphp

<ul class="songs" style="display:flex;flex-direction:column;gap:12px;list-style:none;padding:0">
  @foreach($songs as $song)
    @php
      $src = drive_direct_url($song->audio_path);
      $cover = $song->cover_path ? drive_direct_url($song->cover_path) : null;
    @endphp

    <li>
      <div style="display:flex;align-items:center;gap:12px">
        @if($cover)
          <img src="{{ $cover }}" alt="cover" style="width:54px;height:54px;border-radius:8px;object-fit:cover">
        @endif
        <div style="flex:1">
          <div style="font-weight:700">{{ $song->title }}</div>
          <audio controls preload="metadata" style="width:100%;max-width:560px">
            <source src="{{ $src }}" type="audio/mpeg">
            Tu navegador no soporta audio HTML5.
          </audio>
        </div>
      </div>
    </li>
  @endforeach
</ul>