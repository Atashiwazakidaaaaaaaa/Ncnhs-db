<script lang="ts">
  import { slide } from 'svelte/transition';
  import { onMount, onDestroy } from 'svelte';
  import { browser } from '$app/environment';
  import SimpleAdminLogin from './SimpleAdminLogin.svelte';

  // Props
  export let currentPage = 'FACULTY';
  export let showAdminButton = true;
  
  // Debug current page
  $: console.log('Navbar received currentPage:', currentPage);
  
  // State
  let isMobileMenuOpen = false;
  let showLoginModal = false;
  let isAdminLoggedIn = false;
  let showProfileDropdown = false;
  let cookieCheckInterval: NodeJS.Timeout | null = null;
  
  // Navigation items
  const navItems = [
    { name: 'ABOUT', href: '/about' },
    { name: 'ANNOUNCEMENTS', href: '/announcements' },
    { name: 'CALENDAR', href: '/calendar' },
    { name: 'FACULTY', href: '/faculty' }
  ];
  
  // Function to check admin login status from cookies
  function checkAdminStatus() {
    if (browser) {
      const authCookie = document.cookie
        .split('; ')
        .find(row => row.startsWith('auth='));
      const newAdminStatus = !!authCookie;
      
      if (newAdminStatus !== isAdminLoggedIn) {
        isAdminLoggedIn = newAdminStatus;
        console.log('Navbar - Admin status changed:', isAdminLoggedIn);
      }
    }
  }

  // Start checking for cookie changes periodically
  function startCookieCheck() {
    if (browser && !cookieCheckInterval) {
      cookieCheckInterval = setInterval(checkAdminStatus, 500);
    }
  }

  // Stop checking for cookie changes
  function stopCookieCheck() {
    if (cookieCheckInterval) {
      clearInterval(cookieCheckInterval);
      cookieCheckInterval = null;
    }
  }
  
  function toggleLoginModal() {
    showLoginModal = !showLoginModal;
  }

  function toggleProfileDropdown() {
    showProfileDropdown = !showProfileDropdown;
  }

  function logout() {
    if (browser) {
      // Clear the auth cookie
      document.cookie = 'auth=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
      isAdminLoggedIn = false;
      showProfileDropdown = false;
      
      // Refresh the page to update the UI
      window.location.reload();
    }
  }

  // Close dropdown when clicking outside
  function handleOutsideClick(event: MouseEvent) {
    const target = event.target as HTMLElement;
    if (showProfileDropdown && !target.closest('.profile-dropdown')) {
      showProfileDropdown = false;
    }
  }

  onMount(() => {
    checkAdminStatus();
    startCookieCheck();
    
    if (browser) {
      document.addEventListener('click', handleOutsideClick);
    }
  });

  onDestroy(() => {
    stopCookieCheck();
    
    if (browser) {
      document.removeEventListener('click', handleOutsideClick);
    }
  });
</script>

<SimpleAdminLogin bind:open={showLoginModal} />

<header class="sticky top-0 w-full bg-green-900/80 p-3 shadow-lg backdrop-blur-md border-b border-green-700/30 z-20">
  <div class="container mx-auto flex items-center justify-between">
    <!-- Left Side: Icon and School Name -->
    <div class="flex items-center gap-3 text-white">
      {#if showAdminButton}
        {#if isAdminLoggedIn}
          <!-- Profile Dropdown when logged in -->
          <div class="relative profile-dropdown">
            <button
              class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-blue-700 shadow-md hover:from-blue-600 hover:to-blue-800 transition-all duration-300 hover:scale-105 cursor-pointer"
              on:click={toggleProfileDropdown}
              aria-label="Profile Menu"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </button>
            
            <!-- Dropdown Menu -->
            {#if showProfileDropdown}
              <div class="absolute top-14 left-0 bg-white rounded-lg shadow-lg border border-gray-200 py-2 min-w-48 z-50" transition:slide={{ duration: 200 }}>
                <div class="px-4 py-2 border-b border-gray-100">
                  <p class="text-sm font-semibold text-gray-900">Admin Panel</p>
                  <p class="text-xs text-gray-500">Logged in as Administrator</p>
                </div>
                <div class="py-1">
                  <button
                    on:click={logout}
                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors flex items-center gap-2"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                  </button>
                </div>
              </div>
            {/if}
          </div>
        {:else}
          <!-- Login button when not logged in -->
          <button
            class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-green-500 to-green-700 shadow-md hover:from-green-600 hover:to-green-800 transition-all duration-300 hover:scale-105 cursor-pointer"
            on:click={toggleLoginModal}
            aria-label="Admin Login"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </button>
        {/if}
      {/if}
      
      <h1 class="hidden font-bold tracking-wide sm:block md:text-xl">
        <span class="text-yellow-300">NEW CABALAN</span> NATIONAL HIGH SCHOOL
      </h1>
      <h1 class="block font-bold sm:hidden">NCNHS</h1>
    </div>

    <!-- Mobile Menu Button (visible on small screens) -->
    <button 
      class="md:hidden rounded-md p-2 text-white hover:bg-green-800"
      on:click={() => isMobileMenuOpen = !isMobileMenuOpen}
      aria-label={isMobileMenuOpen ? "Close navigation menu" : "Open navigation menu"}
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d={isMobileMenuOpen ? "M6 18L18 6M6 6l12 12" : "M4 6h16M4 12h16M4 18h16"} />
      </svg>
    </button>

    <!-- Right Side: Navigation (Desktop) -->
    <nav class="hidden md:flex items-center gap-2 md:gap-3">
      {#each navItems as item}
        <a 
          href={item.href}
          class={`px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-300 hover:scale-105 ${
            currentPage === item.name 
              ? 'text-green-900 bg-gradient-to-r from-yellow-300 to-yellow-400 shadow-md hover:shadow-lg' 
              : 'text-white hover:bg-green-700/50'
          }`}
        >
          {item.name}
        </a>
      {/each}
    </nav>
  </div>
  
  <!-- Mobile Navigation Menu -->
  {#if isMobileMenuOpen}
    <div class="md:hidden bg-green-800/90 mt-2 p-2 rounded-lg" transition:slide={{ duration: 200 }}>
      <nav class="flex flex-col space-y-2">
        {#each navItems as item}
          <a 
            href={item.href}
            class={`px-3 py-2 rounded-lg text-sm font-semibold transition-colors text-left ${
              currentPage === item.name 
                ? 'text-green-900 bg-gradient-to-r from-yellow-300 to-yellow-400' 
                : 'text-white hover:bg-green-700/50'
            }`}
            on:click={() => isMobileMenuOpen = false}
          >
            {item.name}
          </a>
        {/each}
      </nav>
    </div>
  {/if}
</header>