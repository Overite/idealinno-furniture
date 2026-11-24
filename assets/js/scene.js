import * as THREE from 'three';
import { RoundedBoxGeometry } from 'three/addons/geometries/RoundedBoxGeometry.js';

const host = document.getElementById('scene');
if (host) {
  try {
    init(host);
  } catch (err) {
    // No WebGL: hide the scene and let the text carry the hero.
    host.style.display = 'none';
  }
}

// Draws a wood grain texture so no image files are needed.
function woodTexture(base, dark, seed) {
  const c = document.createElement('canvas');
  c.width = 512; c.height = 512;
  const g = c.getContext('2d');
  g.fillStyle = base;
  g.fillRect(0, 0, 512, 512);
  let s = seed;
  const rnd = () => { s = (s * 16807) % 2147483647; return s / 2147483647; };
  for (let i = 0; i < 170; i++) {
    const x = rnd() * 512;
    g.strokeStyle = dark;
    g.globalAlpha = 0.05 + rnd() * 0.16;
    g.lineWidth = 0.6 + rnd() * 2.4;
    g.beginPath();
    g.moveTo(x, 0);
    for (let y = 0; y <= 512; y += 64) g.lineTo(x + Math.sin(y * 0.013 + i) * (3 + rnd() * 5), y);
    g.stroke();
  }
  g.globalAlpha = 0.18;
  for (let k = 0; k < 3; k++) {
    const kx = rnd() * 512, ky = rnd() * 512;
    for (let r = 4; r < 26; r += 4) {
      g.strokeStyle = dark; g.lineWidth = 1.2;
      g.beginPath(); g.ellipse(kx, ky, r * 0.7, r * 1.6, 0, 0, Math.PI * 2); g.stroke();
    }
  }
  const t = new THREE.CanvasTexture(c);
  t.colorSpace = THREE.SRGBColorSpace;
  t.wrapS = t.wrapT = THREE.RepeatWrapping;
  t.anisotropy = 4;
  return t;
}

function init(host) {
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.outputColorSpace = THREE.SRGBColorSpace;
  host.appendChild(renderer.domElement);

  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(32, 1, 0.1, 100);
  camera.position.set(0, 0.5, 12.5);

  scene.add(new THREE.AmbientLight(0xffffff, 1.0));
  const key = new THREE.DirectionalLight(0xfff1de, 2.4);
  key.position.set(4, 7, 8);
  scene.add(key);
  const rim = new THREE.PointLight(0xff6a13, 55, 26);
  rim.position.set(-6, 1, 3);
  scene.add(rim);

  const rig = new THREE.Group();
  scene.add(rig);

  const W = 3.4, H = 4.6, D = 1.5, T = 0.1;
  const wood = new THREE.MeshStandardMaterial({ map: woodTexture('#a8683a', '#3f2412', 1234), roughness: 0.55, metalness: 0.02 });
  const woodDoor = new THREE.MeshStandardMaterial({ map: woodTexture('#b97444', '#4a2a15', 987), roughness: 0.5, metalness: 0.02 });
  const inside = new THREE.MeshStandardMaterial({ map: woodTexture('#7c4a28', '#2c180b', 555), roughness: 0.8 });
  const metal = new THREE.MeshStandardMaterial({ color: 0xff6a13, roughness: 0.3, metalness: 0.6 });

  const box = (w, h, d, mat, x, y, z, parent = rig) => {
    const m = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), mat);
    m.position.set(x, y, z);
    parent.add(m);
    return m;
  };

  // Carcass
  box(T, H, D, wood, -W / 2 + T / 2, 0, 0);          // left side
  box(T, H, D, wood, W / 2 - T / 2, 0, 0);           // right side
  box(W, T, D, wood, 0, H / 2 - T / 2, 0);           // top
  box(W, T, D, wood, 0, -H / 2 + 0.35, 0);           // bottom
  box(W, 0.3, D - 0.1, inside, 0, -H / 2 + 0.15, -0.05); // plinth
  box(W - 2 * T, H - 0.4, 0.06, inside, 0, 0.1, -D / 2 + 0.03); // back
  // Shelves and hanging rail
  box(W / 2 - T, T * 0.8, D - 0.2, inside, -W / 4 + T / 2, 0.7, -0.05);
  box(W / 2 - T, T * 0.8, D - 0.2, inside, -W / 4 + T / 2, -0.7, -0.05);
  box(T * 0.8, H - 0.5, D - 0.2, inside, 0, 0.05, -0.05);  // divider
  const rail = new THREE.Mesh(new THREE.CylinderGeometry(0.04, 0.04, W / 2 - 2 * T, 16), metal);
  rail.rotation.z = Math.PI / 2;
  rail.position.set(W / 4 - T / 2, 1.5, -0.05);
  rig.add(rail);
  // A few garments on the rail
  const cols = [0xffffff, 0x2b2a27, 0xff6a13, 0xd9d4c8, 0x3d5a80];
  cols.forEach((c, i) => {
    const gmt = new THREE.Mesh(
      new RoundedBoxGeometry(0.1, 1.25, 0.8, 2, 0.04),
      new THREE.MeshStandardMaterial({ color: c, roughness: 0.9 })
    );
    gmt.position.set(0.28 + i * 0.22, 0.82, -0.05);
    rig.add(gmt);
  });

  // Doors: pivot groups at the outer edges
  const doorW = W / 2, doorH = H - 0.4;
  function makeDoor(side) {
    const pivot = new THREE.Group();
    pivot.position.set(side * (W / 2), 0.1, D / 2);
    const slab = new THREE.Mesh(new RoundedBoxGeometry(doorW - 0.03, doorH, 0.09, 2, 0.012), woodDoor);
    slab.position.set(-side * (doorW / 2), 0, 0);
    pivot.add(slab);
    const handle = new THREE.Mesh(new RoundedBoxGeometry(0.06, 0.7, 0.07, 2, 0.02), metal);
    handle.position.set(-side * (doorW - 0.22), 0, 0.09);
    pivot.add(handle);
    rig.add(pivot);
    return pivot;
  }
  const doorL = makeDoor(-1); // hinge on the left, door extends right
  const doorR = makeDoor(1);

  // Floor shadow
  const sc = document.createElement('canvas');
  sc.width = sc.height = 256;
  const sg = sc.getContext('2d');
  const grad = sg.createRadialGradient(128, 128, 10, 128, 128, 126);
  grad.addColorStop(0, 'rgba(0,0,0,0.55)');
  grad.addColorStop(1, 'rgba(0,0,0,0)');
  sg.fillStyle = grad; sg.fillRect(0, 0, 256, 256);
  const shadow = new THREE.Mesh(
    new THREE.PlaneGeometry(6.5, 4),
    new THREE.MeshBasicMaterial({ map: new THREE.CanvasTexture(sc), transparent: true, depthWrite: false })
  );
  shadow.rotation.x = -Math.PI / 2;
  shadow.position.set(0, -H / 2 - 0.02, 0);
  rig.add(shadow);

  // Drifting sawdust
  const N = 70;
  const pos = new Float32Array(N * 3);
  const seeds = [];
  for (let i = 0; i < N; i++) {
    pos[i * 3] = (Math.random() - 0.5) * 8;
    pos[i * 3 + 1] = (Math.random() - 0.5) * 6;
    pos[i * 3 + 2] = (Math.random() - 0.5) * 4 + 1;
    seeds.push({ sp: 0.1 + Math.random() * 0.25, ph: Math.random() * 6.28 });
  }
  const pg = new THREE.BufferGeometry();
  pg.setAttribute('position', new THREE.BufferAttribute(pos, 3));
  const dust = new THREE.Points(pg, new THREE.PointsMaterial({ color: 0xffb27a, size: 0.07, transparent: true, opacity: 0.8, depthWrite: false }));
  scene.add(dust);

  const pointer = { x: 0, y: 0 };
  window.addEventListener('pointermove', (ev) => {
    pointer.x = (ev.clientX / window.innerWidth - 0.5) * 2;
    pointer.y = (ev.clientY / window.innerHeight - 0.5) * 2;
  }, { passive: true });

  function resize() {
    const w = host.clientWidth, h = host.clientHeight;
    if (!w || !h) return;
    renderer.setSize(w, h, false);
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
  }
  new ResizeObserver(resize).observe(host);
  resize();

  const ease = (x) => x * x * (3 - 2 * x);
  // 9 second loop: closed, open, hold, close.
  function openAmount(t) {
    const p = t % 9;
    if (p < 1.5) return 0;
    if (p < 3) return ease((p - 1.5) / 1.5);
    if (p < 6.2) return 1;
    if (p < 7.7) return 1 - ease((p - 6.2) / 1.5);
    return 0;
  }

  const clock = new THREE.Clock();
  let visible = true, raf = 0;

  function frame() {
    const t = clock.getElapsedTime();
    const open = reduce ? 0.55 : openAmount(t);
    const a = open * 1.75;
    doorL.rotation.y = -a;
    doorR.rotation.y = a;

    const scrollTurn = Math.min(window.scrollY, 1400) * 0.0042;
    rig.rotation.y += ((-0.5 + scrollTurn + Math.sin(t * 0.45) * 0.14 + pointer.x * 0.22) - rig.rotation.y) * 0.08;
    rig.rotation.x += ((pointer.y * 0.06) - rig.rotation.x) * 0.05;
    rig.position.y = Math.sin(t * 0.9) * 0.08;

    const p = dust.geometry.attributes.position;
    for (let i = 0; i < N; i++) {
      let y = p.getY(i) - seeds[i].sp * 0.012;
      if (y < -3) y = 3;
      p.setY(i, y);
      p.setX(i, p.getX(i) + Math.sin(t * 0.6 + seeds[i].ph) * 0.002);
    }
    p.needsUpdate = true;

    renderer.render(scene, camera);
    if (visible && !reduce) raf = requestAnimationFrame(frame);
  }

  new IntersectionObserver((entries) => {
    visible = entries[0].isIntersecting;
    if (visible && !reduce) {
      cancelAnimationFrame(raf);
      clock.start();
      raf = requestAnimationFrame(frame);
    }
  }).observe(host);

  frame();
}
