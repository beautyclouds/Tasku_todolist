<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-900 text-center px-4 transition-colors duration-300">
    <!-- Logo Gambar -->
    <img src="/img/logo-taskula.png" alt="Taskula Logo" class="w-50 h-auto mb-6" />

    <!-- Judul & Deskripsi -->
    <h2 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-100 mb-2">
      Atur Tugasmu, Capai Tujuanmu.
    </h2>
    <p class="text-gray-500 dark:text-gray-300 mb-6">
      Taskula bantu kamu mengelola proyek dan tim dengan mudah.
    </p>

    <!-- Tombol Aksi -->
    <div class="flex flex-col sm:flex-row gap-4">
      <a
        href="/register"
        class="px-6 py-3 text-white rounded-xl transition"
        style="background-color: #113f67;"
      >
        Mulai Sekarang
      </a>
      <a
        href="/login"
        class="px-6 py-3 border rounded-xl transition border-[#113f67] text-[#113f67] dark:border-white dark:text-white"
      >
        Login
      </a>
    </div>

    <!-- Footer -->
    <footer class="mt-16 text-gray-400 dark:text-gray-500 text-sm">
      © 2025 Taskula
    </footer>
  </div>
</template>

<script setup lang="ts">
// Halaman statis, tidak butuh script logic
</script>
