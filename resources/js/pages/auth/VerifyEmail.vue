<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};
</script>

<template>
  <div class="flex min-h-screen font-['Poppins'] dark:text-white">
    <!-- KIRI: Gambar -->
    <div class="w-[30%] bg-cover bg-center" style="background-image: url('/img/bgregis.jpg')"></div>

    <!-- KANAN: Form -->
    <div class="w-[70%] flex flex-col justify-center items-center px-8 bg-white dark:bg-gray-900">
      <!-- Logo -->
      <img src="/img/logo-taskula.png" alt="Logo" class="w-35 mb-2" />

      <!-- Judul -->
      <h2
        class="text-3xl font-semibold text-transparent bg-clip-text bg-gradient-to-r from-[#113f67] to-[#A5BBC9] mb-6 tracking-wide"
      >
        VERIFY EMAIL
      </h2>

      <Head title="Email verification" />

      <!-- Deskripsi -->
      <div class="text-center text-sm text-gray-700 dark:text-gray-300 max-w-md mb-6">
        Please verify your email address by clicking on the link we just emailed to you.
      </div>

      <!-- Status jika berhasil -->
      <div
        v-if="status === 'verification-link-sent'"
        class="mb-4 text-center text-sm font-medium text-green-600 dark:text-green-400"
      >
        A new verification link has been sent to the email address you provided during registration.
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="w-full max-w-sm space-y-4 text-center">
        <button
          type="submit"
          :disabled="form.processing"
          class="bg-[#113f67] text-white font-semibold w-full py-2 rounded-md hover:bg-blue-900 transition duration-200"
        >
          <span v-if="form.processing" class="flex items-center justify-center gap-2">
            <LoaderCircle class="w-4 h-4 animate-spin" />
            Sending...
          </span>
          <span v-else>Resend verification email</span>
        </button>

        <!-- Logout -->
        <TextLink
          :href="route('logout')"
          method="post"
          as="button"
          class="block mx-auto text-sm text-[#113f67] underline underline-offset-4 dark:text-[#A5BBC9]"
        >
          Log out
        </TextLink>
      </form>
    </div>
  </div>
</template>
