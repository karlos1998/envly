<script setup lang="ts">
import AppLogo from '@/Components/AppLogo.vue';
import { useTranslations } from '@/composables/useTranslations';
import type { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

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
                        <AppLogo />
                    </Link>

                    <div v-if="canLogin" class="d-flex ga-2">
                        <v-btn :href="route('login')" variant="text" color="primary">
                            {{ t('nav.login') }}
                        </v-btn>
                        <v-btn :href="route('register')" color="primary" variant="flat">
                            {{ t('nav.register') }}
                        </v-btn>
                    </div>
                </header>

                <section class="guest-grid">
                    <div class="hero-panel">
                        <div class="hero-terminal" aria-hidden="true">
                            <div class="terminal-dot" />
                            <span>envly://control-plane</span>
                        </div>

                        <div class="ops-kicker mb-5">SECRETS / ENVIRONMENTS / CI EXPORT</div>
                        <h1 class="hero-title">{{ t('app.tagline') }}</h1>
                        <p class="hero-copy">
                            Versioned .env files, scoped project identifiers, and tokenized plain-text endpoints for deployment pipelines.
                        </p>

                        <div class="signal-grid" aria-hidden="true">
                            <span>PROJECT_ID</span>
                            <strong>global</strong>
                            <span>DEFAULT_ENV</span>
                            <strong>main</strong>
                            <span>EXPORT</span>
                            <strong>plain/text</strong>
                        </div>
                    </div>

                    <v-card class="auth-card ops-card" rounded="lg">
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
    padding-top: 28px;
    padding-bottom: 44px;
}

.guest-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: clamp(34px, 7vh, 88px);
}

.brand-link {
    text-decoration: none;
}

.guest-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.08fr) minmax(360px, 460px);
    align-items: center;
    gap: clamp(34px, 7vw, 92px);
}

.hero-panel {
    position: relative;
    overflow: hidden;
    border-radius: 24px;
    padding: clamp(32px, 5vw, 64px);
    min-height: 570px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background:
        linear-gradient(135deg, rgba(13, 24, 33, 0.92), rgba(6, 14, 22, 0.96)),
        radial-gradient(circle at 70% 20%, rgba(89, 243, 183, 0.13), transparent 18rem);
    border: 1px solid rgba(89, 243, 183, 0.16);
    box-shadow: 0 32px 110px rgba(0, 0, 0, 0.38), inset 0 1px 0 rgba(255, 255, 255, 0.04);
}

.hero-panel::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: linear-gradient(rgba(89, 243, 183, 0.045) 1px, transparent 1px), linear-gradient(90deg, rgba(89, 243, 183, 0.045) 1px, transparent 1px);
    background-size: 28px 28px;
    mask-image: linear-gradient(90deg, black, transparent 74%);
    pointer-events: none;
}

.hero-terminal {
    position: absolute;
    top: 24px;
    left: 24px;
    right: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border: 1px solid rgba(148, 163, 184, 0.16);
    border-radius: 12px;
    color: rgba(230, 237, 243, 0.56);
    font-size: 12px;
}

.terminal-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #59f3b7;
    box-shadow: 0 0 22px rgba(89, 243, 183, 0.9);
}

.hero-title {
    position: relative;
    color: #f4f7fb;
    font-size: clamp(40px, 5.4vw, 74px);
    line-height: 0.98;
    letter-spacing: -0.075em;
    max-width: 760px;
    margin: 0;
}

.hero-copy {
    position: relative;
    color: rgba(230, 237, 243, 0.68);
    font-size: 16px;
    line-height: 1.75;
    max-width: 560px;
    margin-top: 28px;
}

.signal-grid {
    position: relative;
    display: grid;
    grid-template-columns: 150px 1fr;
    gap: 10px 18px;
    max-width: 430px;
    margin-top: 34px;
    padding: 18px;
    border: 1px solid rgba(89, 243, 183, 0.18);
    border-radius: 16px;
    background: rgba(5, 11, 17, 0.62);
}

.signal-grid span {
    color: rgba(148, 163, 184, 0.78);
    font-size: 12px;
}

.signal-grid strong {
    color: #59f3b7;
    font-size: 12px;
    text-align: right;
}

.auth-card {
    padding: clamp(28px, 4vw, 44px);
    border-radius: 20px !important;
}

@media (max-width: 920px) {
    .guest-grid {
        grid-template-columns: 1fr;
    }

    .hero-panel {
        min-height: auto;
        padding-top: 84px;
    }
}
</style>
