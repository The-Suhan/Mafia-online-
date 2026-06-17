<template>
    <div class="reset-password-page">
        <div class="reset-password-page__backdrop" aria-hidden="true" />
        <div class="reset-password-page__scanlines" aria-hidden="true" />

        <section class="reset-password-page__section">
            <div class="reset-password-page__glow" aria-hidden="true" />

            <div class="reset-password-page__card">
                <header class="reset-password-page__card-header">
                    <div class="reset-password-page__badge">
                        <span class="reset-password-page__badge-letter">M</span>
                    </div>
                    <h1 class="reset-password-page__title">
                        <span>M<span class="reset-password-page__title--accent">a</span>fia</span>
                    </h1>
                    <div class="reset-password-page__divider" />
                    <p class="reset-password-page__subtitle">
                        Choose a new password for your account.
                    </p>
                </header>

                <!-- Invalid token state -->
                <template v-if="isInvalidLink">
                    <div class="reset-password-page__state">
                        <div class="reset-password-page__state-icon reset-password-page__state-icon--error">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                        </div>
                        <h2 class="reset-password-page__state-title">Invalid Reset Link</h2>
                        <p class="reset-password-page__state-desc">
                            This password reset link is invalid or has expired.
                        </p>
                        <RouterLink to="/forgot-password" class="reset-password-page__link">
                            Request a new reset link
                        </RouterLink>
                    </div>
                </template>

                <!-- Success state -->
                <template v-else-if="submitted">
                    <div class="reset-password-page__state">
                        <div class="reset-password-page__state-icon reset-password-page__state-icon--success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                        </div>
                        <h2 class="reset-password-page__state-title">Password Reset!</h2>
                        <p class="reset-password-page__state-desc">
                            Your password has been updated. Redirecting you to sign in…
                        </p>
                    </div>
                </template>

                <!-- Form state -->
                <template v-else>
                    <form class="reset-password-page__form" @submit.prevent="handleSubmit" novalidate>
                        <!-- New Password -->
                        <div class="reset-password-page__field">
                            <label for="password" class="reset-password-page__label">New Password</label>
                            <div class="reset-password-page__input-wrapper">
                                <svg class="reset-password-page__input-icon" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                                <BaseInput id="password" v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'" autocomplete="new-password"
                                    placeholder="Min. 8 characters" :class="{ 'is-invalid': errors.password }"
                                    class="reset-password-page__input reset-password-page__input--with-icon reset-password-page__input--with-toggle" />
                                <button type="button" class="reset-password-page__password-toggle"
                                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                    @click="showPassword = !showPassword">
                                    <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                                        <path
                                            d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                                        <path
                                            d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                                        <line x1="2" x2="22" y1="2" y2="22" />
                                    </svg>
                                </button>
                            </div>
                            <span v-if="errors.password" class="reset-password-page__error">{{ errors.password }}</span>
                        </div>

                        <!-- Confirm Password -->
                        <div class="reset-password-page__field">
                            <label for="password_confirmation" class="reset-password-page__label">Confirm New
                                Password</label>
                            <div class="reset-password-page__input-wrapper">
                                <svg class="reset-password-page__input-icon" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                                <BaseInput id="password_confirmation" v-model="form.password_confirmation"
                                    :type="showConfirm ? 'text' : 'password'" autocomplete="new-password"
                                    placeholder="Repeat your password"
                                    :class="{ 'is-invalid': errors.password_confirmation }"
                                    class="reset-password-page__input reset-password-page__input--with-icon reset-password-page__input--with-toggle" />
                                <button type="button" class="reset-password-page__password-toggle"
                                    :aria-label="showConfirm ? 'Hide password' : 'Show password'"
                                    @click="showConfirm = !showConfirm">
                                    <svg v-if="!showConfirm" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                                        <path
                                            d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                                        <path
                                            d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                                        <line x1="2" x2="22" y1="2" y2="22" />
                                    </svg>
                                </button>
                            </div>
                            <span v-if="errors.password_confirmation" class="reset-password-page__error">
                                {{ errors.password_confirmation }}
                            </span>
                        </div>

                        <!-- General error -->
                        <p v-if="errors.general" class="reset-password-page__error reset-password-page__error--general">
                            {{ errors.general }}
                        </p>

                        <BaseButton type="submit" class="reset-password-page__submit" :disabled="isLoading">
                            {{ isLoading ? 'Resetting…' : 'Reset Password' }}
                        </BaseButton>
                    </form>
                </template>

                <!-- Footer -->
                <p v-if="!submitted" class="reset-password-page__footer">
                    <RouterLink to="/login" class="reset-password-page__link">
                        ← Back to Sign In
                    </RouterLink>
                </p>
            </div>
        </section>
    </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../store'
import BaseButton from '@/shared/components/BaseButton.vue'
import BaseInput from '@/shared/components/BaseInput.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const token = route.query.token
const email = route.query.email
const isInvalidLink = computed(() => !token || !email)

const form = reactive({ password: '', password_confirmation: '' })
const errors = reactive({ password: '', password_confirmation: '', general: '' })
const showPassword = ref(false)
const showConfirm = ref(false)
const isLoading = ref(false)
const submitted = ref(false)

function clearErrors() {
    errors.password = ''
    errors.password_confirmation = ''
    errors.general = ''
}

function validateForm() {
    clearErrors()
    let valid = true
    if (!form.password) {
        errors.password = 'Password is required.'
        valid = false
    } else if (form.password.length < 8) {
        errors.password = 'Password must be at least 8 characters.'
        valid = false
    }
    if (!form.password_confirmation) {
        errors.password_confirmation = 'Please confirm your password.'
        valid = false
    } else if (form.password !== form.password_confirmation) {
        errors.password_confirmation = 'Passwords do not match.'
        valid = false
    }
    return valid
}

async function handleSubmit() {
    if (!validateForm()) return
    isLoading.value = true
    try {
        await authStore.resetPassword({ token, email, ...form })
        submitted.value = true
        setTimeout(() => router.push('/login'), 2000)
    } catch (err) {
        const serverErrors = err?.response?.data?.errors
        if (serverErrors) {
            if (serverErrors.password) errors.password = [].concat(serverErrors.password)[0]
            if (serverErrors.password_confirmation) errors.password_confirmation = [].concat(serverErrors.password_confirmation)[0]
            if (serverErrors.token || serverErrors.email) errors.general = 'This reset link is invalid or has expired.'
        } else {
            errors.general = err?.response?.data?.message || 'Something went wrong. Please try again.'
        }
    } finally {
        isLoading.value = false
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
$color-success: oklch(0.65 0.18 145);

.reset-password-page {
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

    // ── State panels (success / invalid) ──────────────────────────────
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
        margin-bottom: 0.5rem;

        &--success {
            background-color: oklch(0.55 0.21 25 / 0.15);
            color: $color-primary;
        }

        &--error {
            background-color: oklch(0.6 0.23 25 / 0.15);
            color: $color-destructive;
        }
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

    // ── Form ──────────────────────────────────────────────────────────
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

        &--with-toggle {
            padding-right: 2.5rem;
        }
    }

    &__password-toggle {
        position: absolute;
        right: 0.5rem;
        top: 50%;
        transform: translateY(-50%);
        display: flex;
        width: 1.75rem;
        height: 1.75rem;
        align-items: center;
        justify-content: center;
        border-radius: 0.375rem;
        background: transparent;
        border: none;
        cursor: pointer;
        color: $color-muted-fg;
        transition: color 0.15s;

        &:hover {
            color: $color-foreground;
        }
    }

    &__error {
        font-size: 0.78rem;
        color: $color-destructive;

        &--general {
            text-align: center;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            background-color: oklch(0.6 0.23 25 / 0.1);
            border: 1px solid oklch(0.6 0.23 25 / 0.3);
        }
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