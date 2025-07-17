<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
              🔮 Lig Tahminleri
            </h1>
            <p class="text-gray-600">
              {{ currentWeek }}. hafta sonrası tahmini lig tablosu
            </p>
          </div>
          <Link
            :href="route('champions-league.index')"
            class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition-colors"
          >
            ← Geri Dön
          </Link>
        </div>
      </div>

      <!-- Prediction Info -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
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
      <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-xl font-semibold text-gray-900">📊 Tahmini Final Lig Tablosu</h2>
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
      </div>

      <!-- Statistics -->
      <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 İstatistikler</h3>
          <div class="space-y-3">
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

        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">🎯 Şampiyonluk Olasılığı</h3>
          <div class="space-y-3">
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

        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">⚽ Maç Analizi</h3>
          <div class="space-y-3">
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
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import type { PredictedStanding } from '@/types/champions-league'

const props = defineProps<{
  currentWeek: number
  predictedStandings: PredictedStanding[]
}>()

const getTopScorer = () => {
  if (!props.predictedStandings?.length) return '-'
  const topScorer = props.predictedStandings.reduce((prev, current) =>
    prev.goals_for > current.goals_for ? prev : current
  )
  return `${topScorer.team.name} (${topScorer.goals_for})`
}

const getBestDefense = () => {
  if (!props.predictedStandings?.length) return '-'
  const bestDefense = props.predictedStandings.reduce((prev, current) =>
    prev.goals_against < current.goals_against ? prev : current
  )
  return `${bestDefense.team.name} (${bestDefense.goals_against})`
}

const getHighestPoints = () => {
  if (!props.predictedStandings?.length) return '-'
  const highestPoints = props.predictedStandings.reduce((prev, current) =>
    prev.points > current.points ? prev : current
  )
  return `${highestPoints.team.name} (${highestPoints.points})`
}

const getChampionshipProbability = (position: number) => {
  const probabilities = [60, 25, 15] // İlk 3 takımın şampiyonluk olasılıkları
  return probabilities[position] || 0
}
</script>
