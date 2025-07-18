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
      <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-xl font-semibold text-gray-900">📊 Lig Tablosu</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Sıra
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Takım
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  O
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  G
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  B
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  M
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  A
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Y
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Av
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Puan
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="standing in localStandings" :key="standing.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ standing.position }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8">
                      <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                        <img :src="standing.team.logo" :alt="standing.team.name" />
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        {{ standing.team.name }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ standing.team.city }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                  {{ standing.wins + standing.draws + standing.losses }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                  {{ standing.wins }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                  {{ standing.draws }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                  {{ standing.losses }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                  {{ standing.goals_for }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                  {{ standing.goals_against }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                  {{ standing.goal_difference }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-900">
                  {{ standing.points }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Predictions Section -->
      <div v-if="showPredictions" class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-xl font-semibold text-gray-900">🔮 Lig Tahminleri</h2>
          <p class="text-sm text-gray-600 mt-1">
            {{ currentWeek }}. hafta sonrası tahmini lig tablosu
          </p>
        </div>

        <!-- Prediction Info -->
        <div class="px-6 py-4 bg-blue-50 border-b border-blue-200">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-blue-800">
                Tahmin Nasıl Hesaplanıyor?
              </h3>
              <div class="mt-2 text-sm text-blue-700">
                <p>
                  Bu tahmin, {{ currentWeek }}. haftaya kadar oynanan maçların gerçek sonuçları ile
                  kalan maçların simüle edilmiş sonuçları birleştirilerek hesaplanmıştır.
                  Takımların güç seviyeleri ve ev sahibi avantajları dikkate alınmıştır.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Predicted League Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Sıra
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Takım
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  O
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  G
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  B
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  M
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  A
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Y
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Av
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Puan
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Güç
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(standing, index) in predictedStandings" :key="index" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  <div class="flex items-center">
                    <span class="mr-2">{{ standing.position }}</span>
                    <!-- Trophy for winner -->
                    <span v-if="standing.position === 1" class="text-yellow-500">🏆</span>
                    <!-- Medal for runner-up -->
                    <span v-else-if="standing.position === 2" class="text-gray-400">🥈</span>
                    <!-- Bronze for 3rd -->
                    <span v-else-if="standing.position === 3" class="text-amber-600">🥉</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8">
                      <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                        <img :src="standing.team.logo" :alt="standing.team.name" class="w-full h-full object-cover" />
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        {{ standing.team.name }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ standing.team.city }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                  {{ standing.matches_played }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                  {{ standing.wins }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                  {{ standing.draws }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                  {{ standing.losses }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                  {{ standing.goals_for }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                  {{ standing.goals_against }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                  <span :class="standing.goal_difference > 0 ? 'text-green-600' : standing.goal_difference < 0 ? 'text-red-600' : 'text-gray-900'">
                    {{ standing.goal_difference > 0 ? '+' : '' }}{{ standing.goal_difference }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-900">
                  {{ standing.points }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                  <div class="flex items-center justify-center">
                    <div class="w-16 bg-gray-200 rounded-full h-2">
                      <div
                        class="bg-blue-600 h-2 rounded-full"
                        :style="{ width: (standing.team.power_level / 100) * 100 + '%' }"
                      ></div>
                    </div>
                    <span class="ml-2 text-xs text-gray-500">{{ standing.team.power_level }}</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Statistics -->
        <div class="px-6 py-4 bg-gray-50">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-sm p-4">
              <h3 class="text-lg font-semibold text-gray-900 mb-3">📈 İstatistikler</h3>
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">En Çok Gol Atan:</span>
                  <span class="text-sm font-medium text-gray-900">
                    {{ getTopScorer() }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">En Az Gol Yiyen:</span>
                  <span class="text-sm font-medium text-gray-900">
                    {{ getBestDefense() }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">En Yüksek Puan:</span>
                  <span class="text-sm font-medium text-gray-900">
                    {{ getHighestPoints() }}
                  </span>
                </div>
              </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4">
              <h3 class="text-lg font-semibold text-gray-900 mb-3">🎯 Şampiyonluk Olasılığı</h3>
              <div class="space-y-2">
                <div v-for="(standing, index) in predictedStandings.slice(0, 3)" :key="index" class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">{{ standing.team.name }}:</span>
                  <div class="flex items-center">
                    <div class="w-12 bg-gray-200 rounded-full h-2 mr-2">
                      <div
                        class="bg-green-600 h-2 rounded-full"
                        :style="{ width: getChampionshipProbability(index) + '%' }"
                      ></div>
                    </div>
                    <span class="text-xs text-gray-500">{{ getChampionshipProbability(index) }}%</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4">
              <h3 class="text-lg font-semibold text-gray-900 mb-3">⚽ Maç Analizi</h3>
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Toplam Maç:</span>
                  <span class="text-sm font-medium text-gray-900">6</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Oynanan Maç:</span>
                  <span class="text-sm font-medium text-gray-900">{{ currentWeek }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Kalan Maç:</span>
                  <span class="text-sm font-medium text-gray-900">{{ 6 - currentWeek }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

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
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'
import MatchRow from '@/components/MatchRow.vue'
import EditMatchModal from '@/components/EditMatchModal.vue'
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

// Prediction helper functions
const getTopScorer = () => {
  if (!predictedStandings.value?.length) return '-'
  const topScorer = predictedStandings.value.reduce((prev, current) =>
    prev.goals_for > current.goals_for ? prev : current
  )
  return `${topScorer.team.name} (${topScorer.goals_for})`
}

const getBestDefense = () => {
  if (!predictedStandings.value?.length) return '-'
  const bestDefense = predictedStandings.value.reduce((prev, current) =>
    prev.goals_against < current.goals_against ? prev : current
  )
  return `${bestDefense.team.name} (${bestDefense.goals_against})`
}

const getHighestPoints = () => {
  if (!predictedStandings.value?.length) return '-'
  const highestPoints = predictedStandings.value.reduce((prev, current) =>
    prev.points > current.points ? prev : current
  )
  return `${highestPoints.team.name} (${highestPoints.points})`
}

const getChampionshipProbability = (position: number) => {
  const probabilities = [60, 25, 15] // İlk 3 takımın şampiyonluk olasılıkları
  return probabilities[position] || 0
}

// Initialize current week on component mount
updateCurrentWeek()

// Auto-load predictions if week 3 is already completed on page load
if (currentWeek.value >= 3) {
  loadPredictions()
}
</script>
