<?php
session_start();
$cfg = require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/helpers.php';

// Post/Redirect/Get so a refresh never resends the form.
$result = handle_contact($cfg);
if ($result !== null && $result['ok']) {
    $_SESSION['flash'] = $result;
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '#contact');
    exit;
}
if ($result === null && isset($_SESSION['flash'])) {
    $result = $_SESSION['flash'];
    unset($_SESSION['flash']);
}
$old = $result['old'] ?? [];

$hasPhoto = is_file(__DIR__ . '/' . $cfg['about_photo']);
$callUrl = 'tel:+' . $cfg['phone_intl'];
$waUrl = 'https://wa.me/' . $cfg['phone_intl'] . '?text=' . rawurlencode('Hello Idealinno, I found your website and would like a quote for some furniture.');

// Gallery shows only when photos exist: assets/img/gallery-1.jpg, gallery-2.jpg ...
$gallery = glob(__DIR__ . '/assets/img/gallery-*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [];
sort($gallery);

$services = [
    ['Wardrobes', 'Wardrobes made to the size of your room, finished with care and delivered on time. Order one piece or a full set.'],
    ['Kitchen cabinets', 'Cupboards and kitchen cabinets built with practical details such as detachable shelves and swivel wheels.'],
    ['Custom furniture from your sketch', 'Bring a drawing, a photo or your measurements. We follow your sketch closely and match the size and quality you ask for.'],
    ['Carpentry work', 'Careful, well-detailed carpentry that holds up in daily use, completed to your specification.'],
];

$steps = [
    ['Send your idea', 'Call or message us with a sketch, photos or measurements of what you want built.'],
    ['Get an estimate', 'We price the job and send you an estimate online, so you know the cost before work starts.'],
    ['We build it', 'Our carpenters make your piece in the workshop, with close attention to size, wood and finish.'],
    ['Delivery', 'Your furniture is finished to spec and delivered when we promised.'],
];

$reviews = [
    ['Philip A.', 'Gave a sketch of the cupboard he wanted and insisted on the size and quality. He praised the wood, the detachable shelves and the swivel wheels.'],
    ['Ade O.', 'Ordered three sets of wardrobes. They were finished to spec, on time and delivered promptly, and the proprietor was courteous and professional.'],
    ['A quantity surveyor', 'Called the craftsmanship very good, well detailed and accurate, and said the team works without needing supervision.'],
];

$good = ['Online estimates', 'On-site services', 'Appointment required', 'Debit cards accepted', 'Wheelchair-accessible entrance'];
$topics = ['Wardrobes', 'Kitchen cabinets', 'Custom furniture from a sketch', 'Carpentry work', 'Something else'];
$tape = ['Wardrobes', 'Kitchen cabinets', 'Custom furniture', 'Carpentry', 'Online estimates', 'Delivered on time'];

require __DIR__ . '/includes/header.php';
?>
<main id="main">

  <!-- Hero -->
  <section id="top" class="hero">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-7 reveal">
          <span class="kicker kicker-light"><?= e($cfg['city']) ?> &middot; Olowora, Isheri</span>
          <h1 class="mt-3">Made to<br><em>measure.</em><br>Built to<br>last.</h1>
          <p class="hero-lead mt-4">Custom wardrobes, kitchen cabinets and carpentry, built to your sketch and size by Idealinno Furniture and Carpentry in Lagos.</p>
          <div class="d-flex flex-wrap gap-3 mt-4">
            <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener" class="btn btn-ember btn-lg">Get a quote on WhatsApp</a>
            <a href="<?= e($callUrl) ?>" class="btn btn-line-light btn-lg">Call <?= e($cfg['phone']) ?></a>
          </div>
          <div class="hero-meta mt-4">
            <a href="<?= e($cfg['maps_url']) ?>" target="_blank" rel="noopener"><span class="stars" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span> <strong><?= e($cfg['rating']) ?></strong> from <?= (int)$cfg['rating_count'] ?> Google reviews</a>
          </div>
        </div>
        <div class="col-lg-5">
          <div id="scene" class="scene" role="img" aria-label="Animated 3D wooden wardrobe whose doors open and close and turn as you scroll"></div>
        </div>
      </div>
    </div>
  </section>

  <div class="marquee" aria-hidden="true">
    <div class="marquee-track">
      <?php for ($r = 0; $r < 4; $r++): foreach ($tape as $t): ?>
        <span><?= e($t) ?></span><i>&#10033;</i>
      <?php endforeach; endfor; ?>
    </div>
  </div>

  <!-- About -->
  <section id="about" class="py-section">
    <div class="container">
      <div class="row g-5 align-items-center">
        <div class="col-md-5 col-lg-4 reveal">
          <div class="frame" data-tilt>
            <?php if ($hasPhoto): ?>
              <img src="<?= e($cfg['about_photo']) ?>" alt="Our workshop" loading="lazy">
            <?php else: ?>
              <img src="<?= e($cfg['about_image']) ?>" alt="<?= e($cfg['about_alt']) ?>" loading="lazy" referrerpolicy="no-referrer">
            <?php endif; ?>
          </div>
          <?php if (!$hasPhoto): ?>
            <p class="photo-credit"><a href="<?= e($cfg['about_credit_url']) ?>" target="_blank" rel="noopener"><?= e($cfg['about_credit']) ?></a></p>
          <?php endif; ?>
        </div>
        <div class="col-md-7 col-lg-8 reveal">
          <span class="kicker">About us</span>
          <p class="statement mt-3">Tell us what you need. <mark>We build it right.</mark></p>
          <p class="text-ink-2 fs-5 mt-3">Idealinno is a furniture maker and carpentry workshop in Olowora, Isheri, Lagos. Customers bring a sketch, a photo or just a size, and we turn it into wardrobes, kitchen cabinets and other pieces, with careful attention to detail. The business is led by <?= e($cfg['proprietor']) ?>, whom clients describe as courteous and professional.</p>
          <ul class="stats mt-5">
            <li><strong><?= e($cfg['rating']) ?>/5</strong><span>Google rating, <?= (int)$cfg['rating_count'] ?> reviews</span></li>
            <li><strong>Online estimates</strong><span>Know the cost first</span></li>
            <li><strong>By appointment</strong><span>Visit the workshop</span></li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- Services -->
  <section id="services" class="py-section services">
    <div class="container">
      <div class="reveal">
        <span class="kicker">What we build</span>
        <h2 class="mt-2 mb-5">Furniture for how you live</h2>
      </div>
      <div class="svc-list">
        <?php foreach ($services as $i => [$title, $text]): ?>
          <a class="svc reveal" href="#contact" style="--d:<?= $i * 60 ?>ms">
            <h3><?= e($title) ?></h3>
            <p><?= e($text) ?></p>
            <span class="go" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Process -->
  <section id="approach" class="py-section process">
    <div class="container">
      <div class="reveal">
        <span class="kicker kicker-light">How it works</span>
        <h2 class="mt-2 mb-5">From sketch to delivery</h2>
      </div>
      <div class="process-grid">
        <?php foreach ($steps as $i => [$title, $text]): ?>
          <div class="process-item reveal" style="--d:<?= $i * 80 ?>ms">
            <div class="no"><?= $i + 1 ?></div>
            <h3><?= e($title) ?></h3>
            <p><?= e($text) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php if ($gallery): ?>
  <section class="py-section" aria-label="Photos">
    <div class="container">
      <div class="reveal"><span class="kicker">Our work</span><h2 class="mt-2 mb-5">Recent pieces</h2></div>
      <div class="gallery">
        <?php foreach ($gallery as $path): ?>
          <figure class="reveal"><img src="assets/img/<?= e(basename($path)) ?>" alt="Furniture made by Idealinno" loading="lazy"></figure>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Reviews -->
  <section id="reviews" class="py-section reviews-bg">
    <div class="container">
      <div class="reveal">
        <span class="kicker" style="color:var(--ink)">Reviews</span>
        <h2 class="mt-2">Customers say it fits</h2>
        <p class="fs-5 mt-2 mb-5">Summarised from public Google reviews. <a href="<?= e($cfg['maps_url']) ?>" target="_blank" rel="noopener" style="color:var(--ink)">Read them all on Google</a>.</p>
      </div>
      <div class="row g-4">
        <?php foreach ($reviews as $i => [$who, $text]): ?>
          <div class="col-lg-4 reveal" style="--d:<?= $i * 80 ?>ms">
            <figure class="review" data-tilt>
              <div class="stars" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
              <blockquote><?= e($text) ?></blockquote>
              <figcaption><?= e($who) ?> <span>on Google</span></figcaption>
            </figure>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Contact -->
  <section id="contact" class="py-section contact">
    <div class="container">
      <div class="row g-5">
        <div class="col-lg-6 reveal">
          <span class="kicker kicker-light">Contact</span>
          <h2 class="mt-2">Let's build something.</h2>
          <a class="big-phone" href="<?= e($callUrl) ?>"><?= e($cfg['phone']) ?></a>
          <div class="d-flex flex-wrap gap-3 mb-4">
            <a class="btn btn-ember btn-lg" href="<?= e($waUrl) ?>" target="_blank" rel="noopener">WhatsApp us</a>
            <a class="btn btn-line-light btn-lg" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&amp;query=<?= e((string)$cfg['maps_lat']) ?>,<?= e((string)$cfg['maps_lng']) ?>">Get directions</a>
          </div>
          <dl class="contact-list">
            <div><dt>Address</dt><dd><?= e($cfg['address']) ?></dd></div>
            <div><dt>Hours</dt><dd><?= e($cfg['hours']) ?></dd></div>
            <?php if (!empty($cfg['email'])): ?>
              <div><dt>Email</dt><dd><?= e($cfg['email']) ?></dd></div>
            <?php endif; ?>
          </dl>
          <ul class="chips">
            <?php foreach ($good as $g): ?><li><?= e($g) ?></li><?php endforeach; ?>
          </ul>
        </div>
        <div class="col-lg-6 reveal">
          <form class="contact-form" method="post" action="#contact" novalidate>
            <h3 class="h4 mb-3">Or ask for an estimate</h3>
            <?php if ($result): ?>
              <div class="alert <?= $result['ok'] ? 'alert-success' : 'alert-danger' ?>" role="alert"><?= e($result['message']) ?></div>
            <?php endif; ?>
            <input type="hidden" name="form" value="contact">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div class="d-none" aria-hidden="true">
              <label for="website">Leave this field empty</label>
              <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="name">Your name</label>
                <input class="form-control" id="name" name="name" required maxlength="80" autocomplete="name" value="<?= e($old['name'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="email">Email</label>
                <input class="form-control" type="email" id="email" name="email" required maxlength="120" autocomplete="email" value="<?= e($old['email'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="phone">Phone (optional)</label>
                <input class="form-control" id="phone" name="phone" maxlength="40" autocomplete="tel" value="<?= e($old['phone'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="topic">What do you need?</label>
                <select class="form-select" id="topic" name="topic">
                  <option value="">Choose a topic</option>
                  <?php foreach ($topics as $t): ?>
                    <option <?= ($old['topic'] ?? '') === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label" for="message">Tell us about it (size, material, where it goes)</label>
                <textarea class="form-control" id="message" name="message" rows="4" required maxlength="3000"><?= e($old['message'] ?? '') ?></textarea>
              </div>
              <div class="col-12">
                <button class="btn btn-ink btn-lg" type="submit">Send message</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
