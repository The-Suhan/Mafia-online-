<template>
    <div class="forgot-password-page">
        <div class="forgot-password-page__backdrop" aria-hidden="true" />
        <div class="forgot-password-page__scanlines" aria-hidden="true" />

        <section class="forgot-password-page__section">
            <div class="forgot-password-page__glow" aria-hidden="true" />

            <div class="forgot-password-page__card">
                <header class="forgot-password-page__card-header">
                    <div class="forgot-password-page__badge">
                        <span class="forgot-password-page__badge-letter">M</span>
                    </div>
                    <h1 class="forgot-password-page__title">
                        <span>M<span class="forgot-password-page__title--accent">a</span>fia</span>
                    </h1>
                    <div class="forgot-password-page__divider" />
                    <p class="forgot-password-page__subtitle">
                        Enter your email and we'll send you a reset link.
                    </p>
                </header>

                <!-- Success state -->
                <template v-if="submitted">
                    <div class="forgot-password-page__state">
                        <div class="forgot-password-page__state-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                        </div>
                        <h2 class="forgot-password-page__state-title">Check Your Inbox</h2>
                        <p class="forgot-password-page__state-desc">
                            We've sent a reset link to
                            <strong class="forgot-password-page__state-email">{{ form.email }}</strong>.
                        </p>
                    </div>
                </template>

                <!-- Form state -->
                <template v-else>
                    <form class="forgot-password-page__form" novalidate @submit.prevent="handleSubmit">
                        <div class="forgot-password-page__field">
                            <label for="email" class="forgot-password-page__label">Email</label>
                            <div class="forgot-password-page__input-wrapper">
                                <svg class="forgot-password-page__input-icon" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect width="20" height="16" x="2" y="4" rx="2" />
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                                </svg>
                                <BaseInput id="email" v-model="form.email" type="email" autocomplete="email"
                                    placeholder="you@thefamily.com" :class="{ 'is-invalid': errors.email }"
                                    class="forgot-password-page__input forgot-password-page__input--with-icon" />
                            </div>
                            <span v-if="errors.email" class="forgot-password-page__error">{{ errors.email }}</span>
                        </div>

                        <BaseButton type="submit" class="forgot-password-page__submit" :disabled="loading">
                            {{ loading ? 'Sending…' : 'Send Reset Link' }}
                        </BaseButton>
                    </form>
                </template>

                <p class="forgot-password-page__footer">
                    <RouterLink to="/login" class="forgot-password-page__link">
                        ← Back to Sign In
                    </RouterLink>
                </p>
            </div>
        </section>
    </div>
</template>

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
        errors.email = err?.response?.data?.message ?? 'Something went wrong. Please try again.'
    } finally {
        loading.value = false
    }
}
</script>

<style lang="scss" scoped>
@use '@/shared/styles/variables' as v;
@use '@/shared/styles/mixins' as m;

$color-bg: oklch(0.16 0.005 285);
$color-primary: oklch(0.55 0.21 25);
$color-foreground: oklch(0.92 0.004 285);
$color-muted-fg: oklch(0.65 0.005 285);
$color-border: oklch(0.3 0.01 285);
$color-destructive: oklch(0.6 0.23 25);

.forgot-password-page {
    position: relative;
    display: flex;
    min-height: 100svh;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background-color: $color-bg;
    padding: 2.5rem 1rem;

    &__backdrop {
        pointer-events: none;
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 50% -10%,
                oklch(0.55 0.21 25 / 0.18),
                transparent 55%);
    }

    &__scanlines {
        pointer-events: none;
        position: absolute;
        inset: 0;
        opacity: 0.04;
        background-image:
            linear-gradient(oklch(1 0 0) 1px, transparent 1px),
            linear-gradient(90deg, oklch(1 0 0) 1px, transparent 1px);
        background-size: 40px 40px;
    }

    &__section {
        position: relative;
        width: 100%;
        max-width: 28rem;
    }

    &__glow {
        position: absolute;
        inset: -1px;
        border-radius: 1rem;
        background-color: oklch(0.55 0.21 25 / 0.3);
        opacity: 0.6;
        filter: blur(2rem);
        pointer-events: none;
    }

    &__card {
        position: relative;
        border-radius: 1rem;
        border: 1px solid oklch(0.55 0.21 25 / 0.3);
        background-color: oklch(0.205 0.006 285 / 0.8);
        padding: 2rem;
        backdrop-filter: blur(4px);
        box-shadow:
            0 0 40px -12px $color-primary,
            inset 0 1px 0 0 oklch(1 0 0 / 0.04);

        @media (min-width: 640px) {
            padding: 2.5rem;
        }

        &-header {
            margin-bottom: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
    }

    &__badge {
        margin-bottom: 1rem;
        display: flex;
        width: 3rem;
        height: 3rem;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 1px solid oklch(0.55 0.21 25 / 0.4);
        background-color: oklch(0.55 0.21 25 / 0.1);

        &-letter {
            font-family: var(--font-cinzel, serif);
            font-size: 1.25rem;
            font-weight: 900;
            color: $color-primary;
        }
    }

    &__title {
        font-family: var(--font-cinzel, serif);
        font-size: 2.5rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.35em;
        color: $color-foreground;
        padding-left: 0.35em;

        @media (min-width: 640px) {
            font-size: 3rem;
        }

        &--accent {
            color: $color-primary;
        }
    }

    &__divider {
        margin-top: 0.75rem;
        height: 1px;
        width: 4rem;
        background: linear-gradient(to right, transparent, $color-primary, transparent);
    }

    &__subtitle {
        margin-top: 0.75rem;
        font-size: 0.875rem;
        color: $color-muted-fg;
        text-wrap: balance;
    }

    // ── State panel (success) ─────────────────────────────────────────
    &__state {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0.5rem;
        padding: 0.5rem 0 0.75rem;
    }

    &__state-icon {
        display: flex;
        width: 3rem;
        height: 3rem;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: oklch(0.55 0.21 25 / 0.15);
        color: $color-primary;
        margin-bottom: 0.5rem;
    }

    &__state-title {
        font-family: var(--font-cinzel, serif);
        font-size: 1.125rem;
        font-weight: 700;
        color: $color-foreground;
        margin: 0;
    }

    &__state-desc {
        font-size: 0.875rem;
        color: $color-muted-fg;
        margin: 0;
        line-height: 1.6;
    }

    &__state-email {
        color: $color-primary;
        font-weight: 600;
    }

    // ── Form ─────────────────────────────────────────────────────────
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
        font-size: 0.7rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: $color-muted-fg;
    }

    &__input-wrapper {
        position: relative;
    }

    &__input-icon {
        pointer-events: none;
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: $color-muted-fg;
        width: 1rem;
        height: 1rem;
    }

    &__input {
        height: 2.75rem;
        width: 100%;
        background-color: oklch(0.25 0.008 285 / 0.6);
        color: $color-foreground;
        border: 1px solid $color-border;
        border-radius: 0.5rem;
        padding: 0 0.75rem;
        font-size: 0.875rem;
        transition: border-color 0.15s, box-shadow 0.15s;
        outline: none;

        &::placeholder {
            color: oklch(0.65 0.005 285 / 0.6);
        }

        &:focus {
            border-color: $color-primary;
            box-shadow: 0 0 0 3px oklch(0.55 0.21 25 / 0.2);
        }

        &.is-invalid {
            border-color: $color-destructive;
            box-shadow: 0 0 0 3px oklch(0.6 0.23 25 / 0.2);
        }

        &--with-icon {
            padding-left: 2.5rem;
        }
    }

    &__error {
        font-size: 0.78rem;
        color: $color-destructive;
    }

    &__submit {
        margin-top: 0.25rem;
        height: 2.75rem;
        width: 100%;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        box-shadow: 0 0 20px -4px $color-primary;
        transition: box-shadow 0.2s;

        &:hover:not(:disabled) {
            box-shadow: 0 0 28px -2px $color-primary;
        }
    }

    &__footer {
        margin-top: 1.75rem;
        text-align: center;
        font-size: 0.875rem;
        color: $color-muted-fg;
    }

    &__link {
        font-weight: 500;
        color: $color-primary;
        text-decoration: none;
        text-underline-offset: 4px;
        transition: color 0.15s;

        &:hover {
            color: $color-foreground;
            text-decoration: underline;
        }
    }
}
</style>