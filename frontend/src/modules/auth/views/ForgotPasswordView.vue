<script setup>
import { reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/modules/auth/store'
import BaseButton from '@/shared/components/BaseButton.vue'
import BaseInput from '@/shared/components/BaseInput.vue'

const authStore = useAuthStore()

const form = reactive({ email: '' })
const errors = reactive({ email: '' })
const loading = ref(false)
const submitted = ref(false)

function validate() {
    errors.email = ''
    if (!form.email) {
        errors.email = 'Email is required.'
        return false
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        errors.email = 'Please enter a valid email address.'
        return false
    }
    return true
}

async function handleSubmit() {
    if (!validate()) return
    loading.value = true
    try {
        await authStore.forgotPassword({ email: form.email })
        submitted.value = true
    } catch (err) {
        errors.email =
            err?.response?.data?.message ?? 'Something went wrong. Please try again.'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="forgot-password-page">
        <!-- Atmospheric backdrop -->
        <div class="forgot-password-page__backdrop" aria-hidden="true" />
        <div class="forgot-password-page__scanlines" aria-hidden="true" />
        <div class="forgot-password-page__glow" aria-hidden="true" />

        <div class="forgot-password-page__wrapper">
            <div class="forgot-password-page__card-glow" aria-hidden="true" />

            <div class="forgot-password-page__card">
                <!-- Top accent line -->
                <div class="forgot-password-page__accent-line" aria-hidden="true" />

                <!-- Header -->
                <header class="forgot-password-page__header">
                    <div class="forgot-password-page__badge">M</div>
                    <h1 class="forgot-password-page__title">
                        M<span class="forgot-password-page__title-accent">A</span>FI<span
                            class="forgot-password-page__title-accent">A</span>
                    </h1>
                    <span class="forgot-password-page__family-badge">The Family</span>
                </header>

                <div class="forgot-password-page__divider" aria-hidden="true" />

                <!-- Success state -->
                <template v-if="submitted">
                    <div class="forgot-password-page__success">
                        <div class="forgot-password-page__success-icon" aria-hidden="true">✓</div>
                        <h2 class="forgot-password-page__subtitle">Check Your Inbox</h2>
                        <p class="forgot-password-page__description">
                            We've sent a password reset link to
                            <strong class="forgot-password-page__email-highlight">{{ form.email }}</strong>.
                        </p>
                    </div>
                </template>

                <!-- Form state -->
                <template v-else>
                    <div class="forgot-password-page__body">
                        <h2 class="forgot-password-page__subtitle">Reset Your Password</h2>
                        <p class="forgot-password-page__description">
                            Enter your email and we'll send you a link to reset your password.
                        </p>
                    </div>

                    <form class="forgot-password-page__form" novalidate @submit.prevent="handleSubmit">
                        <div class="forgot-password-page__field">
                            <label class="forgot-password-page__label" for="email">
                                <svg class="forgot-password-page__field-icon" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect width="20" height="16" x="2" y="4" rx="2" />
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                                </svg>
                                Email Address
                            </label>
                            <BaseInput id="email" v-model="form.email" type="email" placeholder="enter@youremail.com"
                                autocomplete="email" :class="{ 'forgot-password-page__input--error': errors.email }"
                                class="forgot-password-page__input" />
                            <p v-if="errors.email" class="forgot-password-page__error" role="alert">
                                {{ errors.email }}
                            </p>
                        </div>

                        <BaseButton type="submit" class="forgot-password-page__submit" :disabled="loading">
                            <span v-if="loading" class="forgot-password-page__spinner" aria-hidden="true" />
                            {{ loading ? 'Sending…' : 'Send Reset Link' }}
                        </BaseButton>
                    </form>
                </template>

                <!-- Footer -->
                <p class="forgot-password-page__footer">
                    <RouterLink to="/login" class="forgot-password-page__back-link">
                        <svg class="forgot-password-page__back-icon" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m12 19-7-7 7-7" />
                            <path d="M19 12H5" />
                        </svg>
                        Back to Sign In
                    </RouterLink>
                </p>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
@use '@/shared/styles/variables' as v;
@use '@/shared/styles/mixins' as m;

.forgot-password-page {
    position: relative;
    display: flex;
    min-height: 100vh;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background-color: v.$color-background;
    padding: 3rem 1rem;

    // ── Atmospheric layers ──────────────────────────────────────────
    &__backdrop {
        position: absolute;
        inset: 0;
        background-image: url('/noir-backdrop.png');
        background-size: cover;
        background-position: center;
        opacity: 0.28;
        pointer-events: none;
    }

    &__scanlines {
        position: absolute;
        inset: 0;
        background-image: repeating-linear-gradient(to bottom,
                transparent 0px,
                transparent 2px,
                rgba(0, 0, 0, 0.06) 2px,
                rgba(0, 0, 0, 0.06) 4px);
        pointer-events: none;
    }

    &__glow {
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse at center,
                transparent 0%,
                v.$color-background-fade 100%);
        pointer-events: none;
    }

    // ── Layout wrapper ──────────────────────────────────────────────
    &__wrapper {
        position: relative;
        z-index: 10;
        width: 100%;
        max-width: 28rem;
    }

    // ── Card glow halo ──────────────────────────────────────────────
    &__card-glow {
        position: absolute;
        inset: -1px;
        border-radius: 1.125rem;
        background: rgba(v.$color-primary, 0.1);
        filter: blur(2rem);
        pointer-events: none;
    }

    // ── Card ────────────────────────────────────────────────────────
    &__card {
        position: relative;
        overflow: hidden;
        border-radius: 1rem;
        border: 1px solid rgba(v.$color-border, 0.7);
        background: rgba(v.$color-card, 0.82);
        padding: 2rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.65);
        backdrop-filter: blur(20px);

        @include m.breakpoint(sm) {
            padding: 2.5rem;
        }
    }

    // ── Top accent line ─────────────────────────────────────────────
    &__accent-line {
        position: absolute;
        inset-inline: 0;
        top: 0;
        height: 1px;
        background: linear-gradient(to right,
                transparent,
                rgba(v.$color-primary, 0.7),
                transparent);
    }

    // ── Header ──────────────────────────────────────────────────────
    &__header {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0.5rem;
    }

    &__badge {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        border: 1px solid rgba(v.$color-primary, 0.5);
        background: rgba(v.$color-primary, 0.12);
        color: v.$color-primary;
        font-family: v.$font-display;
        font-size: 1.125rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }

    &__title {
        font-family: v.$font-display;
        font-size: 2.25rem;
        font-weight: 900;
        letter-spacing: 0.3em;
        color: v.$color-foreground;
        margin: 0;
    }

    &__title-accent {
        color: v.$color-primary;
    }

    &__family-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border-radius: 9999px;
        border: 1px solid rgba(v.$color-primary, 0.4);
        background: rgba(v.$color-primary, 0.1);
        padding: 0.25rem 0.75rem;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.25em;
        color: v.$color-primary;
    }

    // ── Divider ─────────────────────────────────────────────────────
    &__divider {
        margin: 1.75rem 0;
        height: 1px;
        background: linear-gradient(to right,
                transparent,
                rgba(v.$color-border, 0.6),
                transparent);
    }

    // ── Body text ───────────────────────────────────────────────────
    &__body {
        text-align: center;
        margin-bottom: 1.75rem;
    }

    &__subtitle {
        font-family: v.$font-display;
        font-size: 1.375rem;
        font-weight: 700;
        color: v.$color-foreground;
        margin: 0 0 0.5rem;
    }

    &__description {
        font-size: 0.875rem;
        line-height: 1.6;
        color: v.$color-muted-foreground;
        margin: 0;
    }

    // ── Form ────────────────────────────────────────────────────────
    &__form {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    &__field {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    &__label {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        color: v.$color-muted-foreground;
        user-select: none;
    }

    &__field-icon {
        width: 0.85rem;
        height: 0.85rem;
        flex-shrink: 0;
        opacity: 0.7;
    }

    &__input {

        // BaseInput gets its own styles; we add overrides here
        &--error {
            // passes the error state — assumes BaseInput accepts a class
            border-color: v.$color-destructive !important;
            box-shadow: 0 0 0 3px rgba(v.$color-destructive, 0.18) !important;
        }
    }

    &__error {
        font-size: 0.78rem;
        font-weight: 500;
        color: v.$color-destructive;
        margin: 0;
    }

    &__submit {
        width: 100%;
        height: 2.75rem;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    &__spinner {
        display: inline-block;
        width: 0.85rem;
        height: 0.85rem;
        border: 2px solid rgba(255, 255, 255, 0.35);
        border-top-color: #fff;
        border-radius: 50%;
        animation: fp-spin 0.65s linear infinite;
    }

    // ── Success state ────────────────────────────────────────────────
    &__success {
        text-align: center;
        padding: 0.5rem 0 0.75rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
    }

    &__success-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        border: 1.5px solid rgba(v.$color-primary, 0.5);
        background: rgba(v.$color-primary, 0.12);
        color: v.$color-primary;
        font-size: 1.25rem;
        font-weight: 700;
    }

    &__email-highlight {
        color: v.$color-primary;
        font-weight: 600;
    }

    // ── Footer ──────────────────────────────────────────────────────
    &__footer {
        margin-top: 1.5rem;
        text-align: center;
        font-size: 0.75rem;
        color: v.$color-muted-foreground;
    }

    &__back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-weight: 500;
        color: v.$color-primary;
        text-decoration: none;
        transition: opacity 0.15s ease;

        &:hover {
            opacity: 0.75;
            text-decoration: underline;
            text-underline-offset: 3px;
        }
    }

    &__back-icon {
        width: 0.875rem;
        height: 0.875rem;
        flex-shrink: 0;
    }
}

@keyframes fp-spin {
    to {
        transform: rotate(360deg);
    }
}
</style>