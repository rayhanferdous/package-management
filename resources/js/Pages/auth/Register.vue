<script setup>
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})
</script>

<template>
  <Head title="Register" />

  <div class="flex min-h-screen items-center justify-center bg-gray-100 px-4">
    <div class="w-full max-w-md rounded bg-white p-6 shadow">
      <h1 class="text-2xl font-semibold text-center mb-2">
        Create an account
      </h1>
      <p class="text-center text-sm text-gray-500 mb-6">
        Enter your details below to create your account
      </p>

      <form
        @submit.prevent="form.post(route('register'), {
          onFinish: () => form.reset('password', 'password_confirmation')
        })"
        class="space-y-5"
      >
        <!-- Name -->
        <div>
          <label class="block text-sm mb-1">Name</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full border rounded px-3 py-2"
            required
            autofocus
          />
          <p v-if="form.errors.name" class="text-sm text-red-600">
            {{ form.errors.name }}
          </p>
        </div>

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

        <!-- Confirm Password -->
        <div>
          <label class="block text-sm mb-1">Confirm Password</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            class="w-full border rounded px-3 py-2"
            required
          />
          <p
            v-if="form.errors.password_confirmation"
            class="text-sm text-red-600"
          >
            {{ form.errors.password_confirmation }}
          </p>
        </div>

        <!-- Submit -->
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-black text-white py-2 rounded disabled:opacity-50"
          data-test="register-user-button"
        >
          {{ form.processing ? 'Creating account…' : 'Create account' }}
        </button>

        <!-- Login link -->
        <p class="text-center text-sm text-gray-500">
          Already have an account?
          <a href="/login" class="text-blue-600 hover:underline">
            Log in
          </a>
        </p>
      </form>
    </div>
  </div>
</template>
