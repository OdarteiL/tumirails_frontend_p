<template>
  <div class="min-h-screen p-8">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6 space-y-4">
      <h1 class="text-2xl font-semibold">Welcome</h1>
      <p class="text-gray-600">You're signed in.</p>
      <button @click="logout" class="bg-gray-800 text-white rounded px-4 py-2">Logout</button>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const store = useAuthStore();

async function logout() {
  await store.logout();
  router.push('/login');
}
</script>
