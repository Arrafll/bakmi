<template>
    <CustomerLayout title="Selamat Datang di Bakmi Jawa Mas Agus!" subtitle="Sajian lezat pilihan kami">
        <div class="page-root">
            <div class="grain-overlay" aria-hidden="true"></div>

            <main>
            <!-- Hero Section -->
            <section class="hero">
                <div class="hero-inner">
                    <div class="hero-badge">✦ Buka Setiap Hari</div>
                    <h1 class="hero-name">{{ company.name }}</h1>
                    <p class="hero-tagline">{{ company.tagline }}</p>
                    <p class="hero-desc text-justify">Di setiap mangkuk Bakmi Mas Agus, tersimpan dedikasi untuk
                        menghadirkan rasa yang tidak hanya lezat, tetapi juga berkesan. Kami percaya bahwa makanan
                        terbaik adalah yang mampu menghadirkan kehangatan dan kebersamaan.

                        Menggunakan bahan berkualitas dan racikan bumbu khas, kami menghadirkan bakmi yang menggugah
                        selera sekaligus menghadirkan rasa "rumah" di setiap suapan.</p>
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
                <span>✦</span><span>✦</span><span>✦</span>
            </div>

            <!-- Info Strip -->
            <section class="info-strip">

                <!-- Alamat + Telepon side by side -->
                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">
                            <MapPinIcon class="w-4 h-4 text-amber-700 mr-1" />  Alamat
                        </span>
                        <p class="info-value">{{ company.address }}</p>
                    </div>
                    <div class="info-divider-v" aria-hidden="true"></div>
                    <div class="info-item">
                        <span class="info-label"><PhoneIcon class="w-4 h-4 text-amber-700 mr-1" /> Telepon</span>
                        <p class="info-value">{{ company.phone }}</p>
                    </div>
                </div>

                <!-- Jam Buka -->
                <div class="hours-block">
                    <span class="info-label"><ClockIcon class="w-4 h-4 text-amber-700 mr-1" /> Jam Buka</span>
                    <ul class="hours-list">
                        <li v-for="(hours, day) in company.opening_hours" :key="day">
                            <span class="hours-day">{{ day }}</span>
                            <span class="hours-time">{{ hours }}</span>
                        </li>
                    </ul>
                </div>

            </section>
        </main>
    </div>
</CustomerLayout>
</template>

<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue'
import AppFooter from '@/Components/AppFooter.vue'
import { MapPinIcon, ClockIcon, PhoneIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    company: {
        type: Object,
        default: () => ({}),
    },
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap');

/* ── Root ─────────────────────────────────────────── */
.page-root {
    min-height: 100vh;
    background-color: #fef3e2;
    font-family: 'DM Sans', sans-serif;
    color: #2c1a09;
    position: relative;
    overflow-x: hidden;
}

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
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #92400e;
    background: rgba(146, 64, 14, 0.08);
    border: 1px solid rgba(146, 64, 14, 0.2);
    padding: 5px 14px;
    border-radius: 100px;
    margin-bottom: 24px;
    animation: fadeUp 0.5s ease both;
}

.hero-name {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(3rem, 7vw, 5.5rem);
    font-weight: 600;
    line-height: 1.0;
    color: #1c0a00;
    letter-spacing: -0.02em;
    margin: 0 0 14px;
    animation: fadeUp 0.5s 0.1s ease both;
}

.hero-tagline {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 1.4rem;
    color: #92400e;
    margin: 0 0 20px;
    line-height: 1.5;
    animation: fadeUp 0.5s 0.2s ease both;
}

.hero-desc {
    font-size: 0.9rem;
    line-height: 1.8;
    color: #5a3a1a;
    margin: 0;
    white-space: pre-line;
    animation: fadeUp 0.5s 0.3s ease both;
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

/* ── Info Strip ───────────────────────────────────── */
.info-strip {
    position: relative;
    z-index: 1;
    max-width: 1100px;
    margin: 0 auto 72px;
    padding: 0 40px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Alamat + Telepon row */
.info-row {
    display: flex;
    align-items: stretch;
    background: rgba(146, 64, 14, 0.05);
    border: 1px solid rgba(146, 64, 14, 0.1);
    border-radius: 16px;
    overflow: hidden;
}

.info-item {
    flex: 1;
    padding: 20px 24px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.info-divider-v {
    width: 1px;
    background: rgba(146, 64, 14, 0.1);
    margin: 16px 0;
}

/* Hours block */
.hours-block {
    background: rgba(146, 64, 14, 0.05);
    border: 1px solid rgba(146, 64, 14, 0.1);
    border-radius: 16px;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.hours-block .info-label {
    flex-shrink: 0;
    margin-bottom: 0;
}

/* Shared label */
.info-label {
    display: inline-flex;
    font-family: 'Playfair Display';
    font-size: 12px;
    font-weight: 690;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #92400e;
    margin-bottom: 4px;
}

.info-value {
    font-family: serif;
    font-size: 1.1rem;
    font-weight: 600;
    color: #a7501d;
    margin: 0;
    line-height: 1.4;
}

/* Hours list — inline pills */
.hours-list {
    flex: 1;
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.hours-list li {
    display: flex;
    align-items: center;
    gap: 5px;
    background: rgba(146, 64, 14, 0.07);
    border: 1px solid rgba(146, 64, 14, 0.12);
    border-radius: 100px;
    padding: 3px 12px 3px 10px;
}

.hours-day {
    font-size: 13px;
    font-weight: 500;
    color: #4a2910;
}

.hours-time {
    font-family: serif;
    font-size: 13px;
    font-weight: 600;
    color: #92400e;
}

.hours-time::before {
    content: '·';
    margin-right: 4px;
    opacity: 0.4;
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

    .hero-image-wrap {
        width: 280px;
    }

    .hero-image {
        height: 320px;
    }

    .info-strip {
        padding: 0 24px;
    }

    .hours-block {
        flex-direction: column;
        gap: 12px;
    }
}

@media (max-width: 560px) {
    .info-row {
        flex-direction: column;
    }

    .info-divider-v {
        width: auto;
        height: 1px;
        margin: 0 16px;
    }

    .hours-list {
        grid-template-columns: 1fr;
    }

    .hours-day {
        min-width: 90px;
    }
}
</style>
