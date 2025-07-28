<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Lock } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

const form = useForm({
  password: '',
});

const submit = () => {
  form.post(route('password.confirm'), {
    onFinish: () => {
      form.reset();
    },
  });
};
</script>

<template>
  <div class="flex min-h-screen font-['Poppins']">
    <!-- LEFT PANEL -->
    <div
      class="w-[30%] bg-cover bg-center relative hidden md:block"
      style="background-image: url('/img/bgregis.jpg')"
    >
      <div class="absolute top-1/2 right-0 -translate-y-1/2 translate-x-1 flex flex-col gap-4"></div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="w-full md:w-[70%] flex flex-col justify-center items-center px-8 bg-white dark:bg-gray-900 transition duration-300">
      <!-- Logo -->
      <img
        src="/img/logo-taskula.png"
        alt="Logo"
        class="w-36 mb-2 dark:brightness-90 transition duration-300"
      />

      <!-- Title -->
      <h2
        class="text-3xl font-semibold text-transparent bg-clip-text bg-gradient-to-r from-[#113f67] to-[#A5BBC9] dark:from-blue-300 dark:to-blue-100 mb-6 tracking-wide"
      >
        CONFIRM PASSWORD
      </h2>

      <Head title="Confirm Password" />

      <!-- Description -->
      <div class="text-center text-sm text-gray-700 dark:text-gray-300 max-w-md mb-6">
        This is a secure area of the application. Please confirm your password before continuing.
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="w-full max-w-sm space-y-4">
        <!-- Password -->
        <div class="relative">
          <Lock class="absolute left-3 top-3 text-[#113f67] dark:text-blue-300 w-5 h-5" />
          <input
            v-model="form.password"
            type="password"
            placeholder="Password"
            required
            autocomplete="current-password"
            class="pl-10 w-full border border-[#113f67] dark:border-blue-300 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
          <InputError :message="form.errors.password" />
        </div>

        <!-- Button -->
        <button
          type="submit"
          :disabled="form.processing"
          class="bg-[#113f67] dark:bg-blue-700 text-white font-semibold w-full py-2 rounded-md hover:bg-blue-900 dark:hover:bg-blue-600 transition duration-200"
        >
          <span v-if="form.processing" class="flex items-center justify-center gap-2">
            <LoaderCircle class="h-4 w-4 animate-spin" />
            Confirming...
          </span>
          <span v-else>Confirm Password</span>
        </button>
      </form>
    </div>
  </div>
</template>
