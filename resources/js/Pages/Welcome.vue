<script setup lang="ts">
import AppFooter from '@/Components/AppFooter.vue';
import AppLogo from '@/Components/AppLogo.vue';
import ThemeModeSwitch from '@/Components/ThemeModeSwitch.vue';
import { useTranslations } from '@/composables/useTranslations';
import type { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage<PageProps>();
const { t } = useTranslations();

const canLogin = computed(() => !page.props.auth.user);
const seoTitle = computed(() => t('landing.seo.title'));
const seoDescription = computed(() => t('landing.seo.description'));
const seoKeywords = computed(() => t('landing.seo.keywords'));
</script>

<template>
    <v-app>
        <Head :title="seoTitle">
            <meta name="description" :content="seoDescription" />
            <meta name="keywords" :content="seoKeywords" />
            <meta property="og:title" :content="seoTitle" />
            <meta property="og:description" :content="seoDescription" />
            <meta property="og:type" content="website" />
            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:title" :content="seoTitle" />
            <meta name="twitter:description" :content="seoDescription" />
        </Head>

        <v-main class="landing-shell">
            <v-container class="landing-container" max-width="1240">
                <header class="landing-header">
                    <Link :href="route('projects.index')" class="brand-link">
                        <AppLogo />
                    </Link>

                    <div class="d-flex align-center ga-2">
                        <ThemeModeSwitch />
                        <template v-if="canLogin">
                            <v-btn :href="route('login')" variant="text" color="primary">
                                {{ t('nav.login') }}
                            </v-btn>
                            <v-btn :href="route('register')" class="env-action-btn" color="primary" variant="flat">
                                {{ t('nav.register') }}
                            </v-btn>
                        </template>
                    </div>
                </header>

                <section class="hero-section">
                    <div class="hero-copy">
                        <div class="ops-kicker mb-4">{{ t('landing.hero.kicker') }}</div>
                        <h1>{{ t('landing.hero.title') }}</h1>
                        <p>
                            {{ t('landing.hero.body') }}
                        </p>

                        <div class="hero-actions">
                            <v-btn :href="route('register')" class="env-action-btn" variant="flat" size="large">
                                {{ t('landing.hero.primary_cta') }}
                            </v-btn>
                            <v-btn :href="route('login')" variant="outlined" color="primary" size="large">
                                {{ t('landing.hero.secondary_cta') }}
                            </v-btn>
                        </div>

                        <div class="hero-note">{{ t('landing.hero.note') }}</div>
                    </div>

                    <div class="hero-cards">
                        <article class="metric-card ops-card">
                            <span>{{ t('landing.metrics.one.label') }}</span>
                            <strong>{{ t('landing.metrics.one.value') }}</strong>
                        </article>
                        <article class="metric-card ops-card">
                            <span>{{ t('landing.metrics.two.label') }}</span>
                            <strong>{{ t('landing.metrics.two.value') }}</strong>
                        </article>
                        <article class="metric-card ops-card">
                            <span>{{ t('landing.metrics.three.label') }}</span>
                            <strong>{{ t('landing.metrics.three.value') }}</strong>
                        </article>
                    </div>
                </section>

                <section class="seo-section">
                    <h2>{{ t('landing.seo_section.title') }}</h2>
                    <p>
                        {{ t('landing.seo_section.body') }}
                    </p>

                    <div class="feature-grid">
                        <article class="feature-card ops-card">
                            <h3>{{ t('landing.features.one.title') }}</h3>
                            <p>{{ t('landing.features.one.body') }}</p>
                        </article>
                        <article class="feature-card ops-card">
                            <h3>{{ t('landing.features.two.title') }}</h3>
                            <p>{{ t('landing.features.two.body') }}</p>
                        </article>
                        <article class="feature-card ops-card">
                            <h3>{{ t('landing.features.three.title') }}</h3>
                            <p>{{ t('landing.features.three.body') }}</p>
                        </article>
                        <article class="feature-card ops-card">
                            <h3>{{ t('landing.features.four.title') }}</h3>
                            <p>{{ t('landing.features.four.body') }}</p>
                        </article>
                    </div>
                </section>

                <section class="workflow-section ops-card">
                    <h2>{{ t('landing.workflow.title') }}</h2>
                    <div class="workflow-grid">
                        <article>
                            <span>01</span>
                            <h3>{{ t('landing.workflow.one.title') }}</h3>
                            <p>{{ t('landing.workflow.one.body') }}</p>
                        </article>
                        <article>
                            <span>02</span>
                            <h3>{{ t('landing.workflow.two.title') }}</h3>
                            <p>{{ t('landing.workflow.two.body') }}</p>
                        </article>
                        <article>
                            <span>03</span>
                            <h3>{{ t('landing.workflow.three.title') }}</h3>
                            <p>{{ t('landing.workflow.three.body') }}</p>
                        </article>
                    </div>
                </section>

                <section class="cta-section">
                    <div>
                        <h2>{{ t('landing.cta.title') }}</h2>
                        <p>{{ t('landing.cta.body') }}</p>
                    </div>
                    <div class="cta-actions">
                        <v-btn :href="route('register')" class="env-action-btn" variant="flat" size="large">
                            {{ t('landing.cta.primary') }}
                        </v-btn>
                        <v-btn :href="route('login')" variant="text" color="primary" size="large">
                            {{ t('landing.cta.secondary') }}
                        </v-btn>
                    </div>
                </section>

                <AppFooter />
            </v-container>
        </v-main>
    </v-app>
</template>

<style scoped>
.landing-shell {
    min-height: 100vh;
}

.landing-container {
    padding-top: 28px;
    padding-bottom: 64px;
}

.landing-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 42px;
}

.brand-link {
    text-decoration: none;
}

.hero-section {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 28px;
    margin-bottom: 40px;
}

.hero-copy {
    padding: 34px;
    border-radius: 24px;
    border: 1px solid color-mix(in srgb, var(--envly-primary) 16%, transparent);
    background: linear-gradient(155deg, var(--envly-card-start), var(--envly-card-end));
}

.hero-copy h1 {
    margin: 0;
    font-size: clamp(34px, 5vw, 64px);
    line-height: 0.96;
    letter-spacing: -0.06em;
    color: var(--envly-text);
}

.hero-copy p {
    margin-top: 20px;
    margin-bottom: 0;
    max-width: 720px;
    color: var(--envly-muted);
    line-height: 1.72;
}

.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 28px;
}

.hero-note {
    margin-top: 14px;
    font-size: 13px;
    color: var(--envly-muted);
}

.hero-cards {
    display: grid;
    gap: 12px;
}

.metric-card {
    border-radius: 18px;
    padding: 20px;
}

.metric-card span {
    display: block;
    font-size: 12px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--envly-muted);
}

.metric-card strong {
    display: block;
    margin-top: 8px;
    font-size: 19px;
    line-height: 1.35;
    color: var(--envly-text);
}

.seo-section {
    margin-top: 28px;
}

.seo-section h2,
.workflow-section h2,
.cta-section h2 {
    margin: 0;
    font-size: clamp(28px, 4vw, 44px);
    line-height: 1.04;
    letter-spacing: -0.04em;
    color: var(--envly-text);
}

.seo-section > p {
    margin-top: 16px;
    max-width: 920px;
    color: var(--envly-muted);
    line-height: 1.8;
}

.feature-grid {
    margin-top: 20px;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.feature-card {
    border-radius: 16px;
    padding: 22px;
}

.feature-card h3 {
    margin: 0;
    font-size: 20px;
    color: var(--envly-text);
}

.feature-card p {
    margin-top: 10px;
    margin-bottom: 0;
    color: var(--envly-muted);
    line-height: 1.7;
}

.workflow-section {
    margin-top: 20px;
    border-radius: 22px;
    padding: 28px;
}

.workflow-grid {
    margin-top: 16px;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
}

.workflow-grid article {
    padding: 16px;
    border-radius: 14px;
    border: 1px solid color-mix(in srgb, var(--envly-primary) 14%, transparent);
    background: color-mix(in srgb, var(--envly-background) 68%, transparent);
}

.workflow-grid span {
    font-size: 12px;
    letter-spacing: 0.1em;
    color: var(--envly-primary);
}

.workflow-grid h3 {
    margin-top: 8px;
    margin-bottom: 8px;
    color: var(--envly-text);
}

.workflow-grid p {
    margin: 0;
    color: var(--envly-muted);
    line-height: 1.7;
}

.cta-section {
    margin-top: 24px;
    border-top: 1px solid color-mix(in srgb, var(--envly-primary) 22%, transparent);
    padding-top: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
}

.cta-section p {
    margin-top: 10px;
    margin-bottom: 0;
    color: var(--envly-muted);
}

.cta-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

@media (max-width: 1024px) {
    .hero-section {
        grid-template-columns: 1fr;
    }

    .feature-grid {
        grid-template-columns: 1fr;
    }

    .workflow-grid {
        grid-template-columns: 1fr;
    }

    .cta-section {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
