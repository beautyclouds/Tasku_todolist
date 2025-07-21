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
    <div class="w-[30%] bg-cover bg-center relative" style="background-image: url('/img/bgregis.jpg')">
      <div class="absolute top-1/2 right-0 -translate-y-1/2 translate-x-1 flex flex-col gap-4">
        
      </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="w-[70%] flex flex-col justify-center items-center px-8 bg-white">
      <!-- Logo -->
      <img src="/img/logo-taskula.png" alt="Logo" class="w-35 mb-2" />

      <!-- Title -->
      <h2 class="text-3xl font-semibold text-transparent bg-clip-text bg-gradient-to-r from-[#113f67] to-[#A5BBC9] mb-6 tracking-wide">
        CONFIRM PASSWORD
      </h2>

      <Head title="Confirm Password" />

      <!-- Description -->
      <div class="text-center text-sm text-gray-700 max-w-md mb-6">
        This is a secure area of the application. Please confirm your password before continuing.
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="w-full max-w-sm space-y-4">
        <!-- Password -->
        <div class="relative">
          <Lock class="absolute left-3 top-3 text-[#113f67] w-5 h-5" />
          <input
            v-model="form.password"
            type="password"
            placeholder="Password"
            required
            autocomplete="current-password"
            class="pl-10 w-full border border-[#113f67] rounded-md py-2 px-3 focus:outline-none"
          />
          <InputError :message="form.errors.password" />
        </div>

        <!-- Button -->
        <button
          type="submit"
          :disabled="form.processing"
          class="bg-[#113f67] text-white font-semibold w-full py-2 rounded-md hover:bg-blue-900 transition duration-200"
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

