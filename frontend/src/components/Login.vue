<template>
  <div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-lg shadow p-6">
      <h1 class="text-2xl font-semibold mb-4">Sign in</h1>
      <form @submit.prevent="onSubmit" class="space-y-4">
        <input v-model="email" type="email" placeholder="Email" class="w-full border rounded px-3 py-2" required />
        <input v-model="password" type="password" placeholder="Password" class="w-full border rounded px-3 py-2" required />
        <button :disabled="loading" class="w-full bg-blue-600 text-white rounded py-2">
          {{ loading ? 'Signing in...' : 'Sign in' }}
        </button>
        <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
      </form>
      <p class="mt-4 text-sm">No account? <router-link to="/register" class="text-blue-600">Sign up</router-link></p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const store = useAuthStore();
const router = useRouter();

const email = ref('');
const password = ref('');
const loading = ref(false);
const error = ref(null);

async function onSubmit() {
  loading.value = true; error.value = null;
  try {
    await store.login({ email: email.value, password: password.value });
    router.push('/dashboard');
  } catch (e) {
    error.value = store.error || 'Login failed';
  } finally {
    loading.value = false;
  }
}
</script>
