<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Mail } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

defineProps<{
  status?: string;
}>();

const form = useForm({
  email: '',
});

const submit = () => {
  form.post(route('password.email'));
};
</script>

<template>
  <div class="flex min-h-screen font-['Poppins'] bg-white dark:bg-[#0f172a]">
    <!-- LEFT SIDE -->
    <div class="w-[30%] bg-cover bg-center relative" style="background-image: url('/img/bgregis.jpg')">
      <!-- Tetap pakai gambar latar -->
    </div>

    <!-- RIGHT SIDE -->
    <div class="w-[70%] flex flex-col justify-center items-center px-8 transition duration-300">
      <img src="/img/logo-taskula.png" alt="Logo" class="w-36 mb-2" />

      <!-- Title -->
      <h2
        class="text-3xl font-semibold text-transparent bg-clip-text bg-gradient-to-r from-[#113f67] to-[#A5BBC9] mb-6 tracking-wide"
      >
        FORGOT PASSWORD
      </h2>

      <Head title="Forgot Password" />

      <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600 dark:text-green-400">
        {{ status }}
      </div>

      <form @submit.prevent="submit" class="w-full max-w-sm space-y-4">
        <!-- Email -->
        <div class="relative">
          <Mail class="absolute left-3 top-3 text-[#113f67] dark:text-[#A5BBC9] w-5 h-5" />
          <input
            v-model="form.email"
            type="email"
            placeholder="Email"
            required
            autocomplete="off"
            autofocus
            class="pl-10 w-full border border-[#113f67] dark:border-[#A5BBC9] dark:bg-[#1e293b] dark:text-white rounded-md py-2 px-3 focus:outline-none placeholder:text-gray-400 dark:placeholder:text-gray-400"
          />
          <InputError :message="form.errors.email" />
        </div>

        <!-- Submit -->
        <button
          type="submit"
          :disabled="form.processing"
          class="bg-[#113f67] text-white font-semibold w-full py-2 rounded-md hover:bg-blue-900 transition duration-200"
        >
          <span v-if="form.processing" class="flex items-center justify-center gap-2">
            <LoaderCircle class="w-4 h-4 animate-spin" />
            Sending...
          </span>
          <span v-else>Email password reset link</span>
        </button>
      </form>

      <!-- Back to login -->
      <div class="mt-4 text-sm text-gray-700 dark:text-gray-300 text-center">
        Or, return to
        <TextLink :href="route('login')" class="text-[#113f67] dark:text-[#A5BBC9] hover:underline">log in</TextLink>
      </div>
    </div>
  </div>
</template>
