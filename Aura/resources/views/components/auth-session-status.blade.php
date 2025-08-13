@once
@push('styles')
<style>
    html,body{height:100%;}
    #aurora-background{position:fixed;inset:0;z-index:0;overflow:hidden;}
    .aurora-layer{position:absolute;inset:-10px;background-size:300%,200%;background-position:50% 50%,50% 50%;filter:blur(14px) invert(1);opacity:.25;will-change:transform;}
    .aurora-layer.static{
      background-image:
        repeating-linear-gradient(100deg,#fff 0%,#fff 7%,transparent 10%,transparent 12%,#fff 16%),
        repeating-linear-gradient(100deg,#12010f 10%,#200021 15%,#311037 20%,#4a1755 23%,#651a63 26%,#85124f 29%,#12010f 35%);
    }
    .aurora-layer.animated::after{
      content:"";position:absolute;inset:0;
      background-image:
        repeating-linear-gradient(100deg,#fff 0%,#fff 7%,transparent 10%,transparent 12%,#fff 16%),
        repeating-linear-gradient(100deg,#12010f 10%,#200021 15%,#311037 20%,#4a1755 23%,#651a63 26%,#85124f 29%,#12010f 35%);
      background-size:200%,100%;background-attachment:fixed;
      mix-blend-mode:difference;animation:aurora 60s linear infinite;opacity:.25;
    }
    .overlay-black{position:absolute;inset:0;background:rgba(0,0,0,.40);}
    @keyframes aurora{
      from{background-position:50% 50%,50% 50%;}
      to{background-position:350% 50%,350% 50%;}
    }
</style>
@endpush
@endonce

<div id="aurora-background">
  <div class="aurora-layer static"></div>
  <div class="aurora-layer animated"></div>
  <div class="overlay-black"></div>
</div>
