<template>
    <main class="profile-edit">
        <div class="profile-edit__container">
            <!-- Back link -->
            <router-link :to="`/profile/${authStore.user?.id}`" class="profile-edit__back">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                Back to Profile
            </router-link>

            <!-- Page header -->
            <header class="profile-edit__header">
                <h1 class="profile-edit__title">Edit Profile</h1>
                <p class="profile-edit__subtitle">Update your avatar, nickname, and account security.</p>
            </header>

            <!-- Profile Information Card -->
            <section class="profile-edit__card">
                <div class="profile-edit__section-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    <h2 class="profile-edit__section-title">Profile Information</h2>
                </div>

                <!-- Avatar -->
                <div class="profile-edit__avatar-wrap">
                    <button type="button" class="profile-edit__avatar-btn" aria-label="Change avatar"
                        @click="fileInputRef?.click()">
                        <img :src="avatarPreview" alt="Profile avatar" class="profile-edit__avatar-img" />
                        <span class="profile-edit__avatar-overlay">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path
                                    d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                                <circle cx="12" cy="13" r="4" />
                            </svg>
                            <span>Change</span>
                        </span>
                    </button>
                    <button type="button" class="profile-edit__avatar-change-btn" @click="fileInputRef?.click()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                        Change Avatar
                    </button>
                    <p class="profile-edit__avatar-hint">JPG, PNG or GIF. Max size 2MB.</p>
                    <input ref="fileInputRef" type="file" accept="image/*" class="profile-edit__file-input"
                        aria-hidden="true" @change="handleAvatarChange" />
                </div>

                <!-- Profile form -->
                <form class="profile-edit__form" @submit.prevent="handleProfileSubmit" novalidate>
                    <div class="profile-edit__field">
                        <label class="profile-edit__label" for="nickname">Nickname</label>
                        <BaseInput id="nickname" v-model="profileForm.nickname" placeholder="Your in-game name"
                            :class="{ 'is-invalid': profileErrors.nickname }" autocomplete="username" />
                        <p v-if="profileErrors.nickname" class="profile-edit__field-error">
                            {{ profileErrors.nickname }}
                        </p>
                    </div>

                    <div class="profile-edit__field">
                        <label class="profile-edit__label profile-edit__label--muted" for="email">Email</label>
                        <BaseInput id="email" type="email" :model-value="authStore.user?.email ?? ''" disabled readonly
                            class="profile-edit__input--disabled" />
                        <p class="profile-edit__field-hint">Your email cannot be changed.</p>
                    </div>

                    <p v-if="profileSuccess" class="profile-edit__alert profile-edit__alert--success">
                        {{ profileSuccess }}
                    </p>
                    <p v-if="profileErrors.general" class="profile-edit__alert profile-edit__alert--error">
                        {{ profileErrors.general }}
                    </p>

                    <div class="profile-edit__actions">
                        <BaseButton type="button" variant="outline" @click="goToProfile">
                            Cancel
                        </BaseButton>
                        <BaseButton type="submit" :loading="profileLoading">
                            Save Changes
                        </BaseButton>
                    </div>
                </form>
            </section>

            <!-- Change Password Card -->
            <section class="profile-edit__card">
                <div class="profile-edit__section-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                    <h2 class="profile-edit__section-title">Change Password</h2>
                </div>

                <form class="profile-edit__form" @submit.prevent="handlePasswordSubmit" novalidate>
                    <div class="profile-edit__field">
                        <label class="profile-edit__label" for="current-password">Current Password</label>
                        <div class="profile-edit__password-wrap">
                            <BaseInput id="current-password" v-model="passwordForm.current_password"
                                :type="showPasswords.current ? 'text' : 'password'" placeholder="••••••••"
                                autocomplete="current-password"
                                :class="{ 'is-invalid': passwordErrors.current_password }" />
                            <button type="button" class="profile-edit__eye-btn"
                                :aria-label="showPasswords.current ? 'Hide password' : 'Show password'"
                                @click="showPasswords.current = !showPasswords.current">
                                <!-- Eye icon -->
                                <svg v-if="!showPasswords.current" xmlns="http://www.w3.org/2000/svg" width="18"
                                    height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <!-- EyeOff icon -->
                                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
                                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                                    <line x1="1" y1="1" x2="23" y2="23" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="passwordErrors.current_password" class="profile-edit__field-error">
                            {{ passwordErrors.current_password }}
                        </p>
                    </div>

                    <div class="profile-edit__grid-2">
                        <div class="profile-edit__field">
                            <label class="profile-edit__label" for="new-password">New Password</label>
                            <div class="profile-edit__password-wrap">
                                <BaseInput id="new-password" v-model="passwordForm.password"
                                    :type="showPasswords.new ? 'text' : 'password'" placeholder="••••••••"
                                    autocomplete="new-password" :class="{ 'is-invalid': passwordErrors.password }" />
                                <button type="button" class="profile-edit__eye-btn"
                                    :aria-label="showPasswords.new ? 'Hide password' : 'Show password'"
                                    @click="showPasswords.new = !showPasswords.new">
                                    <svg v-if="!showPasswords.new" xmlns="http://www.w3.org/2000/svg" width="18"
                                        height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
                                        <path
                                            d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                                        <line x1="1" y1="1" x2="23" y2="23" />
                                    </svg>
                                </button>
                            </div>
                            <p v-if="passwordErrors.password" class="profile-edit__field-error">
                                {{ passwordErrors.password }}
                            </p>
                        </div>

                        <div class="profile-edit__field">
                            <label class="profile-edit__label" for="confirm-password">Confirm New Password</label>
                            <div class="profile-edit__password-wrap">
                                <BaseInput id="confirm-password" v-model="passwordForm.password_confirmation"
                                    :type="showPasswords.confirm ? 'text' : 'password'" placeholder="••••••••"
                                    autocomplete="new-password"
                                    :class="{ 'is-invalid': passwordErrors.password_confirmation }" />
                                <button type="button" class="profile-edit__eye-btn"
                                    :aria-label="showPasswords.confirm ? 'Hide password' : 'Show password'"
                                    @click="showPasswords.confirm = !showPasswords.confirm">
                                    <svg v-if="!showPasswords.confirm" xmlns="http://www.w3.org/2000/svg" width="18"
                                        height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
                                        <path
                                            d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                                        <line x1="1" y1="1" x2="23" y2="23" />
                                    </svg>
                                </button>
                            </div>
                            <p v-if="passwordErrors.password_confirmation" class="profile-edit__field-error">
                                {{ passwordErrors.password_confirmation }}
                            </p>
                        </div>
                    </div>

                    <p v-if="passwordSuccess" class="profile-edit__alert profile-edit__alert--success">
                        {{ passwordSuccess }}
                    </p>
                    <p v-if="passwordErrors.general" class="profile-edit__alert profile-edit__alert--error">
                        {{ passwordErrors.general }}
                    </p>

                    <div class="profile-edit__actions">
                        <BaseButton type="submit" :loading="passwordLoading">
                            Update Password
                        </BaseButton>
                    </div>
                </form>
            </section>
        </div>
    </main>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/modules/auth/store'
import { useProfileStore } from '@/modules/profile/store'
import BaseButton from '@/shared/components/BaseButton.vue'
import BaseInput from '@/shared/components/BaseInput.vue'

const router = useRouter()
const authStore = useAuthStore()
const profileStore = useProfileStore()

// ── Avatar ──────────────────────────────────────────────────────────────────
const fileInputRef = ref(null)
const avatarFile = ref(null)

const avatarPreview = computed(() => {
    const user = authStore.user
    if (!user) return ''
    const seed = user.nickname ?? user.id ?? 'player'
    return user.avatar_url || `https://api.dicebear.com/7.x/pixel-art/svg?seed=${encodeURIComponent(seed)}`
})

// Override preview when a local file is selected
const localAvatarPreview = ref(null)
const displayAvatar = computed(() => localAvatarPreview.value ?? avatarPreview.value)

function handleAvatarChange(e) {
    const file = e.target.files?.[0]
    if (!file) return
    avatarFile.value = file
    const reader = new FileReader()
    reader.onload = (ev) => { localAvatarPreview.value = ev.target.result }
    reader.readAsDataURL(file)
}

// ── Profile form ─────────────────────────────────────────────────────────────
const profileForm = reactive({ nickname: '' })
const profileErrors = reactive({ nickname: '', general: '' })
const profileSuccess = ref('')
const profileLoading = ref(false)

const NICKNAME_RE = /^[a-zA-Z0-9_]{3,20}$/

function validateProfile() {
    profileErrors.nickname = ''
    profileErrors.general = ''
    if (!profileForm.nickname) {
        profileErrors.nickname = 'Nickname is required.'
        return false
    }
    if (!NICKNAME_RE.test(profileForm.nickname)) {
        profileErrors.nickname = 'Nickname must be 3–20 characters: letters, numbers, or underscores only.'
        return false
    }
    return true
}

async function handleProfileSubmit() {
    if (!validateProfile()) return
    profileLoading.value = true
    profileSuccess.value = ''
    try {
        const payload = new FormData()
        payload.append('nickname', profileForm.nickname)
        if (avatarFile.value) payload.append('avatar', avatarFile.value)
        await profileStore.updateProfile(payload)
        profileSuccess.value = 'Profile updated successfully!'
        avatarFile.value = null
        localAvatarPreview.value = null
    } catch (err) {
        const data = err?.response?.data
        if (data?.errors?.nickname) {
            profileErrors.nickname = data.errors.nickname[0]
        } else {
            profileErrors.general = data?.message ?? 'Something went wrong. Please try again.'
        }
    } finally {
        profileLoading.value = false
    }
}

// ── Password form ─────────────────────────────────────────────────────────────
const passwordForm = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
})
const passwordErrors = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
    general: '',
})
const passwordSuccess = ref('')
const passwordLoading = ref(false)

const showPasswords = reactive({ current: false, new: false, confirm: false })

function validatePassword() {
    passwordErrors.current_password = ''
    passwordErrors.password = ''
    passwordErrors.password_confirmation = ''
    passwordErrors.general = ''
    let valid = true
    if (!passwordForm.current_password) {
        passwordErrors.current_password = 'Current password is required.'
        valid = false
    }
    if (passwordForm.password.length < 8) {
        passwordErrors.password = 'New password must be at least 8 characters.'
        valid = false
    }
    if (passwordForm.password !== passwordForm.password_confirmation) {
        passwordErrors.password_confirmation = 'Passwords do not match.'
        valid = false
    }
    return valid
}

async function handlePasswordSubmit() {
    if (!validatePassword()) return
    passwordLoading.value = true
    passwordSuccess.value = ''
    try {
        await profileStore.updatePassword({ ...passwordForm })
        passwordSuccess.value = 'Password changed successfully!'
        passwordForm.current_password = ''
        passwordForm.password = ''
        passwordForm.password_confirmation = ''
    } catch (err) {
        const data = err?.response?.data
        if (data?.errors) {
            const e = data.errors
            if (e.current_password) passwordErrors.current_password = e.current_password[0]
            if (e.password) passwordErrors.password = e.password[0]
            if (e.password_confirmation) passwordErrors.password_confirmation = e.password_confirmation[0]
        } else {
            passwordErrors.general = data?.message ?? 'Something went wrong. Please try again.'
        }
    } finally {
        passwordLoading.value = false
    }
}

// ── Navigation ────────────────────────────────────────────────────────────────
function goToProfile() {
    router.push(`/profile/${authStore.user?.id}`)
}

// ── Init ──────────────────────────────────────────────────────────────────────
onMounted(() => {
    if (authStore.user) {
        profileForm.nickname = authStore.user.nickname ?? ''
    }
})
</script>

<style lang="scss" scoped>
@use '@/shared/styles/variables' as *;
@use '@/shared/styles/mixins' as *;

.profile-edit {
    min-height: 100vh;
    background: $color-bg;
    padding: 2.5rem 1rem;

    &__container {
        max-width: 560px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    // Back link
    &__back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: $color-text-muted;
        text-decoration: none;
        transition: color 0.15s ease;

        &:hover {
            color: $color-text;
        }
    }

    // Header
    &__header {
        margin-bottom: 0.5rem;
    }

    &__title {
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: $color-text;
        margin: 0 0 0.25rem;
    }

    &__subtitle {
        font-size: 0.875rem;
        color: $color-text-muted;
        margin: 0;
    }

    // Card
    &__card {
        background: $color-card;
        border: 1px solid $color-border;
        border-radius: 16px;
        padding: 1.75rem;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    // Section header row
    &__section-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: $color-primary;

        svg {
            flex-shrink: 0;
        }
    }

    &__section-title {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: $color-text;
        margin: 0;
    }

    // Avatar
    &__avatar-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid $color-border;
    }

    &__avatar-btn {
        position: relative;
        width: 8rem;
        height: 8rem;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid rgba($color-primary-raw, 0.6);
        box-shadow: 0 0 0 4px rgba($color-primary-raw, 0.1);
        background: none;
        cursor: pointer;
        padding: 0;
        transition: box-shadow 0.2s ease;

        &:focus-visible {
            outline: none;
            box-shadow: 0 0 0 4px rgba($color-primary-raw, 0.4);
        }

        &:hover .profile-edit__avatar-overlay,
        &:focus-visible .profile-edit__avatar-overlay {
            opacity: 1;
        }
    }

    &__avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    &__avatar-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
        background: rgba($color-bg-raw, 0.72);
        color: $color-text;
        opacity: 0;
        transition: opacity 0.2s ease;
        font-size: 0.75rem;
        font-weight: 500;
    }

    &__avatar-change-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: $color-primary;
        background: rgba($color-primary-raw, 0.1);
        border: 1px solid rgba($color-primary-raw, 0.4);
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.15s ease;

        &:hover {
            background: rgba($color-primary-raw, 0.2);
        }
    }

    &__avatar-hint {
        font-size: 0.75rem;
        color: $color-text-muted;
        margin: 0;
    }

    &__file-input {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    // Form
    &__form {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    &__field {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
    }

    &__label {
        font-size: 0.875rem;
        font-weight: 500;
        color: $color-text;
        line-height: 1;

        &--muted {
            color: $color-text-muted;
        }
    }

    &__field-error {
        font-size: 0.75rem;
        color: $color-danger;
        margin: 0;
    }

    &__field-hint {
        font-size: 0.75rem;
        color: $color-text-muted;
        margin: 0;
    }

    &__input--disabled {
        cursor: not-allowed;
        opacity: 0.6;
    }

    // Password input with eye toggle
    &__password-wrap {
        position: relative;

        :deep(input) {
            padding-right: 2.75rem;
        }
    }

    &__eye-btn {
        position: absolute;
        right: 0.625rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.25rem;
        color: $color-text-muted;
        display: flex;
        align-items: center;
        transition: color 0.15s ease;

        &:hover {
            color: $color-text;
        }
    }

    // 2-column grid for new/confirm password
    &__grid-2 {
        display: grid;
        gap: 1.25rem;

        @include breakpoint(sm) {
            grid-template-columns: 1fr 1fr;
        }
    }

    // Alerts
    &__alert {
        font-size: 0.875rem;
        padding: 0.625rem 0.875rem;
        border-radius: 8px;
        margin: 0;

        &--success {
            color: #22c55e;
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.25);
        }

        &--error {
            color: $color-danger;
            background: rgba($color-danger-raw, 0.1);
            border: 1px solid rgba($color-danger-raw, 0.25);
        }
    }

    // Actions row
    &__actions {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        padding-top: 0.25rem;

        @include breakpoint(sm) {
            flex-direction: row;
            justify-content: flex-end;
        }
    }
}
</style>