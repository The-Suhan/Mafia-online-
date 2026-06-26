<template>
    <div class="home-page">
        <!-- Ambient glow -->
        <div class="home-page__glow" aria-hidden="true" />

        <!-- Navbar -->
        <header class="home-page__navbar">
            <span class="home-page__navbar-logo">M<span class="home-page__navbar-logo--destructive">A</span>FIA</span>
            <nav class="home-page__navbar-right">
                <RouterLink v-if="authStore.user?.is_admin" to="/admin" class="home-page__admin-link">
                    Admin Panel
                </RouterLink>
                <div class="home-page__user">
                    <span class="home-page__user-avatar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="5" />
                            <path d="M3 21a9 9 0 0 1 18 0" />
                        </svg>
                    </span>
                    <span class="home-page__user-name">{{ authStore.user?.nickname ?? authStore.user?.username }}</span>
                </div>
                <button class="home-page__logout-btn" @click="handleLogout">Logout</button>
            </nav>
        </header>

        <!-- Main content -->
        <main class="home-page__content">
            <!-- Hero -->
            <section class="home-page__hero">
                <span class="home-page__eyebrow">Social Deduction</span>
                <h1 class="home-page__title">M<span class="home-page__title--destructive">A</span>FIA</h1>
                <p class="home-page__subtitle">Trust no one. Find the mafia before they find you.</p>
            </section>

            <!-- Actions -->
            <section class="home-page__actions">
                <BaseButton class="home-page__create-btn" @click="showCreateModal = true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Create Room
                </BaseButton>

                <div class="home-page__divider">
                    <span class="home-page__divider-line" />
                    <span class="home-page__divider-text">or join with code</span>
                    <span class="home-page__divider-line" />
                </div>

                <div class="home-page__join-row">
                    <BaseInput v-model="roomCode" placeholder="ENTER CODE" maxlength="6" aria-label="Room code"
                        class="home-page__code-input" @keydown.enter="handleJoin" />
                    <BaseButton variant="secondary" :disabled="!roomCode.trim()" class="home-page__join-btn"
                        @click="handleJoin">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                            <polyline points="10 17 15 12 10 7" />
                            <line x1="15" y1="12" x2="3" y2="12" />
                        </svg>
                        Join
                    </BaseButton>
                </div>
            </section>

            <!-- Recent rooms -->
            <section class="home-page__rooms">
                <div class="home-page__rooms-header">
                    <h2 class="home-page__rooms-title">Recent Rooms</h2>
                    <span class="home-page__rooms-count">{{ homeStore.rooms.length }} active</span>
                </div>

                <!-- Loading skeletons -->
                <template v-if="homeStore.loading">
                    <div v-for="i in 3" :key="i" class="home-page__skeleton" />
                </template>

                <!-- Error -->
                <p v-else-if="homeStore.error" class="home-page__error">{{ homeStore.error }}</p>

                <!-- Empty -->
                <p v-else-if="homeStore.rooms.length === 0" class="home-page__empty">No open rooms right now.</p>

                <!-- Rooms list -->
                <ul v-else class="home-page__rooms-list">
                    <li v-for="room in homeStore.rooms" :key="room.id">
                        <RoomCard :room="room" @join="handleJoinRoom" />
                    </li>
                </ul>
            </section>
        </main>

        <!-- Create Room Modal -->
        <Teleport to="body">
            <div v-if="showCreateModal" class="home-page__modal-overlay" @click.self="showCreateModal = false">
                <div class="home-page__modal">
                    <div class="home-page__modal-header">
                        <h3 class="home-page__modal-title">Create Room</h3>
                        <button class="home-page__modal-close" aria-label="Close" @click="showCreateModal = false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>
                    </div>

                    <div class="home-page__modal-body">
                        <div class="home-page__field">
                            <label class="home-page__label" for="room-name">Room Name</label>
                            <BaseInput id="room-name" v-model="createForm.name" placeholder="e.g. Midnight Town"
                                maxlength="30" class="home-page__modal-input" />
                        </div>

                        <div class="home-page__field">
                            <label class="home-page__label" for="max-players">Max Players</label>
                            <select id="max-players" v-model="createForm.max_players" class="home-page__select">
                                <option :value="4">4 players</option>
                                <option :value="6">6 players</option>
                                <option :value="8">8 players</option>
                                <option :value="10">10 players</option>
                            </select>
                        </div>

                        <p v-if="createError" class="home-page__form-error">{{ createError }}</p>
                    </div>

                    <div class="home-page__modal-footer">
                        <BaseButton variant="secondary" @click="showCreateModal = false">Cancel</BaseButton>
                        <BaseButton :disabled="!createForm.name.trim() || creating" @click="handleCreate">
                            {{ creating ? 'Creating…' : 'Create' }}
                        </BaseButton>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/modules/auth/store'
import { useHomeStore } from '../store'
import RoomCard from '../components/RoomCard.vue'
import BaseButton from '@/shared/components/BaseButton.vue'
import BaseInput from '@/shared/components/BaseInput.vue'

const router = useRouter()
const authStore = useAuthStore()
const homeStore = useHomeStore()

const roomCode = ref('')
const showCreateModal = ref(false)
const creating = ref(false)
const createError = ref('')
const createForm = ref({ name: '', max_players: 6 })

onMounted(() => {
    homeStore.fetchRooms()
})

async function handleLogout() {
    authStore.logout()
    router.push('/login')
}

async function handleJoin() {
    const code = roomCode.value.trim().toUpperCase()
    if (!code) return
    await handleJoinRoom(code)
}

async function handleJoinRoom(code) {
    try {
        const room = await homeStore.joinRoom(code)
        router.push(`/room/${room.id}`)
    } catch (e) {
        // error is stored in homeStore.error
    }
}

async function handleCreate() {
    if (!createForm.value.name.trim()) return
    creating.value = true
    createError.value = ''
    try {
        const room = await homeStore.createRoom({
            name: createForm.value.name.trim(),
            max_players: createForm.value.max_players,
        })
        showCreateModal.value = false
        createForm.value = { name: '', max_players: 6 }
        router.push(`/room/${room.id}`)
    } catch (e) {
        createError.value = e?.response?.data?.message ?? 'Could not create room.'
    } finally {
        creating.value = false
    }
}
</script>

<style lang="scss" scoped>
@use '@/shared/styles/variables' as *;
@use '@/shared/styles/mixins' as *;

.home-page {
    position: relative;
    display: flex;
    flex-direction: column;
    min-height: 100svh;
    overflow: hidden;

    &__glow {
        pointer-events: none;
        position: absolute;
        left: 50%;
        top: 0;
        z-index: 0;
        width: 42rem;
        height: 42rem;
        transform: translate(-50%, -33%);
        border-radius: 50%;
        background: rgba($color-primary, 0.18);
        filter: blur(120px);
    }

    // ─── Navbar ────────────────────────────────────────────────
    &__navbar {
        position: sticky;
        top: 0;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        background: $color-bg;
        border-bottom: 1px solid $color-border;
    }

    &__navbar-logo {
        font-family: $font-mono;
        font-size: 1.1rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        color: $color-text;

        &--destructive {
            color: $color-primary;
        }
    }

    &__navbar-right {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    &__admin-link {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: $color-primary;
        text-decoration: none;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        border: 1px solid rgba($color-primary, 0.35);
        transition: background 0.15s, border-color 0.15s;

        &:hover {
            background: rgba($color-primary, 0.12);
            border-color: rgba($color-primary, 0.6);
        }
    }

    &__user {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    &__user-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background: rgba($color-primary, 0.15);
        color: $color-primary;
        border: 1px solid rgba($color-primary, 0.3);
    }

    &__user-name {
        font-size: 0.875rem;
        font-weight: 500;
        color: $color-text;
        max-width: 8rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    &__logout-btn {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: $color-text-muted;
        background: none;
        border: 1px solid $color-border;
        border-radius: 6px;
        padding: 0.35rem 0.65rem;
        cursor: pointer;
        transition: color 0.15s, border-color 0.15s;

        &:hover {
            color: $color-text;
            border-color: $color-text-muted;
        }
    }

    // ─── Main content ───────────────────────────────────────────
    &__content {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        gap: 2.5rem;
        width: 100%;
        max-width: 480px;
        margin: 0 auto;
        padding: 2.5rem 1rem 4rem;
    }

    // ─── Hero ───────────────────────────────────────────────────
    &__hero {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    &__eyebrow {
        display: inline-block;
        margin-bottom: 1rem;
        padding: 0.25rem 0.85rem;
        border-radius: 999px;
        border: 1px solid $color-border;
        background: rgba($color-card, 0.6);
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        color: $color-text-muted;
    }

    &__title {
        font-size: clamp(3.5rem, 12vw, 5rem);
        font-weight: 900;
        letter-spacing: -0.02em;
        line-height: 1;
        color: $color-text;
        margin: 0;

        &--destructive {
            color: $color-primary;
        }
    }

    &__subtitle {
        margin-top: 0.75rem;
        font-size: 0.875rem;
        line-height: 1.6;
        color: $color-text-muted;
        max-width: 22rem;
    }

    // ─── Actions ────────────────────────────────────────────────
    &__actions {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    &__create-btn {
        height: 3.5rem;
        font-size: 1rem;
        font-weight: 600;
        gap: 0.5rem;
        box-shadow: 0 8px 30px rgba($color-primary, 0.2);
    }

    &__divider {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.25rem 0;
    }

    &__divider-line {
        flex: 1;
        height: 1px;
        background: $color-border;
    }

    &__divider-text {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: $color-text-muted;
        white-space: nowrap;
    }

    &__join-row {
        display: flex;
        gap: 0.5rem;
    }

    &__code-input {
        flex: 1;
        height: 3.5rem;
        text-align: center;
        font-family: $font-mono;
        font-size: 1.15rem;
        letter-spacing: 0.3em;
        text-transform: uppercase;

        &::placeholder {
            letter-spacing: 0.2em;
            opacity: 0.5;
        }
    }

    &__join-btn {
        height: 3.5rem;
        padding: 0 1.25rem;
        gap: 0.4rem;
        font-weight: 600;
        flex-shrink: 0;
    }

    // ─── Recent rooms ────────────────────────────────────────────
    &__rooms {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    &__rooms-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    &__rooms-title {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: $color-text-muted;
        margin: 0;
    }

    &__rooms-count {
        font-size: 0.7rem;
        color: $color-text-muted;
    }

    &__rooms-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    &__skeleton {
        height: 4.5rem;
        border-radius: 0.75rem;
        background: linear-gradient(90deg,
                $color-card 25%,
                rgba($color-card, 0.5) 50%,
                $color-card 75%);
        background-size: 200% 100%;
        animation: skeleton-shimmer 1.4s ease-in-out infinite;
    }

    &__error {
        font-size: 0.85rem;
        color: $color-primary;
        text-align: center;
        padding: 1rem;
    }

    &__empty {
        font-size: 0.85rem;
        color: $color-text-muted;
        text-align: center;
        padding: 1.5rem;
        border: 1px dashed $color-border;
        border-radius: 0.75rem;
    }

    // ─── Modal ──────────────────────────────────────────────────
    &__modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 50;
        background: rgba(0, 0, 0, 0.65);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    &__modal {
        background: $color-card;
        border: 1px solid $color-border;
        border-radius: 1rem;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
    }

    &__modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.5rem 1rem;
        border-bottom: 1px solid $color-border;
    }

    &__modal-title {
        font-size: 1rem;
        font-weight: 700;
        color: $color-text;
        margin: 0;
    }

    &__modal-close {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 6px;
        background: none;
        border: none;
        color: $color-text-muted;
        cursor: pointer;
        transition: color 0.15s, background 0.15s;

        &:hover {
            color: $color-text;
            background: $color-secondary;
        }
    }

    &__modal-body {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
    }

    &__modal-footer {
        display: flex;
        gap: 0.5rem;
        justify-content: flex-end;
        padding: 1rem 1.5rem 1.25rem;
        border-top: 1px solid $color-border;
    }

    &__field {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    &__label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: $color-text-muted;
    }

    &__modal-input {
        height: 2.75rem;
    }

    &__select {
        height: 2.75rem;
        width: 100%;
        padding: 0 0.75rem;
        background: $color-secondary;
        border: 1px solid $color-border;
        border-radius: 0.5rem;
        color: $color-text;
        font-size: 0.9rem;
        cursor: pointer;
        outline: none;
        transition: border-color 0.15s;

        &:focus {
            border-color: $color-primary;
        }
    }

    &__form-error {
        font-size: 0.8rem;
        color: $color-primary;
    }
}

@keyframes skeleton-shimmer {
    0% {
        background-position: 200% 0;
    }

    100% {
        background-position: -200% 0;
    }
}
</style>