<template>
  <div class="dashboard">
    <button class="menu-btn" @click="toggleMenu">
      ☰
    </button>

    <aside class="side-menu" :class="{ open: isMenuOpen }">
      <ul>
        <li @click="goTo()">📊 Dashboard</li>
        <li @click="goTo()">👤 Perfil</li>
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

      <div v-for="t in transactions" :key="t.id" class="transaction-card" :class="t.category.type">
        <div class="transaction-left">
          <h5>{{ t.description }}</h5>
          <span class="category">{{ t.category.name }} • {{ t.payment_method }}</span>
        </div>
        <div class="transaction-right">
          <p :class="t.category.type === 'income' ? 'amount-income' : 'amount-expense'">
            {{ t.amount < 0 ? '-' : '+' }} R$ {{ Math.abs(t.amount).toFixed(2) }}
          </p>
          <span class="date">{{ formatDate(t.transaction_date) }}</span>
        </div>
        <div class="transaction-actions">
          <button class="edit-btn" @click="openEditModal(t)">✏️</button>
          <button class="delete-btn" @click="openDeleteModal(t)">🗑️</button>
        </div>
      </div>
      <div class="pagination">
        <button
          class="pagination-btn"
          @click="prevPage"
          :disabled="currentPage === 1"
        >
          Anterior
        </button>

        <span>Página {{ currentPage }} de {{ lastPage }}</span>

        <button
          class="pagination-btn"
          @click="nextPage"
          :disabled="currentPage === lastPage"
        >
          Próxima
        </button>
      </div>
    </section>

    <button class="floating-btn" @click="openModal">
      Nova Transação
    </button>

    <div v-if="isModalOpen" class="modal-overlay" @click="closeModal">
      <div class="modal-content" @click.stop>
        <h2 v-if="isEditing">Editar Transação</h2>
        <h2 v-else>Nova Transação</h2>
        <form @submit.prevent="submitTransaction">
          <label>Categoria</label>
          <select v-model="form.category_id" required>
            <option value=0 disabled>Selecione...</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">
              {{ c.name }}
            </option>
          </select>

          <label>Descrição</label>
          <input type="text" v-model="form.description" required />

          <label>Valor</label>
          <input type="number" step="0.01" v-model="form.amount" required />

          <label>Data</label>
          <input type="date" v-model="form.transaction_date" required />

          <label>Método de Pagamento</label>
          <select v-model="form.payment_method" required>
            <option value="credit_card">Cartão de Crédito</option>
            <option value="pix">Pix</option>
            <option value="money">Dinheiro</option>
            <option value="others">Outros</option>
          </select>

          <label class="checkbox-label">
            <input type="checkbox" v-model="form.is_recurring" />
            Recorrente?
          </label>

          <div v-if="form.is_recurring">
            <label>Tipo de Recorrência</label>
            <input
              type="text"
              v-model="form.recurrence_type"
              placeholder="ex: mensal, semanal..."
            />
          </div>

          <div class="buttons-row">
            <button type="submit" class="btn-save">Salvar</button>
            <button type="button" class="btn-cancel" @click="closeModal">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
    <div v-if="isDeleteModalOpen" class="modal-overlay" @click="closeDeleteModal">
      <div class="modal-content" @click.stop>
        <h3>Tem certeza que deseja excluir?</h3>
        <p>Essa ação não pode ser desfeita.</p>

        <div class="buttons-row">
          <button class="btn-cancel" @click="closeDeleteModal">Cancelar</button>
          <button class="btn-save" @click="confirmDelete">Excluir</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import Chart from "chart.js/auto";
import { useAuthStore } from '@/stores/auth'
import router from '@/router'
import api from '@/services/api'

interface Transaction {
  id: number;
  description: string;
  amount: number;
  transaction_date: string;
  payment_method: string;
  category_id: number;
  category: {
    id: number;
    name: string;
    type: 'income' | 'expense';
  };
  is_recurring: boolean;
  recurrence_type: string | null;
}


interface PaginatedResponse<T> {
  current_page: number
  data: T[]
  per_page: number
  total: number
  last_page: number
}

interface Category {
  id: number
  name: string
}

const isMenuOpen = ref(false);
const isModalOpen = ref(false);
const auth = useAuthStore()
const transactions = ref<Transaction[]>([]);
const categories = ref<Category[]>([]);
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)
const perPage = ref(10)
const isEditing = ref(false)
const editingId = ref<number | null>(null)
const isDeleteModalOpen = ref(false)
const deleteId = ref<number | null>(null)
const financeChart = ref<Chart | null>(null);

const openEditModal = (t: Transaction) => {
  isEditing.value = true
  editingId.value = t.id

  form.value = {
    category_id: t.category_id,
    description: t.description,
    amount: t.amount,
    transaction_date: t.transaction_date,
    payment_method: t.payment_method,
    is_recurring: t.is_recurring ?? false,
    recurrence_type: t.recurrence_type ?? null,
  };

  isModalOpen.value = true;
};


const form = ref({
  category_id: 0,
  description: "",
  amount: 0,
  transaction_date: "",
  payment_method: "pix",
  is_recurring: false,
  recurrence_type: null as string | null,
})

const getChartData = (transactions: Transaction[]) => {
  const monthlyData: Record<string, { income: number; expense: number }> = {};

  transactions.forEach(t => {
    const monthKey = new Date(t.transaction_date).toLocaleDateString("pt-BR", { month: "short", year: "numeric" });
    if (!monthlyData[monthKey]) monthlyData[monthKey] = { income: 0, expense: 0 };
    if (t.category.type === "income") monthlyData[monthKey].income += t.amount;
    else monthlyData[monthKey].expense += Math.abs(t.amount);
  });

  const sortedMonths = Object.keys(monthlyData).sort(
    (a, b) => new Date(a).getTime() - new Date(b).getTime()
  ).slice(-6);

  return {
    labels: sortedMonths,
    incomes: sortedMonths.map(month => monthlyData[month]?.income || 0),
    expenses: sortedMonths.map(month => monthlyData[month]?.expense || 0),
  };
};

const renderChart = () => {
  const ctx = document.getElementById("financeChart") as HTMLCanvasElement;
  const { labels, incomes, expenses } = getChartData(transactions.value);

  if (financeChart.value) financeChart.value.destroy();

  financeChart.value = new Chart(ctx, {
    type: "bar",
    data: {
      labels,
      datasets: [
        {
          label: "Receitas",
          data: incomes,
          backgroundColor: "#00d084",
          borderRadius: 6,
        },
        {
          label: "Despesas",
          data: expenses,
          backgroundColor: "#ff4d4d",
          borderRadius: 6,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { labels: { color: "#ccc" } },
        tooltip: {
          enabled: true,
          callbacks: {
            label: function(context) {
              const value = context.parsed.y ?? 0;
              return context.dataset.label + ': R$ ' + value.toFixed(2);
            }
          }
        }
      },
      scales: {
        x: { ticks: { color: "#aaa" }, grid: { display: false } },
        y: {
          ticks: { color: "#aaa", callback: (v) => `R$ ${v}` },
          grid: { color: "#333" }
        },
      }
    },
  });
};


const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};

const logout = () => {
  return auth.logout()
};

const submitTransaction = async () => {
  if (isEditing.value) {
    await updateTransaction()
  } else {
    await createTransaction()
  }
}

const updateTransaction = async () => {
  try {
    await api.put(`/transactions/${editingId.value}`, form.value)
    closeModal()
    await fetchTransactions(currentPage.value)
    renderChart()
  } catch (error) {
    console.error(error)
  }
}


const openDeleteModal = (t: Transaction) => {
  deleteId.value = t.id
  isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
  isDeleteModalOpen.value = false
  deleteId.value = null
}

const confirmDelete = async () => {
  try {
    await api.delete(`/transactions/${deleteId.value}`)
    closeDeleteModal()
    await fetchTransactions(currentPage.value)
    renderChart()
  } catch (error) {
    console.error(error)
  }
}

const goTo = () => {
    router.push('/under-construction')
    return
};

const fetchTransactions = async (page = 1) => {
  try {
    const { data } = await api.get<PaginatedResponse<Transaction>>('/transactions', {
      params: { page, per_page: perPage.value },
    })
    transactions.value = data.data
    currentPage.value = data.current_page
    lastPage.value = data.last_page
    total.value = data.total
  } catch (error) {
    console.error(error)
  }
}

const nextPage = () => {
  if (currentPage.value < lastPage.value) {
    fetchTransactions(currentPage.value + 1)
  }
}

const prevPage = () => {
  if (currentPage.value > 1) {
    fetchTransactions(currentPage.value - 1)
  }
}

const openModal = () => {
  isModalOpen.value = true;
}

const closeModal = () => {
  isModalOpen.value = false
  isEditing.value = false
  editingId.value = null
  resetForm()
}


const resetForm = () => {
  form.value = {
    category_id: 0,
    description: "",
    amount: 0,
    transaction_date: "",
    payment_method: "pix",
    is_recurring: false,
    recurrence_type: null,
  };
}

const fetchCategories = async () => {
  try {
    const { data } = await api.get("/categories");
    categories.value = data;
  } catch (error) {
    console.error(error);
  }
}

const createTransaction = async () => {
  try {
    await api.post("/transactions", form.value);
    closeModal();
    await fetchTransactions(currentPage.value);
    renderChart()
  } catch (error) {
    console.error(error);
  }
}

const formatDate = (dateStr: string) => {
  const date = new Date(dateStr);
  return date.toLocaleDateString("pt-BR", { day: "2-digit", month: "short" });
};

onMounted(async () => {
  await fetchTransactions();
  await fetchCategories();
  renderChart();
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
  width: 100%;
  height: 100%;
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

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 1rem;
}

.pagination-btn {
  background-color: #00a86b;
  color: #fff;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  background-color: #00d084;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

.modal-content {
  background: #1a1d29;
  padding: 2rem;
  width: 90%;
  max-width: 420px;
  border-radius: 14px;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.4);
  color: #fff;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-content h2 {
  margin-bottom: 1.5rem;
  text-align: center;
  color: #00d084;
}

.modal-content input,
.modal-content select {
  width: 100%;
  padding: 0.75rem;
  margin-bottom: 1rem;
  background: #0f111a;
  border: 1px solid #333;
  border-radius: 8px;
  color: #fff;
  box-sizing: border-box;
}

.modal-content label {
  margin-bottom: 0.4rem;
  display: block;
  font-size: 0.9rem;
  color: #ccc;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.checkbox-label input[type="checkbox"] {
  width: auto;
  margin: 0;
}

.buttons-row {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  margin-top: 1.5rem;
}

.buttons-row button {
  flex: 1;
  padding: 0.7rem;
  border: none;
  border-radius: 8px;
  color: white;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-save {
  background: #00a86b;
}

.btn-save:hover {
  background: #00d084;
}

.btn-cancel {
  background: #ff4d4d;
}

.btn-cancel:hover {
  background: #ff6b6b;
}

.transaction-actions {
  display: flex;
  gap: 0.6rem;
  margin-top: 0.6rem;
}

.edit-btn,
.delete-btn {
  background: #232736;
  border: none;
  padding: 0.4rem 0.6rem;
  border-radius: 6px;
  cursor: pointer;
  color: #fff;
  transition: 0.2s ease;
}

.edit-btn:hover {
  background: #00a86b;
}

.delete-btn:hover {
  background: #ff4d4d;
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
