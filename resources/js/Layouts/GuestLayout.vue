<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useTranslations } from '@/composables/useTranslations';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();
const { t } = useTranslations();
const canLogin = computed(() => !page.props.auth.user);
</script>

<template>
    <v-app>
        <v-main class="guest-shell">
            <v-container class="guest-container" max-width="1180">
                <header class="guest-header">
                    <Link :href="route('projects.index')" class="brand-link">
                        <span class="brand-mark">E</span>
                        <span>{{ t('app.name') }}</span>
                    </Link>

                    <div v-if="canLogin" class="d-flex ga-2">
                        <v-btn :href="route('login')" variant="text" color="primary">
                            {{ t('nav.login') }}
                        </v-btn>
                        <v-btn :href="route('register')" color="primary" rounded="xl">
                            {{ t('nav.register') }}
                        </v-btn>
                    </div>
                </header>

                <section class="guest-grid">
                    <div class="hero-panel">
                        <div class="hero-kicker">ENVLY</div>
                        <h1 class="hero-title">{{ t('app.tagline') }}</h1>
                        <p class="hero-copy">
                            Secure project envs, clean history, and one plain-text endpoint for CI.
                        </p>
                        <div class="hero-orbit hero-orbit-one" />
                        <div class="hero-orbit hero-orbit-two" />
                    </div>

                    <v-card class="auth-card" rounded="xl">
                        <slot />
                    </v-card>
                </section>
            </v-container>
        </v-main>
    </v-app>
</template>

<style scoped>
.guest-shell {
    min-height: 100vh;
    overflow: hidden;
}

.guest-container {
    min-height: 100vh;
    padding-top: 24px;
    padding-bottom: 40px;
}

.guest-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: clamp(32px, 6vh, 78px);
}

.brand-link {
    color: #172a3a;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    font-weight: 900;
    letter-spacing: -0.045em;
    text-decoration: none;
    font-size: 24px;
}

.brand-mark {
    display: grid;
    place-items: center;
    width: 42px;
    height: 42px;
    border-radius: 15px;
    background: #225c4d;
    color: #fffaf0;
    box-shadow: 0 18px 45px rgba(34, 92, 77, 0.28);
}

.guest-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.05fr) minmax(360px, 460px);
    align-items: center;
    gap: clamp(32px, 7vw, 92px);
}

.hero-panel {
    position: relative;
    border-radius: 42px;
    padding: clamp(32px, 6vw, 72px);
    min-height: 560px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background:
        linear-gradient(135deg, rgba(255, 250, 240, 0.7), rgba(245, 229, 199, 0.28)),
        radial-gradient(circle at 20% 20%, rgba(200, 107, 60, 0.18), transparent 18rem);
    border: 1px solid rgba(23, 42, 58, 0.08);
    box-shadow: 0 30px 90px rgba(23, 42, 58, 0.08);
}

.hero-kicker {
    color: #c86b3c;
    font-weight: 900;
    letter-spacing: 0.34em;
    margin-bottom: 20px;
}

.hero-title {
    color: #122130;
    font-size: clamp(44px, 5.8vw, 78px);
    line-height: 0.96;
    letter-spacing: -0.07em;
    max-width: 720px;
    margin: 0;
}

.hero-copy {
    color: rgba(18, 33, 48, 0.68);
    font-size: 18px;
    line-height: 1.6;
    max-width: 460px;
    margin-top: 28px;
}

.hero-orbit {
    position: absolute;
    pointer-events: none;
    border-radius: 999px;
    border: 1px solid rgba(34, 92, 77, 0.16);
}

.hero-orbit-one {
    width: 180px;
    height: 180px;
    right: 56px;
    top: 56px;
}

.hero-orbit-two {
    width: 86px;
    height: 86px;
    right: 148px;
    top: 102px;
    background: rgba(34, 92, 77, 0.08);
}

.auth-card {
    padding: clamp(28px, 4vw, 44px);
    background: rgba(255, 250, 240, 0.92);
    border: 1px solid rgba(23, 42, 58, 0.1);
    box-shadow: 0 32px 100px rgba(23, 42, 58, 0.16);
    backdrop-filter: blur(18px);
}

@media (max-width: 920px) {
    .guest-grid {
        grid-template-columns: 1fr;
    }

    .hero-panel {
        min-height: auto;
    }
}
</style>
