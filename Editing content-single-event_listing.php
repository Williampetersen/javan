<?php
// WP Event Manager - Platinumlist-inspired single event template (fully dynamic, no hardcoded/demo content)
global $post;
$event = $post;
$start_date = get_event_start_date();
$end_date   = get_event_end_date();
$start_time = get_event_start_time();
$end_time   = get_event_end_time();
$address    = get_event_address();
$location   = get_event_location();
$separator  = get_wpem_date_time_separator();
$check_ticket_visibility = get_option('event_manager_enable_event_ticket_prices_filter', true);
wp_enqueue_script('wp-event-manager-slick-script');
wp_enqueue_style('wp-event-manager-slick-style');
do_action('set_single_listing_view_count');

// Helper: show 'New' badge for events published in last 7 days
$is_new = (strtotime($event->post_date) > strtotime('-7 days'));

$venue_name = get_event_location();
$venue_addr = get_event_address();
$venue_lat  = function_exists('get_the_event_latitude')  ? get_the_event_latitude()  : '';
$venue_lng  = function_exists('get_the_event_longitude') ? get_the_event_longitude() : '';
$directions_url = $venue_addr
    ? 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($venue_addr)
    : '#';

if ($venue_lat && $venue_lng) {
    $map_src = "https://maps.google.com/maps?q={$venue_lat},{$venue_lng}&output=embed";
} elseif ($venue_addr) {
    $map_src = "https://maps.google.com/maps?q=" . urlencode($venue_addr) . "&output=embed";
} else {
    $map_src = '';
}

remove_action('single_event_sidebar_end', 'show_event_tags_sidebar');

// assume $start_date = get_event_start_date() and $start_time = get_event_start_time()
$event_timestamp_ms = strtotime("$start_date $start_time") * 1000;

// grab WP's localized date/time and turn it into "2025-09-28T00:00:00+02:00"
$start_dt = get_event_start_date() . ' ' . get_event_start_time();
$iso      = date( 'c', strtotime( $start_dt ) ); 

// Build an ISO-8601 timestamp for JS
$start_dt = get_event_start_date() . ' ' . get_event_start_time();
$iso_ts   = date( 'c', strtotime( $start_dt ) );
?>
<style>
/* --- Modern Platinumlist-inspired styles --- */
.event-hero-wrap { 
  position:relative; 
  width:100%; 
  max-width:1200px; 
  margin:0 auto 32px; 
  border-radius:18px; 
  overflow:hidden; 
  box-shadow:0 8px 32px rgba(30,42,80,0.10); 
  background:#fff; 
}
.event-hero-swiper { 
  width:100%; 
  height:500px; /* or 600px, or whatever height you want */
  background:#222; 
  display:flex; 
  align-items:center; 
  justify-content:center; 
}
.event-hero-swiper .swiper-slide {
  position: relative;
  width: 100%;
  height: 500px; /* match the swiper height */
  overflow: hidden;
  border-radius: 24px;
}
.event-hero-swiper .swiper-slide,
.media-wrapper,
.event-hero-img,
.event-hero-video {
  width: 100%;
  height: 100%;
  margin: 0;
  padding: 0;
  border-radius: 18px; /* Match .event-hero-wrap border-radius */
  object-fit: cover;
  background: #000;
  display: block;
}
.event-hero-video::-webkit-media-controls,
.event-hero-video::-webkit-media-controls-panel {
  display: none !important;
  opacity: 0 !important;
}
.event-hero-video::-webkit-media-controls-enclosure {
  display: none !important;
}
.event-hero-video::-webkit-media-controls-overlay-play-button {
  display: none !important;
}
@media (max-width:900px) { .event-hero-swiper, .event-hero-img, .event-hero-video, .event-hero-iframe { height:200px; } }
@media (max-width:600px) { .event-hero-swiper, .event-hero-img, .event-hero-video, .event-hero-iframe { height:120px; } }
.event-hero-badge { position:absolute; top:18px; left:18px; background:#ffd600; color:#222; font-weight:700; font-size:1em; border-radius:8px; padding:4px 16px; z-index:2; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
.event-hero-title { position:absolute; left:0; bottom:0; width:100%; background:linear-gradient(0deg,rgba(0,0,0,0.7) 60%,rgba(0,0,0,0.1) 100%); color:#fff; padding:2em 2em 1.2em 2em; font-size:2.1em; font-weight:700; z-index:2; }
@media (max-width:600px) { .event-hero-title { font-size:1.2em; padding:1em; } }

.event-main-row { display:flex; gap:2.5em; max-width:1200px; margin:0 auto; padding:0 1em 2em 1em; }
.event-main-left { flex:1; min-width:0; }
.event-main-right { width:340px; min-width:260px; max-width:340px; position:relative; }
@media (max-width:900px) { .event-main-row { flex-direction:column; gap:1.5em; } .event-main-right { width:100%; max-width:100%; min-width:0; } }

.event-card { background:#fff; border-radius:14px; box-shadow:0 2px 16px rgba(30,42,80,0.08); padding:2em 2em 1.5em 2em; margin-bottom:1.5em; }
@media (max-width:600px) { .event-card { padding:1em; } }

.event-ticket-box { position:sticky; top:32px; z-index:10; background:#f8fbff; border:1.5px solid #e3eaf5; border-radius:14px; box-shadow:0 2px 12px rgba(30,42,80,0.06); padding:1.5em 1.2em; margin-bottom:1.5em; }
.event-ticket-date { font-size:1.1em; color:#1976d2; font-weight:600; margin-bottom:0.5em; display:flex; align-items:center; gap:8px; }
.event-ticket-price { font-size:2em; font-weight:700; color:#1976d2; margin-bottom:0.7em; }
.event-ticket-btn { display:block; width:100%; background:#1976d2; color:#fff; font-size:1.1em; font-weight:600; border:none; border-radius:8px; padding:1em 0; margin-bottom:1em; transition:background 0.2s; cursor:pointer; text-align:center; text-decoration:none; }
.event-ticket-btn:hover { background:#125ea2; }

.event-info-list { list-style:none; padding:0; margin:0 0 1em 0; }
.event-info-list li { display:flex; align-items:center; gap:10px; font-size:1.05em; margin-bottom:0.5em; color:#444; }
.event-info-list i { color:#1976d2; font-size:1.2em; }

.event-accordion { border-radius:10px; overflow:hidden; margin-bottom:1.2em; box-shadow:0 2px 12px rgba(0,0,0,0.04); background:#fff; }
.event-accordion-header { background:#f7f7f7; padding:1em 1.2em; cursor:pointer; font-weight:600; font-size:1.1em; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #f0f0f0; }
.event-accordion-header.active { background:#e9ecef; }
.event-accordion-content { display:none; padding:1.2em; background:#fff; }
.event-accordion-content.active { display:block; }
.event-accordion-arrow { font-size:1.2em; transition:transform 0.2s; }
.event-accordion-header.active .event-accordion-arrow { transform:rotate(90deg); }

.event-section-title {
  font-size: 1.2em;
  font-weight: 600;
  margin-bottom: 0.7em;
}

.event-performer-card { display:flex; align-items:center; gap:1em; background:#f7f9fa; border-radius:10px; padding:1em; margin-top:1em; }
.event-performer-img { width:56px; height:56px; border-radius:50%; object-fit:cover; background:#e0e0e0; }
.event-performer-info { flex:1; }
.event-performer-name { font-weight:600; font-size:1.1em; }
.event-performer-play { color:#1976d2; font-size:1.5em; margin-left:0.5em; cursor:pointer; }

/* Responsive tweaks */
@media (max-width:600px) {
  .event-main-row { padding:0 0.2em 1em 0.2em; }
  .event-section-title { font-size:1em; }
  
}

.wpem-sidebar-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 0 10px rgba(0,0,0,0.05);
  padding: 20px;
  margin-bottom: 20px;
  border: 1px solid #e6e6e6;
  max-width: 340px;
  min-width: 220px;
  font-size: 15px;
  word-break: break-word;
}
.wpem-sidebar-card h3 {
  font-size: 18px;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.wpem-sidebar-card .wpem-icon-clock {
  font-size: 20px;
}
@media (max-width: 900px) {
  .wpem-single-event-right-content {
    max-width: 100%;
  }
  .wpem-sidebar-card {
    max-width: 100%;
    min-width: 0;
  }
}
@media (max-width: 600px) {
  .wpem-single-event-right-content {
    width: 100%;
    margin: 0 auto 20px auto;
  }
  .wpem-sidebar-card {
    max-width: 100%;
    min-width: 0;
    padding: 16px;
    font-size: 14px;
  }
  .wpem-sidebar-card h3 {
    font-size: 16px;
  }
}

/* Custom Sidebar Cards: Date & Start, Change Plans Note */
.event-sidebar-card,
.event-sidebar-note-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  padding: 16px;
  margin-bottom: 16px;
  max-width: 340px;
  min-width: 280px;
}
.event-sidebar-date,
.event-sidebar-start { display: flex; align-items: center; gap: 6px; }
.event-sidebar-date { font-size: 0.9em; color: #1976d2; margin-bottom: 8px; }
.event-sidebar-start { font-size: 1em; color: #444; }
.event-sidebar-note-card { display: flex; align-items: flex-start; gap: 12px; background: #f3f3ff; border: 1px solid #e0e0ff; }
.event-sidebar-note-card i { font-size: 1.4em; color: #8e44ad; margin-top: 4px; }
.event-sidebar-note-text strong { display: block; color: #8e44ad; margin-bottom: 4px; }
.event-sidebar-note-text p { margin: 0; font-size: 0.95em; color: #555; }
@media (max-width: 900px) {
  .wpem-single-event-right-content {
    max-width: 100%;
  }
  .event-sidebar-card,
  .event-sidebar-note-card {
    max-width: 100%;
    min-width: 0;
  }
}
@media (max-width: 600px) {
  .wpem-single-event-right-content {
    width: 100%;
    margin: 0 auto 20px auto;
  }
  .event-sidebar-card,
  .event-sidebar-note-card {
    max-width: 100%;
    min-width: 0;
    padding: 12px;
    font-size: 15px;
  }
  .event-sidebar-date { font-size: 1em; }
  .event-sidebar-start { font-size: 1em; }
  .event-sidebar-note-card i { font-size: 1.2em; }
  .event-sidebar-note-text strong { font-size: 1em; }
  .event-sidebar-note-text p { font-size: 0.98em; }
}

.wpem-event-title-main {
  font-size: 2em;
  font-weight: 700;
  margin-bottom: 0.5em;
  color: #222;
}

.location-card {
  display: flex;
  gap: 16px;
  margin-bottom: 16px;
}
.location-card__icon {
  flex: 0 0 72px;
  height: 72px;
  background: #fff;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.location-card__icon i {
  font-size: 24px;
  color: #1976d2;
}
.location-card__content {
  flex: 1;
  background: #F5F7FA;
  border-radius: 12px;
  padding: 16px;
}
.location-card__content a {
  color: #1976d2;
  font-weight: 600;
  text-decoration: none;
  font-size: 1em;
}
.location-card__content p {
  margin: 8px 0;
  color: #444;
  font-size: 0.95em;
}
.location-card__content .directions-link {
  display: inline-flex;
  align-items: center;
  font-size: 0.9em;
  color: #1976d2;
  text-decoration: none;
}
.location-card__content .directions-link i {
  margin-left: 4px;
}
@media (max-width: 600px) {
  .location-card { flex-direction: column; gap: 8px; }
  .location-card__icon { margin-bottom: 8px; }
}

.swiper {
  width: 100%;
  max-width: 1000px;
  margin: 0 auto 40px;
}
.swiper-slide {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 350px;
  border-radius: 24px;
  overflow: hidden;
}
.video-wrapper {
  width: 100%;
  height: 100%;
  border-radius: 24px;
  overflow: hidden;
}
.clean-video {
  width: 100%;
  height: 100%;
  border-radius: 24px;
  object-fit: cover;
  pointer-events: none;
  background: #000;
}
.clean-video::-webkit-media-controls,
.clean-video::-webkit-media-controls-enclosure,
.clean-video::-webkit-media-controls-panel,
.clean-video::-webkit-media-controls-play-button,
.clean-video::-webkit-media-controls-timeline,
.clean-video::-webkit-media-controls-current-time-display,
.clean-video::-webkit-media-controls-time-remaining-display,
.clean-video::-webkit-media-controls-seek-back-button,
.clean-video::-webkit-media-controls-seek-forward-button,
.clean-video::-webkit-media-controls-fullscreen-button,
.clean-video::-webkit-media-controls-volume-slider,
.clean-video::-webkit-media-controls-mute-button,
.clean-video::-moz-media-controls,
.clean-video::-ms-media-controls {
  display: none !important;
  opacity: 0 !important;
  visibility: hidden !important;
  height: 0 !important;
  width: 0 !important;
}
@media (max-width:900px) { .swiper-slide, .video-wrapper, .clean-video { height:200px; } }
@media (max-width:600px) { .swiper-slide, .video-wrapper, .clean-video { height:120px; } }

.event-hero-wrap,
.event-hero-swiper .swiper-slide,
.media-wrapper,
.event-hero-img,
.event-hero-video {
  border-radius: 12;
}

/* common arrow styles */
.custom-arrow {
  position: absolute;
  top: 50%;
  width: 48px;
  height: 48px;
  margin-top: -24px;
  background: url('/wp-content/themes/your-theme/images/arrow-circle.png') no-repeat center;
  background-size: contain;
  cursor: pointer;
  z-index: 5;
  opacity: 0.8;
  transition: opacity .2s;
  transform: none;
}
.custom-arrow:hover { opacity: 1; }
.swiper-button-prev.custom-arrow {
  left: 16px;
  transform: rotate(0deg);
}
.swiper-button-next.custom-arrow {
  right: 16px;
  transform: rotate(0deg);
}

/* Sidebar countdown styling */
.sidebar-countdown {
  display: flex;
  justify-content: space-between;
  background: #fff;
  border: 1px solid #e6e6e6;
  border-radius: 6px;
  padding: .5em;
  margin-top: .5em;
}
.sidebar-countdown > div {
  flex: 1;
  text-align: center;
}
.cd-label {
  display: block;
  font-size: .75em;
  color: #666;
  margin-top: .2em;
}

/* Hide original countdown */
#wpem-countdown { 
  display: none !important; 
}

/* Style the cloned countdown in sidebar */
#sidebar-event-countdown {
  margin-top: 1em;
  padding: 1em;
  background: #fff;
  border: 1px solid #e6e6e6;
  border-radius: 6px;
}

/* New Countdown Design */
.countdown-container {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  background: #e8f0ff;
  padding: 20px;
  border-radius: 8px;
  margin: 1em 0;
}
.countdown-left {
  display: flex;
  align-items: center;
  margin-right: 30px;
}
.countdown-icon {
  font-size: 32px;
  margin-right: 12px;
  color: #0073e6;
}
.countdown-title {
  font-size: 18px;
  font-weight: bold;
  color: #0073e6;
}
.countdown-subtitle {
  font-size: 14px;
  color: #555;
}
.countdown-timer {
  display: flex;
  gap: 10px;
}
.time-box {
  text-align: center;
  padding: 10px;
  min-width: 60px;
  background: #fff;
  border-radius: 6px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.time-box span {
  display: block;
}
.time-box .time-label {
  margin-top: 4px;
  font-size: 10px;
  color: #666;
}
@media (max-width: 600px) {
  .countdown-container {
    padding: 15px;
  }
  .countdown-left {
    margin-right: 15px;
  }
  .countdown-icon {
    font-size: 24px;
  }
  .countdown-title {
    font-size: 16px;
  }
  .countdown-subtitle {
    font-size: 12px;
  }
  .time-box {
    padding: 8px;
    min-width: 50px;
  }
}
</style>

<div class="single_event_listing">
  <div class="wpem-main wpem-single-event-page">
    <?php if (get_option('event_manager_hide_expired_content', 1) && 'expired' === $post->post_status) : ?>
      <div class="wpem-alert wpem-alert-danger"><?php esc_html_e('This listing has been expired.', 'wp-event-manager'); ?></div>
    <?php else : if (is_event_cancelled()) : ?>
      <div class="wpem-alert wpem-alert-danger">
        <span class="event-cancelled"><?php esc_html_e('This event has been cancelled.', 'wp-event-manager'); ?></span>
      </div>
    <?php elseif (!attendees_can_apply() && 'preview' !== $post->post_status) : ?>
      <div class="wpem-alert wpem-alert-danger">
        <span class="listing-expired"><?php esc_html_e('Registrations have closed.', 'wp-event-manager'); ?></span>
      </div>
    <?php endif; do_action('single_event_listing_start'); ?>

    <!-- HERO SECTION -->
    <div class="event-hero-wrap">
      <?php
      $image_1 = get_field('event_image_1');
      $video_1 = get_field('event_video_1');
      $event_banners = get_event_banner();
      ?>
      <div class="event-hero-swiper swiper mySwiper">
        <div class="swiper-wrapper">
          <?php if (!empty($image_1)) : ?>
            <div class="swiper-slide">
              <div class="media-wrapper">
                <img class="event-hero-img" src="<?php echo esc_url($image_1['url']); ?>" alt="Event Image" />
              </div>
            </div>
          <?php endif; ?>
          <?php if (!empty($video_1)) :
            $video_ext = pathinfo(parse_url($video_1, PHP_URL_PATH), PATHINFO_EXTENSION);
            $is_direct_video = in_array(strtolower($video_ext), ['mp4', 'webm', 'ogg']); ?>
            <div class="swiper-slide">
              <div class="media-wrapper">
                <video 
                  class="event-hero-video"
                  autoplay 
                  muted 
                  playsinline 
                  preload="metadata"
                  loop
                  poster="<?php echo !empty($image_1) ? esc_url($image_1['url']) : ''; ?>"
                >
                  <source src="<?php echo esc_url($video_1); ?>" type="video/<?php echo esc_attr($video_ext); ?>">
                  Your browser does not support the video tag.
                </video>
              </div>
            </div>
          <?php endif; ?>
          <?php if (is_array($event_banners) && sizeof($event_banners) >= 1) :
            foreach ($event_banners as $banner_value) : ?>
              <div class="swiper-slide">
                <div class="media-wrapper">
                  <img class="event-hero-img" src="<?php echo esc_url($banner_value); ?>" alt="<?php the_title(); ?>" />
                </div>
              </div>
            <?php endforeach; endif; ?>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev custom-arrow"></div>
        <div class="swiper-button-next custom-arrow"></div>
        <?php if ($is_new): ?><div class="event-hero-badge">New</div><?php endif; ?>
        <!-- <div class="event-hero-title"><?php the_title(); ?></div> -->
      </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
      const swiper = new Swiper('.mySwiper', {
        loop: true,
        effect: 'fade',
        fadeEffect: { crossFade: true },
        allowTouchMove: false,
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
      });
      // --- Custom async/await sequence logic ---
      const slides = Array.from(swiper.slides);
      const videoSlideIndex  = slides.findIndex(s => s.querySelector('video'));
      const bannerSlideIndex = slides.findIndex(s => s.querySelector('img.event-hero-img'));
      const videoEl = document.querySelector('.event-hero-video');

      function resetVideos() {
        document.querySelectorAll('.event-hero-video').forEach(v => {
          v.pause();
          v.currentTime = 0;
        });
      }
      function showBanner(ms) {
        resetVideos();
        swiper.slideToLoop(bannerSlideIndex);
        return new Promise(res => setTimeout(res, ms));
      }
      function showVideo(ms, pauseAfter) {
        resetVideos();
        swiper.slideToLoop(videoSlideIndex);
        return new Promise(res => {
          videoEl.currentTime = 0;
          videoEl.play().catch(() => {});
          setTimeout(() => {
            if (pauseAfter) videoEl.pause();
            res();
          }, ms);
        });
      }
      async function runSequence() {
        // wait for video metadata so duration is valid
        if (!videoEl.duration || isNaN(videoEl.duration)) {
          await new Promise(r => videoEl.addEventListener('loadedmetadata', r, { once: true }));
        }
        while (true) {
          await showBanner(3000);                               // 3 s image
          await showVideo(8000, true);                          // 8 s video → pause
          await showBanner(5000);                               // 5 s image
          await showVideo(videoEl.duration * 1000, false);      // full video
          await showBanner(3000);                               // 3 s image
        }
      }
      runSequence();
    });
    </script>

    <!-- MAIN CONTENT ROW -->
    <div class="event-main-row">
      <!-- LEFT COLUMN -->
      <div class="event-main-left">
        <div class="event-card">
          <h2 class="wpem-event-title-main" style="margin-bottom: 0.5em;"><?php the_title(); ?></h2>

         
          <!-- Date and Time each on their own line -->
          <div class="wpem-event-date-row" style="display:flex;align-items:center;gap:4px;color:#1976d2;font-size:1em;margin-bottom:4px;">
            <i class="wpem-icon-calendar"></i> <?php echo esc_html(date_i18n('D d M', strtotime($start_date))); ?>
          </div>
          <div class="wpem-event-time-row" style="display:flex;align-items:center;gap:4px;color:#1976d2;font-size:1em;margin-bottom:12px;">
            <i class="wpem-icon-clock"></i> <b><?php esc_html_e('Start:', 'wp-event-manager'); ?></b> <?php echo esc_html($start_time); ?>
          </div>
          <!-- Short Description (excerpt) -->
          <?php if (!empty($event->post_excerpt)): ?>
            <div style="color:#555; font-size:1.1em; margin-bottom:1em;"> <?php echo esc_html($event->post_excerpt); ?> </div>
          <?php endif; ?>
          <!-- Full Description -->
          <div class="event-section-title"><?php esc_html_e('About this event', 'wp-event-manager'); ?></div>
          <div style="margin-bottom:1.5em; color:#333; line-height:1.7;">
            <?php
            do_action( 'single_event_overview_start' );
            echo wp_kses_post( wpautop( apply_filters( 'wpem_the_content', $event->post_content ) ) );
            do_action( 'single_event_overview_end' );
            ?>
          </div>
          <!-- Event description section end-->
          <?php
              // … earlier you have:
              $venue_name    = get_event_location();
              $venue_address = get_event_address();
            
              // instead of “/dir/?api=1” we’ll just search the address:
              $directions_url = $venue_address
                ? 'https://www.google.com/maps?q=' . urlencode( $venue_address )
                : '#';
            
              // (you can still drop in your saved iframe if you like)
              $map_url = get_post_meta( $event->ID, '_google_map_embed', true );
            ?>
            
            <!-- Section title -->
            <h2 class="event-section-title">Location</h2>
            
            <!-- Card wrapper -->
            <div class="event-card location-card-wrapper">
            
              <!-- Icon + Venue info -->
              <div class="location-card">
                <div class="location-card__icon">
                  <i class="wpem-icon-location"></i>
                </div>
                <div class="location-card__content">
                  <?php if ( $venue_name ) : ?>
                    <a href="<?php echo esc_url( $directions_url ); ?>"
                       class="location-card__venue"
                       target="_blank"
                       rel="noopener">
                      <?php echo esc_html( $venue_name ); ?>
                    </a>
                  <?php endif; ?>
            
                  <?php if ( $venue_address ) : ?>
                    <div class="location-card__address">
                      <?php echo nl2br( esc_html( $venue_address ) ); ?>
                    </div>
                  <?php endif; ?>
            
                  <a href="<?php echo esc_url( $directions_url ); ?>"
                     class="location-card__directions"
                     target="_blank"
                     rel="noopener">
                    View directions <i class="wpem-icon-arrow-right"></i>
                  </a>
                </div>
              </div>
            
              <!-- Optional Map embed (unchanged) -->
              <?php if ( $map_url ) : ?>
                <div class="location-card__map">
                  <iframe
                    src="<?php echo esc_url( $map_url ); ?>"
                    width="100%"
                    height="350"
                    style="border:0;border-radius:12px;"
                    loading="lazy"
                    allowfullscreen
                    referrerpolicy="no-referrer-when-downgrade"
                  ></iframe>
                </div>
              <?php endif; ?>
            
            </div>



          <!-- Accordions: Important Note, Policies, Terms -->
          <?php
          // Example: Use custom fields or meta for these sections
          $important_note = get_post_meta($event->ID, '_event_important_note', true);
          $event_policies = get_post_meta($event->ID, '_event_policies', true);
          $event_terms    = get_post_meta($event->ID, '_event_terms', true);
          ?>
          <?php if ($important_note): ?>
            <div class="event-accordion">
              <div class="event-accordion-header" onclick="toggleAccordion(this)">
                <?php esc_html_e('Important Note', 'wp-event-manager'); ?> <span class="event-accordion-arrow">▶</span>
              </div>
              <div class="event-accordion-content"> <?php echo wp_kses_post($important_note); ?> </div>
            </div>
          <?php endif; ?>
          <?php if ($event_policies): ?>
            <div class="event-accordion">
              <div class="event-accordion-header" onclick="toggleAccordion(this)">
                <?php esc_html_e('Event Policies', 'wp-event-manager'); ?> <span class="event-accordion-arrow">▶</span>
              </div>
              <div class="event-accordion-content"> <?php echo wp_kses_post($event_policies); ?> </div>
            </div>
          <?php endif; ?>
          <?php if ($event_terms): ?>
            <div class="event-accordion">
              <div class="event-accordion-header" onclick="toggleAccordion(this)">
                <?php esc_html_e('Terms & Conditions', 'wp-event-manager'); ?> <span class="event-accordion-arrow">▶</span>
              </div>
              <div class="event-accordion-content"> <?php echo wp_kses_post($event_terms); ?> </div>
            </div>
          <?php endif; ?>
          
          <!-- Performer/Artist Info (if available) -->
          <?php $performer_img = get_post_meta($event->ID, '_event_performer_img', true);
                $performer_name = get_post_meta($event->ID, '_event_performer_name', true);
                $performer_track = get_post_meta($event->ID, '_event_performer_track', true);
          if ($performer_name): ?>
            <div class="event-performer-card">
              <?php if ($performer_img): ?><img class="event-performer-img" src="<?php echo esc_url($performer_img); ?>" alt="<?php echo esc_attr($performer_name); ?>" /><?php endif; ?>
              <div class="event-performer-info">
                <div class="event-performer-name"><?php echo esc_html($performer_name); ?></div>
                <?php if ($performer_track): ?>
                  <a class="event-performer-play" href="<?php echo esc_url($performer_track); ?>" target="_blank" title="Play"><i class="wpem-icon-play"></i></a>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <!-- RIGHT COLUMN (SIDEBAR) -->
      <div class="event-main-right">
        <?php do_action('single_event_sidebar_start'); ?>
            <div class="event-ticket-box custom-wrapper" style="display: flex; flex-direction: column; gap: 10px; padding: 14px;">
            <!-- Section Title -->
            <div style="display: flex; align-items: center; gap: 8px; font-size: 1.1em; color: #1976d2; font-weight: 600; margin-bottom: 8px;">
                <i class="wpem-icon-calendar"></i>
                <?php esc_html_e('Date & Time', 'wp-event-manager'); ?>
            </div>
        
            <!-- Event Date -->
            <div style="display: flex; align-items: center; gap: 6px; font-size: 1em; color: #1976d2;">
                <i class="wpem-icon-calendar"></i>
                <span>
                    <?php echo esc_html(date_i18n('D d M', strtotime($start_date))); ?>
                </span>
            </div>
            
            <!-- Event Start Time -->
            <div style="display: flex; align-items: center; gap: 6px; font-size: 1em; color: #1976d2;">
                <i class="wpem-icon-clock"></i>
                <span>
                    <b><?php esc_html_e('Start:', 'wp-event-manager'); ?></b>
                    <?php echo esc_html($start_time); ?>
                </span>
            </div>
            
            <!-- Event End Time (optional) -->
            <?php if ($end_time): ?>
            <div style="display: flex; align-items: center; gap: 6px; font-size: 1em; color: #1976d2;">
                <i class="wpem-icon-clock"></i>
                <span>
                    <b><?php esc_html_e('Ends:', 'wp-event-manager'); ?></b>
                    <?php echo esc_html($end_time); ?>
                </span>
            
            <?php endif; ?>
        
         </div>
            
        <?php do_action('single_event_sidebar_end'); ?>

</div>
        <?php
        $artists = [];
        
        // build a uniform array for artist 1–3
        for ( $i = 1; $i <= 3; $i++ ) {
            if ( $i === 1 ) {
                $img_key        = '_artist_img';
                $name_key       = '_artist_name';
                $link_key       = '_artist_link';
                $track_key      = '_artist_track';
                $track_name_key = '_artist_track_name';
            } else {
                $img_key        = "_artist_{$i}_image";
                $name_key       = "_artist_{$i}_name";
                $link_key       = "_artist_{$i}_link";
                $track_key      = "_artist{$i}_track";
                $track_name_key = "_artist{$i}_track_name";
            }
        
            $img        = get_post_meta( $event->ID, $img_key,        true );
            $name       = get_post_meta( $event->ID, $name_key,       true );
            $link       = get_post_meta( $event->ID, $link_key,       true );
            $track_url  = get_post_meta( $event->ID, $track_key,      true );
            $track_name = get_post_meta( $event->ID, $track_name_key, true );
        
            if ( $img && $name ) {
                $artists[] = compact( 'img','name','link','track_url','track_name' );
            }
        }
        
        if ( ! empty( $artists ) ) : ?>
          <div class="event-sidebar-card">
            <div class="event-section-title" style="margin-top:0;">
              <?php esc_html_e( 'Artists', 'wp-event-manager' ); ?>
            </div>
        
            <?php foreach ( $artists as $index => $artist ) :
              // give each audio element a unique ID
              $audio_id = 'artist-audio-' . $index;
            ?>
              <div class="artist-card" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        
                <div style="display:flex;align-items:center;gap:12px;">
                  <img
                    src="<?php echo esc_url( $artist['img'] ); ?>"
                    alt="<?php echo esc_attr( $artist['name'] ); ?>"
                    style="width:48px;height:48px;border-radius:50%;object-fit:cover;"
                  />
        
                  <div>
                    <?php if ( $artist['link'] ) : ?>
                      <a 
                        href="<?php echo esc_url( $artist['link'] ); ?>" 
                        target="_blank" 
                        style="font-weight:600;color:#222;text-decoration:none;"
                      >
                        <?php echo esc_html( $artist['name'] ); ?>
                      </a>
                    <?php else : ?>
                      <div style="font-weight:600;color:#222;">
                        <?php echo esc_html( $artist['name'] ); ?>
                      </div>
                    <?php endif; ?>
        
                    <?php if ( $artist['track_name'] ) : ?>
                      <div style="font-size:0.9em;color:#666;">
                        <?php echo esc_html( $artist['track_name'] ); ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
        
                <div style="display:flex;align-items:center;gap:16px;">
                  <!-- ♥ Favorite button -->
                  <div style="display:flex;align-items:center;gap:16px;">
                  <?php
                    // zero-based $index → slot 1,2,3
                    $slot  = $index + 1;
                    $likes = (int) get_post_meta( $event->ID, "_artist_{$slot}_likes", true );
                  ?>
                  <button
                    class="artist-like-btn<?php echo $likes ? ' liked' : '';?>"
                    data-event="<?php echo $event->ID;?>"
                    data-artist="<?php echo $slot;?>"
                    style="background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:4px;font-size:1em;"
                  >
                    <i class="wpem-icon-heart"></i>
                    <span class="like-count"><?php echo $likes;?></span>
                  </button>
                
                  <!-- your existing play/pause button stays here -->
                  
                </div>

        
                  <?php if ( $artist['track_url'] ) : ?>
                    <!-- ▶ Play/Pause button -->
                    <button
                      type="button"
                      class="artist-play-btn"
                      data-audio-id="<?php echo esc_attr( $audio_id ); ?>"
                      aria-label="<?php esc_attr_e( 'Play track', 'wp-event-manager' ); ?>"
                      style="background:none;border:none;cursor:pointer;"
                    >
                      <i class="wpem-icon-play" aria-hidden="true"></i>
                    </button>
        
                    <!-- hidden audio element -->
                    <audio id="<?php echo esc_attr( $audio_id ); ?>" preload="none">
                      <source src="<?php echo esc_url( $artist['track_url'] ); ?>" />
                    </audio>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        
          <style>
            .artist-like-btn .wpem-icon-heart { color: #ccc; transition: color .2s; }
            .artist-like-btn.liked .wpem-icon-heart { color: #e74c3c; }
            .artist-like-btn.liked .like-count { font-weight:600; }
            .artist-like-btn.processing { opacity: .6; pointer-events: none; }

            /* Heart hover */
            .artist-fav i {
              font-size: 1.2em;
              color: #ccc;
              transition: transform .15s, color .15s;
            }
            .artist-fav:hover i {
              transform: scale(1.3);
              color: #e33;
            }
            /* Play button styling */
            .artist-play-btn i {
              font-size: 1.2em;
              color: #1976d2;
              transition: color .15s;
            }
            .artist-play-btn:hover i {
              color: #125ea2;
            }
            
          </style>
        
          <script>
            document.addEventListener('DOMContentLoaded', function(){
              // keep track of the one playing audio so we can pause it
              let currentAudio = null;
              let currentBtn   = null;
        
              document.querySelectorAll('.artist-play-btn').forEach( btn => {
                btn.addEventListener('click', function(){
                  const audio = document.getElementById(this.dataset.audioId);
                  if (!audio) return;
        
                  // if clicking a new track, pause the old one
                  if ( currentAudio && currentAudio !== audio ) {
                    currentAudio.pause();
                    if ( currentBtn ) {
                      currentBtn.querySelector('i').className = 'wpem-icon-play';
                    }
                  }
        
                  if ( audio.paused ) {
                    audio.play();
                    this.querySelector('i').className = 'wpem-icon-pause';
                    currentAudio = audio;
                    currentBtn   = this;
                  } else {
                    audio.pause();
                    this.querySelector('i').className = 'wpem-icon-play';
                  }
                });
              });
        
              // stop playback when leaving page or reloading
              window.addEventListener('pagehide', () => {
                if ( currentAudio ) currentAudio.pause();
              });
            });
          </script>
        <?php endif; ?>
        <?php
        // --- DJs Section -----------------------------------------------------------
        // build uniform array of up to 3 DJs
        $djs = [];
        for ( $i = 1; $i <= 3; $i++ ) {
            // meta-keys for each DJ
            $img_key  = ( $i === 1 ) ? '_dj_img'  : "_dj_img{$i}";
            $name_key = ( $i === 1 ) ? '_dj_name' : "_dj_name{$i}";
            $link_key = ( $i === 1 ) ? '_dj_link' : "_dj_link{$i}";
        
            $img  = get_post_meta( $event->ID, $img_key,  true );
            $name = get_post_meta( $event->ID, $name_key, true );
            $link = get_post_meta( $event->ID, $link_key, true );
        
            if ( $img && $name ) {
                $djs[] = compact( 'img','name','link' );
            }
        }
        
        if ( ! empty( $djs ) ) : ?>
          <div class="event-sidebar-card">
            <div class="event-section-title" style="margin-top:0;">
              <?php esc_html_e( 'Dj', 'wp-event-manager' ); ?>
            </div>
        
            <?php foreach ( $djs as $dj ) : ?>
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
        
                <img
                  src="<?php echo esc_url( $dj['img'] ); ?>"
                  alt="<?php echo esc_attr( $dj['name'] ); ?>"
                  style="width:48px;height:48px;border-radius:50%;object-fit:cover;"
                />
        
                <div>
                  <?php if ( ! empty( $dj['link'] ) ) : ?>
                    <a
                      href="<?php echo esc_url( $dj['link'] ); ?>"
                      target="_blank"
                      style="font-weight:bold;color:#333;text-decoration:none;"
                    >
                      <?php echo esc_html( $dj['name'] ); ?>
                    </a>
                  <?php else : ?>
                    <div style="font-weight:bold;color:#333;">
                      <?php echo esc_html( $dj['name'] ); ?>
                    </div>
                  <?php endif; ?>
                </div>
        
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

                <?php
        $organizer_img      = get_post_meta($event->ID, '_organizer_img', true);
        $organizer_name     = get_post_meta($event->ID, '_organizer_name', true);
        $organizer_link     = get_post_meta($event->ID, '_organizer_link', true);
        $organizer_fb       = get_post_meta($event->ID, '_organizer_fb', true);
        $organizer_instagram= get_post_meta($event->ID, '_organizer_instagram', true);
        $organizer_website  = get_post_meta($event->ID, '_organizer_website', true);
        ?>

<?php if ($organizer_img && $organizer_name): ?>
  <div class="event-sidebar-card">
    <div class="event-section-title" style="margin-top: 0;"><?php esc_html_e('Organizer', 'wp-event-manager'); ?></div>
    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
      <img src="<?php echo esc_url($organizer_img); ?>" alt="<?php echo esc_attr($organizer_name); ?>" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;">
      <div>
        <?php if (!empty($organizer_link)): ?>
          <a href="<?php echo esc_url($organizer_link); ?>" target="_blank" style="font-weight: bold; color: #333;"><?php echo esc_html($organizer_name); ?></a>
        <?php else: ?>
          <div style="font-weight: bold; color: #333;"><?php echo esc_html($organizer_name); ?></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Social Links -->
    <div style="display: flex; gap: 10px; flex-wrap: wrap; font-size: 1.2em;">
      <?php if (!empty($organizer_fb)): ?>
        <a href="<?php echo esc_url($organizer_fb); ?>" target="_blank" title="Facebook" style="color: #1877f2;"><i class="wpem-icon-facebook"></i></a>
      <?php endif; ?>
      <?php if (!empty($organizer_instagram)): ?>
        <a href="<?php echo esc_url($organizer_instagram); ?>" target="_blank" title="Instagram" style="color: #e4405f;"><i class="wpem-icon-instagram"></i></a>
      <?php endif; ?>
      <?php if (!empty($organizer_website)): ?>
        <a href="<?php echo esc_url($organizer_website); ?>" target="_blank" title="Website" style="color: #1976d2;"><i class="wpem-icon-globe"></i></a>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

        <!-- Social Share -->
        <div class="event-card">
          <div class="event-section-title"><?php esc_html_e('Share this event', 'wp-event-manager'); ?></div>
          <div style="display:flex; gap:0.7em; flex-wrap:wrap;">
            <a class="wpem-social-icon wpem-facebook" href="https://www.facebook.com/sharer/sharer.php?u=<?php display_event_permalink(); ?>" title="Share on Facebook" target="_blank"><i class="wpem-icon-facebook"></i></a>
            <a class="wpem-social-icon wpem-twitter" href="https://twitter.com/share?url=<?php display_event_permalink(); ?>" title="Share on Twitter" target="_blank"><i class="wpem-icon-twitter"></i></a>
            <a class="wpem-social-icon wpem-linkedin" href="https://www.linkedin.com/sharing/share-offsite/?&url=<?php display_event_permalink(); ?>" title="Share on Linkedin" target="_blank"><i class="wpem-icon-linkedin"></i></a>
            <a class="wpem-social-icon wpem-pinterest" href="https://pinterest.com/pin/create/button/?url=<?php display_event_permalink(); ?>" title="Share on Pinterest" target="_blank"><i class="wpem-icon-pinterest"></i></a>
          </div>
        </div>
        <div class="wpem-col-xs-12 wpem-col-sm-5 wpem-col-md-4 wpem-single-event-right-content">
          <div class="wpem-single-event-body-sidebar">
            <!-- Remove Event Tags box from sidebar -->
            <!-- Custom Sidebar Cards: Date & Start, Change Plans Note -->
            <div class="event-sidebar-note-card">
              <i class="wpem-icon-sync"></i>
              <div class="event-sidebar-note-text">
                <strong><?php esc_html_e("It's okay to change plans!", 'wp-event-manager'); ?></strong>
                <p><?php esc_html_e("If you can't make it, you can always exchange your ticket with another fan.", 'wp-event-manager'); ?></p>
              </div>
            </div>
            <!-- End Custom Sidebar Cards -->
          </div>
        </div>
      </div>
    </div>
    <script>
    // Accordion toggle
    function toggleAccordion(header) {
      var content = header.nextElementSibling;
      var isActive = header.classList.contains('active');
      document.querySelectorAll('.event-accordion-header').forEach(function(h){ h.classList.remove('active'); });
      document.querySelectorAll('.event-accordion-content').forEach(function(c){ c.classList.remove('active'); });
      if (!isActive) { header.classList.add('active'); content.classList.add('active'); }
    }
    </script>
    <?php do_action('single_event_listing_end'); ?>
    <?php endif; ?>
  </div>
</div>




