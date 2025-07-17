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

        <Link
            :href="route('champions-league.predictions')"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
          🔮 Tahminler
        </Link>
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
import { Link } from '@inertiajs/vue3'
import MatchRow from '@/components/MatchRow.vue'
import EditMatchModal from '@/components/EditMatchModal.vue'
import type { Team, Match, Standing, MatchForm } from '@/types/champions-league'

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
const showEditModal = ref(false)
const editingMatch = ref<Match | null>(null)

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
}

const playWeek = async (week: number) => {
  isLoading.value = true
  try {
    const response = await axios.post(route('api.champions-league.matches.play-week'), { week })
    if (response.data) {
      updateLocalState(response.data)
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
      }
    } catch (error) {
      console.error('Maçlar sıfırlanırken hata oluştu:', error)
      alert('Maçlar sıfırlanırken bir hata oluştu!')
    } finally {
      isLoading.value = false
    }
  }
}
</script>
