<script lang="ts">
  import { slide } from 'svelte/transition';
  import { createEventDispatcher } from 'svelte';
  
  const dispatch = createEventDispatcher();
  
  // Props
  export let currentPage = 'FACULTY';
  export let showAdminButton = true;
  
  // State
  let isMobileMenuOpen = false;
  
  // Navigation items
  const navItems = [
    { name: 'ABOUT', href: '/about' },
    { name: 'ANNOUNCEMENTS', href: '/announcements' },
    { name: 'CALENDAR', href: '/calendar' },
    { name: 'FACULTY', href: '/faculty' }
  ];
  
  function handleAdminClick() {
    dispatch('adminClick');
  }
  
  function handleNavClick(item: string) {
    dispatch('navClick', item);
    isMobileMenuOpen = false;
  }
</script>

<header class="sticky top-0 w-full bg-green-900/80 p-3 shadow-lg backdrop-blur-md border-b border-green-700/30 z-20">
  <div class="container mx-auto flex items-center justify-between">
    <!-- Left Side: Icon and School Name -->
    <div class="flex items-center gap-3 text-white">
      {#if showAdminButton}
        <button 
          class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-green-500 to-green-700 shadow-md hover:from-green-600 hover:to-green-800 transition-all duration-300 hover:scale-105 cursor-pointer"
          on:click={handleAdminClick}
          aria-label="Admin Login"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        </button>
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
        <button 
          class={`px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-300 hover:scale-105 ${
            currentPage === item.name 
              ? 'text-green-900 bg-gradient-to-r from-yellow-300 to-yellow-400 shadow-md hover:shadow-lg' 
              : 'text-white hover:bg-green-700/50'
          }`}
          on:click={() => handleNavClick(item.name)}
        >
          {item.name}
        </button>
      {/each}
    </nav>
  </div>
  
  <!-- Mobile Navigation Menu -->
  {#if isMobileMenuOpen}
    <div class="md:hidden bg-green-800/90 mt-2 p-2 rounded-lg" transition:slide={{ duration: 200 }}>
      <nav class="flex flex-col space-y-2">
        {#each navItems as item}
          <button 
            class={`px-3 py-2 rounded-lg text-sm font-semibold transition-colors text-left ${
              currentPage === item.name 
                ? 'text-green-900 bg-gradient-to-r from-yellow-300 to-yellow-400' 
                : 'text-white hover:bg-green-700/50'
            }`}
            on:click={() => handleNavClick(item.name)}
          >
            {item.name}
          </button>
        {/each}
      </nav>
    </div>
  {/if}
</header>