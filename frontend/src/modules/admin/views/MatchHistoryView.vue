<template>
    <AdminLayout>
        <div class="admin-match-history">
            <header class="admin-match-history__header">
                <div>
                    <h1 class="admin-match-history__title">MATCH HISTORY</h1>
                    <p class="admin-match-history__subtitle">
                        {{ matchesTotal }} {{ matchesTotal === 1 ? 'match' : 'matches' }} total
                    </p>
                </div>
            </header>

            <div class="admin-match-history__toolbar">
                <div class="admin-match-history__status-group" role="group" aria-label="Filter by status">
                    <button v-for="opt in STATUS_OPTIONS" :key="opt.value" type="button"
                        class="admin-match-history__status-btn"
                        :class="{ 'admin-match-history__status-btn--active': status === opt.value }"
                        :aria-pressed="status === opt.value" @click="setStatus(opt.value)">
                        {{ opt.label }}
                    </button>
                </div>

                <div class="admin-match-history__date-range">
                    <CalendarDays class="admin-match-history__date-icon" :size="16" aria-hidden="true" />
                    <input v-model="fromDate" type="date" class="admin-match-history__date-input"
                        aria-label="From date" />
                    <span class="admin-match-history__date-separator">–</span>
                    <input v-model="toDate" type="date" class="admin-match-history__date-input" aria-label="To date" />
                </div>
            </div>

            <div class="admin-match-history__table-wrapper">
                <table class="admin-match-history__table">
                    <thead>
                        <tr>
                            <th>Match ID</th>
                            <th>Room Name</th>
                            <th>Players</th>
                            <th>Winner</th>
                            <th>Duration</th>
                            <th>Started At</th>
                            <th>Ended At</th>
                            <th class="admin-match-history__col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="matchesLoading">
                            <td colspan="8" class="admin-match-history__state-cell">Loading matches…</td>
                        </tr>
                        <tr v-else-if="mappedMatches.length === 0">
                            <td colspan="8" class="admin-match-history__state-cell">No matches found.</td>
                        </tr>
                        <tr v-for="match in mappedMatches" v-else :key="match.id" class="admin-match-history__row">
                            <td class="admin-match-history__match-id">#{{ match.id }}</td>
                            <td>{{ match.roomName }}</td>
                            <td>
                                {{ match.playerCount }}<span v-if="match.maxPlayers">/{{ match.maxPlayers }}</span>
                            </td>
                            <td>
                                <span v-if="match.status !== 'finished'"
                                    class="admin-match-history__badge admin-match-history__badge--in-progress">
                                    In Progress
                                </span>
                                <span v-else-if="match.winner === 'mafia'"
                                    class="admin-match-history__badge admin-match-history__badge--mafia">
                                    <Skull :size="14" aria-hidden="true" />
                                    MAFIA
                                </span>
                                <span v-else class="admin-match-history__badge admin-match-history__badge--villagers">
                                    <Shield :size="14" aria-hidden="true" />
                                    VILLAGERS
                                </span>
                            </td>
                            <td>{{ formatDuration(match.durationSeconds) }}</td>
                            <td>{{ formatDateTime(match.startedAt) }}</td>
                            <td>{{ formatDateTime(match.endedAt) }}</td>
                            <td class="admin-match-history__col-actions">
                                <button type="button" class="admin-match-history__action-btn"
                                    aria-label="View match details" @click="viewDetails(match.id)">
                                    <Eye :size="16" aria-hidden="true" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <footer class="admin-match-history__pagination">
                <button type="button" class="admin-match-history__page-btn" :disabled="currentPage <= 1"
                    @click="goToPage(currentPage - 1)">
                    <ChevronLeft :size="16" aria-hidden="true" />
                    Prev
                </button>
                <span class="admin-match-history__page-info">
                    Page {{ currentPage }} of {{ pageCount }}
                </span>
                <button type="button" class="admin-match-history__page-btn" :disabled="currentPage >= pageCount"
                    @click="goToPage(currentPage + 1)">
                    Next
                    <ChevronRight :size="16" aria-hidden="true" />
                </button>
            </footer>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { CalendarDays, Eye, Skull, Shield, ChevronLeft, ChevronRight } from 'lucide-vue-next'
// NOT: '../components/AdminLayout.vue' yolu projenizdeki mevcut dosyaya işaret ediyor, yeniden üretilmedi.
import AdminLayout from '../components/AdminLayout.vue'
import { useAdminStore } from '../store'

const adminStore = useAdminStore()
const router = useRouter()

const STATUS_OPTIONS = [
    { label: 'All', value: 'all' },
    { label: 'Finished', value: 'finished' },
    { label: 'In Progress', value: 'in_progress' },
]

const status = ref('all')
const fromDate = ref('')
const toDate = ref('')
const currentPage = ref(1)

const matches = computed(() => adminStore.matches)
const matchesTotal = computed(() => adminStore.matchesTotal)
const matchesLoading = computed(() => adminStore.matchesLoading)
const pageCount = computed(() => adminStore.matchesTotalPages)

function loadMatches() {
    adminStore.fetchMatches({
        page: currentPage.value,
        status: status.value,
        dateFrom: fromDate.value,
        dateTo: toDate.value,
    })
}

function setStatus(value) {
    status.value = value
    currentPage.value = 1
}

function goToPage(page) {
    if (page < 1 || page > pageCount.value) return
    currentPage.value = page
}

function viewDetails(id) {
    router.push(`/admin/history-match/${id}`)
}

// NOT: Backend alan adları varsayımdır (örn. room_name, player_count, duration_seconds...).
// Gerçek API yanıtınızdaki alan adları farklıysa sadece bu fonksiyonu güncellemeniz yeterli.
function mapMatch(match) {
    return {
        id: match.id,
        roomName: match.room_name ?? match.roomName ?? '—',
        playerCount: match.player_count ?? match.playerCount ?? 0,
        maxPlayers: match.max_players ?? match.maxPlayers ?? null,
        winner: match.winner ?? null, // 'mafia' | 'villagers' | null
        status: match.status ?? 'in_progress', // 'finished' | 'in_progress'
        durationSeconds: match.duration_seconds ?? match.durationSeconds ?? null,
        startedAt: match.started_at ?? match.startedAt ?? null,
        endedAt: match.ended_at ?? match.endedAt ?? null,
    }
}

const mappedMatches = computed(() => matches.value.map(mapMatch))

function formatDuration(seconds) {
    if (seconds === null || seconds === undefined) return '—'
    const m = Math.floor(seconds / 60)
    const s = Math.floor(seconds % 60)
    return `${m}:${String(s).padStart(2, '0')}`
}

function formatDateTime(iso) {
    if (!iso) return '—'
    const date = new Date(iso)
    if (Number.isNaN(date.getTime())) return '—'
    return date.toLocaleString(undefined, {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

watch([status, fromDate, toDate, currentPage], loadMatches)
onMounted(loadMatches)
</script>

<style lang="scss" scoped>
// NOT: Proje genelindeki _variables.scss / _mixins.scss dosyalarının gerçek içeriği
// paylaşılmadığı için renkler burada bağımsız olarak tanımlandı (orijinal v0 tasarımındaki
// oklch koyu tema paletine karşılık gelecek şekilde). Projenizde eşdeğer token'lar varsa
// bu değişkenleri onlarla değiştirebilirsiniz.
$color-bg-card: #211a17;
$color-border: rgba(255, 255, 255, 0.08);
$color-text: #f5f1ec;
$color-text-muted: #a89c92;
$color-primary: #e6483f;

$color-mafia: #ef4444;
$color-mafia-bg: rgba(239, 68, 68, 0.15);
$color-mafia-border: rgba(239, 68, 68, 0.4);

$color-villagers: #34d399;
$color-villagers-bg: rgba(52, 211, 153, 0.15);
$color-villagers-border: rgba(52, 211, 153, 0.4);

$color-in-progress: #9ca3af;
$color-in-progress-bg: rgba(156, 163, 175, 0.15);

.admin-match-history {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;

    &__header {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
    }

    &__title {
        font-size: 1.25rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        color: $color-text;
    }

    &__subtitle {
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: $color-text-muted;
    }

    &__toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
        justify-content: space-between;
    }

    &__status-group {
        display: inline-flex;
        border: 1px solid $color-border;
        background: $color-bg-card;
        border-radius: 0.5rem;
        padding: 0.25rem;
    }

    &__status-btn {
        border: none;
        background: transparent;
        color: $color-text-muted;
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: background-color 0.15s ease, color 0.15s ease;

        &:hover {
            color: $color-text;
        }

        &--active {
            background: $color-primary;
            color: #fff;
        }
    }

    &__date-range {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid $color-border;
        background: $color-bg-card;
        border-radius: 0.5rem;
        padding: 0 0.75rem;
        height: 2.5rem;
    }

    &__date-icon {
        color: $color-text-muted;
    }

    &__date-input {
        background: transparent;
        border: none;
        color: $color-text;
        font-size: 0.875rem;
        color-scheme: dark;

        &:focus {
            outline: none;
        }
    }

    &__date-separator {
        color: $color-text-muted;
    }

    &__table-wrapper {
        overflow-x: auto;
        border: 1px solid $color-border;
        border-radius: 0.75rem;
    }

    &__table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;

        thead th {
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: $color-text-muted;
            border-bottom: 1px solid $color-border;
            white-space: nowrap;
        }

        tbody td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid $color-border;
            color: $color-text;
            white-space: nowrap;
        }
    }

    &__row {
        transition: background-color 0.15s ease;

        &:hover {
            background: rgba(255, 255, 255, 0.03);
        }
    }

    &__match-id {
        font-family: var(--font-mono, 'Geist Mono', monospace);
        color: $color-text-muted;
    }

    &__state-cell {
        text-align: center;
        padding: 2rem 1rem;
        color: $color-text-muted;
    }

    &__badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.25rem 0.625rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;

        &--mafia {
            color: $color-mafia;
            background: $color-mafia-bg;
            border: 1px solid $color-mafia-border;
        }

        &--villagers {
            color: $color-villagers;
            background: $color-villagers-bg;
            border: 1px solid $color-villagers-border;
        }

        &--in-progress {
            color: $color-in-progress;
            background: $color-in-progress-bg;
        }
    }

    &__col-actions {
        text-align: right;
    }

    &__action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border: 1px solid $color-border;
        background: $color-bg-card;
        color: $color-text-muted;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: color 0.15s ease, border-color 0.15s ease;

        &:hover {
            color: $color-text;
            border-color: $color-primary;
        }
    }

    &__pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.875rem;
        color: $color-text-muted;
    }

    &__page-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        border: 1px solid $color-border;
        background: $color-bg-card;
        color: $color-text;
        padding: 0.5rem 0.875rem;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        cursor: pointer;
        transition: opacity 0.15s ease;

        &:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
    }
}
</style>