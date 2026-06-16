<template>
    <div class="not-found">
        <div class="not-found__vignette" aria-hidden="true"></div>
        <div class="not-found__scanlines" aria-hidden="true"></div>

        <div class="not-found__tape not-found__tape--top" aria-hidden="true">
            <div class="not-found__tape-track">
                <span v-for="i in 12" :key="`tape-top-${i}`" class="not-found__tape-text">
                    CRIME SCENE &mdash; DO NOT CROSS
                    <span class="not-found__tape-star">★</span>
                </span>
            </div>
        </div>
        <div class="not-found__tape not-found__tape--bottom" aria-hidden="true">
            <div class="not-found__tape-track">
                <span v-for="i in 12" :key="`tape-bottom-${i}`" class="not-found__tape-text">
                    UNSOLVED &mdash; STAY BACK
                    <span class="not-found__tape-star">★</span>
                </span>
            </div>
        </div>

        <div class="not-found__content">
            <div class="not-found__heading-wrap">
                <h1 class="not-found__heading">404</h1>

                <span class="not-found__bullet-hole not-found__bullet-hole--a" aria-hidden="true">
                    <svg viewBox="0 0 100 100" class="not-found__bullet-cracks" fill="none" stroke="currentColor"
                        stroke-width="1.4">
                        <path d="M50 50 L18 8" />
                        <path d="M50 50 L88 14" />
                        <path d="M50 50 L96 56" />
                        <path d="M50 50 L74 94" />
                        <path d="M50 50 L20 90" />
                        <path d="M50 50 L4 60" />
                    </svg>
                    <span class="not-found__bullet-impact"></span>
                </span>

                <span class="not-found__bullet-hole not-found__bullet-hole--b" aria-hidden="true">
                    <svg viewBox="0 0 100 100" class="not-found__bullet-cracks" fill="none" stroke="currentColor"
                        stroke-width="1.4">
                        <path d="M50 50 L18 8" />
                        <path d="M50 50 L88 14" />
                        <path d="M50 50 L96 56" />
                        <path d="M50 50 L74 94" />
                        <path d="M50 50 L20 90" />
                        <path d="M50 50 L4 60" />
                    </svg>
                    <span class="not-found__bullet-impact"></span>
                </span>

                <span class="not-found__bullet-hole not-found__bullet-hole--c" aria-hidden="true">
                    <svg viewBox="0 0 100 100" class="not-found__bullet-cracks" fill="none" stroke="currentColor"
                        stroke-width="1.4">
                        <path d="M50 50 L18 8" />
                        <path d="M50 50 L88 14" />
                        <path d="M50 50 L96 56" />
                        <path d="M50 50 L74 94" />
                        <path d="M50 50 L20 90" />
                        <path d="M50 50 L4 60" />
                    </svg>
                    <span class="not-found__bullet-impact"></span>
                </span>
            </div>

            <p class="not-found__case-closed">Case Closed</p>

            <h2 class="not-found__subheading">This page got whacked by the mafia.</h2>

            <p class="not-found__description">
                Whatever you were lookin&apos; for, it sleeps with the fishes now. No
                witnesses, no evidence&hellip; just a chalk outline where the link
                used to be.
            </p>

            <BaseButton class="not-found__back-button" @click="goHome">
                <Home class="not-found__back-icon" aria-hidden="true" />
                Back to Home
            </BaseButton>

            <p class="not-found__footer-note">Or you didn&apos;t see nothin&apos;.</p>
        </div>
    </div>
</template>

<script setup>
// NOTE: This assumes `lucide-vue-next` is available in the project (the
// Vue equivalent of the `lucide-react` icon used by the original page).
import { Home } from 'lucide-vue-next'
import { useRouter } from 'vue-router'
import BaseButton from '@/shared/components/BaseButton.vue'

const router = useRouter()

function goHome() {
    router.push('/')
}
</script>

<style lang="scss" scoped>
// NOTE ON TOKENS
// ---------------------------------------------------------------------
// Matched against the real `_variables.scss`: $color-bg, $color-text,
// $color-text-muted, $color-border, $color-destructive, $color-primary-fg,
// $font-mono. `$color-primary-fg` (a near-white tone) is used as the
// text color on the red back-button, standing in for a destructive-fg
// token since the variables file doesn't define one separately.
//
// The original page distinguishes Tailwind's `destructive` (red) token
// from `primary`, so this file maps the 404 glow / "Case Closed" / back
// button to `$color-destructive` rather than `$color-primary`.
//
// `_variables.scss` has no breakpoint variables, so the media queries
// below use plain pixel values (640px / 768px) instead. If
// `_mixins.scss` exposes a breakpoint mixin, swap these in.
//
// `BaseButton` is rendered here with no `variant` prop and styled purely
// through the `.not-found__back-button` class override, since the
// original React version also overrides its Button's default styling
// with custom classes rather than relying on a built-in "destructive"
// variant. If your BaseButton doesn't forward `class` to its root
// element, you may need `:deep()` here instead.
@use '@/shared/styles/variables' as *;
@use '@/shared/styles/mixins' as *;

.not-found {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    overflow: hidden;
    background: $color-bg;
    color: $color-text;
    padding: 4rem 1.5rem;
    text-align: center;

    &__vignette {
        position: absolute;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        background: radial-gradient(120% 90% at 50% 35%, transparent 40%, rgba(0, 0, 0, 0.85) 100%);
    }

    &__scanlines {
        position: absolute;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        opacity: 0.04;
        background-image: repeating-linear-gradient(to bottom,
                rgba(255, 255, 255, 0.6) 0px,
                rgba(255, 255, 255, 0.6) 1px,
                transparent 1px,
                transparent 3px);
    }

    // Crime-scene tape
    &__tape {
        position: absolute;
        left: 50%;
        z-index: 1;
        width: 140%;
        overflow: hidden;
        pointer-events: none;
        background: #e3b505;
        padding: 0.5rem 0;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(0, 0, 0, 0.4);

        &--top {
            top: 14%;
            transform: translateX(-50%) rotate(-4deg);
        }

        &--bottom {
            bottom: 10%;
            transform: translateX(-50%) rotate(3deg);
        }
    }

    &__tape-track {
        display: flex;
        align-items: center;
        white-space: nowrap;
    }

    &__tape-text {
        margin: 0 1rem;
        font-family: $font-mono, monospace;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.3em;
        color: #000;

        @media (min-width: 768px) {
            font-size: 0.875rem;
        }
    }

    &__tape-star {
        margin: 0 1rem;
        color: rgba(0, 0, 0, 0.4);
    }

    // Content
    &__content {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    &__heading-wrap {
        position: relative;
    }

    &__heading {
        margin: 0;
        font-family: $font-mono, monospace;
        font-size: 7rem;
        font-weight: 900;
        line-height: 1;
        letter-spacing: -0.04em;
        color: $color-text;
        text-shadow: 0 2px 0 #000, 0 8px 24px rgba(0, 0, 0, 0.9), 0 0 48px rgba($color-destructive, 0.35);

        @media (min-width: 640px) {
            font-size: 10rem;
        }

        @media (min-width: 768px) {
            font-size: 13rem;
        }
    }

    // Bullet holes punched through the heading
    &__bullet-hole {
        position: absolute;
        display: inline-block;

        &--a {
            top: 18%;
            left: 14%;
            width: 48px;
            height: 48px;

            @media (min-width: 768px) {
                width: 64px;
                height: 64px;
            }
        }

        &--b {
            top: 8%;
            right: 20%;
            width: 40px;
            height: 40px;
        }

        &--c {
            bottom: 14%;
            right: 8%;
            width: 56px;
            height: 56px;

            @media (min-width: 768px) {
                width: 80px;
                height: 80px;
            }
        }
    }

    &__bullet-cracks {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        color: rgba($color-text, 0.15);
    }

    &__bullet-impact {
        position: absolute;
        inset: 0;
        border-radius: 999px;
        background: radial-gradient(circle at 42% 38%,
                #000 0%,
                #000 32%,
                #1c1c1c 46%,
                rgba(120, 120, 120, 0.35) 64%,
                rgba(0, 0, 0, 0) 72%);
        box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.9), 0 0 0 1px rgba(255, 255, 255, 0.04);
    }

    &__case-closed {
        margin: 0.5rem 0 0;
        font-family: $font-mono, monospace;
        font-size: 0.875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4em;
        color: $color-destructive;
    }

    &__subheading {
        margin: 1.5rem 0 0;
        max-width: 36rem;
        font-size: 1.5rem;
        font-weight: 700;
        color: $color-text;

        @media (min-width: 768px) {
            font-size: 1.875rem;
        }
    }

    &__description {
        margin: 0.75rem 0 0;
        max-width: 28rem;
        line-height: 1.6;
        color: $color-text-muted;
    }

    &__back-button {
        margin-top: 2rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        height: 44px;
        padding: 0 1.5rem;
        font-size: 1rem;
        font-weight: 600;
        background: $color-destructive;
        color: $color-primary-fg;
        box-shadow: 0 10px 30px rgba($color-destructive, 0.3);
        transition: opacity 0.2s ease;

        &:hover {
            opacity: 0.9;
        }
    }

    &__back-icon {
        width: 16px;
        height: 16px;
    }

    &__footer-note {
        margin: 1.5rem 0 0;
        font-family: $font-mono, monospace;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.2em;
        color: rgba($color-text-muted, 0.6);
    }
}
</style>