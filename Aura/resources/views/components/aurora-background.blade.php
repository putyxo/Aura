<style>
  #aurora-test{
    position: fixed;
    inset: 0;
    z-index: -2;              /* antes 0 → mándalo bien al fondo */
    overflow: hidden;
    pointer-events: none;
    background-image:
      repeating-linear-gradient(100deg,#000 0%,#000 7%,transparent 10%,transparent 12%,#000 16%),
      repeating-linear-gradient(100deg,#12010f 10%,#200021 15%,#311037 10%,#4a1755 10%,#570c55 10%,#420827 10%,#12010f 10%);
    background-size: 500% 400%;
    background-position: 50% 50%, 50% 50%;
    animation: aurora-move 60s linear infinite;
    filter: blur(112px);

  }
  #aurora-overlay{
    position: fixed;
    inset: 0;
    background: rgba(92, 4, 70, 0.1);
    z-index: -1;              /* antes 1 → queda también por detrás de todo */
    pointer-events: none;
  }
  @keyframes aurora-move{
    from{background-position:50% 50%,50% 50%}
    to  {background-position:350% 50%,350% 50%}
  }
</style>

<div id="aurora-test"></div>
<div id="aurora-overlay"></div>
