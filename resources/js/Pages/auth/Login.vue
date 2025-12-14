<script setup>
import { Head, useForm } from '@inertiajs/vue3'

defineProps({
  status: String,
  canResetPassword: Boolean,
  canRegister: Boolean,
})

const form = useForm({
  email: '',
  password: '',
  remember: false,
})
</script>

<template>
  <Head title="Log in" />

  <div class="flex min-h-screen items-center justify-center bg-gray-100 px-4">
    <div class="w-full max-w-md rounded bg-white p-6 shadow">
      <h1 class="text-2xl font-semibold text-center mb-2">
        Log in
      </h1>

      <form
        @submit.prevent="form.post(route('login'), {
          onFinish: () => form.reset('password')
        })"
        class="space-y-5"
      >
        <!-- Email -->
        <div>
          <label class="block text-sm mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full border rounded px-3 py-2"
            required
          />
          <p v-if="form.errors.email" class="text-sm text-red-600">
            {{ form.errors.email }}
          </p>
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm mb-1">Password</label>
          <input
            v-model="form.password"
            type="password"
            class="w-full border rounded px-3 py-2"
            required
          />
          <p v-if="form.errors.password" class="text-sm text-red-600">
            {{ form.errors.password }}
          </p>
        </div>

        <!-- Remember -->
        <div class="flex items-center">
          <input v-model="form.remember" type="checkbox" class="mr-2" />
          <span class="text-sm">Remember me</span>
        </div>

        <!-- Submit -->
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-black text-white py-2 rounded disabled:opacity-50"
        >
          {{ form.processing ? 'Logging in…' : 'Log in' }}
        </button>
      </form>
    </div>
  </div>
</template>
