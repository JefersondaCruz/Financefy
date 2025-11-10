<template>
  <div class="dashboard">
    <button class="menu-btn" @click="toggleMenu">
      ☰
    </button>

    <aside class="side-menu" :class="{ open: isMenuOpen }">
      <ul>
        <li @click="goTo('dashboard')">📊 Dashboard</li>
        <li @click="goTo('profile')">👤 Perfil</li>
        <li @click="logout">👋  Sair</li>
      </ul>
    </aside>

    <div v-if="isMenuOpen" class="overlay" @click="toggleMenu"></div>

    <header class="header">
      <h2>Dashboard Financeiro</h2>
    </header>

    <section class="metrics">
      <div class="card negative">
        <h4>Despesas</h4>
        <p class="value">R$ 615.40</p>
        <span class="info">+12,5% vs mês anterior</span>
      </div>

      <div class="card positive">
        <h4>Receitas</h4>
        <p class="value">R$ 6500.00</p>
        <span class="info">+8,2% vs mês anterior</span>
      </div>

      <div class="card neutral">
        <h4>Saldo</h4>
        <p class="value">R$ 5884.60</p>
        <span class="info">Positivo</span>
      </div>

      <div class="card excellent">
        <h4>Taxa de Economia</h4>
        <p class="value">90.5%</p>
        <span class="info">Excelente!</span>
      </div>
    </section>

    <section class="chart-section">
      <h4>Evolução Financeira</h4>
      <canvas id="financeChart"></canvas>
    </section>

    <section class="transactions-section">
      <h4>Transações Recentes</h4>

      <div v-for="(t, index) in transactions" :key="index" class="transaction-card" :class="t.type">
        <div class="transaction-left">
          <h5>{{ t.title }}</h5>
          <span class="category">{{ t.category }} • {{ t.method }}</span>
        </div>
        <div class="transaction-right">
          <p :class="t.type === 'income' ? 'amount-income' : 'amount-expense'">
            {{ t.amount < 0 ? '-' : '+' }} R$ {{ Math.abs(t.amount).toFixed(2) }}
          </p>
          <span class="date">{{ formatDate(t.date) }}</span>
        </div>
      </div>
    </section>

    <button class="floating-btn">Nova Transação</button>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import Chart from "chart.js/auto";
import { useAuthStore } from '@/stores/auth'
import router from '@/router'

const isMenuOpen = ref(false);
const auth = useAuthStore()

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;

};

const logout = () => {
  return auth.logout()

};

const goTo = (page: string) => {
  if (page === 'profile') {
    router.push('/profile')
    return
  }
  toggleMenu();
};

const transactions = ref([
  { title: "Mercado", date: "2025-11-01", amount: -120.5, method: "Pix", category: "Alimentação", type: "expense" },
  { title: "Salário", date: "2025-11-05", amount: 5000.0, method: "Transferência", category: "Salário", type: "income" },
  { title: "Netflix", date: "2025-11-02", amount: -39.9, method: "Cartão", category: "Lazer", type: "expense" },
  { title: "Gasolina", date: "2025-11-03", amount: -250.0, method: "Pix", category: "Transporte", type: "expense" },
  { title: "Freelance", date: "2025-11-07", amount: 1500.0, method: "Pix", category: "Freelance", type: "income" },
  { title: "Academia", date: "2025-11-08", amount: -120.0, method: "Cartão", category: "Saúde", type: "expense" },
  { title: "Restaurante", date: "2025-11-09", amount: -85.0, method: "Cartão", category: "Alimentação", type: "expense" },
]);

const formatDate = (dateStr: string) => {
  const date = new Date(dateStr);
  return date.toLocaleDateString("pt-BR", { day: "2-digit", month: "short" });
};

onMounted(() => {
  const ctx = document.getElementById("financeChart") as HTMLCanvasElement;
  new Chart(ctx, {
    type: "line",
    data: {
      labels: ["Jun", "Jul", "Ago", "Set", "Out", "Nov"],
      datasets: [
        {
          label: "Receitas",
          data: [5000, 5500, 5200, 5300, 5600, 6800],
          borderColor: "#00d084",
          tension: 0.3,
        },
        {
          label: "Despesas",
          data: [3000, 3500, 3400, 3600, 3700, 1900],
          borderColor: "#ff4d4d",
          tension: 0.3,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: false,
      resizeDelay: 200,
      plugins: { legend: { labels: { color: "#ccc" } } },
      scales: {
        x: { ticks: { color: "#aaa" } },
        y: { ticks: { color: "#aaa" } },
      },
    },
  });
});

</script>

<style scoped>
.dashboard {
  width: 100vw;
  min-height: 100vh;
  background-color: #1a1d29;
  color: #f1f1f1;
  display: flex;
  flex-direction: column;
  gap: 2rem;
  padding: 1.5rem 3rem;
  box-sizing: border-box;
  margin-left: calc(-50vw + 50%);
  margin-right: calc(-50vw + 50%);
}

.menu-btn {
  position: fixed;
  top: 20px;
  left: 20px;
  background: #232736;
  color: #fff;
  border: none;
  font-size: 1.5rem;
  padding: 0.4rem 0.8rem;
  border-radius: 8px;
  cursor: pointer;
  z-index: 1001;
  transition: background 0.3s ease;
}
.menu-btn:hover {
  background: #00d084;
}

.side-menu {
  position: fixed;
  top: 0;
  left: -250px;
  height: 100%;
  width: 230px;
  background-color: #232736;
  box-shadow: 3px 0 10px rgba(0, 0, 0, 0.4);
  transition: left 0.3s ease;
  padding-top: 70px;
  z-index: 1000;
}

.side-menu.open {
  left: 0;
}

.side-menu ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.side-menu li {
  padding: 1rem 1.5rem;
  color: #ccc;
  font-size: 1.1rem;
  cursor: pointer;
  transition: background 0.3s ease, color 0.3s ease;
}

.side-menu li:hover {
  background-color: #00d084;
  color: #fff;
}

.overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
  z-index: 999;
}

.header {
  display: flex;
  justify-content: center;
  align-items: center;
  background: #00d084;
  color: #fff;
  border-radius: 10px;
  padding: 1rem;
}

.metrics {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
  gap: 1.5rem;
}

.card {
  background-color: #232736;
  border-radius: 12px;
  padding: 1rem 1.5rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.card h4 {
  font-size: 1rem;
  color: #aaa;
}

.card .value {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0.4rem 0;
}

.card .info {
  font-size: 0.9rem;
}

.card.negative .value { color: #ff4d4d; }
.card.positive .value { color: #00d084; }
.card.excellent .value { color: #00ffa3; }

.chart-section {
  background-color: #232736;
  border-radius: 12px;
  padding: 1.5rem;
  height: 400px;
  width: 100%;
  display: flex;
  flex-direction: column;
}

.chart-section h4 {
  margin-bottom: 1rem;
  color: #f1f1f1;
}

.chart-section canvas {
  flex: 1;
  width: 100% !important;
  height: 100% !important;
  max-height: 100%;
  object-fit: contain;
}

body, html {
  overflow-x: hidden;
}

.floating-btn {
  position: fixed;
  bottom: 30px;
  right: 30px;
  background-color: #00a86b;
  color: white;
  border: none;
  padding: 0.9rem 1.4rem;
  border-radius: 50px;
  font-weight: 500;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  transition: all 0.2s ease;
}

.floating-btn:hover {
  background-color: #00d084;
}

.transactions-section {
  background-color: #232736;
  border-radius: 12px;
  padding: 1.5rem;
  color: #f1f1f1;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.transactions-section h4 {
  margin-bottom: 0.5rem;
  font-size: 1.2rem;
  color: #fff;
}

.transaction-card {
  background: #1e2230;
  border-radius: 10px;
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
  transition: background 0.3s ease;
}

.transaction-card:hover {
  background: #2a2f42;
}

.transaction-left h5 {
  margin: 0;
  font-size: 1rem;
  color: #fff;
}

.category {
  font-size: 0.85rem;
  color: #aaa;
}

.transaction-right {
  text-align: right;
}

.amount-income {
  color: #00d084;
  font-weight: 600;
}

.amount-expense {
  color: #ff4d4d;
  font-weight: 600;
}

.date {
  font-size: 0.85rem;
  color: #aaa;
}

@media (max-width: 768px) {
  .dashboard {
    padding: 1rem;
  }

  .metrics {
    grid-template-columns: 1fr 1fr;
  }

  .chart-section {
    height: 300px;
  }
}

</style>
