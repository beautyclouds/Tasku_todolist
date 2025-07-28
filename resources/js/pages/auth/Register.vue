<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Mail, Lock, User } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <div class="flex min-h-screen font-['Poppins'] bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 transition-colors duration-300">
    <!-- LEFT SIDE 30% -->
    <div
      class="w-[30%] bg-cover bg-center relative hidden md:block"
      style="background-image: url('/img/bgregis.jpg')"
    >
      <!-- Tombol-tombol di sisi kiri -->
      <div class="absolute top-1/2 right-0 -translate-y-1/2 translate-x-1 flex flex-col gap-4">
        <button
          class="px-7 py-3 font-bold rounded-l-full rounded-r-none shadow-md transition
           bg-white text-[#113f67] 
           dark:bg-gray-900 dark:text-white dark:hover:bg-[#0e2f4f]
           hover:bg-[#e0e0e0]"
        >
          Register
        </button>
        <Link
          href="/login"
          class="px-7 py-3 font-bold rounded-l-full rounded-r-none transition
           text-white hover:bg-white hover:text-[#113f67]
           dark:text-white dark:hover:bg-gray-800"
        >
          Login
        </Link>
      </div>
    </div>

    <!-- RIGHT SIDE 70% -->
    <div class="w-full md:w-[70%] flex flex-col justify-center items-center px-8 bg-white dark:bg-gray-900">
      <img src="/img/logo-taskula.png" alt="Logo" class="w-35 mb-2 dark:brightness-90" />

      <!-- Judul Gradien -->
      <h2 class="text-3xl font-semibold text-transparent bg-clip-text bg-gradient-to-r from-[#113f67] to-[#A5BBC9] mb-6 tracking-wide">
        REGISTER
      </h2>

      <Head title="Register" />

      <form @submit.prevent="submit" class="w-full max-w-sm space-y-4">
        <!-- Name -->
        <div class="relative">
          <User class="absolute left-3 top-3 text-[#113f67] dark:text-[#A5BBC9] w-5 h-5" />
          <input
            v-model="form.name"
            type="text"
            placeholder="Username"
            required
            class="pl-10 w-full border border-[#113f67] dark:border-[#A5BBC9] rounded-md py-2 px-3 focus:outline-none bg-white dark:bg-gray-800 dark:text-white"
          />
          <InputError :message="form.errors.name" />
        </div>

        <!-- Email -->
        <div class="relative">
          <Mail class="absolute left-3 top-3 text-[#113f67] dark:text-[#A5BBC9] w-5 h-5" />
          <input
            v-model="form.email"
            type="email"
            placeholder="Email"
            required
            class="pl-10 w-full border border-[#113f67] dark:border-[#A5BBC9] rounded-md py-2 px-3 focus:outline-none bg-white dark:bg-gray-800 dark:text-white"
          />
          <InputError :message="form.errors.email" />
        </div>

        <!-- Password -->
        <div class="relative">
          <Lock class="absolute left-3 top-3 text-[#113f67] dark:text-[#A5BBC9] w-5 h-5" />
          <input
            v-model="form.password"
            type="password"
            placeholder="Password"
            required
            class="pl-10 w-full border border-[#113f67] dark:border-[#A5BBC9] rounded-md py-2 px-3 focus:outline-none bg-white dark:bg-gray-800 dark:text-white"
          />
          <InputError :message="form.errors.password" />
        </div>

        <!-- Confirm Password -->
        <div class="relative">
          <Lock class="absolute left-3 top-3 text-[#113f67] dark:text-[#A5BBC9] w-5 h-5" />
          <input
            v-model="form.password_confirmation"
            type="password"
            placeholder="Confirm Password"
            required
            class="pl-10 w-full border border-[#113f67] dark:border-[#A5BBC9] rounded-md py-2 px-3 focus:outline-none bg-white dark:bg-gray-800 dark:text-white"
          />
          <InputError :message="form.errors.password_confirmation" />
        </div>

        <!-- Submit -->
        <button
          type="submit"
          :disabled="form.processing"
          class="bg-[#113f67] text-white font-semibold w-full py-2 rounded-md hover:bg-blue-900 transition duration-200"
        >
          <span v-if="form.processing" class="flex items-center justify-center gap-2">
            <LoaderCircle class="w-4 h-4 animate-spin" />
            Loading...
          </span>
          <span v-else>Register</span>
        </button>
      </form>
    </div>
  </div>
</template>

