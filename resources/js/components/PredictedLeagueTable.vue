<template>
  <LeagueTable
    title="🔮 Lig Tahminleri"
    :subtitle="`${currentWeek}. hafta sonrası tahmini lig tablosu`"
    :standings="predictedStandings"
    :show-power-level="true"
    :show-statistics="true"
    :show-championship-probability="true"
    :show-match-analysis="true"
    :current-week="currentWeek"
    info-title="Tahmin Nasıl Hesaplanıyor?"
    :info-content="`Bu tahmin, ${currentWeek}. haftaya kadar oynanan maçların gerçek sonuçları ile kalan maçların simüle edilmiş sonuçları birleştirilerek hesaplanmıştır. Takımların güç seviyeleri ve ev sahibi avantajları dikkate alınmıştır.`"
  />
</template>

<script setup lang="ts">
import LeagueTable from '@/components/LeagueTable.vue'
import type { PredictedStanding } from '@/types/champions-league'

defineProps<{
  predictedStandings: PredictedStanding[]
  currentWeek: number
}>()
</script> 