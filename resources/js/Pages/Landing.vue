<template>
  <div class="page-root">
    <!-- Texture overlay -->
    <div class="grain-overlay" aria-hidden="true"></div>

    <AppHeader title="Selamat Datang" subtitle="Sajian lezat pilihan kami" />

    <main>
      <!-- Hero Section -->
      <section class="hero">
        <div class="hero-inner">
          <div class="hero-badge">✦ Buka Setiap Hari</div>
          <h1 class="hero-name">{{ company.name }}</h1>
          <p class="hero-tagline">{{ company.tagline }}</p>
          <p class="text-grey">Di setiap mangkuk Bakmi Mas Agus, tersimpan dedikasi untuk menghadirkan rasa yang tidak hanya lezat, tetapi juga berkesan. Kami percaya bahwa makanan terbaik adalah yang mampu menghadirkan kehangatan dan kebersamaan.

Menggunakan bahan berkualitas dan racikan bumbu khas, kami menghadirkan bakmi yang menggugah selera sekaligus menghadirkan rasa “rumah” di setiap suapan.</p>
        </div>
        <div class="hero-image-wrap">
          <div class="hero-image-frame">
            <img :src="'/images/logo.jpeg'" alt="hero" class="hero-image" />
          </div>
          <div class="hero-image-accent" aria-hidden="true"></div>
        </div>
      </section>

      <!-- Divider -->
      <div class="section-divider">
        <span>✦</span>
        <span>✦</span>
        <span>✦</span>
      </div>

      <!-- Info Section -->
      <section class="info-grid">
        <div class="info-card info-card--wide">
          <span class="info-label">Tentang Kami</span>
          <p class="info-desc">{{ company.description }}</p>
        </div>

        <div class="info-card">
          <span class="info-label">Alamat</span>
          <p class="info-value">{{ company.address }}</p>
        </div>

        <div class="info-card">
          <span class="info-label">Telepon</span>
          <p class="info-value">{{ company.phone }}</p>
        </div>

        <div class="info-card info-card--hours">
          <span class="info-label">Jam Buka</span>
          <ul class="hours-list">
            <li v-for="(hours, day) in company.opening_hours" :key="day">
              <span class="hours-day">{{ day }}</span>
              <span class="hours-time">{{ hours }}</span>
            </li>
          </ul>
        </div>
      </section>
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'

const props = defineProps({
  company: {
    type: Object,
    default: () => ({}),
  },
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap');

/* ── Root & Theme ─────────────────────────────────── */
.page-root {
  min-height: 100vh;
  background-color: #fef3e2;
  font-family: 'DM Sans', sans-serif;
  color: #2c1a09;
  position: relative;
  overflow-x: hidden;
}

/* Grain texture */
.grain-overlay {
  pointer-events: none;
  position: fixed;
  inset: 0;
  z-index: 0;
  opacity: 0.03;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
}

/* ── Hero ─────────────────────────────────────────── */
.hero {
  position: relative;
  z-index: 1;
  max-width: 1100px;
  margin: 0 auto;
  padding: 80px 40px 60px;
  display: flex;
  align-items: center;
  gap: 64px;
}

.hero-inner {
  flex: 1;
  min-width: 0;
}

.hero-badge {
  display: inline-block;
  font-family: 'DM Sans', sans-serif;
  font-size: 11px;
  font-weight: 500;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: #92400e;
  background: rgba(146, 64, 14, 0.08);
  border: 1px solid rgba(146, 64, 14, 0.2);
  padding: 5px 14px;
  border-radius: 100px;
  margin-bottom: 28px;
  animation: fadeUp 0.5s ease both;
}

.hero-name {
  font-family: 'Cormorant Garamond', serif;
  font-size: clamp(3rem, 7vw, 5.5rem);
  font-weight: 600;
  line-height: 1.0;
  color: #1c0a00;
  letter-spacing: -0.02em;
  margin: 0 0 16px;
  animation: fadeUp 0.5s 0.1s ease both;
}

.hero-tagline {
  font-family: 'Cormorant Garamond', serif;
  font-style: italic;
  font-size: 1.4rem;
  color: #92400e;
  margin: 0 0 40px;
  line-height: 1.5;
  animation: fadeUp 0.5s 0.2s ease both;
}

.hero-actions {
  display: flex;
  gap: 16px;
  align-items: center;
  animation: fadeUp 0.5s 0.3s ease both;
}

.btn-primary {
  display: inline-block;
  padding: 13px 30px;
  background: #92400e;
  color: #fef3e2;
  font-family: 'DM Sans', sans-serif;
  font-size: 14px;
  font-weight: 500;
  letter-spacing: 0.04em;
  text-decoration: none;
  border-radius: 100px;
  transition: background 0.2s, transform 0.15s;
}

.btn-primary:hover {
  background: #78340b;
  transform: translateY(-1px);
}

.btn-ghost {
  display: inline-block;
  padding: 13px 20px;
  color: #92400e;
  font-family: 'DM Sans', sans-serif;
  font-size: 14px;
  font-weight: 400;
  text-decoration: none;
  transition: color 0.2s;
}

.btn-ghost:hover {
  color: #1c0a00;
}

/* ── Hero Image ───────────────────────────────────── */
.hero-image-wrap {
  position: relative;
  flex-shrink: 0;
  width: 380px;
  animation: fadeUp 0.6s 0.15s ease both;
}

.hero-image-frame {
  position: relative;
  z-index: 2;
  border-radius: 220px 220px 160px 160px;
  overflow: hidden;
  border: 3px solid rgba(146, 64, 14, 0.15);
  box-shadow: 0 24px 64px rgba(146, 64, 14, 0.15);
}

.hero-image {
  display: block;
  width: 100%;
  height: 440px;
  object-fit: cover;
}

.hero-image-accent {
  position: absolute;
  inset: -12px -12px 12px 12px;
  z-index: 1;
  border-radius: 220px 220px 160px 160px;
  border: 2px dashed rgba(146, 64, 14, 0.2);
}

/* ── Divider ──────────────────────────────────────── */
.section-divider {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 24px;
  padding: 20px 0;
  color: #b45309;
  font-size: 10px;
  letter-spacing: 0.5em;
}

.section-divider::before,
.section-divider::after {
  content: '';
  flex: 1;
  max-width: 200px;
  height: 1px;
  background: linear-gradient(to right, transparent, rgba(180, 83, 9, 0.35));
}

.section-divider::after {
  background: linear-gradient(to left, transparent, rgba(180, 83, 9, 0.35));
}

/* ── Info Grid ────────────────────────────────────── */
.info-grid {
  position: relative;
  z-index: 1;
  max-width: 1100px;
  margin: 0 auto 80px;
  padding: 0 40px;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.info-card {
  background: rgba(146, 64, 14, 0.06);
  border: 1px solid rgba(146, 64, 14, 0.1);
  border-radius: 20px;
  padding: 28px 24px;
  transition: transform 0.2s, background 0.2s;
}

.info-card:hover {
  transform: translateY(-3px);
  background: rgba(146, 64, 14, 0.1);
}

.info-card--wide {
  grid-column: span 2;
}

.info-card--hours {
  grid-column: span 2;
}

.info-label {
  display: block;
  font-size: 10px;
  font-weight: 500;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: #92400e;
  margin-bottom: 12px;
}

.info-desc {
  font-size: 0.95rem;
  line-height: 1.75;
  color: #4a2910;
  margin: 0;
}

.info-value {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1.3rem;
  font-weight: 600;
  color: #1c0a00;
  margin: 0;
  line-height: 1.4;
}

/* ── Hours ────────────────────────────────────────── */
.hours-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px 16px;
}

.hours-list li {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  gap: 8px;
  padding-bottom: 8px;
  border-bottom: 1px solid rgba(146, 64, 14, 0.1);
}

.hours-day {
  font-size: 13px;
  font-weight: 500;
  color: #4a2910;
}

.hours-time {
  font-family: 'Cormorant Garamond', serif;
  font-size: 14px;
  color: #92400e;
}

/* ── Animation ────────────────────────────────────── */
@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(18px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ── Responsive ───────────────────────────────────── */
@media (max-width: 900px) {
  .hero {
    flex-direction: column-reverse;
    padding: 48px 24px 40px;
    gap: 40px;
    text-align: center;
  }

  .hero-actions {
    justify-content: center;
  }

  .hero-image-wrap {
    width: 280px;
  }

  .hero-image {
    height: 320px;
  }

  .info-grid {
    grid-template-columns: 1fr 1fr;
    padding: 0 24px;
  }

  .info-card--wide,
  .info-card--hours {
    grid-column: span 2;
  }
}

@media (max-width: 560px) {
  .info-grid {
    grid-template-columns: 1fr;
  }

  .info-card--wide,
  .info-card--hours {
    grid-column: span 1;
  }

  .hours-list {
    grid-template-columns: 1fr;
  }
}
</style>