<template>
  <div class="under-construction">
    <button class="menu-btn" @click="toggleMenu">
      ☰
    </button>

    <aside class="side-menu" :class="{ open: isMenuOpen }">
      <ul>
        <li @click="goTo('dashboard')">📊 Dashboard</li>
        <li @click="goTo('profile')">👤 Perfil</li>
        <li @click="logout">👋 Sair</li>
      </ul>
    </aside>

    <main class="content">
      <i class="icon">🚧</i>

      <p class="message">
        Esta funcionalidade está em desenvolvimento 🚧
      </p>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";

const isMenuOpen = ref(false);
const router = useRouter();

const toggleMenu = () => (isMenuOpen.value = !isMenuOpen.value);

const goTo = (page: string) => {
  router.push(`/${page}`);
};

const logout = () => {
  localStorage.removeItem("auth_token");
  router.push("/login");
};
</script>

<style scoped>
html, body {
  height: 100%;
  margin: 0;
  background-color: #1e1e2c;
}

.under-construction {
  background-color: #1e1e2c;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
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

.app-bar {
  background: #7CFF00;
  color: #000;
  padding: 16px;
  font-weight: bold;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.content {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  text-align: center;
  margin-top: 20px;
}

.icon {
  font-size: 80px;
  color: #7CFF00;
}

.message {
  color: #ffffffb3;
  font-size: 18px;
  margin-top: 20px;
  max-width: 300px;
}
</style>
