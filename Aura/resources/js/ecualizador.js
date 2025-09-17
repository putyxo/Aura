
(() => {
  // Config
  const FREQS = [32,64,125,250,500,1000,2000,4000,8000,16000];
  const GAIN_MIN = -12, GAIN_MAX = 12, Q_DEFAULT = 1.0;

  // DOM
  const bandsWrap = document.getElementById('bands');
  const preamp = document.getElementById('preamp');
  const preampVal = document.getElementById('preampVal');
  const qAll = document.getElementById('qAll');
  const qVal = document.getElementById('qVal');
  const resetBtn = document.getElementById('resetBtn');

  // WebAudio
  let ac, filters=[], gPreamp, srcNode;

  const dbToGain = db => Math.pow(10, db/20);

  function ensureCtx(){
    if (ac) return;
    ac = new (window.AudioContext||window.webkitAudioContext)();

    // Crear filtros
    filters = FREQS.map(freq=>{
      const f = ac.createBiquadFilter();
      f.type = 'peaking';
      f.frequency.value = freq;
      f.Q.value = Q_DEFAULT;
      f.gain.value = 0;
      return f;
    });

    // Preamp
    gPreamp = ac.createGain();
    gPreamp.gain.value = dbToGain(parseFloat(preamp.value || '0'));

    // Cadena fija: [filters] -> preamp -> destino
    for (let i=0;i<filters.length-1;i++) filters[i].connect(filters[i+1]);
    filters[filters.length-1].connect(gPreamp);
    gPreamp.connect(ac.destination);
  }

  function connectSourceToEq(node){
    ensureCtx();
    node.disconnect?.();
    node.connect(filters[0]);
    srcNode = node;
    if (ac.state === 'suspended') ac.resume().catch(()=>{});
  }

  // Construir UI de bandas
  function buildBands(){
    bandsWrap.innerHTML='';
    FREQS.forEach((freq, idx)=>{
      const band = document.createElement('div'); band.className='band';

      const val = document.createElement('div'); val.className='num'; val.textContent='0 dB'; val.id='v'+idx;

      const sl = document.createElement('input');
      sl.type='range'; sl.min=String(GAIN_MIN); sl.max=String(GAIN_MAX); sl.step='0.5'; sl.value='0';
      sl.addEventListener('input', ()=>{
        ensureCtx();
        const db = parseFloat(sl.value);
        filters[idx].gain.value = db;
        val.textContent = db + ' dB';
      });

      const lb = document.createElement('label');
      lb.textContent = (freq>=1000?(freq/1000)+'k':freq) + 'Hz';

      band.appendChild(val); band.appendChild(sl); band.appendChild(lb);
      bandsWrap.appendChild(band);
    });
  }
  buildBands();

  // Controles
  qAll.addEventListener('input', ()=>{
    ensureCtx();
    const q = parseFloat(qAll.value);
    qVal.textContent = q.toFixed(1);
    filters.forEach(f => f.Q.value = q);
  });

  preamp.addEventListener('input', ()=>{
    ensureCtx();
    const db = parseFloat(preamp.value);
    preampVal.textContent = db + ' dB';
    gPreamp.gain.value = dbToGain(db);
  });

  resetBtn.addEventListener('click', ()=>{
    document.querySelectorAll('.band input[type="range"]').forEach((el,i)=>{
      el.value='0';
      document.getElementById('v'+i).textContent='0 dB';
      if (filters[i]) filters[i].gain.value = 0;
    });
    preamp.value='0'; preamp.dispatchEvent(new Event('input'));
    qAll.value=String(Q_DEFAULT); qAll.dispatchEvent(new Event('input'));
  });

  // API pública: conecta un <audio> externo
  // Uso: window.bindEqualizerTo(miAudioElement);
  window.bindEqualizerTo = function(audioEl){
    if (!audioEl) return;
    ensureCtx();
    try{
      const media = ac.createMediaElementSource(audioEl);
      connectSourceToEq(media);
    }catch(e){
      if (srcNode){
        try{ srcNode.disconnect(); }catch{}
        try{ srcNode.connect(filters[0]); }catch{}
      }
    }
  };
})();
