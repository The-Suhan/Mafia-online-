<template>
    <div class="reset-password-page">
        <!-- Atmospheric noir backdrop -->
        <div aria-hidden="true" class="reset-password-page__backdrop" />
        <div aria-hidden="true" class="reset-password-page__overlay" />
        <div aria-hidden="true" class="reset-password-page__gradient" />
        <div aria-hidden="true" class="reset-password-page__scanlines" />

        <main class="reset-password-page__main">
            <div class="reset-password-card">
                <!-- Glow effect -->
                <div aria-hidden="true" class="reset-password-card__glow" />

                <div class="reset-password-card__inner">
                    <!-- Logo / Brand -->
                    <div class="reset-password-card__brand">
                        <div class="reset-password-card__badge">M</div>
                        <h1 class="reset-password-card__title">
                            <span class="reset-password-card__title-accent">M</span>AFIA
                        </h1>
                        <span class="reset-password-card__tagline">The Family</span>
                    </div>

                    <div class="reset-password-card__divider" />

                    <!-- Invalid token state -->
                    <template v-if="isInvalidLink">
                        <div class="reset-password-card__error-state">
                            <div class="reset-password-card__error-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" y1="8" x2="12" y2="12" />
                                    <line x1="12" y1="16" x2="12.01" y2="16" />
                                </svg>
                            </div>
                            <h2 class="reset-password-card__subtitle">Invalid Reset Link</h2>
                            <p class="reset-password-card__description">
                                This password reset link is invalid or has expired.
                            </p>
                            <router-link to="/forgot-password" class="reset-password-card__back-link">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" aria-hidden="true">
                                    <polyline points="15 18 9 12 15 6" />
                                </svg>
                                Request a new reset link
                            </router-link>
                        </div>
                    </template>

                    <!-- Success state -->
                    <template v-else-if="submitted">
                        <div class="reset-password-card__success">
                            <div class="reset-password-card__success-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                    <polyline points="22 4 12 14.01 9 11.01" />
                                </svg>
                            </div>
                            <h2 class="reset-password-card__subtitle">Password Reset!</h2>
                            <p class="reset-password-card__description">
                                Your password has been updated. Redirecting you to sign in…
                            </p>
                        </div>
                    </template>

                    <!-- Form state -->
                    <template v-else>
                        <div class="reset-password-card__header">
                            <h2 class="reset-password-card__subtitle">Set New Password</h2>
                            <p class="reset-password-card__description">
                                Choose a new password for your account.
                            </p>
                        </div>

                        <form class="reset-password-card__form" @submit.prevent="handleSubmit" novalidate>
                            <!-- New Password -->
                            <div class="reset-password-card__field">
                                <label for="password" class="reset-password-card__label">
                                    New Password
                                </label>
                                <div class="reset-password-card__input-wrapper">
                                    <span class="reset-password-card__input-icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                        </svg>
                                    </span>
                                    <BaseInput id="password" v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'" name="password"
                                        autocomplete="new-password" placeholder="Min. 8 characters"
                                        :class="{ 'is-error': errors.password }"
                                        class="reset-password-card__input reset-password-card__input--padded-both" />
                                    <button type="button" class="reset-password-card__toggle"
                                        :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                        @click="showPassword = !showPassword">
                                        <!-- Eye icon -->
                                        <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="16"
                                            height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            aria-hidden="true">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                        <!-- EyeOff icon -->
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path
                                                d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
                                            <path
                                                d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                                            <line x1="1" y1="1" x2="23" y2="23" />
                                        </svg>
                                    </button>
                                </div>
                                <p v-if="errors.password" class="reset-password-card__field-error">
                                    {{ errors.password }}
                                </p>
                            </div>

                            <!-- Confirm Password -->
                            <div class="reset-password-card__field">
                                <label for="password_confirmation" class="reset-password-card__label">
                                    Confirm New Password
                                </label>
                                <div class="reset-password-card__input-wrapper">
                                    <span class="reset-password-card__input-icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                        </svg>
                                    </span>
                                    <BaseInput id="password_confirmation" v-model="form.password_confirmation"
                                        :type="showConfirm ? 'text' : 'password'" name="password_confirmation"
                                        autocomplete="new-password" placeholder="Repeat your password"
                                        :class="{ 'is-error': errors.password_confirmation }"
                                        class="reset-password-card__input reset-password-card__input--padded-both" />
                                    <button type="button" class="reset-password-card__toggle"
                                        :aria-label="showConfirm ? 'Hide password' : 'Show password'"
                                        @click="showConfirm = !showConfirm">
                                        <svg v-if="!showConfirm" xmlns="http://www.w3.org/2000/svg" width="16"
                                            height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            aria-hidden="true">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path
                                                d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
                                            <path
                                                d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                                            <line x1="1" y1="1" x2="23" y2="23" />
                                        </svg>
                                    </button>
                                </div>
                                <p v-if="errors.password_confirmation" class="reset-password-card__field-error">
                                    {{ errors.password_confirmation }}
                                </p>
                            </div>

                            <!-- Global API error -->
                            <p v-if="errors.general" class="reset-password-card__general-error">
                                {{ errors.general }}
                            </p>

                            <BaseButton type="submit" :loading="isLoading" :disabled="isLoading"
                                class="reset-password-card__submit">
                                {{ isLoading ? 'Resetting…' : 'Reset Password' }}
                            </BaseButton>
                        </form>
                    </template>

                    <!-- Back link (always visible except on success) -->
                    <div v-if="!submitted" class="reset-password-card__footer">
                        <router-link to="/login" class="reset-password-card__back-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <polyline points="15 18 9 12 15 6" />
                            </svg>
                            Back to Sign In
                        </router-link>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
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

const form = reactive({
    password: '',
    password_confirmation: '',
})

const errors = reactive({
    password: '',
    password_confirmation: '',
    general: '',
})

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
    clearErrors()

    try {
        await authStore.resetPassword({
            token,
            email,
            password: form.password,
            password_confirmation: form.password_confirmation,
        })

        submitted.value = true

        setTimeout(() => {
            router.push('/login')
        }, 2000)
    } catch (err) {
        const serverErrors = err?.response?.data?.errors

        if (serverErrors) {
            if (serverErrors.password) {
                errors.password = Array.isArray(serverErrors.password)
                    ? serverErrors.password[0]
                    : serverErrors.password
            }
            if (serverErrors.password_confirmation) {
                errors.password_confirmation = Array.isArray(serverErrors.password_confirmation)
                    ? serverErrors.password_confirmation[0]
                    : serverErrors.password_confirmation
            }
            if (serverErrors.token || serverErrors.email) {
                errors.general = 'This reset link is invalid or has expired.'
            }
        } else {
            errors.general =
                err?.response?.data?.message || 'Something went wrong. Please try again.'
        }
    } finally {
        isLoading.value = false
    }
}
</script>

<style lang="scss" scoped>
@use '@/shared/styles/variables' as v;
@use '@/shared/styles/mixins' as m;

.reset-password-page {
    position: relative;
    display: flex;
    min-height: 100svh;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    padding: 3rem 1rem;

    &__backdrop {
        position: absolute;
        inset: 0;
        background-image: url('/noir-backdrop.png');
        background-size: cover;
        background-position: center;
    }

    &__overlay {
        position: absolute;
        inset: 0;
        background-color: rgba(v.$color-background, 0.85);
    }

    &__gradient {
        position: absolute;
        inset: 0;
        background: linear-gradient(to bottom,
                rgba(v.$color-background, 0.4) 0%,
                rgba(v.$color-background, 0.7) 50%,
                v.$color-background 100%);
    }

    &__scanlines {
        position: absolute;
        inset: 0;
        pointer-events: none;
        background-image: repeating-linear-gradient(0deg,
                transparent,
                transparent 2px,
                rgba(0, 0, 0, 0.03) 2px,
                rgba(0, 0, 0, 0.03) 4px);
    }

    &__main {
        position: relative;
        z-index: 10;
        display: flex;
        width: 100%;
        justify-content: center;
    }
}

.reset-password-card {
    position: relative;
    width: 100%;
    max-width: 28rem;

    &__glow {
        pointer-events: none;
        position: absolute;
        inset: -1px;
        border-radius: calc(v.$radius * 2);
        background-color: rgba(v.$color-primary, 0.2);
        filter: blur(2rem);
    }

    &__inner {
        position: relative;
        border-radius: calc(v.$radius * 2);
        border: 1px solid rgba(v.$color-border, 0.8);
        background-color: rgba(v.$color-card, 0.8);
        padding: 2rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(1.5rem);

        @include m.breakpoint(sm) {
            padding: 2.5rem;
        }
    }

    &__brand {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        text-align: center;
    }

    &__badge {
        display: flex;
        width: 2.5rem;
        height: 2.5rem;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 1px solid rgba(v.$color-primary, 0.5);
        background-color: rgba(v.$color-primary, 0.1);
        font-family: v.$font-mono;
        font-size: 0.875rem;
        font-weight: 700;
        color: v.$color-primary;
        letter-spacing: 0.05em;
    }

    &__title {
        font-family: v.$font-mono;
        font-size: 1.875rem;
        font-weight: 800;
        letter-spacing: 0.35em;
        color: v.$color-foreground;
        margin: 0;
    }

    &__title-accent {
        color: v.$color-primary;
    }

    &__tagline {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        border-radius: 9999px;
        border: 1px solid rgba(v.$color-primary, 0.4);
        background-color: rgba(v.$color-primary, 0.1);
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: v.$color-primary;
    }

    &__divider {
        margin: 1.5rem 0;
        height: 1px;
        background: linear-gradient(to right,
                transparent,
                rgba(v.$color-border, 0.6),
                transparent);
    }

    &__header {
        text-align: center;
        margin-bottom: 1.5rem;
    }

    &__subtitle {
        font-size: 1.25rem;
        font-weight: 600;
        color: v.$color-foreground;
        margin: 0 0 0.5rem;
        text-wrap: balance;
    }

    &__description {
        font-size: 0.875rem;
        line-height: 1.625;
        color: v.$color-muted-foreground;
        margin: 0;
        text-wrap: pretty;
    }

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
        font-size: 0.875rem;
        font-weight: 500;
        color: v.$color-foreground;
    }

    &__input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    &__input-icon {
        pointer-events: none;
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: v.$color-muted-foreground;
        display: flex;
        align-items: center;
    }

    &__input {
        height: 2.75rem;
        width: 100%;
        border-radius: v.$radius;
        border: 1px solid v.$color-input;
        background-color: rgba(v.$color-background, 0.6);
        font-size: 0.875rem;
        color: v.$color-foreground;
        transition: border-color 0.15s, box-shadow 0.15s;
        outline: none;

        &--padded-both {
            padding-left: 2.5rem;
            padding-right: 2.75rem;
        }

        &::placeholder {
            color: rgba(v.$color-muted-foreground, 0.7);
        }

        &:focus {
            border-color: v.$color-primary;
            box-shadow: 0 0 0 2px rgba(v.$color-primary, 0.4);
        }

        &.is-error {
            border-color: v.$color-destructive;
            box-shadow: 0 0 0 2px rgba(v.$color-destructive, 0.2);
        }
    }

    &__toggle {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        color: v.$color-muted-foreground;
        display: flex;
        align-items: center;
        transition: color 0.15s;

        &:hover {
            color: v.$color-foreground;
        }

        &:focus-visible {
            outline: 2px solid v.$color-ring;
            outline-offset: 2px;
            border-radius: 2px;
        }
    }

    &__field-error {
        font-size: 0.75rem;
        color: v.$color-destructive;
        margin: 0;
    }

    &__general-error {
        font-size: 0.875rem;
        color: v.$color-destructive;
        background-color: rgba(v.$color-destructive, 0.1);
        border: 1px solid rgba(v.$color-destructive, 0.3);
        border-radius: v.$radius;
        padding: 0.625rem 0.875rem;
        margin: 0;
    }

    &__submit {
        width: 100%;
        height: 2.75rem;
        font-size: 0.875rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        margin-top: 0.25rem;
    }

    &__footer {
        margin-top: 2rem;
        display: flex;
        justify-content: center;
    }

    &__back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: v.$color-muted-foreground;
        text-decoration: none;
        transition: color 0.15s;

        &:hover {
            color: v.$color-primary;
        }

        &:focus-visible {
            outline: 2px solid v.$color-ring;
            outline-offset: 2px;
            border-radius: 2px;
        }
    }

    // Success state
    &__success,
    &__error-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0;
    }

    &__success-icon,
    &__error-icon {
        display: flex;
        width: 3rem;
        height: 3rem;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-bottom: 1rem;
    }

    &__success-icon {
        background-color: rgba(v.$color-primary, 0.15);
        color: v.$color-primary;
    }

    &__error-icon {
        background-color: rgba(v.$color-destructive, 0.15);
        color: v.$color-destructive;
    }
}
</style>