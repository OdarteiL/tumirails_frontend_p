<template>
  <div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-lg shadow p-6">
      <h1 class="text-2xl font-semibold mb-4">Create account</h1>
      <form @submit.prevent="onSubmit" class="space-y-4">
        <input v-model="name" type="text" placeholder="Full name" class="w-full border rounded px-3 py-2" required />
        <input v-model="email" type="email" placeholder="Email" class="w-full border rounded px-3 py-2" required />
        <input v-model="password" type="password" placeholder="Password" class="w-full border rounded px-3 py-2" required />
        <input v-model="password_confirmation" type="password" placeholder="Confirm Password" class="w-full border rounded px-3 py-2" required />
        <button :disabled="loading" class="w-full bg-green-600 text-white rounded py-2">
          {{ loading ? 'Signing up...' : 'Sign up' }}
        </button>
        <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
      </form>
      <p class="mt-4 text-sm">Have an account? <router-link to="/login" class="text-blue-600">Sign in</router-link></p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const store = useAuthStore();
const router = useRouter();

const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const loading = ref(false);
const error = ref(null);

async function onSubmit() {
  loading.value = true; error.value = null;
  try {
    await store.register({ name: name.value, email: email.value, password: password.value, password_confirmation: password_confirmation.value });
    router.push('/dashboard');
  } catch (e) {
    error.value = store.error || 'Registration failed';
  } finally {
    loading.value = false;
  }
}
</script>
