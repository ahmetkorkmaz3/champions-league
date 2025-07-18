<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
          🏆 Champions League Simülasyonu
        </h1>
        <p class="text-gray-600">
          4 takımlı lig simülasyonu - Premier League kurallarına göre
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="mb-8 flex flex-wrap gap-4">
        <button
          @click="playAllMatches"
          :disabled="isLoading"
          class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg font-medium transition-colors"
        >
          {{ isLoading ? 'Oynatılıyor...' : '🎮 Tüm Ligi Oynat' }}
        </button>

        <button
          @click="resetAllMatches"
          :disabled="isLoading"
          class="bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg font-medium transition-colors"
        >
          {{ isLoading ? 'Sıfırlanıyor...' : '🗑️ Tüm Maçları Sıfırla' }}
        </button>
      </div>

      <!-- League Table -->
      <LeagueTable
        title="📊 Lig Tablosu"
        :standings="localStandings"
      />

      <!-- Predictions Section -->
      <PredictedLeagueTable
        v-if="showPredictions"
        :predicted-standings="predictedStandings"
        :current-week="currentWeek"
      />

      <!-- Matches by Week -->
      <div v-for="(weekMatches, week) in localMatchesByWeek" :key="week" class="bg-white rounded-lg shadow-md mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-900">
            📅 {{ week }}. Hafta
          </h3>
          <button
            v-if="!isWeekPlayed(week)"
            @click="playWeek(week)"
            :disabled="isLoading"
            class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
          >
            {{ isLoading ? 'Oynatılıyor...' : 'Bu Haftayı Oynat' }}
          </button>
        </div>

        <div class="p-6">
          <div class="grid gap-4">
            <MatchRow
              v-for="match in weekMatches"
              :key="match.id"
              :match="match"
              @edit="editMatch"
              @reset="resetMatch"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Match Modal -->
    <EditMatchModal
      :show="showEditModal"
      :match="editingMatch"
      @close="showEditModal = false"
      @save="saveMatchEdit"
    />

    <!-- Championship Celebration Modal -->
    <ChampionshipCelebration
      :show="showChampionshipCelebration"
      :champion="champion"
      @close="closeChampionshipCelebration"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import axios from 'axios'
import MatchRow from '@/components/MatchRow.vue'
import EditMatchModal from '@/components/EditMatchModal.vue'
import LeagueTable from '@/components/LeagueTable.vue'
import PredictedLeagueTable from '@/components/PredictedLeagueTable.vue'
import ChampionshipCelebration from '@/components/ChampionshipCelebration.vue'
import { useChampionship } from '@/composables/useChampionship'
import type { Team, Match, Standing, MatchForm, PredictedStanding } from '@/types/champions-league'

const props = defineProps<{
  teams: Team[]
  matches: Match[]
  matchesByWeek: Record<number, Match[]>
  standings: Standing[]
}>()

// Reactive state
const localMatches = ref<Match[]>(props.matches)
const localMatchesByWeek = ref<Record<number, Match[]>>(props.matchesByWeek)
const localStandings = ref<Standing[]>(props.standings)

const isLoading = ref(false)
const isLoadingPredictions = ref(false)
const showEditModal = ref(false)
const editingMatch = ref<Match | null>(null)
const showPredictions = ref(false)
const currentWeek = ref(0)
const predictedStandings = ref<PredictedStanding[]>([])

// Championship celebration state
const showChampionshipCelebration = ref(false)
const { champion, shouldShowChampionshipCelebration } = useChampionship(localStandings, localMatches)

// Watch for championship celebration
watch(shouldShowChampionshipCelebration, (newValue) => {
  if (newValue) {
    showChampionshipCelebration.value = true
  }
})

const closeChampionshipCelebration = () => {
  showChampionshipCelebration.value = false
}

const isWeekPlayed = (week: number): boolean => {
  return localMatchesByWeek.value[week]?.every(match => match.is_played) || false
}

const updateLocalState = (data: any) => {
  if (data.matches) {
    localMatches.value = data.matches
  }
  if (data.matchesByWeek) {
    localMatchesByWeek.value = data.matchesByWeek
  }
  if (data.standings) {
    localStandings.value = data.standings
  }

  // Update current week after any match operation
  updateCurrentWeek()

  // Auto-load predictions if week 3 is completed
  if (currentWeek.value >= 3 && !showPredictions.value) {
    loadPredictions()
  }
}

const updateCurrentWeek = () => {
  const playedMatches = localMatches.value.filter(match => match.is_played)
  currentWeek.value = playedMatches.length > 0 ? Math.max(...playedMatches.map(match => match.week)) : 0
}

const loadPredictions = async () => {
  isLoadingPredictions.value = true
  try {
    const response = await axios.get(route('api.champions-league.predictions'))
    currentWeek.value = response.data.currentWeek
    predictedStandings.value = response.data.predictedStandings
    showPredictions.value = true
  } catch (error) {
    console.error('Tahminler yüklenirken hata oluştu:', error)
    alert('Tahminler yüklenirken bir hata oluştu!')
  } finally {
    isLoadingPredictions.value = false
  }
}

const playWeek = async (week: number) => {
  isLoading.value = true
  try {
    const response = await axios.post(route('api.champions-league.matches.play-week'), { week })
    if (response.data) {
      updateLocalState(response.data)
      if (currentWeek.value > 3) {
        await loadPredictions()
      }
    }
  } catch (error) {
    console.error('Hafta oynatılırken hata oluştu:', error)
    alert('Hafta oynatılırken bir hata oluştu!')
  } finally {
    isLoading.value = false
  }
}

const playAllMatches = async () => {
  isLoading.value = true
  try {
    const response = await axios.post(route('api.champions-league.matches.play-all'))
    if (response.data) {
      updateLocalState(response.data)
    }
  } catch (error) {
    console.error('Tüm maçlar oynatılırken hata oluştu:', error)
    alert('Tüm maçlar oynatılırken bir hata oluştu!')
  } finally {
    isLoading.value = false
  }
}

const editMatch = (match: Match) => {
  editingMatch.value = match
  showEditModal.value = true
}

const saveMatchEdit = async (form: MatchForm) => {
  try {
    if (!editingMatch.value) return
    const response = await axios.put(route('api.champions-league.matches.update', editingMatch.value.id), form)
    if (response.data) {
      updateLocalState(response.data)
    }
    showEditModal.value = false
    editingMatch.value = null
  } catch (error) {
    console.error('Maç güncellenirken hata oluştu:', error)
    alert('Maç güncellenirken bir hata oluştu!')
  }
}

const resetMatch = async (matchId: number) => {
  if (confirm('Bu maçı sıfırlamak istediğinizden emin misiniz?')) {
    try {
      const response = await axios.put(route('api.champions-league.matches.update', matchId), {
        home_score: null,
        away_score: null,
        is_played: false
      })
      if (response.data) {
        updateLocalState(response.data)
      }
    } catch (error) {
      console.error('Maç sıfırlanırken hata oluştu:', error)
      alert('Maç sıfırlanırken bir hata oluştu!')
    }
  }
}

const resetAllMatches = async () => {
  if (confirm('TÜM maçları sıfırlamak istediğinizden emin misiniz? Bu işlem geri alınamaz!')) {
    isLoading.value = true
    try {
      const response = await axios.post(route('api.champions-league.matches.reset'))
      if (response.data) {
        updateLocalState(response.data)
        showPredictions.value = false
        predictedStandings.value = []
      }
    } catch (error) {
      console.error('Maçlar sıfırlanırken hata oluştu:', error)
      alert('Maçlar sıfırlanırken bir hata oluştu!')
    } finally {
      isLoading.value = false
    }
  }
}



// Initialize current week on component mount
updateCurrentWeek()

// Auto-load predictions if week 3 is already completed on page load
if (currentWeek.value >= 3) {
  loadPredictions()
}
</script>
