

class CustomScene {
    constructor() {
      this.scene = new THREE.Scene();
      this.camera = null;
      this.geometry = null;
      this.material = null;
      this.mesh = null;
      this.renderer = null;
      this.clock = new THREE.Clock();
    }
  
    init(canvas) {
      this.scene.background = new THREE.Color(0x000000);
      this.light = new THREE.SpotLight(0xffffff, 1);
  
      this.camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 1, 100);
      this.geometry = new THREE.PlaneBufferGeometry(30, 10);
      this.material = new THREE.ShaderMaterial({
        vertexShader: `
          precision mediump float;
          varying vec2 vUv;
          void main() {
            vUv = uv;
            gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
          }`,
        fragmentShader: `
          precision highp float;
          #define PI2 6.28318530718
          #define MAX_ITER 5
  
          uniform float uTime;
          uniform vec2 uResolution;
  
          void mainImage(out vec4 fragColor, in vec2 fragCoord) {
            float time = uTime * .12;
            vec2 uv = fragCoord.xy / uResolution.xy;
            vec2 p = mod(uv * PI2, PI2) - 100.0;
            vec2 i = p;
            float c = 0.5;
            float inten = .0094;
  
            for (int n = 0; n < MAX_ITER; n++) {
              float t = time * (4.5 - (2.2 / float(n + 122)));
              i = p + vec2(cos(t - i.x) + sin(t + i.y), sin(t - i.y) + cos(t + i.x));
              c += 1.0 / length(vec2(p.x / (sin(i.x + t) / inten), p.y / (cos(i.y + t) / inten)));
            }
  
            c /= float(MAX_ITER);
            c = 1.10 - pow(c, 1.26);
  
            // Aquí cambia el color de los rayos a morado oscuro:
            vec3 colour = vec3(0.4 * pow(abs(c), 2.5), 0.0, 0.4 * pow(abs(c), 2.5));
  
            fragColor = vec4(colour, 1.0);
          }
  
          void main(void) {
            mainImage(gl_FragColor, gl_FragCoord.xy);
          }`,
        uniforms: {
          uTime: { value: 0.0 },
          uResolution: { value: new THREE.Vector2(window.innerWidth, window.innerHeight) }
        }
      });
  
      this.mesh = new THREE.Mesh(this.geometry, this.material);
      this.renderer = new THREE.WebGLRenderer({ canvas: canvas });
      this.renderer.setPixelRatio(window.devicePixelRatio);
      this.renderer.setSize(window.innerWidth, window.innerHeight);
  
      this.scene.add(this.camera);
      this.scene.add(this.mesh);
      this.scene.add(this.light);
  
      this.mesh.position.set(0, 0, 0);
      this.camera.position.set(0, 0, 10);
      this.light.position.set(0, 0, 10);
      this.light.lookAt(this.mesh.position);
      this.camera.lookAt(this.mesh.position);
  
      this.addEvents();
    }
  
    run() {
      requestAnimationFrame(this.run.bind(this));
      this.material.uniforms.uTime.value = this.clock.getElapsedTime();
      this.renderer.render(this.scene, this.camera);
    }
  
    addEvents() {
      window.addEventListener("resize", this.onResize.bind(this), false);
    }
  
    onResize() {
      this.material.uniforms.uResolution.value.set(window.innerWidth, window.innerHeight);
      this.camera.aspect = window.innerWidth / window.innerHeight;
      this.camera.updateProjectionMatrix();
      this.renderer.setSize(window.innerWidth, window.innerHeight);
    }
  }
  
  window.addEventListener("DOMContentLoaded", () => {
    const canvas = document.getElementById("canvas");
    if (canvas) {
      const scene = new CustomScene();
      scene.init(canvas);
      scene.run();
    } else {
      console.error("No se encontró el canvas con id 'canvas'");
    }
  });

  