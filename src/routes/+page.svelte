<script lang="ts">
  import { onMount } from 'svelte';
  import { fly, fade } from 'svelte/transition';

  let visible = $state(false);

  onMount(() => {
    visible = true;
  });
</script>

<svelte:head>
  <title>New Cabalan National High School</title>
</svelte:head>

<!-- Main container -->
<div class="relative min-h-screen font-sans">
  <!-- Page Content -->
  <div class="relative z-10 flex min-h-screen flex-col">
    
    <!-- Main Content Area -->
    <main class="flex flex-grow flex-col items-center justify-center px-4 sm:px-6 lg:px-8">
      
      {#if visible}
        <!-- Welcome Section -->
        <div class="text-center mb-8 md:mb-12" in:fade={{ duration: 1000 }}>
          <!-- School Logo -->
          <div class="flex justify-center mb-6">
            <img src="/logo.png" alt="NCNHS Logo" class="logo" />
<style>
  .logo {
    width: 200px;
    height: auto;
    margin-bottom: 20px;
    margin-top: -10px;
    margin-left: auto;
    margin-right: auto;
    display: block;
  }
</style>
          </div>
          
          <!-- Welcome Text -->
          <div class="space-y-4" in:fly={{ y: 50, duration: 800, delay: 400 }}>
            <h1 class="text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold text-green-800 leading-tight">
              Welcome to
            </h1>
            <h2 class="text-xl md:text-2xl lg:text-3xl xl:text-4xl font-bold text-blue-800 leading-tight">
              New Cabalan National High School
            </h2>
            <p class="text-base md:text-lg lg:text-xl text-gray-700 max-w-2xl mx-auto leading-relaxed">
              Empowering students with quality education and building tomorrow's leaders through excellence, integrity, and innovation.
            </p>
          </div>
        </div>

        <!-- Quick Access Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 w-full max-w-6xl" in:fly={{ y: 50, duration: 800, delay: 800 }}>
          
          <!-- Announcements Card -->
          <a href="/announcements" 
             class="group bg-white/90 backdrop-blur-sm rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 p-6 border border-green-200 hover:border-green-400">
            <div class="text-center">
              <div class="inline-flex items-center justify-center w-12 h-12 mb-4 bg-green-100 rounded-lg group-hover:bg-green-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Announcements</h3>
              <p class="text-sm text-gray-600">Stay updated with latest school news and announcements</p>
            </div>
          </a>

          <!-- Calendar Card -->
          <a href="/calendar" 
             class="group bg-white/90 backdrop-blur-sm rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 p-6 border border-blue-200 hover:border-blue-400">
            <div class="text-center">
              <div class="inline-flex items-center justify-center w-12 h-12 mb-4 bg-blue-100 rounded-lg group-hover:bg-blue-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Calendar</h3>
              <p class="text-sm text-gray-600">View upcoming events, holidays, and academic activities</p>
            </div>
          </a>

          <!-- Faculty Card -->
          <a href="/faculty" 
             class="group bg-white/90 backdrop-blur-sm rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 p-6 border border-purple-200 hover:border-purple-400">
            <div class="text-center">
              <div class="inline-flex items-center justify-center w-12 h-12 mb-4 bg-purple-100 rounded-lg group-hover:bg-purple-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Faculty</h3>
              <p class="text-sm text-gray-600">Meet our dedicated teachers and staff members</p>
            </div>
          </a>

          <!-- About Card -->
          <a href="/about" 
             class="group bg-white/90 backdrop-blur-sm rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 p-6 border border-yellow-200 hover:border-yellow-400">
            <div class="text-center">
              <div class="inline-flex items-center justify-center w-12 h-12 mb-4 bg-yellow-100 rounded-lg group-hover:bg-yellow-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">About Us</h3>
              <p class="text-sm text-gray-600">Learn about our vision, mission, and core values</p>
            </div>
          </a>
        </div>

        <!-- School Motto -->
        <div class="mt-12 md:mt-16 text-center" in:fly={{ y: 30, duration: 800, delay: 1200 }}>
          <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg p-6 md:p-8 max-w-3xl mx-auto border border-gray-200">
            <blockquote class="text-lg md:text-xl lg:text-2xl font-medium text-gray-800 italic leading-relaxed">
              "Excellence in Education, Excellence in Character"
            </blockquote>
            <cite class="block mt-4 text-sm md:text-base text-gray-600 font-semibold">
              — New Cabalan National High School
            </cite>
          </div>
        </div>
      {/if}
    </main>
  </div>
</div>
