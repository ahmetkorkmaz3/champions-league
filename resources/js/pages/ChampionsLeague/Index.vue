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

        <router-link
          :to="{ name: 'champions-league.predictions' }"
          class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors"
        >
          🔮 Tahminler
        </router-link>
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
              <tr v-for="standing in standings" :key="standing.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ standing.position }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8">
                      <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-sm font-medium text-gray-700">
                          {{ standing.team.name.charAt(0) }}
                        </span>
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
      <div v-for="(weekMatches, week) in matchesByWeek" :key="week" class="bg-white rounded-lg shadow-md mb-6">
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
            <div
              v-for="match in weekMatches"
              :key="match.id"
              class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
            >
              <div class="flex items-center space-x-4 flex-1">
                <!-- Home Team -->
                <div class="flex items-center space-x-3 flex-1 justify-end">
                  <span class="text-sm font-medium text-gray-900">
                    {{ match.home_team.name }}
                  </span>
                  <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center">
                    <span class="text-xs font-medium text-gray-700">
                      {{ match.home_team.name.charAt(0) }}
                    </span>
                  </div>
                </div>

                <!-- Score -->
                <div class="flex items-center space-x-2">
                  <span class="text-lg font-bold text-gray-900">
                    {{ match.is_played ? match.home_score : '-' }}
                  </span>
                  <span class="text-gray-500">-</span>
                  <span class="text-lg font-bold text-gray-900">
                    {{ match.is_played ? match.away_score : '-' }}
                  </span>
                </div>

                <!-- Away Team -->
                <div class="flex items-center space-x-3 flex-1 justify-start">
                  <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center">
                    <span class="text-xs font-medium text-gray-700">
                      {{ match.away_team.name.charAt(0) }}
                    </span>
                  </div>
                  <span class="text-sm font-medium text-gray-900">
                    {{ match.away_team.name }}
                  </span>
                </div>
              </div>

              <!-- Match Actions -->
              <div class="ml-4 flex space-x-2">
                <button
                  v-if="match.is_played"
                  @click="editMatch(match)"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                  Düzenle
                </button>
                <button
                  v-if="match.is_played"
                  @click="resetMatch(match.id)"
                  class="text-red-600 hover:text-red-800 text-sm font-medium"
                >
                  Sıfırla
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Match Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Maç Sonucunu Düzenle</h3>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium">{{ editingMatch?.home_team?.name }}</span>
              <input
                v-model="editForm.home_score"
                type="number"
                min="0"
                class="w-16 px-2 py-1 border border-gray-300 rounded text-center"
              />
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium">{{ editingMatch?.away_team?.name }}</span>
              <input
                v-model="editForm.away_score"
                type="number"
                min="0"
                class="w-16 px-2 py-1 border border-gray-300 rounded text-center"
              />
            </div>
          </div>
          <div class="flex justify-end space-x-3 mt-6">
            <button
              @click="showEditModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
            >
              İptal
            </button>
            <button
              @click="saveMatchEdit"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
            >
              Kaydet
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

interface Team {
  id: number
  name: string
  city: string
}

interface Match {
  id: number
  home_team: Team
  away_team: Team
  home_score: number | null
  away_score: number | null
  is_played: boolean
  week: number
}

interface Standing {
  id: number
  position: number
  team: Team
  wins: number
  draws: number
  losses: number
  goals_for: number
  goals_against: number
  goal_difference: number
  points: number
}

const props = defineProps<{
  teams: Team[]
  matches: Match[]
  matchesByWeek: Record<number, Match[]>
  standings: Standing[]
}>()



const isLoading = ref(false)
const showEditModal = ref(false)
const editingMatch = ref<Match | null>(null)
const editForm = ref({
  home_score: 0,
  away_score: 0,
})

const isWeekPlayed = (week: number): boolean => {
  return props.matchesByWeek[week]?.every(match => match.is_played) || false
}

const playWeek = async (week: number) => {
  isLoading.value = true
  try {
    await router.post(route('api.champions-league.matches.play-week'), { week })
    // Başarılı olduktan sonra sayfayı yeniden ziyaret et
    router.visit(route('champions-league.index'), {
      method: 'get',
      preserveState: false,
      preserveScroll: false
    })
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
    await router.post(route('api.champions-league.matches.play-all'))
    // Başarılı olduktan sonra sayfayı yeniden ziyaret et
    router.visit(route('champions-league.index'), {
      method: 'get',
      preserveState: false,
      preserveScroll: false
    })
  } catch (error) {
    console.error('Tüm maçlar oynatılırken hata oluştu:', error)
    alert('Tüm maçlar oynatılırken bir hata oluştu!')
  } finally {
    isLoading.value = false
  }
}

const editMatch = (match: Match) => {
  editingMatch.value = match
  editForm.value = {
    home_score: match.home_score || 0,
    away_score: match.away_score || 0,
  }
  showEditModal.value = true
}

const saveMatchEdit = async () => {
  try {
    if (!editingMatch.value) return
    await router.put(route('api.champions-league.matches.update', editingMatch.value.id), editForm.value)
    showEditModal.value = false
    editingMatch.value = null
    // Başarılı olduktan sonra sayfayı yeniden ziyaret et
    router.visit(route('champions-league.index'), {
      method: 'get',
      preserveState: false,
      preserveScroll: false
    })
  } catch (error) {
    console.error('Maç güncellenirken hata oluştu:', error)
    alert('Maç güncellenirken bir hata oluştu!')
  }
}

const resetMatch = async (matchId: number) => {
  if (confirm('Bu maçı sıfırlamak istediğinizden emin misiniz?')) {
    try {
      // Maçı sıfırlamak için önce mevcut skoru temizle
      await router.put(route('api.champions-league.matches.update', matchId), {
        home_score: null,
        away_score: null,
        is_played: false
      })
      // Başarılı olduktan sonra sayfayı yeniden ziyaret et
      router.visit(route('champions-league.index'), {
        method: 'get',
        preserveState: false,
        preserveScroll: false
      })
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
      await router.post(route('api.champions-league.matches.reset'))
      // Başarılı olduktan sonra sayfayı yeniden ziyaret et
      router.visit(route('champions-league.index'), {
        method: 'get',
        preserveState: false,
        preserveScroll: false
      })
    } catch (error) {
      console.error('Maçlar sıfırlanırken hata oluştu:', error)
      alert('Maçlar sıfırlanırken bir hata oluştu!')
    } finally {
      isLoading.value = false
    }
  }
}
</script>
