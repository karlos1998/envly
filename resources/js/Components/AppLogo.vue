<script setup lang="ts">
defineProps<{
    compact?: boolean;
}>();
</script>

<template>
    <span class="app-logo" :class="{ 'app-logo--compact': compact }" aria-label="Envly">
        <svg class="app-logo__mark" viewBox="0 0 48 48" role="img" aria-hidden="true">
            <defs>
                <linearGradient id="envly-mark" x1="8" x2="40" y1="6" y2="42" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#59f3b7" />
                    <stop offset="1" stop-color="#0f8f72" />
                </linearGradient>
            </defs>
            <path class="app-logo__shell" d="M8 13.5C8 10.46 10.46 8 13.5 8h21C37.54 8 40 10.46 40 13.5v21c0 3.04-2.46 5.5-5.5 5.5h-21A5.5 5.5 0 0 1 8 34.5v-21Z" />
            <path class="app-logo__bracket" d="M19.5 16 14 24l5.5 8" />
            <path class="app-logo__bracket" d="M28.5 16 34 24l-5.5 8" />
            <path class="app-logo__bolt" d="m25.5 15-4 10h5l-4 8" />
        </svg>
        <span v-if="!compact" class="app-logo__word">Envly</span>
    </span>
</template>

<style scoped>
.app-logo {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    color: var(--envly-text);
    font-family: 'JetBrains Mono', 'SFMono-Regular', Consolas, monospace;
    font-size: 22px;
    font-weight: 900;
    letter-spacing: -0.06em;
}

.app-logo__mark {
    width: 42px;
    height: 42px;
    filter: drop-shadow(0 14px 28px rgba(36, 255, 180, 0.12));
}

.app-logo--compact .app-logo__mark {
    width: 36px;
    height: 36px;
}

.app-logo__shell {
    fill: var(--envly-background);
    stroke: url(#envly-mark);
    stroke-width: 1.8;
}

.app-logo__bracket,
.app-logo__bolt {
    fill: none;
    stroke: #59f3b7;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 2.4;
}

.app-logo__bolt {
    stroke: #f4f7fb;
    stroke-width: 2.1;
}

.app-logo__word {
    transform: translateY(-1px);
}
</style>
